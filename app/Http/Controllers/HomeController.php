<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Ad;

class HomeController extends Controller
{
    public function index()
    {
        $banners = Banner::where('status', true)->get();
        $discoveryCategories = Category::whereNull('parent_id')->get();
        
        $latestAuctions = Ad::with(['category', 'user', 'city'])
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();
            
        $interactiveAds = Ad::with(['category', 'user', 'city'])
            ->orderBy('bids_count', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(12)
            ->get();
            
        $endingSoon = Ad::with(['category', 'user', 'city'])
            ->where('status', 'active')
            ->orderBy('created_at', 'asc') 
            ->limit(8)
            ->get();

        return view('welcome', compact('banners', 'discoveryCategories', 'latestAuctions', 'interactiveAds', 'endingSoon'));
    }
}
