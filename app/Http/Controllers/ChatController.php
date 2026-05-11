<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Setting;
use App\Models\Conversation;
use App\Models\Message;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::whereNull('parent_id')->get();
        $settings = Setting::pluck('value', 'key')->toArray();
        
        $user = auth()->user();
        if (!$user) return redirect()->route('home');

        // Create sample conversations if none exist (for demo)
        if (Conversation::where('user_one_id', $user->id)->orWhere('user_two_id', $user->id)->count() == 0) {
            $otherUsers = \App\Models\User::where('id', '!=', $user->id)->limit(5)->get();
            foreach($otherUsers as $ou) {
                $conv = Conversation::create([
                    'user_one_id' => $user->id,
                    'user_two_id' => $ou->id,
                    'ad_id' => \App\Models\Ad::inRandomOrder()->first()->id,
                    'last_message_at' => now()
                ]);
                Message::create([
                    'conversation_id' => $conv->id,
                    'sender_id' => $ou->id,
                    'content' => 'هل تم الرد علي استفسارك بوضوح ؟',
                    'created_at' => now()->subMinutes(rand(1, 100))
                ]);
            }
        }

        $filter = $request->get('filter', 'all');
        $query = Conversation::where(function($q) use ($user) {
                $q->where('user_one_id', $user->id)
                  ->orWhere('user_two_id', $user->id);
            })
            ->with(['userOne', 'userTwo', 'messages', 'ad'])
            ->orderByRaw('CASE 
                WHEN (user_one_id = ? AND user_one_pinned = 1) OR (user_two_id = ? AND user_two_pinned = 1) THEN 1 
                ELSE 0 
            END DESC', [$user->id, $user->id])
            ->orderBy('last_message_at', 'desc');

        if ($filter == 'unread') {
            $query->whereHas('messages', function($q) use ($user) {
                $q->where('is_read', false)->where('sender_id', '!=', $user->id);
            });
        }

        $conversations = $query->get();
            
        $activeConversation = null;
        $isBlocked = false;
        if ($request->has('id')) {
            $activeConversation = Conversation::with(['messages.sender', 'ad', 'userOne', 'userTwo'])->findOrFail($request->id);
            
            // Block Check
            $otherUserId = $activeConversation->otherUser()->id;
            $isBlocked = \App\Models\BlockedUser::where('user_id', $user->id)->where('blocked_user_id', $otherUserId)->exists() ||
                         \App\Models\BlockedUser::where('user_id', $otherUserId)->where('blocked_user_id', $user->id)->exists();
            
            // Security check
            if ($activeConversation->user_one_id != $user->id && $activeConversation->user_two_id != $user->id) {
                abort(403);
            }

            // Mark messages as read
            Message::where('conversation_id', $activeConversation->id)
                ->where('sender_id', '!=', $user->id)
                ->update(['is_read' => true]);
        }
        return view('chats.index', compact('categories', 'settings', 'conversations', 'activeConversation', 'filter', 'isBlocked'));
    }

    public function sendMessage(Request $request, $id)
    {
        $conversation = Conversation::findOrFail($id);
        $user = auth()->user();
        
        // Check if blocked
        $otherUserId = $conversation->otherUser()->id;
        $blocked = \App\Models\BlockedUser::where(function($q) use ($user, $otherUserId) {
            $q->where('user_id', $user->id)->where('blocked_user_id', $otherUserId)
              ->orWhere('user_id', $otherUserId)->where('blocked_user_id', $user->id);
        })->exists();

        if ($blocked) {
            return response()->json(['success' => false, 'message' => 'لا يمكنك إرسال رسائل لهذا المستخدم']);
        }

        try {
            $request->validate([
                'content' => 'nullable|string|max:5000',
                'attachment' => 'nullable|file|max:10240',
            ]);

            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $filename = time() . '_' . $file->getClientOriginalName();
                $directory = public_path('uploads/chats');
                
                if (!file_exists($directory)) {
                    mkdir($directory, 0777, true);
                }
                
                $file->move($directory, $filename);
                $attachmentPath = 'uploads/chats/' . $filename;
            }

            // At least one must be present
            if (!$request->content && !$attachmentPath) {
                return back()->with('error', 'لا يمكن إرسال رسالة فارغة');
            }

            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => auth()->id(),
                'content' => $request->content ?? '',
                'attachment' => $attachmentPath,
                'type' => $attachmentPath ? 'file' : 'text'
            ]);

            // Update conversation timestamp
            $conversation->update(['last_message_at' => now()]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'sender_name' => auth()->user()->name,
                    'sender_avatar' => auth()->user()->avatar ? asset(auth()->user()->avatar) : 'https://ui-avatars.com/api/?name='.urlencode(auth()->user()->name).'&background=1A73E8&color=fff',
                    'time' => $message->created_at->format('H:i')
                ]);
            }

            return back()->with('success', 'تم إرسال الرسالة');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Chat Send Error: ' . $e->getMessage());
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()]);
            }
            return back()->with('error', 'فشل الإرسال: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $conversation = Conversation::findOrFail($id);
        
        // Security check
        if ($conversation->user_one_id != auth()->id() && $conversation->user_two_id != auth()->id()) {
            abort(403);
        }

        $conversation->delete();

        return redirect()->route('chats.index')->with('success', 'تم حذف المحادثة بنجاح');
    }

    public function togglePin($id)
    {
        $conversation = Conversation::findOrFail($id);
        $user = auth()->user();
        
        if ($conversation->user_one_id == $user->id) {
            $conversation->update(['user_one_pinned' => !$conversation->user_one_pinned]);
            $isPinned = $conversation->user_one_pinned;
        } else {
            $conversation->update(['user_two_pinned' => !$conversation->user_two_pinned]);
            $isPinned = $conversation->user_two_pinned;
        }

        return response()->json(['success' => true, 'is_pinned' => $isPinned]);
    }

    public function toggleMute($id)
    {
        $conversation = Conversation::findOrFail($id);
        $user = auth()->user();
        
        if ($conversation->user_one_id == $user->id) {
            $conversation->update(['user_one_muted' => !$conversation->user_one_muted]);
            $isMuted = $conversation->user_one_muted;
        } else {
            $conversation->update(['user_two_muted' => !$conversation->user_two_muted]);
            $isMuted = $conversation->user_two_muted;
        }

        return response()->json(['success' => true, 'is_muted' => $isMuted]);
    }

    public function toggleBlock($userId)
    {
        $user = auth()->user();
        $blocked = \App\Models\BlockedUser::where('user_id', $user->id)->where('blocked_user_id', $userId)->first();

        if ($blocked) {
            $blocked->delete();
            $isBlocked = false;
        } else {
            \App\Models\BlockedUser::create([
                'user_id' => $user->id,
                'blocked_user_id' => $userId
            ]);
            $isBlocked = true;
        }

        return response()->json(['success' => true, 'is_blocked' => $isBlocked]);
    }
}
