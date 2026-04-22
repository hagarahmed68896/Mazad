<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Policy;
use Illuminate\Http\Request;

class PolicyController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->query('type', 'terms');
        
        $policies = Policy::where('type', $type)
                            ->when($request->search, function ($query) use ($request) {
                                $query->where(function($q) use ($request) {
                                    $q->where('title', 'like', '%' . $request->search . '%')
                                      ->orWhere('content', 'like', '%' . $request->search . '%');
                                });
                            })
                            ->when($request->has('target_audience'), function ($query) use ($request) {
                                $targets = (array) $request->target_audience;
                                if (!in_array('all', $targets) && count($targets) > 0) {
                                    $query->where(function($q) use ($targets) {
                                        $q->where('target_audience', 'like', '%"all"%');
                                        foreach($targets as $target) {
                                            $q->orWhere('target_audience', 'like', '%"' . $target . '"%');
                                            $q->orWhere('target_audience', $target); // backward compatibility
                                        }
                                    });
                                }
                            })
                            ->when($request->status && $request->status !== 'all', function ($query) use ($request) {
                                $query->where('is_active', $request->status === 'active' ? 1 : 0);
                            })
                            ->when($request->sort, function ($query) use ($request) {
                                if ($request->sort === 'title') {
                                    $query->orderBy('title', 'asc');
                                } elseif ($request->sort === 'oldest') {
                                    $query->orderBy('created_at', 'asc');
                                } else {
                                    // 'newest' or 'all' handles via latest()
                                    $query->latest();
                                }
                            }, function($query) {
                                $query->latest();
                            })
                            ->paginate(10);
                            
        $policies->appends([
            'type' => $type, 
            'search' => $request->search,
            'target_audience' => $request->target_audience,
            'status' => $request->status,
            'sort' => $request->sort
        ]);
                            
        return view('admin.policies.index', compact('policies', 'type'));
    }

    public function create(Request $request)
    {
        $type = $request->query('type', 'terms');
        return view('admin.policies.create', compact('type'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:terms,privacy',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'target_audience' => 'required|array',
            'is_active' => 'required|boolean',
        ]);

        $data = $request->all();
        // If all 4 options are selected, we could either save 'all' or keep the array.
        // Easiest is to save as JSON string regardless.
        if (count($request->target_audience) === 4) {
            $data['target_audience'] = json_encode(['all']);
        } else {
            $data['target_audience'] = json_encode($request->target_audience);
        }

        Policy::create($data);

        return redirect()->route('admin.policies.index', ['type' => $request->type])
                         ->with('success', 'تمت الإضافة بنجاح');
    }

    public function edit(Policy $policy)
    {
        return view('admin.policies.edit', compact('policy'));
    }

    public function update(Request $request, Policy $policy)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'target_audience' => 'required|array',
            'is_active' => 'required|boolean',
        ]);

        $data = $request->all();
        if (count($request->target_audience) === 4) {
            $data['target_audience'] = json_encode(['all']);
        } else {
            $data['target_audience'] = json_encode($request->target_audience);
        }

        $policy->update($data);

        return redirect()->route('admin.policies.index', ['type' => $policy->type])
                         ->with('success', 'تم التعديل بنجاح');
    }

    public function destroy(Policy $policy)
    {
        $type = $policy->type;
        $policy->delete();
        
        return redirect()->route('admin.policies.index', ['type' => $type])
                         ->with('success', 'تم الحذف بنجاح');
    }

    public function show(Policy $policy)
    {
        return view('admin.policies.show', compact('policy'));
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'selected' => 'required|array',
            'selected.*' => 'exists:policies,id',
        ]);

        Policy::whereIn('id', $request->selected)->delete();

        return redirect()->back()->with('success', 'تم الحذف متعدد السجلات بنجاح');
    }
}
