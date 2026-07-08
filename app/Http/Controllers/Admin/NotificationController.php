<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use App\Jobs\SendBulkNotificationsJob;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $query = Notification::query()->whereNull('user_id');

        // Filters
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('content', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        // Sorting
        $sort = $request->get('sort', 'newest');
        if ($sort == 'name') {
            $query->orderBy('title', 'asc');
        } elseif ($sort == 'oldest') {
            $query->orderBy('created_at', 'asc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Stats Cards Data
        $cardStats = [
            [
                'label' => 'الإشعارات المرسلة',
                'value' => Notification::whereNull('user_id')->where('status', 'sent')->count(),
                'percentage' => 0.43,
                'color' => '#10B981',
                'icon' => 'sent',
                'sparkline' => [20, 40, 30, 50, 40, 60, 50]
            ],
            [
                'label' => 'الإشعارات المجدولة',
                'value' => Notification::whereNull('user_id')->where('status', 'scheduled')->count(),
                'percentage' => 0.43,
                'color' => '#1A73E8',
                'icon' => 'scheduled',
                'sparkline' => [30, 20, 50, 40, 60, 30, 40]
            ],
            [
                'label' => 'الإشعارات غير المرسلة',
                'value' => Notification::whereNull('user_id')->where('status', 'unsent')->count(),
                'percentage' => 0.43,
                'color' => '#EF4444',
                'icon' => 'unsent',
                'sparkline' => [50, 30, 20, 40, 30, 20, 30]
            ],
        ];

        $notifications = $query->latest()->paginate(10);

        return view('admin.notifications.index', compact('notifications', 'cardStats'));
    }

    public function create()
    {
        return view('admin.notifications.create');
    }

    public function store(Request $request)
    {
        $notification = Notification::create(array_merge($request->all(), ['user_id' => null]));
        
        if ($notification->status === 'sent') {
            $this->sendToAllUsers($notification);
        }
        
        return redirect()->route('admin.notifications.index')->with('success', 'تمت إضافة الإشعار بنجاح');
    }

    public function edit(Notification $notification)
    {
        return view('admin.notifications.edit', compact('notification'));
    }

    public function update(Request $request, Notification $notification)
    {
        $oldStatus = $notification->status;
        $notification->update($request->all());
        
        if ($oldStatus !== 'sent' && $notification->status === 'sent') {
            if (is_null($notification->user_id)) {
                $this->sendToAllUsers($notification);
            }
        }
        
        return redirect()->route('admin.notifications.index')->with('success', 'تم تحديث الإشعار بنجاح');
    }

    public function send(Notification $notification)
    {
        if ($notification->status !== 'sent') {
            $notification->update(['status' => 'sent']);
            
            if (is_null($notification->user_id)) {
                $this->sendToAllUsers($notification);
            }
        }
        return back()->with('success', 'تم إرسال الإشعار بنجاح');
    }

    private function sendToAllUsers(Notification $notification)
    {
        User::where('email', '!=', 'admin@mazad.com')->chunk(100, function ($users) use ($notification) {
            foreach ($users as $user) {
                try {
                    Notification::create([
                        'type' => $notification->type,
                        'title' => $notification->title,
                        'content' => $notification->content,
                        'user_id' => $user->id,
                        'status' => 'sent',
                    ]);
                } catch (\Exception $e) {
                    \Log::error("Failed to send notification to user {$user->id}: " . $e->getMessage());
                }
            }
        });
    }

    public function destroy(Notification $notification)
    {
        $notification->delete();
        return back()->with('success', 'تم حذف الإشعار بنجاح');
    }

    public function markRead(Notification $notification)
    {
        $notification->update(['read_at' => now()]);
        return response()->json(['success' => true]);
    }

    public function markAllRead()
    {
        Notification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
            
        return response()->json(['success' => true]);
    }

    public function bulkDestroy(Request $request)
    {
        if ($request->filled('selected')) {
            Notification::whereIn('id', $request->selected)->delete();
            return back()->with('success', 'تم حذف الإشعارات المحددة بنجاح');
        }
        return back()->with('error', 'يجب تحديد إشعارات أولاً');
    }
}
