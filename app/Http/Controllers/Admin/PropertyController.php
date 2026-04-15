<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Property;

class PropertyController extends Controller
{
    public function index(Category $category)
    {
        $properties = $category->properties()->orderBy('sort_order', 'asc')->orderBy('id', 'asc')->get();
        return view('admin.properties.index', compact('category', 'properties'));
    }

    public function create(Category $category)
    {
        $pages = $category->pages;
        return view('admin.properties.create', compact('category', 'pages'));
    }

    public function store(Request $request, Category $category)
    {
        $request->validate([
            'properties'               => 'required|array|min:1',
            'properties.*.label'       => 'required|string|max:255',
            'properties.*.input_type'  => 'nullable|string|in:text,number,select,checkbox,radio,textarea,date',
            'properties.*.placeholder' => 'nullable|string|max:255',
            'properties.*.help_text'   => 'nullable|string|max:500',
            'properties.*.is_required' => 'nullable|in:0,1',
            'properties.*.options'     => 'nullable|array',
            'properties.*.options.*'   => 'nullable|string|max:255',
            'category_page_id'         => 'nullable|exists:category_pages,id',
        ]);

        $pageId = $request->category_page_id ?: null;

        foreach ($request->properties as $data) {
            $options = null;
            if (!empty($data['options'])) {
                $options = array_values(array_filter($data['options'], fn($v) => trim($v) !== ''));
                $options = $options ?: null;
            }

            Property::create([
                'category_id'      => $category->id,
                'category_page_id' => $pageId,
                'label'            => $data['label'],
                'placeholder'      => $data['placeholder'] ?? null,
                'help_text'        => $data['help_text'] ?? null,
                'input_type'       => $data['input_type'] ?? 'text',
                'options'          => $options,
                'is_required'      => isset($data['is_required']) && $data['is_required'] == '1',
            ]);
        }

        return redirect()->route('admin.categories.properties.index', $category)
                         ->with('success', 'تمت إضافة ' . count($request->properties) . ' خاصية بنجاح');
    }

    public function edit(Category $category, Property $property)
    {
        return view('admin.properties.edit', compact('category', 'property'));
    }

    public function update(Request $request, Category $category, Property $property)
    {
        $request->validate([
            'label'      => 'required|string|max:255',
            'placeholder'=> 'nullable|string|max:255',
            'input_type' => 'required|string|in:text,number,select,checkbox,radio,textarea,date',
            'options'    => 'nullable|string',
            'is_required'=> 'nullable|boolean',
        ]);

        $options = null;
        if ($request->options) {
            $options = array_filter(array_map('trim', explode(',', $request->options)));
        }

        $property->update([
            'label'       => $request->label,
            'placeholder' => $request->placeholder,
            'input_type'  => $request->input_type,
            'options'     => $options ?: null,
            'is_required' => $request->boolean('is_required'),
        ]);

        return redirect()->route('admin.categories.properties.index', $category)
                         ->with('success', 'تم تحديث الخاصية بنجاح');
    }

    public function destroy(Category $category, Property $property)
    {
        $property->delete();
        return back()->with('success', 'تم حذف الخاصية بنجاح');
    }

    public function reorder(Request $request, Category $category)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'exists:properties,id'
        ]);

        foreach ($request->order as $index => $propertyId) {
            Property::where('id', $propertyId)
                    ->where('category_id', $category->id)
                    ->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }
}
