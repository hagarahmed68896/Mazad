<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class BannerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Banner::latest();
        
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('title', 'like', '%' . $request->search . '%');
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
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required',
            'for_user_type' => 'nullable|string|in:normal,partner',
            'items' => 'required|array|min:1',
        ]);

        $items = [];
        $uploadPath = public_path('uploads/banners');
        
        if (!File::isDirectory($uploadPath)) {
            File::makeDirectory($uploadPath, 0777, true, true);
        }

        foreach ($request->items as $index => $itemData) {
            $item = [
                'title'       => $itemData['title'] ?? '',
                'description' => $itemData['description'] ?? '',
                'button_text' => $itemData['button_text'] ?? '',
                'show_button' => $itemData['show_button'] ?? '0',
                'url'         => '#',
                'image'       => null,
            ];

            if ($request->hasFile("items.$index.image")) {
                $file = $request->file("items.$index.image");
                $filename = time() . '_' . $index . '_' . $file->getClientOriginalName();
                $file->move($uploadPath, $filename);
                $item['image'] = 'uploads/banners/' . $filename;
            }

            $items[] = $item;
        }

        // Create a single record for the whole group
        Banner::create([
            'name'        => $request->name,
            'status'      => $request->status,
            'for_user_type'=> $request->for_user_type ?? 'normal',
            'items'       => $items,
            // Fallback for index view (use first item)
            'title'       => $items[0]['title'] ?? $request->name,
            'image'       => $items[0]['image'] ?? null,
            'description' => $items[0]['description'] ?? null,
            'button_text' => $items[0]['button_text'] ?? null,
        ]);

        return redirect()->route('admin.banners.index')->with('success', 'تمت إضافة البانر بنجاح');
    }

    public function edit(Banner $banner)
    {
        return view('admin.banners.edit', compact('banner'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Banner $banner)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required',
            'for_user_type' => 'nullable|string|in:normal,partner',
            'items' => 'required|array|min:1',
        ]);

        $newItems = [];
        $uploadPath = public_path('uploads/banners');

        if (!File::isDirectory($uploadPath)) {
            File::makeDirectory($uploadPath, 0777, true, true);
        }

        foreach ($request->items as $index => $itemData) {
            $item = [
                'title'       => $itemData['title'] ?? '',
                'description' => $itemData['description'] ?? '',
                'button_text' => $itemData['button_text'] ?? '',
                'show_button' => $itemData['show_button'] ?? '0',
                'url'         => '#',
                'image'       => $itemData['existing_image'] ?? null,
            ];

            if ($request->hasFile("items.$index.image")) {
                // Delete old if exists
                if ($item['image'] && File::exists(public_path($item['image']))) {
                    File::delete(public_path($item['image']));
                }
                
                $file = $request->file("items.$index.image");
                $filename = time() . '_' . $index . '_' . $file->getClientOriginalName();
                $file->move($uploadPath, $filename);
                $item['image'] = 'uploads/banners/' . $filename;
            }

            $newItems[] = $item;
        }

        $banner->update([
            'name'        => $request->name,
            'status'      => $request->status,
            'for_user_type'=> $request->for_user_type ?? 'normal',
            'items'       => $newItems,
            // Fallback for index view (use first item)
            'title'       => $newItems[0]['title'] ?? $request->name,
            'image'       => $newItems[0]['image'] ?? null,
            'description' => $newItems[0]['description'] ?? null,
            'button_text' => $newItems[0]['button_text'] ?? null,
        ]);

        return redirect()->route('admin.banners.index')->with('success', 'تم تحديث البانر بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Banner $banner)
    {
        if ($banner->items) {
            foreach ($banner->items as $item) {
                if (isset($item['image']) && $item['image'] && File::exists(public_path($item['image']))) {
                    File::delete(public_path($item['image']));
                }
            }
        }
        
        if ($banner->image && File::exists(public_path($banner->image))) {
            File::delete(public_path($banner->image));
        }

        $banner->delete();

        return redirect()->route('admin.banners.index')->with('success', 'تم حذف البانر بنجاح');
    }
}
