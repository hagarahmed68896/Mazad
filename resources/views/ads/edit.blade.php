@extends('layouts.main')

@section('title', 'تعديل الإعلان | مزاد')

@section('content')
<style>
    .navbar, .categories-nav, .footer { display: none !important; }
    body { background-color: #F8FAFC; }

    .step-container { display: none; }
    .step-container.active { display: block; }

    .progress-bar-container {
        height: 6px;
        background-color: #E2E8F0;
        border-radius: 10px;
        overflow: hidden;
    }
    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #1A73E8, #4285F4);
        transition: width 0.4s ease;
    }

    .form-input {
        width: 100%;
        padding: 1.25rem;
        border-radius: 16px;
        border: 2px solid #F1F5F9;
        background-color: white;
        font-weight: 600;
        transition: all 0.2s;
    }
    .form-input:focus {
        border-color: #1A73E8;
        outline: none;
        box-shadow: 0 0 0 4px rgba(26, 115, 232, 0.1);
    }

    .custom-login-footer {
        background-color: #1557B0;
        background-image: linear-gradient(135deg, #1557B0 0%, #0a3066 100%);
        position: relative;
        overflow: hidden;
        padding: 2rem 0;
        margin-top: auto;
    }
    .custom-login-footer::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        width: 350px;
        height: 100%;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none"><polygon points="0,0 70,0 0,100" fill="rgba(14, 62, 126, 0.4)"/><polygon points="0,20 40,0 0,60" fill="rgba(14, 62, 126, 0.6)"/></svg>');
        background-size: cover;
        z-index: 1;
    }
</style>

<div class="min-h-screen flex flex-col">
    <!-- Custom Header -->
    <header class="bg-white py-6 shadow-sm sticky top-0 z-50">
        <div class="container flex justify-between items-center">
            <div class="logo">
                <a href="/">
                    @include('partials.logo', ['size' => 'text-4xl'])
                </a>
            </div>
            
            <a href="{{ route('ads.show', $ad->id) }}" class="px-6 py-2 border border-gray-200 rounded-xl font-bold text-gray-600 hover:bg-gray-50 transition-all flex items-center gap-2">
                رجوع
                <i class="fa-solid fa-chevron-left text-xs"></i>
            </a>
        </div>
        
        <!-- Global Progress Bar -->
        <div class="container mt-4">
            <div class="flex justify-between items-center mb-2 px-2">
                <span class="text-sm font-black text-primary" id="stepIndicator">المرحلة 1 من 4</span>
                <span class="text-sm font-bold text-gray-400" id="stepTitle">المعلومات الأساسية</span>
            </div>
            <div class="progress-bar-container">
                <div class="progress-fill" id="progressBar" style="width: 25%;"></div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow container py-12 flex flex-col items-center">
        <form action="{{ route('ads.update', $ad->id) }}" method="POST" id="adForm" class="w-full max-w-3xl space-y-8" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @php 
                $stepCounter = 1; 
                $totalPages = count($category->pages) + 3; // Pages + Inspection + Price + Basic Info
            @endphp
            <input type="hidden" name="type" value="{{ $ad->type }}">
            <input type="hidden" name="category_id" value="{{ $category->id }}">

            <!-- Step 1+: Dynamic Category Pages -->
            @foreach($category->pages as $index => $page)
                <div class="step-container {{ $stepCounter == 1 ? 'active' : '' }}" data-step="{{ $stepCounter }}" data-title="{{ $page->name }}">
                    <div class="bg-white p-8 rounded-[32px] shadow-sm border border-gray-100 space-y-6">
                        <h2 class="text-2xl font-black text-gray-900 mb-6">{{ $page->name }}</h2>
                        @foreach($page->properties as $prop)
                            <div class="property-field">
                                <label class="block text-gray-700 font-black mb-3">
                                    {{ $prop->label }}
                                    @if($prop->is_required) <span class="text-red-500">*</span> @endif
                                </label>
                                @php $currentVal = $adProperties[$prop->id] ?? ''; @endphp

                                @if($prop->input_type == 'text')
                                    <input type="text" name="properties[{{ $prop->id }}]" value="{{ $currentVal }}" {{ $prop->is_required ? 'required' : '' }} placeholder="{{ $prop->placeholder }}" class="form-input">
                                @elseif($prop->input_type == 'number')
                                    <input type="number" name="properties[{{ $prop->id }}]" value="{{ $currentVal }}" {{ $prop->is_required ? 'required' : '' }} placeholder="{{ $prop->placeholder }}" class="form-input">
                                @elseif($prop->input_type == 'select')
                                    <select name="properties[{{ $prop->id }}]" {{ $prop->is_required ? 'required' : '' }} class="form-input">
                                        <option value="">اختر...</option>
                                        @foreach($prop->options as $option)
                                            <option value="{{ $option }}" {{ $currentVal == $option ? 'selected' : '' }}>{{ $option }}</option>
                                        @endforeach
                                    </select>
                                @elseif($prop->input_type == 'radio')
                                    <div class="grid grid-cols-2 gap-4">
                                        @foreach($prop->options as $optIndex => $option)
                                            <label class="flex items-center gap-3 p-4 border-2 border-gray-100 rounded-2xl cursor-pointer hover:border-primary transition-all">
                                                <input type="radio" name="properties[{{ $prop->id }}]" value="{{ $option }}" {{ $currentVal == $option ? 'checked' : '' }} class="w-5 h-5 accent-primary">
                                                <span class="font-bold text-gray-700">{{ $option }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                @elseif($prop->input_type == 'file' || $prop->input_type == 'image')
                                    <div class="border-2 border-dashed border-gray-200 rounded-2xl p-6 text-center hover:border-primary transition-all cursor-pointer relative bg-gray-50" onclick="document.getElementById('prop_file_{{ $prop->id }}').click()">
                                        <div class="mb-3 flex justify-center">
                                            @if($currentVal)
                                                <img src="{{ asset($currentVal) }}" class="w-32 h-32 object-cover rounded-xl shadow-sm">
                                            @else
                                                <i class="fa-solid fa-cloud-arrow-up text-4xl text-gray-300"></i>
                                            @endif
                                        </div>
                                        <p class="text-sm font-bold text-gray-500" id="prop_file_text_{{ $prop->id }}">{{ $currentVal ? 'تم رفع ملف سابق' : '0 من 1 ملف' }}</p>
                                        <input type="file" id="prop_file_{{ $prop->id }}" name="properties[{{ $prop->id }}]" class="hidden" accept="image/*" onchange="handlePropFileChange(this, {{ $prop->id }})">
                                    </div>
                                @endif
                                @if($prop->help_text)
                                    <p class="mt-2 text-sm text-gray-400 font-bold">{{ $prop->help_text }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
                @php $stepCounter++; @endphp
            @endforeach

    <!-- Step: Inspection Details -->
    <div class="step-container" data-step="{{ $stepCounter }}" data-title="تفاصيل المعاينة">
        <div class="bg-white p-8 rounded-[32px] shadow-sm border border-gray-100 space-y-8">
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-black text-gray-900">تفاصيل المعاينة</h2>
                <div class="bg-blue-50 text-primary px-4 py-2 rounded-xl font-bold text-sm">
                    <i class="fa-solid fa-location-dot ml-1"></i> 
                </div>
            </div>

            <!-- Map Picker Area -->
            <div class="space-y-4">
                <div id="inspectionMap" style="height: 300px; border-radius: 24px;"></div>
                <div class="grid grid-cols-2 gap-4">
                    <input type="hidden" name="lat" id="latInput" value="{{ $ad->lat ?? '24.7136' }}">
                    <input type="hidden" name="lng" id="lngInput" value="{{ $ad->lng ?? '46.6753' }}">
                    <div class="col-span-2">
                        <label class="block text-gray-700 font-black mb-3">العنوان بالتفصيل </label>
                        <input type="text" name="address" id="addressDisplay" value="{{ $ad->address }}" placeholder="مثال: الرياض، حي النخيل، طريق الأمير تركي" class="form-input">
                    </div>
                </div>
            </div>

            <!-- Date/Time Selection Area -->
            <div class="space-y-6 pt-4 border-t border-gray-50">
                <h3 class="text-xl font-black text-gray-900">متى تكون متاحاً للمعاينة؟</h3>
                <div class="relative">
                    <label class="block text-gray-700 font-black mb-3">اختر التاريخ والوقت</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none border-l-2 border-gray-100 pl-3">
                            <i class="fa-regular fa-calendar text-gray-400 text-xl"></i>
                        </div>
                        <input type="text" 
                               onfocus="(this.type='datetime-local'); setTimeout(() => { this.showPicker ? this.showPicker() : null }, 50)" 
                               onblur="(this.value == '' ? this.type='text' : this.type='datetime-local')" 
                               name="inspection_date" 
                               value="{{ $ad->inspection_date ? \Carbon\Carbon::parse($ad->inspection_date)->format('Y-m-d\TH:i') : '' }}"
                               placeholder="أدخل تاريخ ووقت المعاينة" 
                               class="form-input text-gray-500 font-bold w-full !pr-16 text-right [&::-webkit-calendar-picker-indicator]:hidden cursor-pointer">
                    </div>
                </div>
                <div>
                    <label class="block text-gray-700 font-black mb-3">تفاصيل إضافية عن الموعد</label>
                    <textarea name="inspection_info" rows="3" placeholder="مثال: متاح من الساعة 4 عصراً حتى 9 مساءً، يرجى الاتصال قبل الحضور" class="form-input">{{ $ad->inspection_info }}</textarea>
                </div>
            </div>
        </div>
    </div>
            @php $stepCounter++; @endphp

    <!-- Step: Price & Shipping -->
    <div class="step-container" data-step="{{ $stepCounter }}" data-title="تفاصيل السعر والشحن">
        <div class="bg-white p-8 rounded-[32px] shadow-sm border border-gray-100 space-y-8">
            <h2 class="text-2xl font-black text-gray-900">تفاصيل السعر والشحن</h2>
            
            <!-- Ad Type Selection (Disabled in edit) -->
            <div class="space-y-4">
                <label class="block text-gray-700 font-black">حدد نوع الإعلان</label>
                <div class="relative">
                    <select name="ad_type_display" class="form-input w-full appearance-none bg-gray-50 border-2 border-gray-100 rounded-2xl px-5 py-4 font-bold text-gray-400 outline-none cursor-not-allowed" disabled>
                        <option value="auction" {{ $ad->type == 'auction' ? 'selected' : '' }}>مزاد</option>
                        <option value="direct_purchase" {{ in_array($ad->type, ['direct_purchase', 'standard']) ? 'selected' : '' }}>شراء فوري</option>
                        <option value="tender" {{ $ad->type == 'tender' ? 'selected' : '' }}>مناقصة</option>
                    </select>
                    <input type="hidden" name="ad_type" value="{{ $ad->type }}">
                    <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                        <i class="fa-solid fa-lock text-gray-300"></i>
                    </div>
                </div>
            </div>

            <!-- AUCTION ONLY FIELDS -->
            <div id="auctionFields" class="{{ $ad->type != 'auction' ? 'hidden' : '' }} space-y-8">
                <div class="space-y-4">
                    <label class="block text-gray-700 font-black">نوع المزاد</label>
                    <div class="relative">
                        <select name="auction_type" class="form-input w-full appearance-none bg-white border-2 border-gray-100 rounded-2xl px-5 py-4 font-bold text-gray-700 outline-none focus:border-primary focus:ring-4 focus:ring-blue-50 transition-all cursor-pointer">
                            <option value="electronic" {{ ($ad->auction_type ?? 'electronic') == 'electronic' ? 'selected' : '' }}>إلكتروني</option>
                            <option value="hybrid" {{ ($ad->auction_type ?? '') == 'hybrid' ? 'selected' : '' }}>هجين</option>
                        </select>
                        <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                            <i class="fa-solid fa-chevron-down text-gray-400"></i>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <label class="block text-gray-700 font-black">هل المزاد عن طريق إنفاذ؟</label>
                    <div class="relative">
                        <select name="is_enfaz" class="form-input w-full appearance-none bg-white border-2 border-gray-100 rounded-2xl px-5 py-4 font-bold text-gray-700 outline-none focus:border-primary focus:ring-4 focus:ring-blue-50 transition-all cursor-pointer">
                            <option value="0" {{ ($ad->is_enfaz ?? 0) == 0 ? 'selected' : '' }}>لا</option>
                            <option value="1" {{ ($ad->is_enfaz ?? 0) == 1 ? 'selected' : '' }}>نعم</option>
                        </select>
                        <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                            <i class="fa-solid fa-chevron-down text-gray-400"></i>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block text-gray-700 font-black">السعر الابتدائي</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none border-r-2 border-gray-100 pr-3">
                                <img src="{{ asset('images/Vector-blue.svg') }}" class="w-4 h-4 opacity-60">
                            </div>
                            <input type="number" name="auction_start_price" value="{{ $ad->price }}" class="form-input !pl-16">
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-gray-700 font-black">قيمة العربون</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none border-r-2 border-gray-100 pr-3">
                                <img src="{{ asset('images/Vector-blue.svg') }}" class="w-4 h-4 opacity-60">
                            </div>
                            <input type="number" name="auction_deposit" value="{{ $ad->deposit_amount }}" class="form-input !pl-16">
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-gray-700 font-black">قيمة التزايد</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none border-r-2 border-gray-100 pr-3">
                                <img src="{{ asset('images/Vector-blue.svg') }}" class="w-4 h-4 opacity-60">
                            </div>
                            <input type="number" name="bid_increment" value="{{ $ad->bid_increment }}" class="form-input !pl-16">
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-gray-700 font-black">سعر الشراء (اختياري)</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none border-r-2 border-gray-100 pr-3">
                                <img src="{{ asset('images/Vector-blue.svg') }}" class="w-4 h-4 opacity-60">
                            </div>
                            <input type="number" name="buy_now_price" value="{{ $ad->buy_now_price }}" class="form-input !pl-16">
                        </div>
                    </div>
                </div>
            </div>

            <!-- DIRECT PURCHASE ONLY FIELDS -->
            <div id="directFields" class="{{ !in_array($ad->type, ['direct_purchase', 'standard']) ? 'hidden' : '' }} space-y-8">
                <div class="space-y-4">
                    <label class="block text-gray-700 font-black">آلية البيع</label>
                    <div class="relative">
                        @php 
                            $mechanism = 'fixed';
                            if(($ad->allow_bidding ?? 0) && ($ad->allow_negotiation ?? 0)) $mechanism = 'both';
                            elseif($ad->allow_bidding ?? 0) $mechanism = 'allow_bidding';
                            elseif($ad->allow_negotiation ?? 0) $mechanism = 'allow_negotiation';
                        @endphp
                        <select id="sale_mechanism_select" class="form-input w-full appearance-none bg-white border-2 border-gray-100 rounded-2xl px-5 py-4 font-bold text-gray-700 outline-none focus:border-primary focus:ring-4 focus:ring-blue-50 transition-all cursor-pointer" onchange="updateSaleMechanism(this.value)">
                            <option value="fixed" {{ $mechanism == 'fixed' ? 'selected' : '' }}>سعر ثابت</option>
                            <option value="allow_bidding" {{ $mechanism == 'allow_bidding' ? 'selected' : '' }}>السماح بالمزايدة</option>
                            <option value="allow_negotiation" {{ $mechanism == 'allow_negotiation' ? 'selected' : '' }}>السماح بالسوم</option>
                            <option value="both" {{ $mechanism == 'both' ? 'selected' : '' }}>السماح بالمزايدة والسوم</option>
                        </select>
                        <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                            <i class="fa-solid fa-chevron-down text-gray-400"></i>
                        </div>
                    </div>
                    <input type="hidden" name="allow_bidding" id="hidden_allow_bidding" value="{{ $ad->allow_bidding ? '1' : '0' }}">
                    <input type="hidden" name="allow_negotiation" id="hidden_allow_negotiation" value="{{ $ad->allow_negotiation ? '1' : '0' }}">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block text-gray-700 font-black">سعر البيع</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none border-r-2 border-gray-100 pr-3">
                                <img src="{{ asset('images/Vector-blue.svg') }}" class="w-4 h-4 opacity-60">
                            </div>
                            <input type="number" name="sale_price" value="{{ $ad->price }}" class="form-input !pl-16">
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-gray-700 font-black">أقل سعر بيع</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none border-r-2 border-gray-100 pr-3">
                                <img src="{{ asset('images/Vector-blue.svg') }}" class="w-4 h-4 opacity-60">
                            </div>
                            <input type="number" name="min_sale_price" value="{{ $ad->min_sale_price }}" class="form-input !pl-16">
                        </div>
                        <p class="text-xs text-gray-400 font-bold mt-1">هذه القيمة لن تظهر للمشترين ولكن عند وصول السلعة لها فأنك ملزم بالبيع</p>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-gray-700 font-black">قيمة العربون</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none border-r-2 border-gray-100 pr-3">
                                <img src="{{ asset('images/Vector-blue.svg') }}" class="w-4 h-4 opacity-60">
                            </div>
                            <input type="number" name="direct_deposit" value="{{ $ad->deposit_amount }}" class="form-input !pl-16">
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-gray-700 font-black">الكمية</label>
                        <div class="flex items-center gap-3 p-3 border-2 border-gray-100 rounded-2xl w-fit">
                            <button type="button" onclick="this.nextElementSibling.stepDown()" class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center text-gray-400 hover:text-primary transition-all"><i class="fa-solid fa-minus"></i></button>
                            <input type="number" name="quantity" value="{{ $ad->quantity }}" min="1" class="w-16 text-center font-black border-none focus:ring-0">
                            <button type="button" onclick="this.previousElementSibling.stepUp()" class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-primary transition-all"><i class="fa-solid fa-plus"></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TENDER ONLY FIELDS -->
            <div id="tenderFields" class="{{ $ad->type != 'tender' ? 'hidden' : '' }} space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block text-gray-700 font-black">الميزانية التقديرية (أعلى سعر)</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none border-r-2 border-gray-100 pr-3">
                                <img src="{{ asset('images/Vector-blue.svg') }}" class="w-4 h-4 opacity-60">
                            </div>
                            <input type="number" name="tender_price" value="{{ $ad->price }}" class="form-input !pl-16">
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-gray-700 font-black">قيمة التناقص</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none border-r-2 border-gray-100 pr-3">
                                <img src="{{ asset('images/Vector-blue.svg') }}" class="w-4 h-4 opacity-60">
                            </div>
                            <input type="number" name="tender_bid_increment" value="{{ $ad->bid_increment }}" class="form-input !pl-16">
                        </div>
                    </div>
                </div>
            </div>

            <!-- SHARED FIELDS -->
            <div class="space-y-6 pt-6 border-t border-gray-50">
                <div class="space-y-2">
                    <label class="block text-gray-700 font-black">سعر الشحن</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none border-r-2 border-gray-100 pr-3">
                            <img src="{{ asset('images/Vector-blue.svg') }}" class="w-4 h-4 opacity-60">
                        </div>
                        <input type="number" name="shipping_price" value="{{ $ad->shipping_price }}" class="form-input !pl-16">
                    </div>
                </div>

                <div class="space-y-4">
                    <label class="block text-gray-700 font-black">مناطق الشحن</label>
                    <div class="relative">
                        <select name="shipping_type" class="form-input w-full appearance-none bg-white border-2 border-gray-100 rounded-2xl px-5 py-4 font-bold text-gray-700 outline-none focus:border-primary focus:ring-4 focus:ring-blue-50 transition-all cursor-pointer">
                            <option value="inner" {{ $ad->shipping_type == 'inner' ? 'selected' : '' }}>شحن داخل المدينة</option>
                            <option value="international" {{ $ad->shipping_type == 'international' ? 'selected' : '' }}>شحن دولي</option>
                        </select>
                        <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                            <i class="fa-solid fa-chevron-down text-gray-400"></i>
                        </div>
                    </div>
                </div>

                <!-- Custom City Multi-select Dropdown -->
                <div class="space-y-4 relative" id="citiesDropdownContainer">
                    <label class="block text-gray-700 font-black">المدن</label>
                    @php 
                        $selectedCities = $ad->city_id ? [$ad->city_id] : [];
                    @endphp
                    <div class="relative">
                        <div onclick="document.getElementById('citiesList').classList.toggle('hidden')" class="form-input w-full bg-white border-2 border-gray-100 rounded-2xl px-5 py-4 font-bold text-gray-700 cursor-pointer flex justify-between items-center hover:border-primary transition-all">
                            <span id="citiesSelectedText" class="truncate max-w-[80%]">{{ count($selectedCities) == 0 ? 'كل المدن' : (count($selectedCities) == 1 ? $cities->where('id', $selectedCities[0])->first()->name ?? 'كل المدن' : count($selectedCities) . ' مدن محددة') }}</span>
                            <i class="fa-solid fa-chevron-down text-gray-400"></i>
                        </div>
                        <div id="citiesList" class="hidden absolute top-full mt-2 w-full bg-white border-2 border-gray-50 rounded-2xl shadow-xl z-50 max-h-60 overflow-y-auto p-2">
                            <label class="flex items-center gap-3 p-3 hover:bg-gray-50 rounded-xl cursor-pointer transition-all border-b border-gray-50 mb-1">
                                <input type="checkbox" id="selectAllCities" value="all" {{ count($selectedCities) == 0 ? 'checked' : '' }} class="w-5 h-5 accent-primary rounded" onchange="toggleAllCities(this)">
                                <span class="font-bold text-primary">كل المدن</span>
                            </label>
                            @foreach($cities as $city)
                            <label class="flex items-center gap-3 p-3 hover:bg-gray-50 rounded-xl cursor-pointer transition-all">
                                <input type="checkbox" name="cities[]" value="{{ $city->id }}" {{ in_array($city->id, $selectedCities) ? 'checked' : '' }} class="w-5 h-5 accent-primary rounded city-checkbox" onchange="updateCitiesText()">
                                <span class="font-bold text-gray-600">{{ $city->name }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
            @php $stepCounter++; @endphp

            <!-- Step: Ad Information -->
            <div class="step-container" data-step="{{ $stepCounter }}" data-title="معلومات الإعلان">
                <div class="bg-white p-8 rounded-[32px] shadow-sm border border-gray-100 space-y-6">
                    <h2 class="text-2xl font-black text-gray-900 mb-6 text-center">معلومات الإعلان</h2>
                    
                    <div class="space-y-3">
                        <label class="block text-gray-700 font-black">اسم الإعلان</label>
                        <input type="text" name="title" value="{{ $ad->title }}" required class="form-input">
                    </div>

                    <div class="space-y-3">
                        <label class="block text-gray-700 font-black">وصف الإعلان</label>
                        <textarea name="description" rows="4" required class="form-input">{{ $ad->description }}</textarea>
                    </div>

                    <div class="space-y-3">
                        <label class="block text-gray-700 font-black">الصور الحالية</label>
                        <div class="grid grid-cols-4 gap-4">
                            @if($ad->image)
                                <div class="relative aspect-square rounded-2xl overflow-hidden border-2 border-primary">
                                    <img src="{{ asset($ad->image) }}" class="w-full h-full object-cover">
                                    <div class="absolute top-1 right-1 bg-primary text-white text-[10px] px-2 py-0.5 rounded-full">الرئيسية</div>
                                </div>
                            @endif
                            @foreach($ad->images as $img)
                                <div class="relative aspect-square rounded-2xl overflow-hidden border border-gray-100">
                                    <img src="{{ asset($img->image) }}" class="w-full h-full object-cover">
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="space-y-3 pt-4">
                        <label class="block text-gray-700 font-black">الصور</label>
                        <button type="button" onclick="document.getElementById('imageInput').click()" class="w-full bg-primary text-white font-black py-4 rounded-xl flex items-center justify-center gap-2 hover:bg-blue-700 transition-all shadow-lg shadow-blue-200">
                            <i class="fa-solid fa-cloud-arrow-up text-xl"></i> أضف من هنا
                        </button>
                        <input type="file" id="imageInput" name="images[]" multiple class="hidden" accept="image/*">
                        <div id="imagePreview" class="grid grid-cols-4 gap-4 mt-4"></div>
                        <p class="text-xs text-gray-400 font-bold mt-2">* الصور الجديدة ستحل محل الصور القديمة في حال رفعها</p>
                    </div>

                    <div class="space-y-3">
                        <label class="block text-gray-700 font-black">رابط الفيديو يوتيوب</label>
                        <input type="url" name="youtube_link" value="{{ $ad->youtube_link }}" placeholder="أدخل رابط الفيديو يوتيوب" class="form-input text-left" dir="ltr">
                    </div>

                    <div class="space-y-3">
                        <label class="block text-gray-700 font-black">تاريخ و وقت البدء</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none border-l-2 border-gray-100 pl-3">
                                <i class="fa-regular fa-calendar text-gray-400 text-xl"></i>
                            </div>
                            <input type="text" 
                                   onfocus="(this.type='datetime-local');" 
                                   onblur="(this.value == '' ? this.type='text' : this.type='datetime-local')" 
                                   name="start_date" 
                                   value="{{ $ad->start_date ? \Carbon\Carbon::parse($ad->start_date)->format('Y-m-d\TH:i') : '' }}"
                                   placeholder="أدخل تاريخ ووقت البدء" 
                                   class="form-input text-gray-500 font-bold w-full !pr-16 text-right cursor-pointer">
                        </div>
                    </div>

                    <div class="space-y-3">
                        <label class="block text-gray-700 font-black">تاريخ و وقت الانتهاء</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none border-l-2 border-gray-100 pl-3">
                                <i class="fa-regular fa-calendar text-gray-400 text-xl"></i>
                            </div>
                            <input type="text" 
                                   onfocus="(this.type='datetime-local');" 
                                   onblur="(this.value == '' ? this.type='text' : this.type='datetime-local')" 
                                   name="end_date" 
                                   value="{{ $ad->end_date ? \Carbon\Carbon::parse($ad->end_date)->format('Y-m-d\TH:i') : '' }}"
                                   placeholder="أدخل تاريخ ووقت الانتهاء" 
                                   class="form-input text-gray-500 font-bold w-full !pr-16 text-right cursor-pointer">
                        </div>
                    </div>

                    <div class="space-y-3">
                        <label class="block text-gray-700 font-black">مدة الترسية</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none border-r-2 border-gray-100 pr-3">
                                <span class="text-gray-400 font-bold">أيام</span>
                            </div>
                            <input type="number" name="award_duration" value="{{ $ad->award_duration }}" placeholder="أدخل مدة الترسية" class="form-input !pl-16">
                        </div>
                    </div>

                    <div class="bg-gray-50 p-5 rounded-2xl flex items-center justify-between border border-gray-100">
                        <div class="flex items-center gap-3">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_featured" value="1" {{ $ad->is_featured ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:right-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                            </label>
                            <div>
                                <span class="font-black text-gray-800 text-lg">تميز الإعلان</span>
                                <p class="text-xs text-gray-500 font-bold mt-1 flex items-center gap-1">
                                    <i class="fa-solid fa-circle-info"></i> سيكون الإعلان في المقدمة لمدة 3 أيام بمعدل 10 ريال.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="flex items-center justify-between pt-8 border-t border-gray-100 mt-8">
                <button type="button" id="prevBtn" class="hidden px-8 py-4 rounded-xl font-black text-gray-500 bg-white border-2 border-gray-200 hover:bg-gray-50 transition-all">السابق</button>
                <div class="flex-grow"></div>
                <button type="button" id="nextBtn" class="bg-primary text-white px-10 py-4 rounded-xl text-lg font-black shadow-lg shadow-blue-200 hover:bg-blue-700 transition-all">التالي</button>
                <button type="submit" id="submitBtn" class="hidden bg-primary text-white px-8 py-4 rounded-xl text-lg font-black shadow-lg shadow-blue-200 hover:bg-blue-700 transition-all">تحديث الإعلان</button>
            </div>
        </form>
    </main>

    <!-- Footer -->
    <footer class="custom-login-footer mt-auto">
        <div class="container flex flex-col md:flex-row justify-between items-center text-white/80 font-bold text-sm">
            <div class="flex gap-8 mb-4 md:mb-0">
                <a href="#" class="hover:text-white transition-colors">الشروط و الاحكام</a>
                <a href="#" class="hover:text-white transition-colors">سياسة الخصوصية</a>
            </div>
            <div dir="ltr">
                ©2026 Mazad. All Rights Reserved.
            </div>
        </div>
    </footer>
</div>

<!-- Leaflet Assets -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    let currentStep = 1;
    const totalSteps = {{ $totalPages }};

    const nextBtn = document.getElementById('nextBtn');
    const prevBtn = document.getElementById('prevBtn');
    const submitBtn = document.getElementById('submitBtn');
    const progressBar = document.getElementById('progressBar');
    const stepIndicator = document.getElementById('stepIndicator');
    const stepTitle = document.getElementById('stepTitle');

    // Map Initialization
    let map, marker;
    function initMap() {
        if (map) return;
        const lat = {{ $ad->lat ?? 24.7136 }};
        const lng = {{ $ad->lng ?? 46.6753 }};
        
        map = L.map('inspectionMap').setView([lat, lng], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
        marker = L.marker([lat, lng], { draggable: true }).addTo(map);

        marker.on('dragend', function() {
            const pos = marker.getLatLng();
            document.getElementById('latInput').value = pos.lat;
            document.getElementById('lngInput').value = pos.lng;
            
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${pos.lat}&lon=${pos.lng}&accept-language=ar`)
                .then(r => r.json())
                .then(data => {
                    if(data.display_name) document.getElementById('addressDisplay').value = data.display_name;
                });
        });
    }

    function updateUI() {
        document.querySelectorAll('.step-container').forEach(step => {
            step.classList.toggle('active', parseInt(step.dataset.step) === currentStep);
            if (parseInt(step.dataset.step) === currentStep) {
                stepTitle.innerText = step.dataset.title;
                if (step.dataset.title === 'تفاصيل المعاينة') setTimeout(initMap, 200);
            }
        });

        progressBar.style.width = (currentStep / totalSteps) * 100 + '%';
        stepIndicator.innerText = `المرحلة ${currentStep} من ${totalSteps}`;
        prevBtn.classList.toggle('hidden', currentStep === 1);
        nextBtn.classList.toggle('hidden', currentStep === totalSteps);
        submitBtn.classList.toggle('hidden', currentStep !== totalSteps);
        
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    window.updateSaleMechanism = function(value) {
        document.getElementById('hidden_allow_bidding').value = (value === 'allow_bidding' || value === 'both') ? '1' : '0';
        document.getElementById('hidden_allow_negotiation').value = (value === 'allow_negotiation' || value === 'both') ? '1' : '0';
    };

    window.toggleAllCities = function(selectAllCheckbox) {
        const checkboxes = document.querySelectorAll('.city-checkbox');
        checkboxes.forEach(cb => cb.checked = false);
        updateCitiesText();
    };

    window.updateCitiesText = function() {
        const checkboxes = document.querySelectorAll('.city-checkbox:checked');
        const selectAll = document.getElementById('selectAllCities');
        const textSpan = document.getElementById('citiesSelectedText');
        
        if (checkboxes.length === 0) {
            selectAll.checked = true;
            textSpan.innerText = 'كل المدن';
        } else {
            selectAll.checked = false;
            if (checkboxes.length === 1) {
                textSpan.innerText = checkboxes[0].nextElementSibling.innerText;
            } else {
                textSpan.innerText = checkboxes.length + ' مدن محددة';
            }
        }
    };
    
    document.addEventListener('click', function(e) {
        const container = document.getElementById('citiesDropdownContainer');
        const list = document.getElementById('citiesList');
        if (container && list && !container.contains(e.target)) {
            list.classList.add('hidden');
        }
    });

    nextBtn.onclick = () => { currentStep++; updateUI(); };
    prevBtn.onclick = () => { currentStep--; updateUI(); };

    document.getElementById('imageInput').onchange = function(e) {
        const preview = document.getElementById('imagePreview');
        preview.innerHTML = '';
        const files = e.target.files;
        for (let i = 0; i < files.length; i++) {
            const reader = new FileReader();
            reader.onload = function(event) {
                const div = document.createElement('div');
                div.className = 'relative aspect-square rounded-2xl overflow-hidden border border-gray-100 shadow-sm';
                div.innerHTML = `<img src="${event.target.result}" class="w-full h-full object-cover">`;
                preview.appendChild(div);
            }
            reader.readAsDataURL(files[i]);
        }
    };

    window.handlePropFileChange = function(input, id) {
        const countText = document.getElementById('prop_file_text_' + id);
        if (input.files && input.files[0]) {
            countText.innerText = 'تم اختيار ملف جديد';
        }
    };
    
    updateUI();
</script>
@endsection
