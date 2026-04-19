<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Banner::latest();
        
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }
        
        $banners = $query->paginate(12);
        
        return view('admin.banners.index', compact('banners'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.banners.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validation for the global status and potential items
        $request->validate([
            'status' => 'required',
            'items' => 'required|array|min:1',
            'items.*.title' => 'required|string|max:255',
        ]);

        $status = $request->status;

        foreach ($request->items as $index => $item) {
            $data = [
                'title'       => $item['title'],
                'description' => $item['description'] ?? null,
                'button_text' => $item['button_text'] ?? null,
                'status'      => $status,
            ];

            // Handle nested image upload: items[0][image]
            if ($request->hasFile("items.$index.image")) {
                $data['image'] = $request->file("items.$index.image")->store('banners', 'public');
            }

            Banner::create($data);
        }

        return redirect()->route('admin.banners.index')->with('success', 'تمت إضافة البانرات بنجاح');
    }

    /**
     * Display the specified resource.
     */
    public function show(Banner $banner)
    {
        return view('admin.banners.show', compact('banner'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Banner $banner)
    {
        return view('admin.banners.edit', compact('banner'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Banner $banner)
    {
        // For editing, we usually edit a single banner, 
        // but the UI might still send it nested in items[0]
        
        $data = $request->all();
        
        // If nested in items[0], extract it
        if ($request->has('items.0')) {
            $itemData = $request->input('items.0');
            $data['title'] = $itemData['title'] ?? $banner->title;
            $data['description'] = $itemData['description'] ?? $banner->description;
            $data['button_text'] = $itemData['button_text'] ?? $banner->button_text;
            
            if ($request->hasFile('items.0.image')) {
                $data['image'] = $request->file('items.0.image')->store('banners', 'public');
            }
        } else {
            // Handle flat names if user mapped them
            if ($request->has('name')) $data['title'] = $request->name;
            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('banners', 'public');
            }
        }

        $banner->update($data);

        return redirect()->route('admin.banners.index')->with('success', 'تم تحديث البانر بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Banner $banner)
    {
        if ($banner->image) {
            Storage::disk('public')->delete($banner->image);
        }
        
        $banner->delete();

        return redirect()->route('admin.banners.index')->with('success', 'تم حذف البانر بنجاح');
    }
}
