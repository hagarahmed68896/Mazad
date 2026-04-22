<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $query = Notification::query();

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
                'value' => Notification::where('status', 'sent')->count(),
                'percentage' => 0.43,
                'color' => '#10B981',
                'icon' => 'sent',
                'sparkline' => [20, 40, 30, 50, 40, 60, 50]
            ],
            [
                'label' => 'الإشعارات المجدولة',
                'value' => Notification::where('status', 'scheduled')->count(),
                'percentage' => 0.43,
                'color' => '#1A73E8',
                'icon' => 'scheduled',
                'sparkline' => [30, 20, 50, 40, 60, 30, 40]
            ],
            [
                'label' => 'الإشعارات غير المرسلة',
                'value' => Notification::where('status', 'unsent')->count(),
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
        Notification::create($request->all());
        return redirect()->route('admin.notifications.index')->with('success', 'تمت إضافة الإشعار بنجاح');
    }

    public function edit(Notification $notification)
    {
        return view('admin.notifications.edit', compact('notification'));
    }

    public function update(Request $request, Notification $notification)
    {
        $notification->update($request->all());
        return redirect()->route('admin.notifications.index')->with('success', 'تم تحديث الإشعار بنجاح');
    }

    public function send(Notification $notification)
    {
        $notification->update(['status' => 'sent']);
        return back()->with('success', 'تم إرسال الإشعار بنجاح');
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
