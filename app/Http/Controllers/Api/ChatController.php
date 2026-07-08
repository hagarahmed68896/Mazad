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
    public function index(Request $request)
    {
        $user = Auth::user();
        $search = $request->get('search') ?: $request->get('q');

        $query = Conversation::where(function($q) use ($user) {
            $q->where('user_one_id', $user->id)
              ->orWhere('user_two_id', $user->id);
        });

        if (!empty($search)) {
            $query->where(function($q) use ($search, $user) {
                $q->whereHas('userOne', function($uq) use ($search, $user) {
                    $uq->where('id', '!=', $user->id)
                       ->where('name', 'like', '%' . $search . '%');
                })
                ->orWhereHas('userTwo', function($uq) use ($search, $user) {
                    $uq->where('id', '!=', $user->id)
                       ->where('name', 'like', '%' . $search . '%');
                })
                ->orWhereHas('messages', function($mq) use ($search) {
                    $mq->where('content', 'like', '%' . $search . '%');
                })
                ->orWhereHas('ad', function($aq) use ($search) {
                    $aq->where('title', 'like', '%' . $search . '%');
                });
            });
        }

        $conversations = $query->with(['userOne', 'userTwo', 'messages' => function($q) {
                $q->latest()->limit(1);
            }, 'ad'])
            ->orderBy('last_message_at', 'desc')
            ->get()
            ->map(function($conv) use ($user) {
                $otherUser = $conv->user_one_id == $user->id ? $conv->userTwo : $conv->userOne;
                
                $slimOtherUser = $otherUser ? [
                    'id' => $otherUser->id,
                    'name' => $otherUser->name,
                    'avatar' => $otherUser->avatar ? asset($otherUser->avatar) : null,
                    'account_type' => $otherUser->account_type,
                    'company_name' => $otherUser->company_name,
                ] : null;

                $lastMsg = $conv->messages->first();
                $slimLastMessage = $lastMsg ? [
                    'id' => $lastMsg->id,
                    'content' => $lastMsg->content,
                    'is_read' => (int) $lastMsg->is_read,
                    'type' => $lastMsg->type,
                    'attachment' => $lastMsg->attachment ? asset($lastMsg->attachment) : null,
                    'created_at' => $lastMsg->created_at,
                ] : null;

                $slimAd = $conv->ad ? [
                    'id' => $conv->ad->id,
                    'title' => $conv->ad->title,
                    'price' => (float) $conv->ad->price,
                    'image' => $conv->ad->image ? asset($conv->ad->image) : null,
                ] : null;

                $iBlockedThem = $otherUser ? \App\Models\BlockedUser::where('user_id', $user->id)->where('blocked_user_id', $otherUser->id)->exists() : false;
                $theyBlockedMe = $otherUser ? \App\Models\BlockedUser::where('user_id', $otherUser->id)->where('blocked_user_id', $user->id)->exists() : false;

                return [
                    'id' => $conv->id,
                    'other_user' => $slimOtherUser,
                    'last_message' => $slimLastMessage,
                    'ad' => $slimAd,
                    'unread_count' => $conv->messages()->where('is_read', false)->where('sender_id', '!=', $user->id)->count(),
                    'is_pinned' => ($conv->user_one_id == $user->id ? $conv->user_one_pinned : $conv->user_two_pinned) == 1,
                    'is_muted' => ($conv->user_one_id == $user->id ? $conv->user_one_muted : $conv->user_two_muted) == 1,
                    'is_blocked' => $iBlockedThem || $theyBlockedMe,
                    'i_blocked_them' => $iBlockedThem,
                    'they_blocked_me' => $theyBlockedMe,
                ];
            });

        return response()->json([
            'success' => true,
            'unread_messages_count' => $conversations->sum('unread_count'),
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

        $otherUser = $conversation->user_one_id == $user->id ? $conversation->userTwo : $conversation->userOne;
        
        $slimOtherUser = $otherUser ? [
            'id' => $otherUser->id,
            'name' => $otherUser->name,
            'avatar' => $otherUser->avatar ? asset($otherUser->avatar) : null,
            'account_type' => $otherUser->account_type,
            'company_name' => $otherUser->company_name,
        ] : null;

        $slimAd = $conversation->ad ? [
            'id' => $conversation->ad->id,
            'title' => $conversation->ad->title,
            'price' => (float) $conversation->ad->price,
            'image' => $conversation->ad->image ? asset($conversation->ad->image) : null,
        ] : null;

        $slimMessages = $conversation->messages->map(function($msg) {
            return [
                'id' => $msg->id,
                'sender_id' => $msg->sender_id,
                'sender_name' => $msg->sender->name ?? '',
                'sender_avatar' => ($msg->sender && $msg->sender->avatar) ? asset($msg->sender->avatar) : null,
                'content' => $msg->content,
                'attachment' => $msg->attachment ? asset($msg->attachment) : null,
                'type' => $msg->type,
                'is_read' => (int) $msg->is_read,
                'created_at' => $msg->created_at,
            ];
        });

        $iBlockedThem = $otherUser ? \App\Models\BlockedUser::where('user_id', $user->id)->where('blocked_user_id', $otherUser->id)->exists() : false;
        $theyBlockedMe = $otherUser ? \App\Models\BlockedUser::where('user_id', $otherUser->id)->where('blocked_user_id', $user->id)->exists() : false;

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $conversation->id,
                'other_user' => $slimOtherUser,
                'ad' => $slimAd,
                'messages' => $slimMessages,
                'is_blocked' => $iBlockedThem || $theyBlockedMe,
                'i_blocked_them' => $iBlockedThem,
                'they_blocked_me' => $theyBlockedMe,
            ]
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

        if ($request->content && \App\Models\Message::hasContactInfo($request->content)) {
            return response()->json([
                'success' => false,
                'message' => 'عذراً، غير مسموح بمشاركة وسائل التواصل داخل المحادثات للحفاظ على أمانك.'
            ], 422);
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

        // Load the sender relation to make sure it is populated in the broadcast payload
        $message->load('sender');

        try {
            broadcast(new \App\Events\MessageSent($message))->toOthers();
        } catch (\Exception $e) {
            // Log or ignore broadcasting errors to prevent blocking the message save response
        }

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

    public function startConversation(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'ad_id' => 'nullable|exists:ads,id',
            'service_id' => 'nullable|exists:services,id',
            'user_id' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $recipientId = null;
        $adId = null;
        $initialMessage = null;

        if ($request->filled('ad_id')) {
            $ad = \App\Models\Ad::findOrFail($request->ad_id);
            $recipientId = $ad->user_id;
            $adId = $ad->id;
            $initialMessage = "مرحباً، أود الاستفسار عن إعلانك: " . $ad->title;
        } elseif ($request->filled('service_id')) {
            $service = \App\Models\Service::findOrFail($request->service_id);
            $recipientId = $service->user_id;
            $initialMessage = "مرحباً، أود طلب خدمتك: " . $service->title;
        } elseif ($request->filled('user_id')) {
            $recipientId = $request->user_id;
        }

        if (!$recipientId) {
            return response()->json([
                'success' => false,
                'message' => 'يجب تحديد إعلان، خدمة، أو مستخدم لبدء المحادثة.'
            ], 400);
        }

        if ($recipientId == $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكنك بدء محادثة مع نفسك.'
            ], 400);
        }

        // Check block status
        $isBlocked = BlockedUser::where(function($q) use ($user, $recipientId) {
            $q->where('user_id', $user->id)->where('blocked_user_id', $recipientId)
              ->orWhere('user_id', $recipientId)->where('blocked_user_id', $user->id);
        })->exists();

        if ($isBlocked) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكنك بدء محادثة مع هذا المستخدم بسبب الحظر.'
            ], 403);
        }

        // Retrieve or create conversation
        $conversationQuery = Conversation::where(function($q) use ($user, $recipientId) {
            $q->where(function($sq) use ($user, $recipientId) {
                $sq->where('user_one_id', $user->id)->where('user_two_id', $recipientId);
            })->orWhere(function($sq) use ($user, $recipientId) {
                $sq->where('user_one_id', $recipientId)->where('user_two_id', $user->id);
            });
        });

        if ($adId) {
            $conversationQuery->where('ad_id', $adId);
        } else {
            $conversationQuery->orderByRaw('CASE WHEN ad_id IS NULL THEN 0 ELSE 1 END');
        }

        $conversation = $conversationQuery->orderBy('last_message_at', 'desc')->first();

        if (!$conversation) {
            $conversation = Conversation::create([
                'user_one_id' => $user->id,
                'user_two_id' => $recipientId,
                'ad_id' => $adId,
                'last_message_at' => now(),
            ]);

            // Send initial automated message if it's a new conversation
            if ($initialMessage) {
                Message::create([
                    'conversation_id' => $conversation->id,
                    'sender_id' => $user->id,
                    'content' => $initialMessage,
                    'type' => 'text'
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'تم بدء المحادثة بنجاح',
            'data' => [
                'conversation_id' => $conversation->id,
                'recipient_id' => $recipientId,
            ]
        ]);
    }
}
