@extends('layouts.main')

@section('title', 'تفاصيل الإعلان | مزاد')

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
            
            <a href="{{ route('ads.create', ['type' => $type]) }}" class="px-6 py-2 border border-gray-200 rounded-xl font-bold text-gray-600 hover:bg-gray-50 transition-all flex items-center gap-2">
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
        <form action="{{ route('ads.store') }}" method="POST" id="adForm" class="w-full max-w-3xl space-y-8" enctype="multipart/form-data">
            @csrf
            @php 
                $stepCounter = 1; 
                $totalPages = count($category->pages) + 4; // Pages + Inspection + Price + Basic + Images
            @endphp
            <input type="hidden" name="type" value="{{ $type }}">
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
                                @if($prop->input_type == 'text')
                                    <input type="text" name="properties[{{ $prop->id }}]" {{ $prop->is_required ? 'required' : '' }} placeholder="{{ $prop->placeholder }}" class="form-input">
                                @elseif($prop->input_type == 'number')
                                    <input type="number" name="properties[{{ $prop->id }}]" {{ $prop->is_required ? 'required' : '' }} placeholder="{{ $prop->placeholder }}" class="form-input">
                                @elseif($prop->input_type == 'select')
                                    <select name="properties[{{ $prop->id }}]" {{ $prop->is_required ? 'required' : '' }} class="form-input">
                                        <option value="">اختر...</option>
                                        @foreach($prop->options as $option)
                                            <option value="{{ $option }}">{{ $option }}</option>
                                        @endforeach
                                    </select>
                                @elseif($prop->input_type == 'radio')
                                    <div class="grid grid-cols-2 gap-4">
                                        @foreach($prop->options as $optIndex => $option)
                                            <label class="flex items-center gap-3 p-4 border-2 border-gray-100 rounded-2xl cursor-pointer hover:border-primary transition-all">
                                                <input type="radio" name="properties[{{ $prop->id }}]" value="{{ $option }}" {{ $prop->is_required && $optIndex == 0 ? 'checked' : '' }} class="w-5 h-5 accent-primary">
                                                <span class="font-bold text-gray-700">{{ $option }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                @elseif($prop->input_type == 'file' || $prop->input_type == 'image')
                                    <div class="border-2 border-dashed border-gray-200 rounded-2xl p-6 text-center hover:border-primary transition-all cursor-pointer relative bg-gray-50" onclick="document.getElementById('prop_file_{{ $prop->id }}').click()">
                                        <div class="mb-3 flex justify-center">
                                            @if(str_contains($prop->label, 'سيار') || str_contains($prop->label, 'مركب') || str_contains($prop->label, 'واجه'))
                                                <img src="https://cdn-icons-png.flaticon.com/512/3206/3206015.png" alt="car" class="w-16 h-auto opacity-50 grayscale">
                                            @else
                                                <i class="fa-solid fa-cloud-arrow-up text-4xl text-gray-300"></i>
                                            @endif
                                        </div>
                                        <p class="text-sm font-bold text-gray-500" id="prop_file_text_{{ $prop->id }}">0 من 1 ملف</p>
                                        <input type="file" id="prop_file_{{ $prop->id }}" name="properties[{{ $prop->id }}]" {{ $prop->is_required ? 'required' : '' }} class="hidden" accept="image/*" onchange="handlePropFileChange(this, {{ $prop->id }})">
                                    </div>
                                    <!-- Preview container -->
                                    <div id="prop_file_preview_{{ $prop->id }}" class="hidden mt-3 flex items-center justify-between p-3 border-2 border-gray-100 rounded-xl bg-white shadow-sm transition-all">
                                        <div class="flex items-center gap-2">
                                            <button type="button" onclick="clearPropFile({{ $prop->id }})" class="text-gray-400 hover:text-red-500 hover:bg-red-50 p-2 rounded-lg transition-all"><i class="fa-regular fa-trash-can"></i></button>
                                            <button type="button" onclick="viewPropFile({{ $prop->id }})" class="text-gray-400 hover:text-primary hover:bg-blue-50 p-2 rounded-lg transition-all"><i class="fa-regular fa-eye"></i></button>
                                        </div>
                                        <span class="text-sm font-bold text-gray-400 truncate max-w-[200px]" id="prop_file_name_{{ $prop->id }}">صورة.png</span>
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

    <!-- Leaflet Assets -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <style>
        #inspectionMap {
            height: 300px;
            width: 100%;
            border-radius: 24px;
            border: 2px solid #F1F5F9;
            z-index: 10;
        }
        .date-chip {
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            background: #F8FAFC;
            border: 2px solid #F1F5F9;
            cursor: pointer;
            transition: all 0.2s;
            font-weight: 700;
        }
        .date-chip.active {
            background: #EBF4FF;
            border-color: #1A73E8;
            color: #1A73E8;
        }
    </style>

    <!-- Step: Inspection Details -->
    <div class="step-container {{ $stepCounter == 1 ? 'active' : '' }}" data-step="{{ $stepCounter }}" data-title="تفاصيل المعاينة">
        <div class="bg-white p-8 rounded-[32px] shadow-sm border border-gray-100 space-y-8">
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-black text-gray-900">تفاصيل المعاينة</h2>
                <div class="bg-blue-50 text-primary px-4 py-2 rounded-xl font-bold text-sm">
                    <i class="fa-solid fa-location-dot ml-1"></i> 
                </div>
            </div>

            <!-- Map Picker Area -->
            <div class="space-y-4">
                <div id="inspectionMap"></div>
                <div class="grid grid-cols-2 gap-4">
                    <input type="hidden" name="lat" id="latInput" value="24.7136">
                    <input type="hidden" name="lng" id="lngInput" value="46.6753">
                    <div class="col-span-2">
                        <label class="block text-gray-700 font-black mb-3">العنوان بالتفصيل </label>
                        <input type="text" name="address" id="addressDisplay" placeholder="مثال: الرياض، حي النخيل، طريق الأمير تركي" class="form-input">
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
                        <input type="text" onfocus="(this.type='datetime-local'); setTimeout(() => { this.showPicker ? this.showPicker() : null }, 50)" onblur="(this.value == '' ? this.type='text' : this.type='datetime-local')" name="inspection_date" placeholder="أدخل تاريخ ووقت المعاينة" class="form-input text-gray-500 font-bold w-full !pr-16 text-right [&::-webkit-calendar-picker-indicator]:hidden cursor-pointer">
                    </div>
                </div>
                <div>
                    <label class="block text-gray-700 font-black mb-3">تفاصيل إضافية عن الموعد</label>
                    <textarea name="inspection_info" rows="3" placeholder="مثال: متاح من الساعة 4 عصراً حتى 9 مساءً، يرجى الاتصال قبل الحضور" class="form-input"></textarea>
                </div>
            </div>
        </div>
    </div>
            @php $stepCounter++; @endphp

    <!-- Step: Price & Shipping -->
    <div class="step-container" data-step="{{ $stepCounter }}" data-title="تفاصيل السعر والشحن">
        <div class="bg-white p-8 rounded-[32px] shadow-sm border border-gray-100 space-y-8">
            <h2 class="text-2xl font-black text-gray-900">تفاصيل السعر والشحن</h2>
            
            <!-- Ad Type Selection -->
            <div class="space-y-4">
                <label class="block text-gray-700 font-black">حدد نوع الإعلان</label>
                <div class="relative">
                    <select name="ad_type" onchange="togglePriceFields()" {{ $type == 'tender' ? 'disabled' : '' }} class="form-input w-full appearance-none bg-white border-2 border-gray-100 rounded-2xl px-5 py-4 font-bold text-gray-700 outline-none focus:border-primary focus:ring-4 focus:ring-blue-50 transition-all cursor-pointer {{ $type == 'tender' ? 'bg-gray-50 opacity-70' : '' }}">
                        <option value="auction" {{ $type == 'auction' ? 'selected' : '' }}>مزاد</option>
                        <option value="direct_purchase" {{ in_array($type, ['direct_purchase', 'standard']) ? 'selected' : '' }}>شراء فوري</option>
                        <option value="tender" {{ $type == 'tender' ? 'selected' : '' }}>مناقصة</option>
                    </select>
                    @if($type == 'tender')
                        <input type="hidden" name="ad_type" value="tender">
                    @endif
                    <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                        <i class="fa-solid fa-chevron-down text-gray-400"></i>
                    </div>
                </div>
            </div>

            <!-- AUCTION ONLY FIELDS -->
            <div id="auctionFields" class="{{ $type != 'auction' ? 'hidden' : '' }} space-y-8">
                <div class="space-y-4">
                    <label class="block text-gray-700 font-black">نوع المزاد</label>
                    <div class="relative">
                        <select name="auction_type" class="form-input w-full appearance-none bg-white border-2 border-gray-100 rounded-2xl px-5 py-4 font-bold text-gray-700 outline-none focus:border-primary focus:ring-4 focus:ring-blue-50 transition-all cursor-pointer">
                            <option value="electronic" selected>إلكتروني</option>
                            <option value="hybrid">هجين</option>
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
                            <option value="0" selected>لا</option>
                            <option value="1">نعم</option>
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
                            <input type="number" name="auction_start_price" placeholder="أدخل السعر الابتدائي" class="form-input !pl-16">
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-gray-700 font-black">قيمة العربون</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none border-r-2 border-gray-100 pr-3">
                                <img src="{{ asset('images/Vector-blue.svg') }}" class="w-4 h-4 opacity-60">
                            </div>
                            <input type="number" name="auction_deposit" placeholder="أدخل قيمة العربون" class="form-input !pl-16">
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-gray-700 font-black">قيمة التزايد</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none border-r-2 border-gray-100 pr-3">
                                <img src="{{ asset('images/Vector-blue.svg') }}" class="w-4 h-4 opacity-60">
                            </div>
                            <input type="number" name="bid_increment" placeholder="أدخل قيمة التزايد" class="form-input !pl-16">
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-gray-700 font-black">سعر الشراء (اختياري)</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none border-r-2 border-gray-100 pr-3">
                                <img src="{{ asset('images/Vector-blue.svg') }}" class="w-4 h-4 opacity-60">
                            </div>
                            <input type="number" name="buy_now_price" placeholder="أدخل سعر الشراء" class="form-input !pl-16">
                        </div>
                    </div>
                </div>
            </div>

            <!-- DIRECT PURCHASE ONLY FIELDS -->
            <div id="directFields" class="{{ !in_array($type, ['direct_purchase', 'standard']) ? 'hidden' : '' }} space-y-8">
                <div class="space-y-4">
                    <label class="block text-gray-700 font-black">آلية البيع</label>
                    <div class="relative">
                        <select id="sale_mechanism_select" class="form-input w-full appearance-none bg-white border-2 border-gray-100 rounded-2xl px-5 py-4 font-bold text-gray-700 outline-none focus:border-primary focus:ring-4 focus:ring-blue-50 transition-all cursor-pointer" onchange="updateSaleMechanism(this.value)">
                            <option value="fixed" selected>سعر ثابت</option>
                            <option value="allow_bidding">السماح بالمزايدة</option>
                            <option value="allow_negotiation">السماح بالسوم</option>
                            <option value="both">السماح بالمزايدة والسوم</option>
                        </select>
                        <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                            <i class="fa-solid fa-chevron-down text-gray-400"></i>
                        </div>
                    </div>
                    <input type="hidden" name="allow_bidding" id="hidden_allow_bidding" value="0">
                    <input type="hidden" name="allow_negotiation" id="hidden_allow_negotiation" value="0">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block text-gray-700 font-black">سعر البيع</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none border-r-2 border-gray-100 pr-3">
                                <img src="{{ asset('images/Vector-blue.svg') }}" class="w-4 h-4 opacity-60">
                            </div>
                            <input type="number" name="sale_price" placeholder="أدخل سعر البيع" class="form-input !pl-16">
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-gray-700 font-black">أقل سعر بيع</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none border-r-2 border-gray-100 pr-3">
                                <img src="{{ asset('images/Vector-blue.svg') }}" class="w-4 h-4 opacity-60">
                            </div>
                            <input type="number" name="min_sale_price" placeholder="أدخل أقل سعر بيع" class="form-input !pl-16">
                        </div>
                        <p class="text-xs text-gray-400 font-bold mt-1">هذه القيمة لن تظهر للمشترين ولكن عند وصول السلعة لها فأنك ملزم بالبيع</p>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-gray-700 font-black">قيمة العربون</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none border-r-2 border-gray-100 pr-3">
                                <img src="{{ asset('images/Vector-blue.svg') }}" class="w-4 h-4 opacity-60">
                            </div>
                            <input type="number" name="direct_deposit" placeholder="أدخل قيمة العربون" class="form-input !pl-16">
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-gray-700 font-black">الكمية</label>
                        <div class="flex items-center gap-3 p-3 border-2 border-gray-100 rounded-2xl w-fit">
                            <button type="button" onclick="this.nextElementSibling.stepDown()" class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center text-gray-400 hover:text-primary transition-all"><i class="fa-solid fa-minus"></i></button>
                            <input type="number" name="quantity" value="1" min="1" class="w-16 text-center font-black border-none focus:ring-0">
                            <button type="button" onclick="this.previousElementSibling.stepUp()" class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-primary transition-all"><i class="fa-solid fa-plus"></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TENDER ONLY FIELDS -->
            <div id="tenderFields" class="{{ $type != 'tender' ? 'hidden' : '' }} space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block text-gray-700 font-black">الميزانية التقديرية (أعلى سعر)</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none border-r-2 border-gray-100 pr-3">
                                <img src="{{ asset('images/Vector-blue.svg') }}" class="w-4 h-4 opacity-60">
                            </div>
                            <input type="number" name="tender_price" placeholder="أدخل الميزانية التقديرية" class="form-input !pl-16">
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-gray-700 font-black">قيمة التناقص</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none border-r-2 border-gray-100 pr-3">
                                <img src="{{ asset('images/Vector-blue.svg') }}" class="w-4 h-4 opacity-60">
                            </div>
                            <input type="number" name="tender_bid_increment" placeholder="أدخل قيمة التناقص" class="form-input !pl-16">
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
                        <input type="number" name="shipping_price" placeholder="أدخل سعر الشحن" class="form-input !pl-16">
                    </div>
                </div>

                <div class="space-y-4">
                    <label class="block text-gray-700 font-black">مناطق الشحن</label>
                    <div class="relative">
                        <select name="shipping_type" class="form-input w-full appearance-none bg-white border-2 border-gray-100 rounded-2xl px-5 py-4 font-bold text-gray-700 outline-none focus:border-primary focus:ring-4 focus:ring-blue-50 transition-all cursor-pointer">
                            <option value="inner" selected>شحن داخل المدينة</option>
                            <option value="international">شحن دولي</option>
                        </select>
                        <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                            <i class="fa-solid fa-chevron-down text-gray-400"></i>
                        </div>
                    </div>
                </div>

                <!-- Custom City Multi-select Dropdown -->
                <div class="space-y-4 relative" id="citiesDropdownContainer">
                    <label class="block text-gray-700 font-black">المدن</label>
                    <div class="relative">
                        <div onclick="document.getElementById('citiesList').classList.toggle('hidden')" class="form-input w-full bg-white border-2 border-gray-100 rounded-2xl px-5 py-4 font-bold text-gray-700 cursor-pointer flex justify-between items-center hover:border-primary transition-all">
                            <span id="citiesSelectedText" class="truncate max-w-[80%]">كل المدن</span>
                            <i class="fa-solid fa-chevron-down text-gray-400"></i>
                        </div>
                        <div id="citiesList" class="hidden absolute top-full mt-2 w-full bg-white border-2 border-gray-50 rounded-2xl shadow-xl z-50 max-h-60 overflow-y-auto p-2">
                            <label class="flex items-center gap-3 p-3 hover:bg-gray-50 rounded-xl cursor-pointer transition-all border-b border-gray-50 mb-1">
                                <input type="checkbox" id="selectAllCities" value="all" checked class="w-5 h-5 accent-primary rounded" onchange="toggleAllCities(this)">
                                <span class="font-bold text-primary">كل المدن</span>
                            </label>
                            @foreach($cities as $city)
                            <label class="flex items-center gap-3 p-3 hover:bg-gray-50 rounded-xl cursor-pointer transition-all">
                                <input type="checkbox" name="cities[]" value="{{ $city->id }}" class="w-5 h-5 accent-primary rounded city-checkbox" onchange="updateCitiesText()">
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
                        <input type="text" name="title" required placeholder="أدخل اسم الإعلان" class="form-input">
                    </div>

                    <div class="space-y-3">
                        <label class="block text-gray-700 font-black">وصف الإعلان</label>
                        <textarea name="description" rows="4" required placeholder="أدخل وصف الإعلان" class="form-input"></textarea>
                    </div>

                    <div class="space-y-3">
                        <label class="block text-gray-700 font-black">الصور</label>
                        <button type="button" onclick="document.getElementById('imageInput').click()" class="w-full bg-primary text-white font-black py-4 rounded-xl flex items-center justify-center gap-2 hover:bg-blue-700 transition-all shadow-lg shadow-blue-200">
                            <i class="fa-solid fa-cloud-arrow-up text-xl"></i> أضف من هنا
                        </button>
                        <input type="file" id="imageInput" name="images[]" multiple class="hidden" accept="image/*">
                        <div id="imagePreview" class="grid grid-cols-4 gap-4 mt-4"></div>
                    </div>

                    <div class="space-y-3">
                        <label class="block text-gray-700 font-black">رابط الفيديو يوتيوب</label>
                        <input type="url" name="youtube_link" placeholder="أدخل رابط الفيديو يوتيوب" class="form-input text-left" dir="ltr">
                    </div>

                    <div class="space-y-3">
                        <label class="block text-gray-700 font-black">تاريخ و وقت البدء</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none border-l-2 border-gray-100 pl-3">
                                <i class="fa-regular fa-calendar text-gray-400 text-xl"></i>
                            </div>
                            <input type="text" onfocus="(this.type='datetime-local'); setTimeout(() => { this.showPicker ? this.showPicker() : null }, 50)" onblur="(this.value == '' ? this.type='text' : this.type='datetime-local')" name="start_date" placeholder="أدخل تاريخ ووقت البدء" class="form-input text-gray-500 font-bold w-full !pr-16 text-right [&::-webkit-calendar-picker-indicator]:hidden cursor-pointer">
                        </div>
                    </div>

                    <div class="space-y-3">
                        <label class="block text-gray-700 font-black">تاريخ و وقت الانتهاء</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none border-l-2 border-gray-100 pl-3">
                                <i class="fa-regular fa-calendar text-gray-400 text-xl"></i>
                            </div>
                            <input type="text" onfocus="(this.type='datetime-local'); setTimeout(() => { this.showPicker ? this.showPicker() : null }, 50)" onblur="(this.value == '' ? this.type='text' : this.type='datetime-local')" name="end_date" placeholder="أدخل تاريخ ووقت الانتهاء" class="form-input text-gray-500 font-bold w-full !pr-16 text-right [&::-webkit-calendar-picker-indicator]:hidden cursor-pointer">
                        </div>
                    </div>

                    <div class="space-y-3">
                        <label class="block text-gray-700 font-black">مدة الترسية</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none border-r-2 border-gray-100 pr-3">
                                <span class="text-gray-400 font-bold">أيام</span>
                            </div>
                            <input type="number" name="award_duration" placeholder="أدخل مدة الترسية" class="form-input !pl-16">
                        </div>
                    </div>

                    <div class="bg-gray-50 p-5 rounded-2xl flex items-center justify-between border border-gray-100">
                        <div class="flex items-center gap-3">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_featured" value="1" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:right-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                            </label>
                            <div>
                                <span class="font-black text-gray-800 text-lg">تميز الإعلان</span>
                                <p class="text-xs text-gray-500 font-bold mt-1 flex items-center gap-1">
                                    <i class="fa-solid fa-circle-info"></i> سيكون الإعلان في المقدمة لمدة 3 أيام بمعدل 10 ريال.
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-1 font-black text-gray-800 text-lg">
                            <span>10</span>
                            <img src="{{ asset('images/Vector-blue.svg') }}" class="w-4 h-4">
                        </div>
                    </div>

                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="flex items-center justify-between pt-8 border-t border-gray-100 mt-8">
                <button type="button" id="prevBtn" class="hidden px-8 py-4 rounded-xl font-black text-gray-500 bg-white border-2 border-gray-200 hover:bg-gray-50 transition-all">السابق</button>
                <div class="flex-grow"></div>
                <button type="button" id="nextBtn" class="bg-primary text-white px-10 py-4 rounded-xl text-lg font-black shadow-lg shadow-blue-200 hover:bg-blue-700 transition-all">
                    التالي
                </button>
                <div id="submitBtns" class="hidden flex gap-3">
                    <button type="button" class="px-6 py-4 rounded-xl text-lg font-black text-gray-600 bg-white border-2 border-gray-200 hover:bg-gray-50 transition-all">
                        استمر كمسودة
                    </button>
                    <button type="submit" id="submitBtn" class="bg-primary text-white px-8 py-4 rounded-xl text-lg font-black shadow-lg shadow-blue-200 hover:bg-blue-700 transition-all">
                        نشر الإعلان
                    </button>
                </div>
            </div>
        </form>
    </main>

    <!-- Login-style Footer -->
    <footer class="custom-login-footer mt-auto">
        <div class="container flex flex-col md:flex-row justify-between items-center text-white/80 font-bold text-sm">
            <div class="flex gap-8 mb-4 md:mb-0">
                <a href="#" class="hover:text-white transition-colors">الشروط و الاحكام</a>
                <a href="#" class="hover:text-white transition-colors">سياسة الخصوصية</a>
            </div>
            <div dir="ltr">
                {{ $settings['copyright'] ?? '©2025 copyright' }}
            </div>
        </div>
    </footer>
</div>

<script>
    let currentStep = 1;
    const totalSteps = {{ count($category->pages) + 3 }};

    const nextBtn = document.getElementById('nextBtn');
    const prevBtn = document.getElementById('prevBtn');
    const submitBtn = document.getElementById('submitBtn');
    const progressBar = document.getElementById('progressBar');
    const stepIndicator = document.getElementById('stepIndicator');
    const stepTitle = document.getElementById('stepTitle');
    
    window.togglePriceFields = function() {
        const adTypeSelect = document.querySelector('select[name="ad_type"]');
        if (!adTypeSelect) return;
        const adType = adTypeSelect.value;
        const auctionFields = document.getElementById('auctionFields');
        const directFields = document.getElementById('directFields');
        const tenderFields = document.getElementById('tenderFields');
        
        if (adType === 'auction') {
            auctionFields.classList.remove('hidden');
            directFields.classList.add('hidden');
            tenderFields.classList.add('hidden');
        } else if (adType === 'tender') {
            auctionFields.classList.add('hidden');
            directFields.classList.add('hidden');
            tenderFields.classList.remove('hidden');
        } else {
            auctionFields.classList.add('hidden');
            directFields.classList.remove('hidden');
            tenderFields.classList.add('hidden');
        }
    };

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
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        const container = document.getElementById('citiesDropdownContainer');
        const list = document.getElementById('citiesList');
        if (container && list && !container.contains(e.target)) {
            list.classList.add('hidden');
        }
    });

    // Map Initialization
    let map, marker;
    function initMap() {
        if (map) return;
        map = L.map('inspectionMap').setView([24.7136, 46.6753], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(map);

        marker = L.marker([24.7136, 46.6753], { draggable: true }).addTo(map);
        
        // Reverse Geocoding Function
        async function updateAddress(lat, lng) {
            const display = document.getElementById('addressDisplay');
            display.placeholder = 'جاري جلب العنوان...';
            try {
                const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&accept-language=ar`);
                const data = await response.json();
                if (data && data.display_name) {
                    display.value = data.display_name;
                }
            } catch (error) {
                console.error('Error fetching address:', error);
                display.placeholder = 'مثال: الرياض، حي النخيل، طريق الأمير تركي';
            }
        }

        marker.on('dragend', function(e) {
            const position = marker.getLatLng();
            document.getElementById('latInput').value = position.lat;
            document.getElementById('lngInput').value = position.lng;
            updateAddress(position.lat, position.lng);
        });

        map.on('click', function(e) {
            marker.setLatLng(e.latlng);
            document.getElementById('latInput').value = e.latlng.lat;
            document.getElementById('lngInput').value = e.latlng.lng;
            updateAddress(e.latlng.lat, e.latlng.lng);
        });
    }

    function updateUI() {
        // Show/Hide Steps
        document.querySelectorAll('.step-container').forEach(step => {
            step.classList.remove('active');
            if (parseInt(step.dataset.step) === currentStep) {
                step.classList.add('active');
                stepTitle.innerText = step.dataset.title;
                
                // Init map if we are on inspection step
                if (step.dataset.title === 'تفاصيل المعاينة') {
                    setTimeout(initMap, 200);
                }
            }
        });

        // Update Progress
        const percent = (currentStep / totalSteps) * 100;
        progressBar.style.width = percent + '%';
        stepIndicator.innerText = `المرحلة ${currentStep} من ${totalSteps}`;

        // Buttons visibility
        prevBtn.classList.toggle('hidden', currentStep === 1);
        
        const submitBtnsContainer = document.getElementById('submitBtns');
        if (currentStep === totalSteps) {
            nextBtn.classList.add('hidden');
            if (submitBtnsContainer) {
                submitBtnsContainer.classList.remove('hidden');
            } else {
                submitBtn.classList.remove('hidden');
            }
        } else {
            nextBtn.classList.remove('hidden');
            if (submitBtnsContainer) {
                submitBtnsContainer.classList.add('hidden');
            } else {
                submitBtn.classList.add('hidden');
            }
        }

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    nextBtn.onclick = () => {
        // Validate current step
        const currentStepContainer = document.querySelector(`.step-container[data-step="${currentStep}"]`);
        const inputs = currentStepContainer.querySelectorAll('input[required], textarea[required], select[required]');
        let valid = true;
        inputs.forEach(input => {
            if (!input.value) {
                input.style.borderColor = '#EF4444';
                valid = false;
            } else {
                input.style.borderColor = '#F1F5F9';
            }
        });

        if (valid) {
            currentStep++;
            updateUI();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'عذراً',
                text: 'يرجى ملء جميع الحقول المطلوبة للمتابعة',
                confirmButtonText: 'حسناً',
                confirmButtonColor: '#1A73E8',
                customClass: { popup: 'rounded-[32px]' }
            });
        }
    };

    prevBtn.onclick = () => {
        currentStep--;
        updateUI();
    };

    // Image Preview
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

    // Property File Upload Handlers
    window.handlePropFileChange = function(input, id) {
        const preview = document.getElementById('prop_file_preview_' + id);
        const nameText = document.getElementById('prop_file_name_' + id);
        const countText = document.getElementById('prop_file_text_' + id);
        
        if (input.files && input.files[0]) {
            preview.classList.remove('hidden');
            nameText.innerText = input.files[0].name;
            countText.innerText = '1 من 1 ملف';
            
            // Remove required attribute if valid file is chosen so HTML5 validation passes smoothly
            input.removeAttribute('required');
        } else {
            preview.classList.add('hidden');
            countText.innerText = '0 من 1 ملف';
            // re-add required if it was originally required
            // We can check if the label has a red asterisk as a simple heuristic
            const label = input.closest('.property-field').querySelector('label');
            if(label && label.innerHTML.includes('text-red-500')) {
                input.setAttribute('required', 'required');
            }
        }
    };

    window.clearPropFile = function(id) {
        const input = document.getElementById('prop_file_' + id);
        input.value = ''; // clear file
        handlePropFileChange(input, id);
    };

    window.viewPropFile = function(id) {
        const input = document.getElementById('prop_file_' + id);
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                Swal.fire({
                    imageUrl: e.target.result,
                    imageAlt: 'معاينة الصورة',
                    showConfirmButton: false,
                    showCloseButton: true,
                    customClass: {
                        popup: 'rounded-[32px]',
                        image: 'rounded-xl max-w-full h-auto'
                    }
                });
            };
            reader.readAsDataURL(input.files[0]);
        }
    };
</script>
@endsection
