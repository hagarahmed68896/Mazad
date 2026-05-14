<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ad;
use Illuminate\Support\Facades\Auth;

class AdController extends Controller
{
    public function show(Request $request, $id)
    {
        $ad = Ad::with(['category', 'user.reviewsReceived.reviewer', 'city', 'adProperties.property', 'images', 'attachments', 'questions.user'])
                ->withCount('bids')
                ->find($id);

        if (!$ad) {
            return response()->json([
                'success' => false,
                'message' => 'الإعلان غير موجود'
            ], 404);
        }

        // Increment views
        $ad->increment('views_count');

        // Check if favorited by current authenticated user (if using token)
        $isFavorited = false;
        if ($request->bearerToken() && $user = auth('sanctum')->user()) {
            $isFavorited = $ad->favorites()->where('user_id', $user->id)->exists();
        }
        $ad->is_favorited = $isFavorited;

        // Related Ads
        $relatedAds = Ad::with(['category', 'user', 'city'])
            ->where('category_id', $ad->category_id)
            ->where('id', '!=', $ad->id)
            ->where('status', 'active')
            ->limit(6)
            ->get();

        // Bid info for auction ads
        $bidIncrement = (float) ($ad->bid_increment ?? 500);
        $minimumBid = (float) $ad->price + $bidIncrement;

        // Suggested Services
        $suggestedServices = \App\Models\Service::where('category_id', $ad->category_id)->limit(4)->get();

        return response()->json([
            'success' => true,
            'message' => 'تفاصيل الإعلان',
            'data' => [
                'ad' => $ad,
                'bid_info' => [
                    'bid_increment' => $bidIncrement,
                    'current_price' => (float) $ad->price,
                    'minimum_bid' => $minimumBid,
                ],
                'related_ads' => $relatedAds,
                'suggested_services' => $suggestedServices
            ]
        ]);
    }

    public function createMetadata(Request $request)
    {
        // 1. Fetch categories
        // We will fetch root categories with their subcategories
        $categories = \App\Models\Category::whereNull('parent_id')
            ->with(['subCategories', 'pages' => function($q) {
                $q->with('properties');
            }, 'properties' => function($q) {
                $q->whereNull('category_page_id'); 
            }])
            ->get();

        // 2. Fetch cities
        $cities = \App\Models\City::orderBy('name')->get();

        return response()->json([
            'success' => true,
            'message' => 'بيانات إنشاء الإعلان',
            'data' => [
                'categories' => $categories,
                'cities' => $cities,
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'type' => 'required|in:auction,direct_purchase,standard,tender',
            'description' => 'required|string',
            // images
            'images.*' => 'image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $user = auth('sanctum')->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح'
            ], 401);
        }

        // Handle main image if there are multiple images
        $mainImage = null;
        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $file) {
                $path = $file->store('ads', 'public');
                $imagePaths[] = $path;
                if ($index === 0) {
                    $mainImage = 'storage/' . $path;
                }
            }
        }

        // Set deposit amount depending on type
        $deposit_amount = $request->ad_type === 'auction' ? $request->auction_deposit : $request->direct_deposit;
        $price = $request->ad_type === 'auction' ? $request->auction_start_price : $request->sale_price;

        $ad = Ad::create([
            'user_id' => $user->id,
            'category_id' => $request->category_id,
            'city_id' => $request->city_id ?? 1, // Fallback
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->ad_type ?? $request->type, // API might send ad_type or type
            'address' => $request->address,
            'lat' => $request->lat,
            'lng' => $request->lng,
            'inspection_info' => $request->inspection_info,
            'deposit_amount' => $deposit_amount ?? 0,
            'price' => $price ?? 0,
            'bid_increment' => $request->bid_increment ?? 0,
            'status' => 'active', // or pending depending on business logic
            'end_date' => $request->end_date ?? now()->addDays(7),
            'image' => $mainImage,
            'is_featured' => $request->has('is_featured') ? 1 : 0,
            'is_enfaz' => $request->is_enfaz ?? 0,
        ]);

        // Save Additional Images
        foreach ($imagePaths as $path) {
            \App\Models\AdImage::create([
                'ad_id' => $ad->id,
                'image' => 'storage/' . $path
            ]);
        }

        // Save Dynamic Properties
        if ($request->has('properties') && is_array($request->properties)) {
            foreach ($request->properties as $propertyId => $value) {
                // If it's a file upload (e.g. car front image)
                if ($request->hasFile("properties.{$propertyId}")) {
                    $file = $request->file("properties.{$propertyId}");
                    $path = $file->store('ads/properties', 'public');
                    $value = 'storage/' . $path;
                }

                if ($value !== null && $value !== '') {
                    \App\Models\AdProperty::create([
                        'ad_id' => $ad->id,
                        'property_id' => $propertyId,
                        'value' => $value,
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء الإعلان بنجاح',
            'data' => [
                'ad' => $ad->load('adProperties', 'images')
            ]
        ], 201);
    }
}
