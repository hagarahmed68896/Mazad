<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index(Request $request)
    {
        $faqs = Faq::when($request->search, function ($query) use ($request) {
                        $query->where(function($q) use ($request) {
                            $q->where('question', 'like', '%' . $request->search . '%')
                              ->orWhere('answer', 'like', '%' . $request->search . '%');
                        });
                    })
                    ->when($request->has('target_group'), function ($query) use ($request) {
                        $targets = (array) $request->target_group;
                        if (!in_array('all', $targets) && count($targets) > 0) {
                            $query->where(function($q) use ($targets) {
                                $q->where('target_group', 'like', '%"all"%');
                                foreach($targets as $target) {
                                    $q->orWhere('target_group', 'like', '%"' . $target . '"%');
                                    $q->orWhere('target_group', $target); // backward compatibility
                                }
                            });
                        }
                    })
                    ->when($request->status && $request->status !== 'all', function ($query) use ($request) {
                        $query->where('status', $request->status === 'active' ? 1 : 0);
                    })
                    ->when($request->sort, function ($query) use ($request) {
                        if ($request->sort === 'question') {
                            $query->orderBy('question', 'asc');
                        } elseif ($request->sort === 'oldest') {
                            $query->orderBy('created_at', 'asc');
                        } else {
                            $query->latest();
                        }
                    }, function($query) {
                        $query->latest();
                    })
                    ->paginate($request->per_page ?? 10);
                    
        $faqs->appends([
            'search' => $request->search,
            'target_group' => $request->target_group,
            'status' => $request->status,
            'sort' => $request->sort,
            'per_page' => $request->per_page
        ]);
                            
        return view('admin.faqs.index', compact('faqs'));
    }

    public function create()
    {
        return view('admin.faqs.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'target_group' => 'required|array',
            'status' => 'required|boolean',
        ]);

        $data = $request->all();
        if (count($request->target_group) === 4) {
            $data['target_group'] = json_encode(['all']);
        } else {
            $data['target_group'] = json_encode($request->target_group);
        }

        Faq::create($data);

        return redirect()->route('admin.faqs.index')->with('success', 'تمت إضافة السؤال بنجاح');
    }

    public function show(Faq $faq)
    {
        return view('admin.faqs.show', compact('faq'));
    }

    public function edit(Faq $faq)
    {
        return view('admin.faqs.edit', compact('faq'));
    }

    public function update(Request $request, Faq $faq)
    {
        $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'target_group' => 'required|array',
            'status' => 'required|boolean',
        ]);

        $data = $request->all();
        if (count($request->target_group) === 4) {
            $data['target_group'] = json_encode(['all']);
        } else {
            $data['target_group'] = json_encode($request->target_group);
        }

        $faq->update($data);

        return redirect()->route('admin.faqs.index')->with('success', 'تم التعديل بنجاح');
    }

    public function destroy(Faq $faq)
    {
        $faq->delete();
        return redirect()->route('admin.faqs.index')->with('success', 'تم الحذف بنجاح');
    }

    public function download(Request $request)
    {
        $query = Faq::when($request->search, function ($query) use ($request) {
                        $query->where(function($q) use ($request) {
                            $q->where('question', 'like', '%' . $request->search . '%')
                              ->orWhere('answer', 'like', '%' . $request->search . '%');
                        });
                    })
                    ->when($request->has('target_group'), function ($query) use ($request) {
                        $targets = (array) $request->target_group;
                        if (!in_array('all', $targets) && count($targets) > 0) {
                            $query->where(function($q) use ($targets) {
                                $q->where('target_group', 'like', '%"all"%');
                                foreach($targets as $target) {
                                    $q->orWhere('target_group', 'like', '%"' . $target . '"%');
                                    $q->orWhere('target_group', $target);
                                }
                            });
                        }
                    })
                    ->when($request->status && $request->status !== 'all', function ($query) use ($request) {
                        $query->where('status', $request->status === 'active' ? 1 : 0);
                    });

        $faqs = $query->get();

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=faqs_export_" . date('Y-m-d') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [
            __('المعرّف'),
            __('السؤال'),
            __('الإجابة'),
            __('الفئة المستهدفة'),
            __('الحالة'),
            __('تاريخ الإنشاء')
        ];

        $callback = function() use ($faqs, $columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $columns);

            $labelsMap = ['all' => 'الكل', 'individuals' => 'الأفراد', 'companies' => 'الشركات', 'delegates' => 'مندوبين', 'partners' => 'الشركاء'];

            foreach ($faqs as $faq) {
                $targets = is_array(json_decode($faq->target_group, true)) ? json_decode($faq->target_group, true) : [$faq->target_group];
                $targetLabels = implode('، ', array_map(fn($t) => $labelsMap[$t] ?? $t, $targets));

                $row = [
                    $faq->id,
                    $faq->question,
                    $faq->answer,
                    $targetLabels,
                    $faq->status ? 'نشط' : 'غير نشط',
                    $faq->created_at->format('Y/m/d')
                ];

                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'selected' => 'required|array',
            'selected.*' => 'exists:faqs,id',
        ]);

        Faq::whereIn('id', $request->selected)->delete();

        return redirect()->back()->with('success', 'تم الحذف متعدد السجلات بنجاح');
    }
}
