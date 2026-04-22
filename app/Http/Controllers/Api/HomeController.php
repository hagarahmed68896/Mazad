<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Ad;

class HomeController extends Controller
{
    public function index()
    {
        $banners = Banner::where('status', true)->get();
        $discoveryCategories = Category::whereNull('parent_id')->limit(10)->get();
        
        $latestAuctions = Ad::with(['category', 'user', 'city'])
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();
            
        $interactiveAds = Ad::with(['category', 'user', 'city'])
            ->where('status', 'active')
            ->orderBy('bids_count', 'desc')
            ->limit(8)
            ->get();
            
        $endingSoon = Ad::with(['category', 'user', 'city'])
            ->where('status', 'active')
            ->orderBy('created_at', 'asc') 
            ->limit(8)
            ->get();

        return response()->json([
            'banners' => $banners,
            'categories' => $discoveryCategories,
            'latest_auctions' => $latestAuctions,
            'interactive_ads' => $interactiveAds,
            'ending_soon' => $endingSoon
        ]);
    }
}
