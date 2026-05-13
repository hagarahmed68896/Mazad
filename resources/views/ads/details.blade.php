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
            <a href="{{ route('ads.create', ['type' => $type]) }}" class="px-6 py-2 border border-gray-200 rounded-xl font-bold text-gray-600 hover:bg-gray-50 transition-all flex items-center gap-2">
                <i class="fa-solid fa-chevron-right text-xs"></i>
                رجوع
            </a>
            <div class="logo">
                <a href="/">
                    @include('partials.logo', ['size' => 'text-4xl'])
                </a>
            </div>
            <div class="hidden md:block w-24"></div> <!-- Spacer for centering logo -->
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
                <h2 class="text-2xl font-black text-gray-900">موقع المعاينة</h2>
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
                        <i class="fa-solid fa-calendar-days absolute left-5 top-1/2 -translate-y-1/2 text-primary text-xl pointer-events-none"></i>
                        <input type="datetime-local" name="inspection_date" class="form-input !pl-14">
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
                <div class="grid grid-cols-2 gap-4">
                    <label class="flex items-center justify-between p-4 border-2 border-gray-100 rounded-2xl cursor-pointer hover:border-primary transition-all type-option" data-target="auction">
                        <span class="font-bold">مزاد</span>
                        <input type="radio" name="ad_type" value="auction" {{ $type == 'auction' ? 'checked' : '' }} onchange="togglePriceFields()" class="w-5 h-5 accent-primary">
                    </label>
                    <label class="flex items-center justify-between p-4 border-2 border-gray-100 rounded-2xl cursor-pointer hover:border-primary transition-all type-option" data-target="direct">
                        <span class="font-bold">شراء فوري</span>
                        <input type="radio" name="ad_type" value="direct_purchase" {{ $type == 'direct_purchase' ? 'checked' : '' }} onchange="togglePriceFields()" class="w-5 h-5 accent-primary">
                    </label>
                </div>
            </div>

            <!-- AUCTION ONLY FIELDS -->
            <div id="auctionFields" class="{{ $type != 'auction' ? 'hidden' : '' }} space-y-8">
                <div class="space-y-4">
                    <label class="block text-gray-700 font-black">نوع المزاد</label>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="flex items-center justify-between p-4 border-2 border-gray-100 rounded-2xl cursor-pointer hover:border-primary transition-all">
                            <span class="font-bold">إلكتروني</span>
                            <input type="radio" name="auction_type" value="electronic" checked class="w-5 h-5 accent-primary">
                        </label>
                        <label class="flex items-center justify-between p-4 border-2 border-gray-100 rounded-2xl cursor-pointer hover:border-primary transition-all">
                            <span class="font-bold">هجين</span>
                            <input type="radio" name="auction_type" value="hybrid" class="w-5 h-5 accent-primary">
                        </label>
                    </div>
                </div>

                <label class="flex items-center gap-3 p-4 bg-blue-50 rounded-2xl cursor-pointer">
                    <input type="checkbox" name="is_enfaz" value="1" class="w-6 h-6 accent-primary">
                    <span class="font-black text-primary text-lg">عن طريق إنفاذ</span>
                </label>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block text-gray-700 font-black">السعر الابتدائي</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none border-r-2 border-gray-100 pr-3">
                                <span class="text-gray-400 font-bold">﷼</span>
                            </div>
                            <input type="number" name="auction_start_price" placeholder="أدخل السعر الابتدائي" class="form-input !pl-16">
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-gray-700 font-black">قيمة العربون</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none border-r-2 border-gray-100 pr-3">
                                <span class="text-gray-400 font-bold">﷼</span>
                            </div>
                            <input type="number" name="auction_deposit" placeholder="أدخل قيمة العربون" class="form-input !pl-16">
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-gray-700 font-black">قيمة التزايد</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none border-r-2 border-gray-100 pr-3">
                                <span class="text-gray-400 font-bold">﷼</span>
                            </div>
                            <input type="number" name="bid_increment" placeholder="أدخل قيمة التزايد" class="form-input !pl-16">
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-gray-700 font-black">سعر الشراء (اختياري)</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none border-r-2 border-gray-100 pr-3">
                                <span class="text-gray-400 font-bold">﷼</span>
                            </div>
                            <input type="number" name="buy_now_price" placeholder="أدخل سعر الشراء" class="form-input !pl-16">
                        </div>
                    </div>
                </div>
            </div>

            <!-- DIRECT PURCHASE ONLY FIELDS -->
            <div id="directFields" class="{{ $type != 'direct_purchase' ? 'hidden' : '' }} space-y-8">
                <div class="space-y-4">
                    <label class="block text-gray-700 font-black">آلية البيع</label>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="flex items-center gap-3 p-4 border-2 border-gray-100 rounded-2xl cursor-pointer hover:border-primary transition-all">
                            <input type="checkbox" name="allow_bidding" value="1" class="w-5 h-5 accent-primary">
                            <span class="font-bold text-gray-700">السماح بالمزايدة</span>
                        </label>
                        <label class="flex items-center gap-3 p-4 border-2 border-gray-100 rounded-2xl cursor-pointer hover:border-primary transition-all">
                            <input type="checkbox" name="allow_negotiation" value="1" class="w-5 h-5 accent-primary">
                            <span class="font-bold text-gray-700">السماح بالسوم</span>
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block text-gray-700 font-black">سعر البيع</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none border-r-2 border-gray-100 pr-3">
                                <span class="text-gray-400 font-bold">﷼</span>
                            </div>
                            <input type="number" name="sale_price" placeholder="أدخل سعر البيع" class="form-input !pl-16">
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-gray-700 font-black">أقل سعر بيع</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none border-r-2 border-gray-100 pr-3">
                                <span class="text-gray-400 font-bold">﷼</span>
                            </div>
                            <input type="number" name="min_sale_price" placeholder="أدخل أقل سعر بيع" class="form-input !pl-16">
                        </div>
                        <p class="text-xs text-gray-400 font-bold mt-1">هذه القيمة لن تظهر للمشترين ولكن عند وصول السلعة لها فأنك ملزم بالبيع</p>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-gray-700 font-black">قيمة العربون</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none border-r-2 border-gray-100 pr-3">
                                <span class="text-gray-400 font-bold">﷼</span>
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

            <!-- SHARED FIELDS -->
            <div class="space-y-6 pt-6 border-t border-gray-50">
                <div class="space-y-2">
                    <label class="block text-gray-700 font-black">سعر الشحن</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none border-r-2 border-gray-100 pr-3">
                            <span class="text-gray-400 font-bold">﷼</span>
                        </div>
                        <input type="number" name="shipping_price" placeholder="أدخل سعر الشحن" class="form-input !pl-16">
                    </div>
                </div>

                <div class="space-y-4">
                    <label class="block text-gray-700 font-black">مناطق الشحن</label>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="flex items-center justify-between p-4 border-2 border-gray-100 rounded-2xl cursor-pointer hover:border-primary transition-all">
                            <span class="font-bold">شحن داخل المدينة</span>
                            <input type="radio" name="shipping_type" value="inner" class="w-5 h-5 accent-primary">
                        </label>
                        <label class="flex items-center justify-between p-4 border-2 border-gray-100 rounded-2xl cursor-pointer hover:border-primary transition-all">
                            <span class="font-bold">شحن دولي</span>
                            <input type="radio" name="shipping_type" value="international" class="w-5 h-5 accent-primary">
                        </label>
                    </div>
                </div>

                <!-- City Multi-select -->
                <div class="space-y-4">
                    <label class="block text-gray-700 font-black">المدن</label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3 max-h-60 overflow-y-auto p-4 border-2 border-gray-50 rounded-2xl">
                        @foreach($cities as $city)
                        <label class="flex items-center gap-3 p-3 hover:bg-gray-50 rounded-xl cursor-pointer transition-all">
                            <input type="checkbox" name="cities[]" value="{{ $city->id }}" class="w-5 h-5 accent-primary rounded">
                            <span class="font-bold text-gray-600">{{ $city->name }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
            @php $stepCounter++; @endphp

            <!-- Step: Ad Information -->
            <div class="step-container" data-step="{{ $stepCounter }}" data-title="معلومات الإعلان">
                <div class="bg-white p-8 rounded-[32px] shadow-sm border border-gray-100 space-y-6">
                    <h2 class="text-2xl font-black text-gray-900 mb-6">معلومات الإعلان</h2>
                    <div>
                        <label class="block text-gray-700 font-black mb-3">عنوان الإعلان</label>
                        <input type="text" name="title" required placeholder="مثال: تويوتا كامري 2023 فل كامل" class="form-input">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-black mb-3">وصف الإعلان</label>
                        <textarea name="description" rows="5" required placeholder="اشرح حالة المنتج، المميزات، والعيوب إن وجدت..." class="form-input"></textarea>
                    </div>
                </div>
            </div>
            @php $stepCounter++; @endphp

            <!-- Step: Images -->
            <div class="step-container" data-step="{{ $stepCounter }}" data-title="صور الإعلان">
                <div class="bg-white p-8 rounded-[32px] shadow-sm border border-gray-100 space-y-6">
                    <h2 class="text-2xl font-black text-gray-900 mb-6">صور الإعلان</h2>
                    <div class="border-4 border-dashed border-gray-100 rounded-[32px] p-12 text-center hover:border-primary transition-all cursor-pointer group" onclick="document.getElementById('imageInput').click()">
                        <i class="fa-solid fa-cloud-arrow-up text-5xl text-gray-200 group-hover:text-primary transition-all mb-4"></i>
                        <p class="text-gray-400 font-bold">اسحب الصور هنا أو اضغط للاختيار</p>
                        <input type="file" id="imageInput" name="images[]" multiple class="hidden" accept="image/*">
                    </div>
                    <div id="imagePreview" class="grid grid-cols-4 gap-4 mt-6"></div>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="flex justify-between items-center pt-8">
                <button type="button" id="prevBtn" class="hidden px-10 py-5 rounded-2xl font-black text-gray-500 hover:bg-gray-100 transition-all">السابق</button>
                <div class="flex-grow"></div>
                <button type="button" id="nextBtn" class="bg-primary text-white px-12 py-5 rounded-2xl text-xl font-black shadow-xl shadow-blue-200 hover:bg-blue-700 transition-all transform active:scale-[0.95]">
                    التالي
                </button>
                <button type="submit" id="submitBtn" class="hidden bg-green-600 text-white px-12 py-5 rounded-2xl text-xl font-black shadow-xl shadow-green-200 hover:bg-green-700 transition-all transform active:scale-[0.95]">
                    نشر الإعلان
                </button>
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
    const totalSteps = {{ count($category->pages) + 2 }};

    const nextBtn = document.getElementById('nextBtn');
    const prevBtn = document.getElementById('prevBtn');
    const submitBtn = document.getElementById('submitBtn');
    const progressBar = document.getElementById('progressBar');
    const stepIndicator = document.getElementById('stepIndicator');
    const stepTitle = document.getElementById('stepTitle');
    
    window.togglePriceFields = function() {
        const adType = document.querySelector('input[name="ad_type"]:checked').value;
        const auctionFields = document.getElementById('auctionFields');
        const directFields = document.getElementById('directFields');
        
        if (adType === 'auction') {
            auctionFields.classList.remove('hidden');
            directFields.classList.add('hidden');
        } else {
            auctionFields.classList.add('hidden');
            directFields.classList.remove('hidden');
        }
    };

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
        
        if (currentStep === totalSteps) {
            nextBtn.classList.add('hidden');
            submitBtn.classList.remove('hidden');
        } else {
            nextBtn.classList.remove('hidden');
            submitBtn.classList.add('hidden');
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
</script>
@endsection
