<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Ad;
use App\Models\AdImage;
use App\Models\AdProperty;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdController extends Controller
{
    public function create(Request $request)
    {
        if (Auth::check() && Auth::user()->account_type === 'partner') {
            $categories = Auth::user()->resolvedInterests;
            if ($categories->isEmpty()) {
                $categories = Category::whereNull('parent_id')->get();
            }
            return view('ads.partner-create', compact('categories'));
        }

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
        if (Auth::check() && Auth::user()->account_type === 'partner') {
            $request->validate([
                'title'       => 'required|string|max:255',
                'description' => 'required|string',
                'category_id' => 'required|exists:categories,id',
                'price'       => 'required|numeric|min:0',
                'phone'       => 'required|string',
                'email'       => 'required|email|max:255',
                'image'       => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:20480',
            ], [
                'title.required'       => 'عنوان الإعلان مطلوب.',
                'description.required' => 'وصف الإعلان مطلوب.',
                'category_id.required' => 'التصنيف مطلوب.',
                'category_id.exists'   => 'التصنيف المختار غير صالح.',
                'price.required'       => 'سعر الخدمة مطلوب.',
                'price.numeric'        => 'يجب أن يكون السعر رقماً صالحاً.',
                'phone.required'       => 'رقم الجوال مطلوب.',
                'email.required'       => 'البريد الإلكتروني مطلوب.',
                'email.email'          => 'البريد الإلكتروني غير صالح.',
                'image.required'       => 'صورة الإعلان مطلوبة.',
                'image.image'          => 'يجب أن يكون الملف صورة صالحة.',
                'image.max'            => 'يجب ألا يتجاوز حجم الصورة 20 ميجابايت.',
            ]);

            $user = Auth::user();
            $mainImage = null;

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/ads'), $filename);
                $mainImage = 'uploads/ads/' . $filename;
            }

            DB::beginTransaction();
            try {
                $ad = Ad::create([
                    'user_id'     => $user->id,
                    'category_id' => $request->category_id,
                    'city_id'     => $user->city_id ?? 1,
                    'title'       => $request->title,
                    'description' => $request->description,
                    'type'        => 'direct_purchase',
                    'price'       => $request->price,
                    'quantity'    => 1,
                    'status'      => 'active',
                    'end_date'    => now()->addYear(),
                    'image'       => $mainImage,
                ]);

                if ($mainImage) {
                    AdImage::create([
                        'ad_id' => $ad->id,
                        'image' => $mainImage,
                    ]);
                }

                // Update contact details on user model if changed
                $user->update([
                    'phone' => $request->phone,
                    'email' => $request->email,
                ]);

                DB::commit();

                return redirect()->route('ads.my')
                    ->with('success', 'تم إضافة إعلان الخدمة بنجاح! 🎉');

            } catch (\Exception $e) {
                DB::rollBack();
                return back()->withInput()->with('error', 'حدث خطأ أثناء حفظ الإعلان: ' . $e->getMessage());
            }
        }

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
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'category_id'  => 'required|exists:categories,id',
            'city_id'      => 'required|exists:regions,id',
            'district_id'  => 'required|exists:cities,id',
            'address'      => 'nullable|string',
            'lat'          => ($isRealEstate ? 'required' : 'nullable') . '|numeric|between:-90,90',
            'lng'          => ($isRealEstate ? 'required' : 'nullable') . '|numeric|between:-180,180',
            'images.*'     => 'nullable|image|mimes:jpeg,png,jpg|max:20480',
            'image_urls'   => 'nullable|array',
            'image_urls.*' => 'nullable|url',
            'start_date'   => 'nullable|date|before_or_equal:' . $maxStartDate,
            'enfaz_license_number' => 'nullable|string|max:255',
            'enfaz_brochure' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:20480',
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

        $user = Auth::user();
        $cityId = $request->district_id;

        // Determine price & deposit based on ad_type
        $adClass = $request->ad_type ?? $request->type ?? 'direct_purchase';
        
        $price = 0;
        $depositAmount = 0;
        $bidIncrement = 0;

        if ($adClass === 'auction' || $adClass === 'electronic_auction' || $adClass === 'hybrid_auction') {
            $price = $request->auction_start_price ?? 0;
            $depositAmount = $request->auction_deposit ?? 0;
            $bidIncrement = $request->bid_increment ?? 0;
            $adType = ($request->auction_type === 'hybrid') ? 'hybrid_auction' : 'electronic_auction';
        } elseif ($adClass === 'tender') {
            $price = $request->tender_price ?? 0;
            $depositAmount = 0; // Tenders usually don't have upfront deposit in the same way, or it's different
            $bidIncrement = $request->tender_bid_increment ?? 0;
            $adType = 'tender';
        } else {
            $price = $request->sale_price ?? 0;
            $depositAmount = $request->direct_deposit ?? 0;
            $adType = 'direct_purchase';
        }

        DB::beginTransaction();
        try {
            // Upload images
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
                                // Detect the extension using FILEINFO
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
                            // ignore download failures
                        }
                    }
                }
            }

            // Upload Enfaz Brochure
            $enfazBrochurePath = null;
            if ($request->is_enfaz && $request->hasFile('enfaz_brochure')) {
                $file = $request->file('enfaz_brochure');
                $filename = time() . '_brochure.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/ads/brochures'), $filename);
                $enfazBrochurePath = 'uploads/ads/brochures/' . $filename;
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
                'inspection_info'     => $request->inspection_info,
                'inspection_date'     => $this->parseSafeDate($request->inspection_date, 'Y-m-d'),
                'inspection_end_date' => $this->parseSafeDate($request->inspection_end_date, 'Y-m-d'),
                'deposit_amount' => $depositAmount,
                'price'          => $price,
                'status'         => $request->status === 'incomplete' ? 'incomplete' : (($request->start_date && $this->parseSafeDate($request->start_date, 'Y-m-d H:i:s') && \Carbon\Carbon::parse($this->parseSafeDate($request->start_date, 'Y-m-d H:i:s'))->isFuture()) ? 'upcoming' : 'active'),
                'start_date'     => $this->parseSafeDate($request->start_date, 'Y-m-d H:i:s'),
                'end_date'       => $request->end_date ? $this->parseSafeDate($request->end_date, 'Y-m-d H:i:s') : $defaultEndDate->format('Y-m-d H:i:s'),
                'image'          => $mainImage,
                'is_featured'    => $request->input('is_featured', 0),
                'is_enfaz'       => $request->is_enfaz ?? 0,
                'enfaz_license_number' => $request->is_enfaz ? $request->enfaz_license_number : null,
                'enfaz_brochure'       => $request->is_enfaz ? $enfazBrochurePath : null,
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

            // Save additional images
            foreach ($imagePaths as $path) {
                AdImage::create([
                    'ad_id' => $ad->id,
                    'image' => $path,
                ]);
            }

            // Save dynamic properties
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
                                'ad_id'       => $ad->id,
                                'property_id' => $propertyId,
                                'value'       => $value,
                                'is_hidden'   => $isHidden,
                            ]);
                        }
                    }
                }
            }

            // Save local attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $index => $file) {
                    $originalName = $file->getClientOriginalName();
                    $size = number_format($file->getSize() / 1048576, 2) . 'MB';
                    $type = $file->getClientOriginalExtension();
                    $filename = time() . '_attach_' . $index . '_' . uniqid() . '.' . $type;
                    $file->move(public_path('uploads/attachments'), $filename);
                    $path = 'uploads/attachments/' . $filename;
                    
                    \App\Models\AdAttachment::create([
                        'ad_id' => $ad->id,
                        'file_path' => $path,
                        'file_name' => pathinfo($originalName, PATHINFO_FILENAME),
                        'file_size' => $size,
                        'file_type' => $type
                    ]);
                }
            }

            // Save attachment links (Figma, Drive, etc.)
            if ($request->has('attachment_links') && is_array($request->attachment_links)) {
                foreach ($request->attachment_links as $index => $url) {
                    if (filter_var($url, FILTER_VALIDATE_URL)) {
                        $host = parse_url($url, PHP_URL_HOST);
                        $name = $host ? str_replace('www.', '', $host) : ('رابط خارجي ' . ($index + 1));
                        
                        \App\Models\AdAttachment::create([
                            'ad_id' => $ad->id,
                            'file_path' => $url,
                            'file_name' => $name,
                            'file_size' => '0B',
                            'file_type' => 'link'
                        ]);
                    }
                }
            }

            DB::commit();

            // If featured → process payment directly if card details were provided in the wizard
            if ($ad->is_featured) {
                if ($request->filled('card_number')) {
                    $ad->update([
                        'is_featured' => 1,
                        'status'      => 'active',
                    ]);
                    return redirect()->route('ads.show', $ad->id)
                        ->with('success', 'تم الدفع ونشر الإعلان المميز بنجاح! 🚀');
                }

                return redirect()->route('ads.payment', $ad->id)
                    ->with('info', 'تم إنشاء إعلانك! أكمل عملية الدفع لتفعيل الإعلان المميز.');
            }

            return redirect()->route('ads.show', $ad->id)
                ->with('success', 'تم نشر إعلانك بنجاح!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'حدث خطأ أثناء حفظ الإعلان: ' . $e->getMessage());
        }
    }

    public function showPayment(Ad $ad)
    {
        // Only the owner can pay
        if ($ad->user_id !== Auth::id()) {
            abort(403);
        }

        $settings = Setting::all()->pluck('value', 'key');
        $promotionPrice = (float) ($settings['promotion_price'] ?? 80);
        $vatRate        = (float) ($settings['vat_rate'] ?? 0);
        $vatAmount      = round($promotionPrice * $vatRate / 100, 2);
        $total          = round($promotionPrice + $vatAmount, 2);

        return view('ads.payment', compact('ad', 'promotionPrice', 'vatRate', 'vatAmount', 'total', 'settings'));
    }

    public function processPayment(Request $request, Ad $ad)
    {
        if ($ad->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'card_name'   => 'required|string',
            'card_number' => 'required|string|min:16|max:19',
            'expiry'      => 'required|string',
            'cvv'         => 'required|string|min:3|max:4',
        ]);

        // TODO: Integrate real payment gateway here (e.g. MyFatoorah / Stripe)
        // For now, simulate success and mark the ad as featured+active
        $ad->update([
            'is_featured' => 1,
            'status'      => 'active',
        ]);

        return redirect()->route('ads.show', $ad->id)
            ->with('success', 'تم الدفع بنجاح! إعلانك مميز الآن وسيظهر في المقدمة.');
    }

    public function edit(Ad $ad)
    {
        if ($ad->user_id !== Auth::id()) {
            abort(403);
        }

        if (Auth::check() && Auth::user()->account_type === 'partner') {
            $categories = Auth::user()->resolvedInterests;
            if ($categories->isEmpty()) {
                $categories = Category::whereNull('parent_id')->get();
            }
            return view('ads.partner-edit', compact('ad', 'categories'));
        }

        $category = Category::with(['pages' => function($q) {
            $q->with('properties');
        }, 'properties' => function($q) {
            $q->whereNull('category_page_id'); 
        }])->findOrFail($ad->category_id);

        $cities = \App\Models\City::orderBy('name')->get();
        $type = $ad->type;
        $adProperties = $ad->adProperties->pluck('value', 'property_id');

        return view('ads.edit', compact('ad', 'category', 'cities', 'type', 'adProperties'));
    }

    public function update(Request $request, Ad $ad)
    {
        \Log::debug('Ad Update Request ' . $ad->id . ':', $request->all());
        if ($ad->user_id !== Auth::id()) {
            abort(403);
        }

        if (Auth::check() && Auth::user()->account_type === 'partner') {
            $request->validate([
                'title'       => 'required|string|max:255',
                'description' => 'required|string',
                'category_id' => 'required|exists:categories,id',
                'price'       => 'required|numeric|min:0',
                'phone'       => 'required|string',
                'email'       => 'required|email|max:255',
                'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:20480',
            ], [
                'title.required'       => 'عنوان الإعلان مطلوب.',
                'description.required' => 'وصف الإعلان مطلوب.',
                'category_id.required' => 'التصنيف مطلوب.',
                'category_id.exists'   => 'التصنيف المختار غير صالح.',
                'price.required'       => 'سعر الخدمة مطلوب.',
                'price.numeric'        => 'يجب أن يكون السعر رقماً صالحاً.',
                'phone.required'       => 'رقم الجوال مطلوب.',
                'email.required'       => 'البريد الإلكتروني مطلوب.',
                'email.email'          => 'البريد الإلكتروني غير صالح.',
                'image.image'          => 'يجب أن يكون الملف صورة صالحة.',
                'image.max'            => 'يجب ألا يتجاوز حجم الصورة 20 ميجابايت.',
            ]);

            $user = Auth::user();
            $mainImage = $ad->image;

            if ($request->hasFile('image')) {
                // Delete old image if it exists
                if ($ad->image && !str_starts_with($ad->image, 'http')) {
                    @unlink(public_path($ad->image));
                }
                
                $file = $request->file('image');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/ads'), $filename);
                $mainImage = 'uploads/ads/' . $filename;
            }

            DB::beginTransaction();
            try {
                $ad->update([
                    'title'       => $request->title,
                    'description' => $request->description,
                    'category_id' => $request->category_id,
                    'price'       => $request->price,
                    'image'       => $mainImage,
                ]);

                // Update in AdImage if there is a main image
                if ($request->hasFile('image')) {
                    // Delete old gallery images linked to this service
                    $ad->images()->delete();

                    AdImage::create([
                        'ad_id' => $ad->id,
                        'image' => $mainImage,
                    ]);
                }

                // Update contact details on user model if changed
                $user->update([
                    'phone' => $request->phone,
                    'email' => $request->email,
                ]);

                DB::commit();

                return redirect()->route('ads.my')
                    ->with('success', 'تم تعديل الخدمة بنجاح! 🎉');

            } catch (\Exception $e) {
                DB::rollBack();
                return back()->withInput()->with('error', 'حدث خطأ أثناء حفظ التعديلات: ' . $e->getMessage());
            }
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

        $rules = [
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'city_id'      => 'required|exists:regions,id',
            'district_id'  => 'required|exists:cities,id',
            'address'      => 'nullable|string',
            'lat'          => ($isRealEstate ? 'required' : 'nullable') . '|numeric|between:-90,90',
            'lng'          => ($isRealEstate ? 'required' : 'nullable') . '|numeric|between:-180,180',
            'images.*'     => 'nullable|image|mimes:jpeg,png,jpg|max:20480',
            'image_urls'   => 'nullable|array',
            'image_urls.*' => 'nullable|url',
            'start_date'   => 'nullable|date|before_or_equal:' . $maxStartDate,
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

        $cityId = $request->district_id ?? $ad->city_id;

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
            // Update main image if new ones are uploaded
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
                                // Detect the extension using FILEINFO
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

            foreach ($newImagePaths as $path) {
                if (!$mainImagePath) {
                    $mainImagePath = $path;
                } else {
                    AdImage::create([
                        'ad_id' => $ad->id,
                        'image' => $path,
                    ]);
                }
            }

            // Upload new Enfaz Brochure if provided
            $enfazBrochurePath = $ad->enfaz_brochure;
            if ($request->is_enfaz && $request->hasFile('enfaz_brochure')) {
                // Delete old brochure file if exists
                if ($ad->enfaz_brochure && file_exists(public_path($ad->enfaz_brochure))) {
                    @unlink(public_path($ad->enfaz_brochure));
                }
                $file = $request->file('enfaz_brochure');
                $filename = time() . '_brochure.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/ads/brochures'), $filename);
                $enfazBrochurePath = 'uploads/ads/brochures/' . $filename;
            }

            $ad->update([
                'title'          => $request->title,
                'description'    => $request->description,
                'city_id'        => $cityId,
                'image'          => $mainImagePath,
                'address'        => $request->address,
                'lat'            => $request->lat,
                'lng'            => $request->lng,
                'inspection_info'     => $request->inspection_info,
                'inspection_date'     => $this->parseSafeDate($request->inspection_date, 'Y-m-d'),
                'inspection_end_date' => $this->parseSafeDate($request->inspection_end_date, 'Y-m-d'),
                'deposit_amount' => $depositAmount,
                'price'          => $price,
                'bid_increment'  => $bidIncrement,
                'status'         => $request->status === 'incomplete' ? 'incomplete' : (($request->start_date && $this->parseSafeDate($request->start_date, 'Y-m-d H:i:s') && \Carbon\Carbon::parse($this->parseSafeDate($request->start_date, 'Y-m-d H:i:s'))->isFuture()) ? 'upcoming' : 'active'),
                'end_date'       => $request->end_date ? $this->parseSafeDate($request->end_date, 'Y-m-d H:i:s') : $ad->end_date,
                'is_featured'    => $request->has('is_featured') ? 1 : 0,
                'is_enfaz'       => $request->is_enfaz ?? 0,
                'enfaz_license_number' => $request->is_enfaz ? ($request->enfaz_license_number ?? $ad->enfaz_license_number) : null,
                'enfaz_brochure'       => $request->is_enfaz ? $enfazBrochurePath : null,
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

            // Update dynamic properties
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

            // Save attachments if new ones are uploaded/sent
            $hasNewAttachments = $request->hasFile('attachments') || ($request->has('attachment_links') && is_array($request->attachment_links));
            
            if ($hasNewAttachments) {
                // Delete old attachments first
                $ad->attachments()->delete();

                // Save local attachments
                if ($request->hasFile('attachments')) {
                    foreach ($request->file('attachments') as $index => $file) {
                        $originalName = $file->getClientOriginalName();
                        $size = number_format($file->getSize() / 1048576, 2) . 'MB';
                        $type = $file->getClientOriginalExtension();
                        $filename = time() . '_attach_edit_' . $index . '_' . uniqid() . '.' . $type;
                        $file->move(public_path('uploads/attachments'), $filename);
                        $path = 'uploads/attachments/' . $filename;
                        
                        \App\Models\AdAttachment::create([
                            'ad_id' => $ad->id,
                            'file_path' => $path,
                            'file_name' => pathinfo($originalName, PATHINFO_FILENAME),
                            'file_size' => $size,
                            'file_type' => $type
                        ]);
                    }
                }

                // Save attachment links (Figma, Drive, etc.)
                if ($request->has('attachment_links') && is_array($request->attachment_links)) {
                    foreach ($request->attachment_links as $index => $url) {
                        if (filter_var($url, FILTER_VALIDATE_URL)) {
                            $host = parse_url($url, PHP_URL_HOST);
                            $name = $host ? str_replace('www.', '', $host) : ('رابط خارجي ' . ($index + 1));
                            
                            \App\Models\AdAttachment::create([
                                'ad_id' => $ad->id,
                                'file_path' => $url,
                                'file_name' => $name,
                                'file_size' => '0B',
                                'file_type' => 'link'
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            // Redirect to payment ONLY if is_featured was just turned on (from 0 to 1)
            if ($ad->wasChanged('is_featured') && $ad->is_featured) {
                return redirect()->route('ads.payment', $ad->id)
                    ->with('info', 'أكمل عملية الدفع لتفعيل التمييز.');
            }

            return redirect()->route('ads.show', $ad->id)->with('success', 'تم تحديث الإعلان بنجاح!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'حدث خطأ أثناء تحديث الإعلان: ' . $e->getMessage());
        }
    }

    public function destroy(Ad $ad)
    {
        if ($ad->user_id !== Auth::id()) {
            abort(403);
        }

        if (!in_array($ad->status, ['incomplete', 'pending', 'upcoming'])) {
            return redirect()->back()->with('error', 'لا يمكن حذف الإعلان في حالته الحالية. الحذف متاح فقط للإعلانات غير المكتملة والمستقبلية.');
        }

        $ad->delete();
        return redirect()->route('users.profile', Auth::id())->with('success', 'تم حذف الإعلان بنجاح!');
    }

    public function myAds(Request $request)
    {
        $status = $request->query('status', 'all'); // 'all', 'incomplete', 'active', 'upcoming', 'finished'
        $user = Auth::user();
        
        if ($user->account_type === 'partner') {
            $query = \App\Models\Service::where('user_id', $user->id);
            
            if ($status === 'active') {
                $query->where('status', 'active');
            } elseif ($status === 'pending') {
                $query->where('status', 'pending');
            } elseif ($status === 'rejected') {
                $query->where('status', 'rejected');
            }
            
            // 2. Search Keyword Filter
            if ($request->has('q') && !empty($request->q)) {
                $query->where(function($q) use ($request) {
                    $q->where('title', 'like', '%' . $request->q . '%')
                      ->orWhere('description', 'like', '%' . $request->q . '%');
                });
            }

            // 2.1 Selected Services Filter
            if ($request->has('services') && is_array($request->services) && !empty($request->services)) {
                $query->whereIn('title', $request->services);
            }
            
            // 6. Sorting
            $sort = $request->get('sort', 'newest');
            if ($sort === 'price_high') {
                $query->orderBy('price', 'desc');
            } elseif ($sort === 'price_low') {
                $query->orderBy('price', 'asc');
            } else {
                $query->orderBy('created_at', 'desc');
            }
            
            $ads = $query->paginate(12)->appends($request->query());
            
            $cities = \App\Models\City::orderBy('name')->get();
            $categories = \App\Models\Category::whereNull('parent_id')->with('subCategories')->get();
            $activeCategory = null;
            
            $banners = \App\Models\Banner::where('status', true)->where('for_user_type', 'partner')->get();
            $filterServices = \App\Models\Service::where('user_id', $user->id)
                ->pluck('title')
                ->unique()
                ->toArray();
                
            return view('ads.partner-ads', compact('ads', 'status', 'cities', 'categories', 'activeCategory', 'banners', 'filterServices'));
        }

        $query = Ad::where('user_id', $user->id);
        
        // 1. Tab Status Filter
        if ($status === 'incomplete') {
            $query->whereIn('status', ['pending', 'incomplete']);
        } elseif ($status === 'active') {
            $query->where('status', 'active');
        } elseif ($status === 'upcoming') {
            $query->where('status', 'upcoming');
        } elseif ($status === 'finished') {
            $query->where('status', 'finished');
        }
        
        // 2. Search Keyword Filter
        if ($request->has('q') && !empty($request->q)) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->q . '%')
                  ->orWhere('description', 'like', '%' . $request->q . '%');
            });
        }

        // 2.1 Selected Services Filter (For Partners)
        if ($request->has('services') && is_array($request->services) && !empty($request->services)) {
            $query->whereIn('title', $request->services);
        }
        
        // 3. City Filter
        if ($request->has('city') && !empty($request->city)) {
            $query->where('city_id', $request->city);
        }
        
        // 4. Category Filter
        $activeCategory = null;
        if ($request->has('category') && !empty($request->category)) {
            $activeCategory = \App\Models\Category::with('properties')->find($request->category);
            $query->where('category_id', $request->category);
        }

        // 4.1. Dynamic Category Properties Filter
        if ($activeCategory && $request->has('props') && is_array($request->props)) {
            foreach ($request->props as $propId => $val) {
                if ($val !== null && $val !== '') {
                    $query->whereHas('adProperties', function($q) use ($propId, $val) {
                        $q->where('property_id', $propId);
                        if (is_array($val)) {
                            $q->whereIn('value', $val);
                        } else {
                            $q->where('value', $val);
                        }
                    });
                }
            }
        }
        
        // 5. Ad Type Filter
        if ($request->has('type') && !empty($request->type)) {
            if ($request->type === 'auction') {
                $query->whereIn('type', ['electronic_auction', 'hybrid_auction']);
            } elseif ($request->type === 'direct_purchase') {
                $query->where('type', 'direct_purchase');
            } else {
                $query->where('type', $request->type);
            }
        }
        
        // 6. Sorting
        $sort = $request->get('sort', 'newest');
        if ($sort === 'price_high') {
            $query->orderBy('price', 'desc');
        } elseif ($sort === 'price_low') {
            $query->orderBy('price', 'asc');
        } else {
            $query->orderBy('created_at', 'desc');
        }
        
        $ads = $query->paginate(12)->appends($request->query());
        
        $cities = \App\Models\City::orderBy('name')->get();
        $categories = \App\Models\Category::whereNull('parent_id')->with('subCategories')->get();
        
        return view('ads.my-ads', compact('ads', 'status', 'cities', 'categories', 'activeCategory'));
    }

    /**
     * Set a gallery image as the main ad image via AJAX.
     */
    public function setMainImage(Request $request, Ad $ad)
    {
        if ($ad->user_id !== Auth::id()) abort(403);

        $request->validate(['image_id' => 'required|exists:ad_images,id']);

        $adImage = AdImage::findOrFail($request->image_id);
        if ($adImage->ad_id !== $ad->id) abort(403);

        // Swap: old main → gallery, selected → main
        $oldMain = $ad->image;
        $newMain = $adImage->image;

        // Remove selected from gallery
        $adImage->delete();

        // Push old main back to gallery if it exists
        if ($oldMain) {
            AdImage::create(['ad_id' => $ad->id, 'image' => $oldMain]);
        }

        $ad->update(['image' => $newMain]);

        return response()->json(['success' => true, 'new_main' => '/' . ltrim($newMain, '/')]);
    }

    /**
     * Delete a gallery (AdImage) image via AJAX.
     */
    public function deleteImage(Request $request, AdImage $adImage)
    {
        $ad = Ad::findOrFail($adImage->ad_id);
        if ($ad->user_id !== Auth::id()) abort(403);

        // Optionally delete file from disk
        $filePath = public_path($adImage->image);
        if (file_exists($filePath) && !str_starts_with($adImage->image, 'http')) {
            @unlink($filePath);
        }

        $adImage->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Delete the main ad image via AJAX and promote first gallery image if available.
     */
    public function deleteMainImage(Request $request, Ad $ad)
    {
        if ($ad->user_id !== Auth::id()) abort(403);

        // Delete old main file
        if ($ad->image && !str_starts_with($ad->image, 'http')) {
            @unlink(public_path($ad->image));
        }

        // Promote first gallery image to main
        $firstGallery = $ad->images()->first();
        if ($firstGallery) {
            $ad->update(['image' => $firstGallery->image]);
            $firstGallery->delete();
            $newMain = '/' . ltrim($firstGallery->image, '/');
        } else {
            $ad->update(['image' => null]);
            $newMain = null;
        }

        return response()->json(['success' => true, 'new_main' => $newMain]);
    }

    /**
     * Repost a finished ad.
     */
    public function repost(Request $request, Ad $ad)
    {
        if ($ad->user_id !== Auth::id()) {
            abort(403);
        }

        // Must be finished or end_date must be in the past
        $isFinished = $ad->status === 'finished' || ($ad->end_date && \Carbon\Carbon::parse($ad->end_date)->isPast());
        if (!$isFinished) {
            return redirect()->back()->with('error', 'لا يمكن إعادة نشر الإعلان قبل انتهائه.');
        }

        // Block republish if there are pending_payment orders — buyer must pay first
        $pendingOrder = $ad->orders()->where('status', 'pending_payment')->first();
        if ($pendingOrder) {
            return redirect()->back()->with('error', 'لا يمكن إعادة نشر الإعلان لأن هناك طلب شراء قيد الدفع (رقم الطلب: #' . $pendingOrder->order_number . '). يرجى انتظار إتمام الدفع أو إلغاء الطلب أولاً.');
        }

        // Determine duration
        $duration = 7; // Default 7 days
        if ($ad->start_date && $ad->end_date) {
            $diff = \Carbon\Carbon::parse($ad->start_date)->diffInDays(\Carbon\Carbon::parse($ad->end_date));
            if ($diff > 0) {
                $duration = $diff;
            }
        }

        // Reset start and end date
        $ad->update([
            'start_date' => now(),
            'end_date'   => now()->addDays($duration),
            'status'     => 'active', // Republish as active
        ]);

        return redirect()->back()->with('success', 'تم إعادة نشر الإعلان بنجاح!');
    }

    /**
     * Show promote page with active and upcoming user ads that are not featured.
     */
    public function promotePage(Request $request)
    {
        $user = Auth::user();
        
        // Fetch active and upcoming ads that are not featured
        $ads = Ad::where('user_id', $user->id)
            ->where('is_featured', 0)
            ->whereIn('status', ['active', 'upcoming'])
            ->get();
            
        $settings = Setting::all()->pluck('value', 'key');
        $promotionPrice = (float) ($settings['promotion_price'] ?? 80);
        
        return view('ads.promote', compact('ads', 'promotionPrice'));
    }

    /**
     * Process bulk promotion of selected ads.
     */
    public function processBulkPromotion(Request $request)
    {
        $request->validate([
            'ad_ids' => 'required|array',
            'ad_ids.*' => 'exists:ads,id',
        ]);
        
        $user = Auth::user();
        $adIds = $request->ad_ids;
        
        // Ensure all selected ads belong to the logged-in user
        $adsCount = Ad::where('user_id', $user->id)
            ->whereIn('id', $adIds)
            ->count();
            
        if ($adsCount !== count($adIds)) {
            return back()->with('error', 'حدث خطأ: بعض الإعلانات المحددة لا تنتمي لحسابك.');
        }
        
        // Bulk update is_featured = 1
        Ad::whereIn('id', $adIds)->update([
            'is_featured' => 1,
        ]);
        
        return redirect()->route('ads.my')->with('success', 'تم تمييز الإعلانات المحددة بنجاح! 🚀');
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

    }

    /**
     * Web Partner Service Management Methods
     */
    public function partnerCreateService()
    {
        $categories = Auth::user()->resolvedInterests;
        if ($categories->isEmpty()) {
            $categories = Category::whereNull('parent_id')->get();
        }
        return view('ads.partner-create', compact('categories'));
    }

    public function partnerStoreService(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'price'       => 'required|numeric|min:0',
            'phone'       => 'required|string',
            'email'       => 'required|email|max:255',
            'image'       => 'required_without:image_url|nullable|image|mimes:jpeg,png,jpg,gif,webp|max:20480',
            'image_url'   => 'required_without:image|nullable|url',
        ], [
            'title.required'       => 'عنوان الخدمة مطلوب.',
            'description.required' => 'وصف الخدمة مطلوب.',
            'category_id.required' => 'التصنيف مطلوب.',
            'category_id.exists'   => 'التصنيف المختار غير صالح.',
            'price.required'       => 'سعر الخدمة مطلوب.',
            'price.numeric'        => 'يجب أن يكون السعر رقماً صالحاً.',
            'phone.required'       => 'رقم الجوال مطلوب.',
            'email.required'       => 'البريد الإلكتروني مطلوب.',
            'email.email'          => 'البريد الإلكتروني غير صالح.',
            'image.required_without' => 'صورة الخدمة مطلوبة (ملف أو رابط).',
            'image.image'          => 'يجب أن يكون الملف صورة صالحة.',
            'image.max'            => 'يجب ألا يتجاوز حجم الصورة 20 ميجابايت.',
        ]);

        $user = Auth::user();
        $mainImage = null;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/services'), $filename);
            $mainImage = 'uploads/services/' . $filename;
        } elseif ($request->filled('image_url')) {
            $url = $request->image_url;
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
                    $filename = time() . '_url_' . uniqid() . '.' . $extension;
                    @file_put_contents(public_path('uploads/services/' . $filename), $imageContent);
                    $mainImage = 'uploads/services/' . $filename;
                }
            } catch (\Exception $e) {
                // ignore
            }
        }

        \App\Models\Service::create([
            'user_id'     => $user->id,
            'category_id' => $request->category_id,
            'title'       => $request->title,
            'description' => $request->description,
            'price'       => $request->price,
            'image'       => $mainImage,
            'phone'       => $request->phone,
            'email'       => $request->email,
        ]);

        // Update contact details on user model if changed
        $user->update([
            'phone' => $request->phone,
            'email' => $request->email,
        ]);

        return redirect()->route('ads.my')->with('success', 'تم إضافة الخدمة بنجاح! وهي قيد المراجعة حالياً من قبل الإدارة. 🎉');
    }

    public function partnerEditService($id)
    {
        $ad = \App\Models\Service::findOrFail($id);
        if ($ad->user_id !== Auth::id()) {
            abort(403);
        }
        $categories = Auth::user()->resolvedInterests;
        if ($categories->isEmpty()) {
            $categories = Category::whereNull('parent_id')->get();
        }
        return view('ads.partner-edit', compact('ad', 'categories'));
    }

    public function partnerUpdateService(Request $request, $id)
    {
        $service = \App\Models\Service::findOrFail($id);
        if ($service->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'price'       => 'required|numeric|min:0',
            'phone'       => 'required|string',
            'email'       => 'required|email|max:255',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:20480',
            'image_url'   => 'nullable|url',
        ], [
            'title.required'       => 'عنوان الخدمة مطلوب.',
            'description.required' => 'وصف الخدمة مطلوب.',
            'category_id.required' => 'التصنيف مطلوب.',
            'category_id.exists'   => 'التصنيف المختار غير صالح.',
            'price.required'       => 'سعر الخدمة مطلوب.',
            'price.numeric'        => 'يجب أن يكون السعر رقماً صالحاً.',
            'phone.required'       => 'رقم الجوال مطلوب.',
            'email.required'       => 'البريد الإلكتروني مطلوب.',
            'email.email'          => 'البريد الإلكتروني غير صالح.',
            'image.image'          => 'يجب أن يكون الملف صورة صالحة.',
            'image.max'            => 'يجب ألا يتجاوز حجم الصورة 20 ميجابايت.',
        ]);

        $user = Auth::user();
        $mainImage = $service->image;

        if ($request->hasFile('image')) {
            // Delete old image if it exists
            if ($service->image && !str_starts_with($service->image, 'http')) {
                @unlink(public_path($service->image));
            }
            
            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/services'), $filename);
            $mainImage = 'uploads/services/' . $filename;
        } elseif ($request->filled('image_url')) {
            $url = $request->image_url;
            try {
                $imageContent = @file_get_contents($url);
                if ($imageContent) {
                    // Delete old image if it exists
                    if ($service->image && !str_starts_with($service->image, 'http')) {
                        @unlink(public_path($service->image));
                    }
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
                    $filename = time() . '_url_edit_' . uniqid() . '.' . $extension;
                    @file_put_contents(public_path('uploads/services/' . $filename), $imageContent);
                    $mainImage = 'uploads/services/' . $filename;
                }
            } catch (\Exception $e) {
                // ignore
            }
        }

        $service->update([
            'title'       => $request->title,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'price'       => $request->price,
            'image'       => $mainImage,
            'phone'       => $request->phone,
            'email'       => $request->email,
            'status'      => 'pending',
        ]);

        // Update contact details on user model if changed
        $user->update([
            'phone' => $request->phone,
            'email' => $request->email,
        ]);

        return redirect()->route('ads.my')->with('success', 'تم تعديل الخدمة بنجاح! وسيتم عرضها بعد مراجعة الإدارة. 🎉');
    }

    public function partnerDestroyService($id)
    {
        $service = \App\Models\Service::findOrFail($id);
        if ($service->user_id !== Auth::id()) {
            abort(403);
        }
        if ($service->image && !str_starts_with($service->image, 'http')) {
            @unlink(public_path($service->image));
        }
        $service->delete();
        return redirect()->route('ads.my')->with('success', 'تم حذف الخدمة بنجاح.');
    }
}
