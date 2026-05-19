<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;

class NotificationController extends Controller
{
    protected function apiResponse($success, $message, $data = null, $code = 200)
    {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    public function index(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return $this->apiResponse(false, 'المستخدم غير مصرح له', null, 401);
            }
            $notifications = $user->notifications()->orderBy('created_at', 'desc')->paginate(20);
            return $this->apiResponse(true, 'قائمة التنبيهات', $notifications);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('API Notifications Index Error: ' . $e->getMessage());
            return $this->apiResponse(false, 'حدث خطأ أثناء جلب التنبيهات: ' . $e->getMessage(), null, 500);
        }
    }

    public function markAsRead(Request $request, $id)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return $this->apiResponse(false, 'المستخدم غير مصرح له', null, 401);
            }
            $notification = $user->notifications()->find($id);
            if (!$notification) {
                return $this->apiResponse(false, 'التنبيه غير موجود', null, 404);
            }
            $notification->update(['read_at' => now()]);
            return $this->apiResponse(true, 'تم تحديد التنبيه كمقروء');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('API Notification MarkRead Error: ' . $e->getMessage());
            return $this->apiResponse(false, 'حدث خطأ أثناء تحديث التنبيه: ' . $e->getMessage(), null, 500);
        }
    }

    public function markAllAsRead(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return $this->apiResponse(false, 'المستخدم غير مصرح له', null, 401);
            }
            $user->unreadNotifications()->update(['read_at' => now()]);
            return $this->apiResponse(true, 'تم تحديد جميع التنبيهات كمقروءة');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('API Notification MarkAllRead Error: ' . $e->getMessage());
            return $this->apiResponse(false, 'حدث خطأ أثناء تحديث التنبيهات: ' . $e->getMessage(), null, 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return $this->apiResponse(false, 'المستخدم غير مصرح له', null, 401);
            }
            $notification = $user->notifications()->find($id);
            if (!$notification) {
                return $this->apiResponse(false, 'التنبيه غير موجود', null, 404);
            }
            $notification->delete();
            return $this->apiResponse(true, 'تم حذف التنبيه بنجاح');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('API Notification Delete Error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->apiResponse(false, 'حدث خطأ أثناء حذف التنبيه: ' . $e->getMessage(), null, 500);
        }
    }
}
