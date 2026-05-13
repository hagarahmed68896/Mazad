<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\BlockedUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $conversations = Conversation::where('user_one_id', $user->id)
            ->orWhere('user_two_id', $user->id)
            ->with(['userOne', 'userTwo', 'messages' => function($q) {
                $q->latest()->limit(1);
            }, 'ad'])
            ->orderBy('last_message_at', 'desc')
            ->get()
            ->map(function($conv) use ($user) {
                $otherUser = $conv->user_one_id == $user->id ? $conv->userTwo : $conv->userOne;
                return [
                    'id' => $conv->id,
                    'other_user' => $otherUser,
                    'last_message' => $conv->messages->first(),
                    'ad' => $conv->ad,
                    'unread_count' => $conv->messages()->where('is_read', false)->where('sender_id', '!=', $user->id)->count(),
                    'is_pinned' => ($conv->user_one_id == $user->id ? $conv->user_one_pinned : $conv->user_two_pinned) == 1,
                    'is_muted' => ($conv->user_one_id == $user->id ? $conv->user_one_muted : $conv->user_two_muted) == 1,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $conversations
        ]);
    }

    public function show($id)
    {
        $user = Auth::user();
        $conversation = Conversation::with(['userOne', 'userTwo', 'messages.sender', 'ad'])->findOrFail($id);

        if ($conversation->user_one_id != $user->id && $conversation->user_two_id != $user->id) {
            return response()->json(['success' => false, 'message' => 'غير مصرح لك بالدخول'], 403);
        }

        // Mark as read
        Message::where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', $user->id)
            ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'data' => $conversation
        ]);
    }

    public function sendMessage(Request $request, $id)
    {
        $user = Auth::user();
        $conversation = Conversation::findOrFail($id);
        
        $otherUser = $conversation->user_one_id == $user->id ? $conversation->userTwo : $conversation->userOne;
        
        // Check block
        $isBlocked = BlockedUser::where(function($q) use ($user, $otherUser) {
            $q->where('user_id', $user->id)->where('blocked_user_id', $otherUser->id)
              ->orWhere('user_id', $otherUser->id)->where('blocked_user_id', $user->id);
        })->exists();

        if ($isBlocked) {
            return response()->json(['success' => false, 'message' => 'لا يمكنك إرسال رسائل لهذا المستخدم'], 403);
        }

        $validator = Validator::make($request->all(), [
            'content' => 'nullable|string',
            'attachment' => 'nullable|file|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/chats'), $filename);
            $attachmentPath = 'uploads/chats/' . $filename;
        }

        if (!$request->content && !$attachmentPath) {
            return response()->json(['success' => false, 'message' => 'لا يمكن إرسال رسالة فارغة'], 422);
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'content' => $request->content ?? '',
            'attachment' => $attachmentPath,
            'type' => $attachmentPath ? 'file' : 'text'
        ]);

        $conversation->update(['last_message_at' => now()]);

        return response()->json([
            'success' => true,
            'data' => $message
        ]);
    }

    public function toggleBlock($userId)
    {
        $user = Auth::user();
        $blocked = BlockedUser::where('user_id', $user->id)->where('blocked_user_id', $userId)->first();

        if ($blocked) {
            $blocked->delete();
            $isBlocked = false;
        } else {
            BlockedUser::create([
                'user_id' => $user->id,
                'blocked_user_id' => $userId
            ]);
            $isBlocked = true;
        }

        return response()->json(['success' => true, 'is_blocked' => $isBlocked]);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $conversation = Conversation::findOrFail($id);

        if ($conversation->user_one_id != $user->id && $conversation->user_two_id != $user->id) {
            return response()->json(['success' => false, 'message' => 'غير مصرح لك بالحذف'], 403);
        }

        $conversation->delete();

        return response()->json(['success' => true, 'message' => 'تم حذف المحادثة']);
    }
}
