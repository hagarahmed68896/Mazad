<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\CategoryPage;
use App\Models\Service;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');

        $query = Category::whereNull('parent_id')
                         ->withCount(['ads', 'users']);

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $categories = $query->paginate(12);

        return view('admin.categories.index', compact('categories', 'search'));
    }

    public function show(Category $category)
    {
        // Fetch pages and their properties ordered by sort_order
        $pages = $category->pages()->with(['properties' => function ($query) {
            $query->orderBy('sort_order', 'asc')->orderBy('id', 'asc');
        }])->get();

        $services = $category->services;

        return view('admin.categories.show', compact('category', 'pages', 'services'));
    }


    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg|max:10240',
            'ad_types' => 'nullable|array',
            'deposit_value' => 'nullable|numeric|min:0',
            'tax_value' => 'nullable|numeric|min:0',
            'sub_categories' => 'nullable|array',
            'sub_categories.*' => 'nullable|string|max:255',
            'pages' => 'nullable|array',
            'pages.*' => 'nullable|string|max:255',
            'services' => 'nullable|array',
            'services.*' => 'nullable|string|max:255',
            'ad_type' => 'nullable|string|in:ads,tenders',
        ], [
            'image.max' => 'عذراً، يجب ألا يتجاوز حجم الصورة 10 ميجابايت.',
            'image.image' => 'يجب أن يكون الملف المرفوع صورة.',
            'name.required' => 'يرجى إدخال اسم الفئة.'
        ]);

        try {
            DB::beginTransaction();

            $categoryData = $request->only(['name', 'deposit_value', 'tax_value']);
            $categoryData['ad_types'] = $request->has('ad_type') ? [$request->ad_type] : [];
            
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $filename = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('uploads/categories'), $filename);
                $categoryData['image'] = 'uploads/categories/' . $filename;
            }

            $category = Category::create($categoryData);

            // Sub Categories
            if ($request->has('sub_categories')) {
                foreach ($request->sub_categories as $subName) {
                    if ($subName) {
                        Category::create([
                            'parent_id' => $category->id,
                            'name' => $subName,
                            'ad_types' => $category->ad_types, // Inherit ad types? or maybe nullable
                        ]);
                    }
                }
            }

            // Pages
            if ($request->has('pages')) {
                foreach ($request->pages as $pageName) {
                    if ($pageName) {
                        CategoryPage::create([
                            'category_id' => $category->id,
                            'name' => $pageName,
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('admin.categories.index')->with('success', 'تمت إضافة الفئة بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ أثناء الإضافة: ' . $e->getMessage());
        }
    }

    public function edit(Category $category)
    {
        $category->load(['subCategories', 'pages', 'services']);
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg|max:10240',
            'ad_types' => 'nullable|array',
            'deposit_value' => 'nullable|numeric|min:0',
            'tax_value' => 'nullable|numeric|min:0',
            'sub_categories' => 'nullable|array',
            'pages' => 'nullable|array',
            'services' => 'nullable|array',
            'ad_type' => 'nullable|string|in:ads,tenders',
        ], [
            'image.max' => 'عذراً، يجب ألا يتجاوز حجم الصورة 10 ميجابايت.',
            'image.image' => 'يجب أن يكون الملف المرفوع صورة.'
        ]);

        try {
            DB::beginTransaction();

            $categoryData = $request->only(['name', 'deposit_value', 'tax_value']);
            $categoryData['ad_types'] = $request->has('ad_type') ? [$request->ad_type] : [];
            
            if ($request->hasFile('image')) {
                $rawImage = $category->getRawOriginal('image');
                if ($rawImage) {
                    if (\Illuminate\Support\Str::startsWith($rawImage, 'uploads/')) {
                        $filePath = public_path($rawImage);
                        if (file_exists($filePath)) {
                            unlink($filePath);
                        }
                    } elseif (\Illuminate\Support\Str::startsWith($rawImage, 'categories/')) {
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($rawImage);
                    }
                }
                $image = $request->file('image');
                $filename = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('uploads/categories'), $filename);
                $categoryData['image'] = 'uploads/categories/' . $filename;
            }

            $category->update($categoryData);

            // Sync Sub Categories (simple approach: delete and recreate or match IDs)
            // For now, let's assume we replace them or we would need IDs for actual sync.
            // Screenshot shows a simple list.
            $category->subCategories()->delete();
            if ($request->has('sub_categories')) {
                foreach ($request->sub_categories as $subName) {
                    if ($subName) {
                        Category::create([
                            'parent_id' => $category->id,
                            'name' => $subName,
                            'ad_types' => $category->ad_types,
                        ]);
                    }
                }
            }

            // Sync Pages
            $category->pages()->delete();
            if ($request->has('pages')) {
                foreach ($request->pages as $pageName) {
                    if ($pageName) {
                        CategoryPage::create([
                            'category_id' => $category->id,
                            'name' => $pageName,
                        ]);
                    }
                }
            }



            DB::commit();
            return redirect()->route('admin.categories.index')->with('success', 'تم تحديث الفئة بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ أثناء التحديث: ' . $e->getMessage());
        }
    }

    public function destroy(Category $category)
    {
        try {
            $rawImage = $category->getRawOriginal('image');
            if ($rawImage) {
                if (\Illuminate\Support\Str::startsWith($rawImage, 'uploads/')) {
                    $filePath = public_path($rawImage);
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                } elseif (\Illuminate\Support\Str::startsWith($rawImage, 'categories/')) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($rawImage);
                }
            }
            $category->delete();
            return back()->with('success', 'تم حذف الفئة بنجاح');
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء الحذف: ' . $e->getMessage());
        }
    }
}
