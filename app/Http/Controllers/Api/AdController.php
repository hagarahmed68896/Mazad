<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ad;
use App\Models\AdImage;
use App\Models\AdProperty;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

        $ad->increment('views_count');

        $isFavorited = false;
        if ($request->bearerToken() && $user = auth('sanctum')->user()) {
            $isFavorited = $ad->favorites()->where('user_id', $user->id)->exists();
        }
        $ad->is_favorited = $isFavorited;

        $relatedAds = Ad::with(['category', 'user', 'city'])
            ->where('category_id', $ad->category_id)
            ->where('id', '!=', $ad->id)
            ->where('status', 'active')
            ->limit(6)
            ->get();

        $bidIncrement = (float) ($ad->bid_increment ?? 500);
        $minimumBid = (float) $ad->price + $bidIncrement;

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
        $categories = \App\Models\Category::whereNull('parent_id')
            ->with(['subCategories', 'pages' => function($q) {
                $q->with('properties');
            }, 'properties' => function($q) {
                $q->whereNull('category_page_id'); 
            }])
            ->get();

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
            'images.*' => 'image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $user = auth('sanctum')->user();
        
        $adType = $request->type ?? $request->ad_type;
        $price = 0;
        $depositAmount = 0;
        $bidIncrement = $request->bid_increment ?? 0;

        if ($adType === 'auction') {
            $price = $request->auction_start_price ?? 0;
            $depositAmount = $request->auction_deposit ?? 0;
        } elseif ($adType === 'tender') {
            $price = $request->tender_price ?? 0;
            $bidIncrement = $request->tender_bid_increment ?? 0;
        } else {
            $price = $request->sale_price ?? 0;
            $depositAmount = $request->direct_deposit ?? 0;
        }

        DB::beginTransaction();
        try {
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

            $ad = Ad::create([
                'user_id'        => $user->id,
                'category_id'    => $request->category_id,
                'city_id'        => $request->city_id,
                'title'          => $request->title,
                'description'    => $request->description,
                'type'           => $adType,
                'address'        => $request->address,
                'lat'            => $request->lat,
                'lng'            => $request->lng,
                'inspection_info'=> $request->inspection_info,
                'inspection_date'=> $request->inspection_date,
                'deposit_amount' => $depositAmount,
                'price'          => $price,
                'bid_increment'  => $bidIncrement,
                'status'         => 'active',
                'end_date'       => $request->end_date ?? now()->addDays(7),
                'image'          => $mainImage,
                'is_featured'    => $request->is_featured ? 1 : 0,
                'is_enfaz'       => $request->is_enfaz ?? 0,
                'auction_type'   => $request->auction_type,
                'shipping_price' => $request->shipping_price ?? 0,
                'shipping_type'  => $request->shipping_type,
                'youtube_link'   => $request->youtube_link,
                'start_date'     => $request->start_date,
                'award_duration' => $request->award_duration,
                'quantity'       => $request->quantity ?? 1,
                'min_sale_price' => $request->min_sale_price ?? 0,
                'allow_bidding'  => $request->allow_bidding ?? 0,
                'allow_negotiation' => $request->allow_negotiation ?? 0,
                'buy_now_price'  => $request->buy_now_price ?? 0,
            ]);

            foreach ($imagePaths as $path) {
                AdImage::create(['ad_id' => $ad->id, 'image' => 'storage/' . $path]);
            }

            if ($request->has('properties') && is_array($request->properties)) {
                foreach ($request->properties as $propertyId => $value) {
                    if ($request->hasFile("properties.{$propertyId}")) {
                        $file = $request->file("properties.{$propertyId}");
                        $path = $file->store('ads/properties', 'public');
                        $value = 'storage/' . $path;
                    }
                    if ($value !== null && $value !== '') {
                        AdProperty::create(['ad_id' => $ad->id, 'property_id' => $propertyId, 'value' => $value]);
                    }
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'تم إنشاء الإعلان بنجاح', 'data' => ['ad' => $ad->load('images', 'adProperties')]], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $ad = Ad::findOrFail($id);
        if ($ad->user_id !== auth('sanctum')->id()) {
            return response()->json(['success' => false, 'message' => 'غير مصرح'], 403);
        }

        $adType = $ad->type;
        $price = $ad->price;
        $depositAmount = $ad->deposit_amount;
        $bidIncrement = $ad->bid_increment;

        if ($adType === 'auction') {
            $price = $request->auction_start_price ?? $price;
            $depositAmount = $request->auction_deposit ?? $depositAmount;
            $bidIncrement = $request->bid_increment ?? $bidIncrement;
        } elseif ($adType === 'tender') {
            $price = $request->tender_price ?? $price;
            $bidIncrement = $request->tender_bid_increment ?? $bidIncrement;
        } else {
            $price = $request->sale_price ?? $price;
            $depositAmount = $request->direct_deposit ?? $depositAmount;
        }

        DB::beginTransaction();
        try {
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $file) {
                    $path = $file->store('ads', 'public');
                    if ($index === 0) $ad->image = 'storage/' . $path;
                    AdImage::create(['ad_id' => $ad->id, 'image' => 'storage/' . $path]);
                }
            }

            $ad->update([
                'title'          => $request->title ?? $ad->title,
                'description'    => $request->description ?? $ad->description,
                'address'        => $request->address ?? $ad->address,
                'lat'            => $request->lat ?? $ad->lat,
                'lng'            => $request->lng ?? $ad->lng,
                'inspection_info'=> $request->inspection_info ?? $ad->inspection_info,
                'inspection_date'=> $request->inspection_date ?? $ad->inspection_date,
                'deposit_amount' => $depositAmount,
                'price'          => $price,
                'bid_increment'  => $bidIncrement,
                'end_date'       => $request->end_date ?? $ad->end_date,
                'is_featured'    => $request->has('is_featured') ? ($request->is_featured ? 1 : 0) : $ad->is_featured,
                'auction_type'   => $request->auction_type ?? $ad->auction_type,
                'shipping_price' => $request->shipping_price ?? $ad->shipping_price,
                'shipping_type'  => $request->shipping_type ?? $ad->shipping_type,
                'youtube_link'   => $request->youtube_link ?? $ad->youtube_link,
                'start_date'     => $request->start_date ?? $ad->start_date,
                'award_duration' => $request->award_duration ?? $ad->award_duration,
                'quantity'       => $request->quantity ?? $ad->quantity,
                'min_sale_price' => $request->min_sale_price ?? $ad->min_sale_price,
                'allow_bidding'  => $request->allow_bidding ?? $ad->allow_bidding,
                'allow_negotiation' => $request->allow_negotiation ?? $ad->allow_negotiation,
                'buy_now_price'  => $request->buy_now_price ?? $ad->buy_now_price,
            ]);

            if ($request->has('properties') && is_array($request->properties)) {
                foreach ($request->properties as $propertyId => $value) {
                    if ($request->hasFile("properties.{$propertyId}")) {
                        $file = $request->file("properties.{$propertyId}");
                        $path = $file->store('ads/properties', 'public');
                        $value = 'storage/' . $path;
                    }
                    if ($value !== null && $value !== '') {
                        AdProperty::updateOrCreate(['ad_id' => $ad->id, 'property_id' => $propertyId], ['value' => $value]);
                    }
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'تم تحديث الإعلان بنجاح', 'data' => ['ad' => $ad->load('images', 'adProperties')]]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
