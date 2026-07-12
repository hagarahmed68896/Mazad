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
        $ad = Ad::with(['category', 'user', 'reviews.reviewer', 'city', 'adProperties.property', 'images', 'attachments', 'questions.user'])
                ->withCount('bids')
                ->find($id);

        if (!$ad) {
            return response()->json([
                'success' => false,
                'message' => 'الإعلان غير موجود'
            ], 404);
        }

        // Filter questions: ad owner sees all, others only see visible questions
        $currentUser = auth('sanctum')->user();
        if (!$currentUser || $currentUser->id !== $ad->user_id) {
            $ad->setRelation('questions', $ad->questions->filter(function ($q) {
                return $q->is_visible;
            })->values());

            // Filter hidden properties: ad owner sees all, others only see non-hidden ones
            $ad->setRelation('adProperties', $ad->adProperties->filter(function ($ap) {
                return !$ap->is_hidden;
            })->values());
        }

        // Draft privacy check: If the ad is incomplete, only the owner can view it
        $currentUser = auth('sanctum')->user();
        if ($ad->status === 'incomplete' && (!$currentUser || $currentUser->id !== $ad->user_id)) {
            return response()->json([
                'success' => false,
                'message' => 'الإعلان غير موجود'
            ], 404);
        }

        if (!$currentUser || $currentUser->id !== $ad->user_id) {
            $ad->increment('views_count');
        }

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

        $suggestedServices = \App\Models\Service::where('category_id', $ad->category_id)->where('status', 'active')->limit(4)->get();

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
            ->with([
                'subCategories.pages' => function($q) {
                    $q->has('properties')->with('properties');
                },
                'subCategories.properties' => function($q) {
                    $q->whereNull('category_page_id');
                },
                'pages' => function($q) {
                    $q->has('properties')->with('properties');
                },
                'properties' => function($q) {
                    $q->whereNull('category_page_id'); 
                }
            ])
            ->get();

        $cities = \App\Models\City::orderBy('name')->get();

        return response()->json([
            'success' => true,
            'message' => 'بيانات إنشاء الإعلان',
            'data' => [
                'categories' => $categories,
                'cities' => $cities,
                'max_ad_duration' => (int) (\App\Models\Setting::where('key', 'max_ad_duration')->value('value') ?? 7),
            ]
        ]);
    }

    public function store(Request $request)
    {
        $maxDuration = (int) (\App\Models\Setting::where('key', 'max_ad_duration')->value('value') ?? 7);
        $maxStartDate = \Carbon\Carbon::now()->addDays($maxDuration)->toDateTimeString();
        
        $parsedStartDate = null;
        if ($request->filled('start_date') && is_string($request->start_date)) {
            try {
                $parsedStartDate = \Carbon\Carbon::parse($request->start_date);
            } catch (\Exception $e) {
            }
        }
        
        $maxEndDate = ($parsedStartDate ? $parsedStartDate->copy() : \Carbon\Carbon::now())->addDays($maxDuration)->toDateTimeString();
        $defaultEndDate = ($parsedStartDate ? $parsedStartDate->copy() : \Carbon\Carbon::now())->addDays($maxDuration);

        $isRealEstate = false;
        if ($request->filled('category_id')) {
            $cat = \App\Models\Category::find($request->category_id);
            if ($cat) {
                $isRealEstate = $cat->isRealEstate();
            }
        }

        $rules = [
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'type' => 'required|in:auction,direct_purchase,standard,tender',
            'description' => 'nullable|string',
            'city_id' => ($isRealEstate ? 'nullable' : 'required') . '|exists:regions,id',
            'district_id' => ($isRealEstate ? 'nullable' : 'required') . '|exists:cities,id',
            'address' => 'nullable|string',
            'lat' => ($isRealEstate ? 'required' : 'nullable') . '|numeric|between:-90,90',
            'lng' => ($isRealEstate ? 'required' : 'nullable') . '|numeric|between:-180,180',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:20480',
            'image_urls' => 'nullable|array',
            'image_urls.*' => 'nullable|url',
            'start_date' => 'nullable|date|before_or_equal:' . $maxStartDate,
        ];

        if ($request->filled('start_date')) {
            $rules['end_date'] = 'required|date|after:start_date|before_or_equal:' . $maxEndDate;
        } else {
            $rules['end_date'] = 'required|date|after:now|before_or_equal:' . $maxEndDate;
        }

        $request->validate($rules, [
            'city_id.required' => 'المدينة مطلوبة إجباريًا.',
            'city_id.exists' => 'المدينة المحددة غير صالحة.',
            'district_id.required' => 'الحي مطلوب إجباريًا.',
            'district_id.exists' => 'الحي المحدد غير صالح.',
            'lat.required' => 'موقع الخريطة (خط العرض) مطلوب إجباريًا للعقارات.',
            'lng.required' => 'موقع الخريطة (خط الطول) مطلوب إجباريًا للعقارات.',
            'end_date.after' => 'يجب أن يكون تاريخ الانتهاء بعد تاريخ البدء.',
            'end_date.required' => 'تاريخ الانتهاء مطلوب إجباريًا.',
            'end_date.before_or_equal' => "تاريخ الانتهاء لا يمكن أن يتجاوز الحد الأقصى لمدة عرض الإعلان ($maxDuration يومًا).",
            'start_date.before_or_equal' => "تاريخ البدء لا يمكن أن يتجاوز الحد الأقصى المسموح به ($maxDuration يومًا من الآن).",
            'images.*.max' => 'يجب ألا يتجاوز حجم أي صورة 20 ميجابايت.',
            'images.*.image' => 'يجب أن يكون الملف المرفوع صورة صالحة.',
            'images.*.mimes' => 'يجب أن تكون الصور بصيغة jpeg أو png أو jpg فقط.',
        ]);

        $user = auth('sanctum')->user();
        
        $cityId = $request->district_id;
        if ($isRealEstate) {
            if ($request->filled('district_id')) {
                $cityId = $request->district_id;
            } else {
                $cityId = null;
                if ($request->filled('address')) {
                    $cities = \App\Models\City::all();
                    foreach ($cities as $city) {
                        if (mb_stripos($request->address, $city->name) !== false) {
                            $cityId = $city->id;
                            break;
                        }
                    }
                }

                if (!$cityId && $request->filled('address')) {
                    $regions = \App\Models\Region::all();
                    foreach ($regions as $region) {
                        if (mb_stripos($request->address, $region->name) !== false) {
                            $defaultCity = \App\Models\City::where('region_id', $region->id)->first();
                            if ($defaultCity) {
                                $cityId = $defaultCity->id;
                                break;
                            }
                        }
                    }
                }

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

                if (!$cityId) {
                    $cityId = 1;
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
                }
            }

            // Download images from URLs
            if ($request->has('image_urls') && is_array($request->image_urls)) {
                foreach ($request->image_urls as $index => $url) {
                    if (filter_var($url, FILTER_VALIDATE_URL)) {
                        try {
                            $imageContent = @file_get_contents($url);
                            if ($imageContent) {
                                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                                $mimeType = $finfo->buffer($imageContent);
                                $mimeMap = [
                                    'image/jpeg' => 'jpg',
                                    'image/jpg'  => 'jpg',
                                    'image/png'  => 'png',
                                    'image/gif'  => 'gif',
                                    'image/webp' => 'webp',
                                    'image/svg+xml' => 'svg',
                                    'image/bmp'  => 'bmp',
                                    'image/x-icon' => 'ico',
                                    'image/tiff' => 'tiff',
                                ];
                                $extension = $mimeMap[$mimeType] ?? (pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg');
                                $filename = time() . '_url_' . $index . '_' . uniqid() . '.' . $extension;
                                @file_put_contents(public_path('uploads/ads/' . $filename), $imageContent);
                                $path = 'uploads/ads/' . $filename;
                                $imagePaths[] = $path;
                            }
                        } catch (\Exception $e) {
                            // ignore
                        }
                    }
                }
            }

            if (count($imagePaths) > 0) {
                $mainImage = $imagePaths[0];
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
                'end_date'       => $request->end_date ? $this->parseSafeDate($request->end_date, 'Y-m-d H:i:s') : $defaultEndDate->format('Y-m-d H:i:s'),
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
                    if (is_array($value)) {
                        $value = implode(', ', array_filter($value, fn($v) => $v !== null && $v !== ''));
                    }
                    if ($value !== null && $value !== '') {
                        if (\App\Models\Property::where('id', $propertyId)->exists()) {
                            $isHidden = false;
                            if (isset($request->properties_hidden[$propertyId]) && $request->properties_hidden[$propertyId] == '1') {
                                $isHidden = true;
                            }
                            AdProperty::create([
                                'ad_id' => $ad->id, 
                                'property_id' => $propertyId, 
                                'value' => $value,
                                'is_hidden' => $isHidden
                            ]);
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

        $maxDuration = (int) (\App\Models\Setting::where('key', 'max_ad_duration')->value('value') ?? 7);
        $startDateStr = $request->filled('start_date') ? $request->start_date : $ad->start_date;
        
        $parsedStartDate = null;
        if ($startDateStr && is_string($startDateStr)) {
            try {
                $parsedStartDate = \Carbon\Carbon::parse($startDateStr);
            } catch (\Exception $e) {
            }
        }
        
        $maxStartDate = \Carbon\Carbon::now()->addDays($maxDuration)->toDateTimeString();
        $maxEndDate = ($parsedStartDate ? $parsedStartDate->copy() : \Carbon\Carbon::now())->addDays($maxDuration)->toDateTimeString();

        $isRealEstate = false;
        $catId = $request->category_id ?? $ad->category_id;
        if ($catId) {
            $cat = \App\Models\Category::find($catId);
            if ($cat) {
                $isRealEstate = $cat->isRealEstate();
            }
        }

        $request->validate([
            'address' => 'sometimes|nullable|string',
            'city_id' => ($isRealEstate ? 'sometimes|nullable' : 'sometimes|required') . '|exists:regions,id',
            'district_id' => ($isRealEstate ? 'sometimes|nullable' : 'sometimes|required') . '|exists:cities,id',
            'lat' => ($isRealEstate ? 'sometimes|required' : 'sometimes|nullable') . '|numeric|between:-90,90',
            'lng' => ($isRealEstate ? 'sometimes|required' : 'sometimes|nullable') . '|numeric|between:-180,180',
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'image_urls' => 'sometimes|nullable|array',
            'image_urls.*' => 'sometimes|nullable|url',
            'start_date' => 'sometimes|nullable|date|before_or_equal:' . $maxStartDate,
            'end_date' => 'sometimes|required|date|after:now|before_or_equal:' . $maxEndDate,
        ], [
            'city_id.required' => 'المدينة مطلوبة إجباريًا.',
            'city_id.exists' => 'المدينة المحددة غير صالحة.',
            'district_id.required' => 'الحي مطلوب إجباريًا.',
            'district_id.exists' => 'الحي المحدد غير صالح.',
            'lat.required' => 'موقع الخريطة (خط العرض) مطلوب إجباريًا للعقارات.',
            'lng.required' => 'موقع الخريطة (خط الطول) مطلوب إجباريًا للعقارات.',
            'end_date.after' => 'يجب أن يكون تاريخ الانتهاء بعد تاريخ البدء.',
            'end_date.required' => 'تاريخ الانتهاء مطلوب إجباريًا.',
            'end_date.before_or_equal' => "تاريخ الانتهاء لا يمكن أن يتجاوز الحد الأقصى لمدة عرض الإعلان ($maxDuration يومًا).",
            'start_date.before_or_equal' => "تاريخ البدء لا يمكن أن يتجاوز الحد الأقصى المسموح به ($maxDuration يومًا من الآن).",
        ]);

        $cityId = $request->district_id ?? $ad->city_id;
        if ($isRealEstate) {
            if ($request->filled('district_id')) {
                $cityId = $request->district_id;
            } elseif ($request->filled('address') || $request->filled('lat') || $request->filled('lng')) {
                $resolvedId = null;
                if ($request->filled('address')) {
                    $cities = \App\Models\City::all();
                    foreach ($cities as $city) {
                        if (mb_stripos($request->address, $city->name) !== false) {
                            $resolvedId = $city->id;
                            break;
                        }
                    }
                }

                if (!$resolvedId && $request->filled('address')) {
                    $regions = \App\Models\Region::all();
                    foreach ($regions as $region) {
                        if (mb_stripos($request->address, $region->name) !== false) {
                            $defaultCity = \App\Models\City::where('region_id', $region->id)->first();
                            if ($defaultCity) {
                                $resolvedId = $defaultCity->id;
                                break;
                            }
                        }
                    }
                }

                if (!$resolvedId && $request->filled('lat') && $request->filled('lng')) {
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
                            $resolvedId = $matchedCity->id;
                        }
                    }
                }

                if ($resolvedId) {
                    $cityId = $resolvedId;
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
            $newImagePaths = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $file) {
                    $filename = time() . '_edit_' . $index . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('uploads/ads'), $filename);
                    $path = 'uploads/ads/' . $filename;
                    $newImagePaths[] = $path;
                }
            }

            // Download images from URLs
            if ($request->has('image_urls') && is_array($request->image_urls)) {
                foreach ($request->image_urls as $index => $url) {
                    if (filter_var($url, FILTER_VALIDATE_URL)) {
                        try {
                            $imageContent = @file_get_contents($url);
                            if ($imageContent) {
                                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                                $mimeType = $finfo->buffer($imageContent);
                                $mimeMap = [
                                    'image/jpeg' => 'jpg',
                                    'image/jpg'  => 'jpg',
                                    'image/png'  => 'png',
                                    'image/gif'  => 'gif',
                                    'image/webp' => 'webp',
                                    'image/svg+xml' => 'svg',
                                    'image/bmp'  => 'bmp',
                                    'image/x-icon' => 'ico',
                                    'image/tiff' => 'tiff',
                                ];
                                $extension = $mimeMap[$mimeType] ?? (pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg');
                                $filename = time() . '_url_edit_' . $index . '_' . uniqid() . '.' . $extension;
                                @file_put_contents(public_path('uploads/ads/' . $filename), $imageContent);
                                $path = 'uploads/ads/' . $filename;
                                $newImagePaths[] = $path;
                            }
                        } catch (\Exception $e) {
                            // ignore
                        }
                    }
                }
            }

            if (count($newImagePaths) > 0) {
                // Delete old images first if new ones are uploaded
                $ad->images()->delete();
                foreach ($newImagePaths as $index => $path) {
                    if ($index === 0) {
                        $mainImagePath = $path;
                    }
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
                    if (is_array($value)) {
                        $value = implode(', ', array_filter($value, fn($v) => $v !== null && $v !== ''));
                    }
                    if ($value !== null && $value !== '') {
                        if (\App\Models\Property::where('id', $propertyId)->exists()) {
                            $isHidden = false;
                            if (isset($request->properties_hidden[$propertyId]) && $request->properties_hidden[$propertyId] == '1') {
                                $isHidden = true;
                            }
                            AdProperty::updateOrCreate(
                                ['ad_id' => $ad->id, 'property_id' => $propertyId],
                                [
                                    'value' => $value,
                                    'is_hidden' => $isHidden
                                ]
                            );
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
