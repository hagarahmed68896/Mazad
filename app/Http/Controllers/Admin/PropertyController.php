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
        // Properties NOT assigned to any page
        $properties = $category->properties()
            ->whereNull('category_page_id')
            ->orderBy('sort_order', 'asc')->orderBy('id', 'asc')->get();

        // Pages with their assigned properties
        $pages = $category->pages()->with(['properties' => function($q) {
            $q->orderBy('sort_order', 'asc')->orderBy('id', 'asc');
        }])->get();

        return view('admin.properties.index', compact('category', 'properties', 'pages'));
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
            'properties.*.input_type'  => 'nullable|string|in:text,number,price,select,checkbox,radio,textarea,date,file,map',
            'properties.*.placeholder' => 'nullable|string|max:255',
            'properties.*.help_text'   => 'nullable|string|max:500',
            'properties.*.is_required' => 'nullable|in:0,1',
            'properties.*.is_hideable' => 'nullable|in:0,1',
            'properties.*.options'     => 'nullable|array',
            'properties.*.options.*'   => 'nullable|string|max:255',
            'category_page_id'         => 'nullable|exists:category_pages,id',
        ]);

        $pageId = $request->filled('category_page_id') ? $request->category_page_id : null;

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
                'is_hideable'      => isset($data['is_hideable']) && $data['is_hideable'] == '1',
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
            'help_text'  => 'nullable|string|max:500',
            'input_type' => 'required|string|in:text,number,price,select,checkbox,radio,textarea,date,file,map',
            'options'    => 'nullable|string',
            'is_required'=> 'nullable|in:0,1',
            'is_hideable'=> 'nullable|in:0,1',
        ]);

        // If options were submitted as a comma-separated string, parse them.
        // If the submitted string is empty, keep the existing options (user didn't touch them).
        if ($request->filled('options')) {
            $parsed = array_values(array_filter(array_map('trim', explode(',', $request->options))));
            $options = !empty($parsed) ? $parsed : $property->options;
        } else {
            // options field missing or blank → preserve existing
            $options = $property->options;
        }

        $property->update([
            'label'       => $request->label,
            'placeholder' => $request->placeholder,
            'help_text'   => $request->help_text,
            'input_type'  => $request->input_type,
            'options'     => $options,
            'is_required' => $request->boolean('is_required'),
            'is_hideable' => $request->boolean('is_hideable'),
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
