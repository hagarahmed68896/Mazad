<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Ad;

class AdController extends Controller
{
    public function create(Request $request)
    {
        $type = $request->query('type', 'direct_purchase');
        $categories = Category::whereNull('parent_id')->with('subCategories')->get();
        
        return view('ads.create', compact('type', 'categories'));
    }

    public function createDetails(Request $request)
    {
        $type = $request->query('type', 'direct_purchase');
        $categoryId = $request->query('category');
        
        // Fetch category with its pages and properties
        $category = Category::with(['pages' => function($q) {
            $q->with('properties');
        }, 'properties' => function($q) {
            $q->whereNull('category_page_id'); 
        }])->findOrFail($categoryId);

        // Fetch cities for shipping selection
        $cities = \App\Models\City::orderBy('name')->get();

        return view('ads.details', compact('type', 'category', 'cities'));
    }

    public function store(Request $request)
    {
        // Handle store logic later
    }
}
