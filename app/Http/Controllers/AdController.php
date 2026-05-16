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
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'images.*'    => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $user = Auth::user();

        // Determine price & deposit based on ad_type
        $adType       = $request->ad_type ?? $request->type ?? 'direct_purchase';
        
        $price = 0;
        $depositAmount = 0;
        $bidIncrement = 0;

        if ($adType === 'auction') {
            $price = $request->auction_start_price ?? 0;
            $depositAmount = $request->auction_deposit ?? 0;
            $bidIncrement = $request->bid_increment ?? 0;
        } elseif ($adType === 'tender') {
            $price = $request->tender_price ?? 0;
            $depositAmount = 0; // Tenders usually don't have upfront deposit in the same way, or it's different
            $bidIncrement = $request->tender_bid_increment ?? 0;
        } else {
            $price = $request->sale_price ?? 0;
            $depositAmount = $request->direct_deposit ?? 0;
        }

        DB::beginTransaction();
        try {
            // Upload images
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
                'is_featured'    => $request->has('is_featured') ? 1 : 0,
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

            // Save additional images
            foreach ($imagePaths as $path) {
                AdImage::create([
                    'ad_id' => $ad->id,
                    'image' => 'storage/' . $path,
                ]);
            }

            // Save dynamic properties
            if ($request->has('properties') && is_array($request->properties)) {
                foreach ($request->properties as $propertyId => $value) {
                    if ($request->hasFile("properties.{$propertyId}")) {
                        $file = $request->file("properties.{$propertyId}");
                        $path = $file->store('ads/properties', 'public');
                        $value = 'storage/' . $path;
                    }
                    if ($value !== null && $value !== '') {
                        AdProperty::create([
                            'ad_id'       => $ad->id,
                            'property_id' => $propertyId,
                            'value'       => $value,
                        ]);
                    }
                }
            }

            DB::commit();

            // If featured → go to payment page
            if ($ad->is_featured) {
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
        if ($ad->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'images.*'    => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

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
            // Update main image if new one is uploaded
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $file) {
                    $path = $file->store('ads', 'public');
                    if ($index === 0) {
                        $ad->image = 'storage/' . $path;
                    }
                    AdImage::create([
                        'ad_id' => $ad->id,
                        'image' => 'storage/' . $path,
                    ]);
                }
            }

            $ad->update([
                'title'          => $request->title,
                'description'    => $request->description,
                'address'        => $request->address,
                'lat'            => $request->lat,
                'lng'            => $request->lng,
                'inspection_info'=> $request->inspection_info,
                'inspection_date'=> $request->inspection_date,
                'deposit_amount' => $depositAmount,
                'price'          => $price,
                'bid_increment'  => $bidIncrement,
                'end_date'       => $request->end_date ?? $ad->end_date,
                'is_featured'    => $request->has('is_featured') ? 1 : 0,
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

            // Update dynamic properties
            if ($request->has('properties') && is_array($request->properties)) {
                foreach ($request->properties as $propertyId => $value) {
                    if ($request->hasFile("properties.{$propertyId}")) {
                        $file = $request->file("properties.{$propertyId}");
                        $path = $file->store('ads/properties', 'public');
                        $value = 'storage/' . $path;
                    }
                    
                    if ($value !== null && $value !== '') {
                        AdProperty::updateOrCreate(
                            ['ad_id' => $ad->id, 'property_id' => $propertyId],
                            ['value' => $value]
                        );
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

        $ad->delete();
        return redirect()->route('users.profile', Auth::id())->with('success', 'تم حذف الإعلان بنجاح!');
    }
}
