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
        $notifications = $request->user()->notifications()->orderBy('created_at', 'desc')->paginate(20);
        return $this->apiResponse(true, 'قائمة التنبيهات', $notifications);
    }

    public function markAsRead(Request $request, $id)
    {
        $notification = $request->user()->notifications()->find($id);
        if (!$notification) {
            return $this->apiResponse(false, 'التنبيه غير موجود', null, 404);
        }
        $notification->update(['read_at' => now()]);
        return $this->apiResponse(true, 'تم تحديد التنبيه كمقروء');
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);
        return $this->apiResponse(true, 'تم تحديد جميع التنبيهات كمقروءة');
    }

    public function destroy(Request $request, $id)
    {
        $notification = $request->user()->notifications()->find($id);
        if (!$notification) {
            return $this->apiResponse(false, 'التنبيه غير موجود', null, 404);
        }
        $notification->delete();
        return $this->apiResponse(true, 'تم حذف التنبيه بنجاح');
    }
}
