<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Commission;
use App\Models\Category;

class CommissionController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status', 'all');
        $type = $request->get('type', 'all');

        $query = Commission::with('category');

        if ($search) {
            $query->where(function($q) use ($search) {
                // Map arabic search terms to english types
                $typeMap = [
                    'مزاد' => 'auctions',
                    'شراء' => 'direct_purchase',
                    'فوري' => 'direct_purchase',
                    'مناقص' => 'tenders',
                    'خدم' => 'services'
                ];
                
                $matchedType = null;
                foreach($typeMap as $ar => $en) {
                    if (str_contains($search, $ar)) {
                        $matchedType = $en;
                        break;
                    }
                }

                if ($matchedType) {
                    $q->where('type', $matchedType);
                } else {
                    $q->where('type', 'like', "%{$search}%");
                }
                
                $q->orWhereHas('category', function($cq) use ($search) {
                    $cq->where('name', 'like', "%{$search}%");
                });
            });
        }

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($type !== 'all') {
            $query->where('type', $type);
        }

        $commissions = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('admin.commissions.index', compact('commissions', 'search', 'status', 'type'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('admin.commissions.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:auctions,direct_purchase,tenders,services',
            'category_id' => 'nullable|exists:categories,id',
            'value' => 'required|numeric|min:0',
            'is_percentage' => 'required|boolean',
            'status' => 'required|in:active,inactive',
        ]);

        Commission::create($validated);

        return redirect()->route('admin.payments.commissions.index')->with('success', 'تم إضافة العمولة بنجاح');
    }

    public function edit(Commission $commission)
    {
        $categories = Category::all();
        return view('admin.commissions.edit', compact('commission', 'categories'));
    }

    public function update(Request $request, Commission $commission)
    {
        $validated = $request->validate([
            'type' => 'required|in:auctions,direct_purchase,tenders,services',
            'category_id' => 'nullable|exists:categories,id',
            'value' => 'required|numeric|min:0',
            'is_percentage' => 'required|boolean',
            'status' => 'required|in:active,inactive',
        ]);

        $commission->update($validated);

        return redirect()->route('admin.payments.commissions.index')->with('success', 'تم تحديث العمولة بنجاح');
    }

    public function destroy(Commission $commission)
    {
        $commission->delete();
        return redirect()->route('admin.payments.commissions.index')->with('success', 'تم حذف العمولة بنجاح');
    }
}
