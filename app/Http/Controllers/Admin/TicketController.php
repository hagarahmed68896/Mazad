<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $tickets = Ticket::with('user')
            ->when($request->search, function ($query) use ($request) {
                $query->where(function($q) use ($request) {
                    $q->where('ticket_number', 'like', '%' . $request->search . '%')
                      ->orWhere('reported_about', 'like', '%' . $request->search . '%')
                      ->orWhere('description', 'like', '%' . $request->search . '%')
                      ->orWhereHas('user', function($u) use ($request) {
                          $u->where('name', 'like', '%' . $request->search . '%')
                            ->orWhere('phone', 'like', '%' . $request->search . '%');
                      });
                });
            })
            ->when($request->status && $request->status !== 'all', function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->type && $request->type !== 'all', function ($query) use ($request) {
                $query->where('type', $request->type);
            })
            ->when($request->account_type && $request->account_type !== 'all', function ($query) use ($request) {
                $query->whereHas('user', function($q) use ($request) {
                    $q->where('account_type', $request->account_type);
                });
            })
            ->when($request->sort, function ($query) use ($request) {
                if ($request->sort === 'oldest') {
                    $query->orderBy('created_at', 'asc');
                } elseif ($request->sort === 'name') {
                    $query->whereHas('user', function($q) use ($request) {
                        $q->orderBy('name', 'asc');
                    });
                } else {
                    $query->latest();
                }
            }, function($query) {
                $query->latest();
            })
            ->paginate($request->per_page ?? 10);

        $tickets->appends($request->all());

        return view('admin.tickets.index', compact('tickets'));
    }

    public function show(Ticket $ticket)
    {
        $ticket->load(['user', 'logs']);
        return view('admin.tickets.show', compact('ticket'));
    }

    public function update(Request $request, Ticket $ticket)
    {
        if ($request->has('action')) {
            $ticket->logs()->create([
                'action' => $request->action,
                'note' => $request->note
            ]);
            
            $updateData = ['status' => 'resolved'];
            if ($request->action === 'reply') {
                $updateData['notes'] = $request->note;
            }
            $ticket->update($updateData);

            if ($ticket->user_id) {
                $title = $ticket->type === 'inquiry' ? 'تم الرد على استفسارك' : 'تمت معالجة بلاغك';
                $content = $ticket->type === 'inquiry' 
                    ? 'تم الرد على استفسارك رقم ' . $ticket->ticket_number . ' بنجاح. يمكنك قراءة الرد في صفحة استفساراتي.'
                    : 'تم مراجعة ومعالجة البلاغ رقم ' . $ticket->ticket_number . ' بنجاح واتخاذ الإجراءات اللازمة.';

                \App\Models\Notification::create([
                    'user_id' => $ticket->user_id,
                    'title' => $title,
                    'content' => $content,
                    'type' => 'alert',
                    'status' => 'sent',
                ]);
            }

            return redirect()->back()->with('success', 'تم اتخاذ الإجراء بنجاح وتم حل التذكرة.');
        }

        if ($request->has('status')) {
            $oldStatus = $ticket->status;
            $ticket->update(['status' => $request->status]);

            if ($request->status === 'resolved' && $oldStatus !== 'resolved' && $ticket->user_id) {
                $title = $ticket->type === 'inquiry' ? 'تم الرد على استفسارك' : 'تمت معالجة بلاغك';
                $content = $ticket->type === 'inquiry' 
                    ? 'تم الرد على استفسارك رقم ' . $ticket->ticket_number . ' بنجاح. يمكنك قراءة الرد في صفحة استفساراتي.'
                    : 'تم مراجعة ومعالجة البلاغ رقم ' . $ticket->ticket_number . ' بنجاح واتخاذ الإجراءات اللازمة.';

                \App\Models\Notification::create([
                    'user_id' => $ticket->user_id,
                    'title' => $title,
                    'content' => $content,
                    'type' => 'alert',
                    'status' => 'sent',
                ]);
            }

            return redirect()->back()->with('success', 'تم تحديث حالة التذكرة بنجاح');
        }

        $oldStatus = $ticket->status;
        $ticket->update($request->only('status', 'notes'));

        if ($ticket->status === 'resolved' && $oldStatus !== 'resolved' && $ticket->user_id) {
            $title = $ticket->type === 'inquiry' ? 'تم الرد على استفسارك' : 'تمت معالجة بلاغك';
            $content = $ticket->type === 'inquiry' 
                ? 'تم الرد على استفسارك رقم ' . $ticket->ticket_number . ' بنجاح. يمكنك قراءة الرد في صفحة استفساراتي.'
                : 'تم مراجعة ومعالجة البلاغ رقم ' . $ticket->ticket_number . ' بنجاح واتخاذ الإجراءات اللازمة.';

            \App\Models\Notification::create([
                'user_id' => $ticket->user_id,
                'title' => $title,
                'content' => $content,
                'type' => 'alert',
                'status' => 'sent',
            ]);
        }

        return redirect()->back()->with('success', 'تم التحديث بنجاح');
    }

    public function destroy(Ticket $ticket)
    {
        $ticket->delete();
        return redirect()->route('admin.tickets.index')->with('success', 'تم حذف التذكرة بنجاح');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'selected' => 'required|array',
            'selected.*' => 'exists:tickets,id',
        ]);

        Ticket::whereIn('id', $request->selected)->delete();

        return redirect()->back()->with('success', 'تم الحذف الجماعي بنجاح');
    }

    public function download(Request $request)
    {
        $tickets = Ticket::with('user')
            ->when($request->search, function ($query) use ($request) {
                $query->where(function($q) use ($request) {
                    $q->where('ticket_number', 'like', '%' . $request->search . '%')
                      ->orWhereHas('user', function($u) use ($request) {
                          $u->where('name', 'like', '%' . $request->search . '%');
                      });
                });
            })
            ->when($request->status && $request->status !== 'all', function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->get();

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=tickets_export_" . date('Y-m-d') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [
            __('رقم التذكرة'),
            __('النوع'),
            __('اسم المستخدم'),
            __('رقم الجوال'),
            __('المُبلغ عنه'),
            __('الوصف'),
            __('الحالة'),
            __('التاريخ')
        ];

        $callback = function() use ($tickets, $columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $columns);

            $statusMap = ['resolved' => 'تم الحل', 'under_review' => 'قيد المراجعة', 'rejected' => 'مرفوضة'];
            $typeMap = ['report' => 'بلاغ', 'inquiry' => 'استفسار'];

            foreach ($tickets as $ticket) {
                $row = [
                    $ticket->ticket_number,
                    $typeMap[$ticket->type] ?? $ticket->type,
                    $ticket->user?->name,
                    $ticket->phone ?? $ticket->user?->phone,
                    $ticket->reported_about,
                    $ticket->description,
                    $statusMap[$ticket->status] ?? $ticket->status,
                    $ticket->created_at->format('Y/m/d')
                ];
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
