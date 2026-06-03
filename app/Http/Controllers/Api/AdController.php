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

        // Draft privacy check: If the ad is incomplete, only the owner can view it
        $currentUser = auth('sanctum')->user();
        if ($ad->status === 'incomplete' && (!$currentUser || $currentUser->id !== $ad->user_id)) {
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
        $rules = [
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'type' => 'required|in:auction,direct_purchase,standard,tender',
            'description' => 'nullable|string',
            'address' => 'required_without_all:lat,lng|nullable|string',
            'lat' => 'required_without:address|nullable|numeric|between:-90,90',
            'lng' => 'required_without:address|nullable|numeric|between:-180,180',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:20480',
            'start_date' => 'nullable|date',
        ];

        if ($request->filled('start_date')) {
            $rules['end_date'] = 'required|date|after:start_date';
        } else {
            $rules['end_date'] = 'required|date|after:now';
        }

        $request->validate($rules, [
            'end_date.after' => 'يجب أن يكون تاريخ الانتهاء بعد تاريخ البدء.',
            'end_date.required' => 'تاريخ الانتهاء مطلوب إجباريًا.',
            'images.*.max' => 'يجب ألا يتجاوز حجم أي صورة 20 ميجابايت.',
            'images.*.image' => 'يجب أن يكون الملف المرفوع صورة صالحة.',
            'images.*.mimes' => 'يجب أن تكون الصور بصيغة jpeg أو png أو jpg فقط.',
        ]);

        $user = auth('sanctum')->user();
        
        // Dynamically extract city_id from address text or coordinates
        $cityId = $request->city_id;
        if ($request->filled('address')) {
            $cities = \App\Models\City::all();
            foreach ($cities as $city) {
                if (mb_stripos($request->address, $city->name) !== false) {
                    $cityId = $city->id;
                    break;
                }
            }
        }

        // Coordinate distance mapping fallback if address didn't match or is missing but lat/lng are present
        if (!$cityId && $request->filled('lat') && $request->filled('lng')) {
            $majorCities = [
                'الرياض' => ['lat' => 24.7136, 'lng' => 46.6753],
                'جدة' => ['lat' => 21.4858, 'lng' => 39.1925],
                'الدمام' => ['lat' => 26.4207, 'lng' => 50.0888],
                'مكة' => ['lat' => 21.3891, 'lng' => 39.8579],
                'المدينة' => ['lat' => 24.4672, 'lng' => 39.6111],
            ];

            $minDistance = null;
            $nearestCityName = null;
            $userLat = (float) $request->lat;
            $userLng = (float) $request->lng;

            foreach ($majorCities as $cityName => $coords) {
                $dist = sqrt(pow($userLat - $coords['lat'], 2) + pow($userLng - $coords['lng'], 2));
                if ($minDistance === null || $dist < $minDistance) {
                    $minDistance = $dist;
                    $nearestCityName = $cityName;
                }
            }

            if ($nearestCityName) {
                $matchedCity = \App\Models\City::where('name', 'like', '%' . $nearestCityName . '%')->first();
                if ($matchedCity) {
                    $cityId = $matchedCity->id;
                }
            }
        }

        $adClass = $request->type ?? $request->ad_type ?? 'direct_purchase';
        $price = 0;
        $depositAmount = 0;
        $bidIncrement = $request->bid_increment ?? 0;

        if ($adClass === 'auction' || $adClass === 'electronic_auction' || $adClass === 'hybrid_auction') {
            $price = $request->auction_start_price ?? 0;
            $depositAmount = $request->auction_deposit ?? 0;
            $adType = ($request->auction_type === 'hybrid') ? 'hybrid_auction' : 'electronic_auction';
        } elseif ($adClass === 'tender') {
            $price = $request->tender_price ?? 0;
            $bidIncrement = $request->tender_bid_increment ?? 0;
            $adType = 'tender';
        } else {
            $price = $request->sale_price ?? 0;
            $depositAmount = $request->direct_deposit ?? 0;
            $adType = 'direct_purchase';
        }

        DB::beginTransaction();
        try {
            $mainImage = null;
            $imagePaths = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $file) {
                    $filename = time() . '_' . $index . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('uploads/ads'), $filename);
                    $path = 'uploads/ads/' . $filename;
                    $imagePaths[] = $path;
                    if ($index === 0) {
                        $mainImage = $path;
                    }
                }
            }

            $ad = Ad::create([
                'user_id'        => $user->id,
                'category_id'    => $request->category_id,
                'city_id'        => $cityId,
                'title'          => $request->title,
                'description'    => $request->description,
                'type'           => $adType,
                'address'        => $request->address,
                'lat'            => $request->lat,
                'lng'            => $request->lng,
                'inspection_info'=> $request->inspection_info,
                'inspection_date'=> $this->parseSafeDate($request->inspection_date, 'Y-m-d'),
                'inspection_end_date'=> $this->parseSafeDate($request->inspection_end_date, 'Y-m-d'),
                'deposit_amount' => $depositAmount,
                'price'          => $price,
                'bid_increment'  => $bidIncrement,
                'status'         => 'active',
                'end_date'       => $request->end_date ? $this->parseSafeDate($request->end_date, 'Y-m-d H:i:s') : now()->addDays(7),
                'image'          => $mainImage,
                'is_featured'    => $request->is_featured ? 1 : 0,
                'is_enfaz'       => $request->is_enfaz ?? 0,
                'auction_type'   => $request->auction_type,
                'shipping_price' => $request->shipping_price ?? 0,
                'shipping_type'  => $request->shipping_type,
                'youtube_link'   => $request->youtube_link,
                'start_date'     => $this->parseSafeDate($request->start_date, 'Y-m-d H:i:s'),
                'award_duration' => $request->award_duration,
                'quantity'       => $request->quantity ?? 1,
                'min_sale_price' => $request->min_sale_price ?? 0,
                'allow_bidding'  => $request->allow_bidding ?? 0,
                'allow_negotiation' => $request->allow_negotiation ?? 0,
                'buy_now_price'  => $request->buy_now_price ?? 0,
            ]);

            foreach ($imagePaths as $path) {
                AdImage::create(['ad_id' => $ad->id, 'image' => $path]);
            }

            if ($request->has('properties') && is_array($request->properties)) {
                foreach ($request->properties as $propertyId => $value) {
                    if ($request->hasFile("properties.{$propertyId}")) {
                        $file = $request->file("properties.{$propertyId}");
                        $filename = time() . '_prop_' . $propertyId . '.' . $file->getClientOriginalExtension();
                        $file->move(public_path('uploads/ads/properties'), $filename);
                        $value = 'uploads/ads/properties/' . $filename;
                    }
                    if ($value !== null && $value !== '') {
                        if (\App\Models\Property::where('id', $propertyId)->exists()) {
                            AdProperty::create(['ad_id' => $ad->id, 'property_id' => $propertyId, 'value' => $value]);
                        }
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

        $request->validate([
            'address' => 'sometimes|required_without_all:lat,lng|nullable|string',
            'lat' => 'sometimes|required_without:address|nullable|numeric|between:-90,90',
            'lng' => 'sometimes|required_without:address|nullable|numeric|between:-180,180',
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'end_date' => 'sometimes|required|date|after:now',
        ], [
            'end_date.after' => 'يجب أن يكون تاريخ الانتهاء بعد تاريخ البدء.',
            'end_date.required' => 'تاريخ الانتهاء مطلوب إجباريًا.',
        ]);

        // Dynamically extract city_id from address text or coordinates if updated
        $cityId = $request->city_id ?? $ad->city_id;
        if ($request->filled('address')) {
            $cities = \App\Models\City::all();
            foreach ($cities as $city) {
                if (mb_stripos($request->address, $city->name) !== false) {
                    $cityId = $city->id;
                    break;
                }
            }
        }

        // Coordinate distance mapping fallback if address didn't match or is missing but lat/lng are present
        if (($request->filled('lat') || $request->filled('lng')) && ($request->filled('lat') && $request->filled('lng'))) {
            // Only search if city wasn't successfully resolved by address text
            if ($cityId == $ad->city_id || !$cityId) {
                $majorCities = [
                    'الرياض' => ['lat' => 24.7136, 'lng' => 46.6753],
                    'جدة' => ['lat' => 21.4858, 'lng' => 39.1925],
                    'الدمام' => ['lat' => 26.4207, 'lng' => 50.0888],
                    'مكة' => ['lat' => 21.3891, 'lng' => 39.8579],
                    'المدينة' => ['lat' => 24.4672, 'lng' => 39.6111],
                ];

                $minDistance = null;
                $nearestCityName = null;
                $userLat = (float) $request->lat;
                $userLng = (float) $request->lng;

                foreach ($majorCities as $cityName => $coords) {
                    $dist = sqrt(pow($userLat - $coords['lat'], 2) + pow($userLng - $coords['lng'], 2));
                    if ($minDistance === null || $dist < $minDistance) {
                        $minDistance = $dist;
                        $nearestCityName = $cityName;
                    }
                }

                if ($nearestCityName) {
                    $matchedCity = \App\Models\City::where('name', 'like', '%' . $nearestCityName . '%')->first();
                    if ($matchedCity) {
                        $cityId = $matchedCity->id;
                    }
                }
            }
        }

        $adType = $ad->type;
        $price = $ad->price;
        $depositAmount = $ad->deposit_amount;
        $bidIncrement = $ad->bid_increment;

        if ($adType === 'auction' || $adType === 'electronic_auction' || $adType === 'hybrid_auction') {
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
            $mainImagePath = $ad->image;
            if ($request->hasFile('images')) {
                // Delete old images first
                $ad->images()->delete();

                foreach ($request->file('images') as $index => $file) {
                    $filename = time() . '_edit_' . $index . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('uploads/ads'), $filename);
                    $path = 'uploads/ads/' . $filename;
                    if ($index === 0) $mainImagePath = $path;
                    AdImage::create(['ad_id' => $ad->id, 'image' => $path]);
                }
            }

            $ad->update([
                'title'          => $request->title ?? $ad->title,
                'description'    => $request->description ?? $ad->description,
                'city_id'        => $cityId,
                'image'          => $mainImagePath,
                'address'        => $request->address ?? $ad->address,
                'lat'            => $request->lat ?? $ad->lat,
                'lng'            => $request->lng ?? $ad->lng,
                'inspection_info'=> $request->inspection_info ?? $ad->inspection_info,
                'inspection_date'=> $request->has('inspection_date') ? $this->parseSafeDate($request->inspection_date, 'Y-m-d') : $ad->inspection_date,
                'inspection_end_date'=> $request->has('inspection_end_date') ? $this->parseSafeDate($request->inspection_end_date, 'Y-m-d') : $ad->inspection_end_date,
                'deposit_amount' => $depositAmount,
                'price'          => $price,
                'bid_increment'  => $bidIncrement,
                'end_date'       => $request->end_date ? $this->parseSafeDate($request->end_date, 'Y-m-d H:i:s') : $ad->end_date,
                'is_featured'    => $request->has('is_featured') ? ($request->is_featured ? 1 : 0) : $ad->is_featured,
                'auction_type'   => $request->auction_type ?? $ad->auction_type,
                'shipping_price' => $request->shipping_price ?? $ad->shipping_price,
                'shipping_type'  => $request->shipping_type ?? $ad->shipping_type,
                'youtube_link'   => $request->youtube_link ?? $ad->youtube_link,
                'start_date'     => $request->has('start_date') ? $this->parseSafeDate($request->start_date, 'Y-m-d H:i:s') : $ad->start_date,
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
                        $filename = time() . '_edit_prop_' . $propertyId . '.' . $file->getClientOriginalExtension();
                        $file->move(public_path('uploads/ads/properties'), $filename);
                        $value = 'uploads/ads/properties/' . $filename;
                    }
                    if ($value !== null && $value !== '') {
                        if (\App\Models\Property::where('id', $propertyId)->exists()) {
                            AdProperty::updateOrCreate(['ad_id' => $ad->id, 'property_id' => $propertyId], ['value' => $value]);
                        }
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

    public function myAds(Request $request)
    {
        $status = $request->query('status', 'all'); // 'all', 'incomplete', 'active', 'upcoming', 'finished'
        $user = auth('sanctum')->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح'
            ], 401);
        }

        $query = Ad::where('user_id', $user->id);
        
        // Tab Status Filter
        if ($status === 'incomplete') {
            $query->whereIn('status', ['pending', 'incomplete']);
        } elseif ($status === 'active') {
            $query->where('status', 'active');
        } elseif ($status === 'upcoming') {
            $query->where('status', 'upcoming');
        } elseif ($status === 'finished') {
            $query->where('status', 'finished');
        }
        
        // Search Keyword Filter
        if ($request->has('q') && !empty($request->q)) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->q . '%')
                  ->orWhere('description', 'like', '%' . $request->q . '%');
            });
        }
        
        // City Filter
        if ($request->has('city') && !empty($request->city)) {
            $query->where('city_id', $request->city);
        }
        
        // Category Filter
        if ($request->has('category') && !empty($request->category)) {
            $query->where('category_id', $request->category);
        }
        
        // Sorting
        $sort = $request->get('sort', 'newest');
        if ($sort === 'price_high') {
            $query->orderBy('price', 'desc');
        } elseif ($sort === 'price_low') {
            $query->orderBy('price', 'asc');
        } else {
            $query->orderBy('created_at', 'desc');
        }
        
        $ads = $query->with(['category', 'city', 'images'])->get();
        
        return response()->json([
            'success' => true,
            'message' => 'قائمة إعلاناتي الشخصية',
            'data' => $ads
        ]);
    }

    public function destroy($id)
    {
        $ad = Ad::findOrFail($id);
        if ($ad->user_id !== auth('sanctum')->id()) {
            return response()->json(['success' => false, 'message' => 'غير مصرح'], 403);
        }

        if (!in_array($ad->status, ['incomplete', 'pending', 'upcoming'])) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن حذف الإعلان في حالته الحالية. الحذف متاح فقط للإعلانات غير المكتملة والمستقبلية.'
            ], 400);
        }

        $ad->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الإعلان بنجاح!'
        ]);
    }

    public function repost(Request $request, $id)
    {
        $ad = Ad::findOrFail($id);
        if ($ad->user_id !== auth('sanctum')->id()) {
            return response()->json(['success' => false, 'message' => 'غير مصرح'], 403);
        }

        // Must be finished or end_date must be in the past
        $isFinished = $ad->status === 'finished' || ($ad->end_date && \Carbon\Carbon::parse($ad->end_date)->isPast());
        if (!$isFinished) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن إعادة نشر الإعلان قبل انتهائه.'
            ], 400);
        }

        // Determine duration
        $duration = 7; // Default 7 days
        if ($ad->start_date && $ad->end_date) {
            $diff = \Carbon\Carbon::parse($ad->start_date)->diffInDays(\Carbon\Carbon::parse($ad->end_date));
            if ($diff > 0) {
                $duration = $diff;
            }
        }

        // Reset start and end date and status
        $ad->update([
            'start_date' => now(),
            'end_date'   => now()->addDays($duration),
            'status'     => 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إعادة نشر الإعلان بنجاح!',
            'data' => $ad
        ]);
    }

    public function repostBulk(Request $request)
    {
        $user = auth('sanctum')->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح'
            ], 401);
        }

        $query = Ad::where('user_id', $user->id);

        if ($request->has('ad_ids') && is_array($request->ad_ids) && count($request->ad_ids) > 0) {
            // Ensure all specified ads belong to this user
            $query->whereIn('id', $request->ad_ids);
        } else {
            // Repost all finished/expired ads of this user
            $query->where(function($q) {
                $q->where('status', 'finished')
                  ->orWhere('end_date', '<', now());
            });
        }

        $adsToRepost = $query->get();

        if ($adsToRepost->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'لم يتم العثور على أي إعلانات منتهية لإعادة نشرها.'
            ], 400);
        }

        $count = 0;
        foreach ($adsToRepost as $ad) {
            // Determine duration
            $duration = 7; // Default 7 days
            if ($ad->start_date && $ad->end_date) {
                $diff = \Carbon\Carbon::parse($ad->start_date)->diffInDays(\Carbon\Carbon::parse($ad->end_date));
                if ($diff > 0) {
                    $duration = $diff;
                }
            }

            $ad->update([
                'start_date' => now(),
                'end_date'   => now()->addDays($duration),
                'status'     => 'active',
            ]);
            $count++;
        }

        return response()->json([
            'success' => true,
            'message' => "تم إعادة نشر {$count} إعلان بنجاح! 🎉",
            'data' => [
                'reposted_count' => $count
            ]
        ]);
    }

    /**
     * Process simulated payment for a featured ad via API.
     */
    public function processPayment(Request $request, $id)
    {
        $ad = Ad::findOrFail($id);

        if ($ad->user_id !== auth('sanctum')->id()) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح'
            ], 403);
        }

        $request->validate([
            'card_name'   => 'required|string',
            'card_number' => 'required|string|min:16|max:19',
            'expiry'      => 'required|string',
            'cvv'         => 'required|string|min:3|max:4',
        ]);

        $ad->update([
            'is_featured' => 1,
            'status'      => 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم الدفع بنجاح وتفعيل الإعلان المميز! 🚀',
            'data' => $ad
        ]);
    }

    /**
     * Process bulk promotion of selected ads via API.
     */
    public function processBulkPromotion(Request $request)
    {
        $request->validate([
            'ad_ids' => 'required|array',
            'ad_ids.*' => 'exists:ads,id',
        ]);
        
        $user = auth('sanctum')->user();
        $adIds = $request->ad_ids;
        
        // Ensure all selected ads belong to the logged-in user
        $adsCount = Ad::where('user_id', $user->id)
            ->whereIn('id', $adIds)
            ->count();
            
        if ($adsCount !== count($adIds)) {
            return response()->json([
                'success' => false,
                'message' => 'بعض الإعلانات المحددة لا تنتمي لحسابك.'
            ], 403);
        }
        
        // Bulk update is_featured = 1
        Ad::whereIn('id', $adIds)->update([
            'is_featured' => 1,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'تم تمييز الإعلانات المحددة بنجاح! 🚀'
        ]);
    }

    /**
     * Safely parse a date string from the request into a specific format for database insertion.
     */
    private function parseSafeDate($dateString, $format = 'Y-m-d')
    {
        if (empty($dateString)) {
            return null;
        }

        try {
            // First, try standard parsing
            return \Carbon\Carbon::parse($dateString)->format($format);
        } catch (\Exception $e) {
            // If standard parsing fails, try specific common formats
            $formats = ['d-m-Y', 'd/m/Y', 'Y-m-d', 'Y/m/d', 'd-m-Y H:i:s', 'd/m/Y H:i:s', 'Y-m-d H:i:s'];
            foreach ($formats as $f) {
                try {
                    return \Carbon\Carbon::createFromFormat($f, $dateString)->format($format);
                } catch (\Exception $ex) {
                    continue;
                }
            }
        }

        return null;
    }
}
