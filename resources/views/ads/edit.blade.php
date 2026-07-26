@extends('layouts.main')

@section('title', 'تعديل الإعلان | تمت')

@section('content')
@php
    $isRealEstate = false;
    if (isset($ad) && $ad->category) {
        $cat = $ad->category;
        while ($cat) {
            $name = mb_strtolower($cat->name);
            if (
                str_contains($name, 'عقار') || 
                str_contains($name, 'العقارات') || 
                str_contains($name, 'عقارات') || 
                str_contains($name, 'real estate') || 
                str_contains($name, 'realestate') ||
                str_contains($name, 'property') ||
                str_contains($name, 'properties')
            ) {
                $isRealEstate = true;
                break;
            }
            $cat = $cat->parent;
        }
    }
@endphp
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
                $totalPages = $category->pages->filter(fn($p) => $p->properties->isNotEmpty())->count() + ($category->properties->isNotEmpty() ? 1 : 0) + 3; // Category props + Pages + Inspection + Price + Basic Info
            @endphp
            <input type="hidden" name="type" value="{{ $ad->type }}">
            <input type="hidden" name="category_id" value="{{ $category->id }}">

            @if ($errors->any())
                <div class="bg-red-50 border-r-4 border-red-500 p-5 rounded-2xl mb-6 shadow-sm">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 mt-0.5">
                            <i class="fa-solid fa-circle-exclamation text-red-500 text-xl"></i>
                        </div>
                        <div>
                            <p class="text-sm font-black text-red-800">
                                عذراً! يرجى تصحيح الأخطاء التالية قبل الاستمرار:
                            </p>
                            <ul class="mt-2 list-disc list-inside text-xs font-bold text-red-700 space-y-1" style="list-style-type: disc; padding-right: 1.25rem;">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Step: Category-level properties (not assigned to any page) -->
            @if($category->properties->isNotEmpty())
                <div class="step-container {{ $stepCounter == 1 ? 'active' : '' }}" data-step="{{ $stepCounter }}" data-title="{{ $category->name }}">
                    <div class="bg-white p-8 rounded-[32px] shadow-sm border border-gray-100 space-y-6">
                        <h2 class="text-2xl font-black text-gray-900 mb-6">{{ $category->name }}</h2>
                        @foreach($category->properties as $prop)
                            <div class="property-field">
                                <label class="block text-gray-700 font-black mb-1">
                                    {{ $prop->label }}
                                    @if($prop->is_required) <span class="text-red-500">*</span> @endif
                                </label>
                                @if($prop->help_text)
                                    <p class="mb-3 text-sm text-gray-400 font-bold">{{ $prop->help_text }}</p>
                                @elseif($prop->placeholder && ($prop->input_type == 'checkbox' || $prop->input_type == 'radio'))
                                    <p class="mb-3 text-sm text-gray-400 font-bold">{{ $prop->placeholder }}</p>
                                @endif
                                @php $currentVal = $adProperties[$prop->id] ?? ''; @endphp

                                @if($prop->input_type == 'text')
                                    <input type="text" name="properties[{{ $prop->id }}]" value="{{ $currentVal }}" {{ $prop->is_required ? 'required' : '' }} placeholder="{{ $prop->placeholder }}" class="form-input">
                                @elseif($prop->input_type == 'number')
                                    <input type="number" name="properties[{{ $prop->id }}]" value="{{ $currentVal }}" {{ $prop->is_required ? 'required' : '' }} placeholder="{{ $prop->placeholder }}" class="form-input">
                                @elseif($prop->input_type == 'select')
                                    <select name="properties[{{ $prop->id }}]" {{ $prop->is_required ? 'required' : '' }} class="form-input">
                                        <option value="">اختر...</option>
                                        @foreach($prop->options ?? [] as $option)
                                            <option value="{{ $option }}" {{ $currentVal == $option ? 'selected' : '' }}>{{ $option }}</option>
                                        @endforeach
                                    </select>
                                @elseif($prop->input_type == 'radio')
                                    <div class="grid grid-cols-2 gap-4">
                                        @foreach($prop->options ?? [] as $option)
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
                                @elseif($prop->input_type == 'checkbox')
                                    @php 
                                        $selectedValues = array_map('trim', explode(',', $currentVal ?? ''));
                                    @endphp
                                    <div class="grid grid-cols-2 gap-4">
                                        @foreach($prop->options ?? [] as $option)
                                            <label class="flex items-center gap-3 p-4 border-2 border-gray-100 rounded-2xl cursor-pointer hover:border-primary transition-all">
                                                <input type="checkbox" name="properties[{{ $prop->id }}][]" value="{{ $option }}" {{ in_array($option, $selectedValues) ? 'checked' : '' }} class="w-5 h-5 accent-primary rounded">
                                                <span class="font-bold text-gray-700">{{ $option }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                @elseif($prop->input_type == 'textarea')
                                    <textarea name="properties[{{ $prop->id }}]" {{ $prop->is_required ? 'required' : '' }} placeholder="{{ $prop->placeholder }}" rows="4" class="form-input">{{ $currentVal }}</textarea>
                                @elseif($prop->input_type == 'date')
                                    <input type="date" name="properties[{{ $prop->id }}]" value="{{ $currentVal }}" {{ $prop->is_required ? 'required' : '' }} class="form-input">
                                @elseif($prop->input_type == 'price')
                                    <div class="relative">
                                        <input type="number" name="properties[{{ $prop->id }}]" value="{{ $currentVal }}" {{ $prop->is_required ? 'required' : '' }} placeholder="{{ $prop->placeholder }}" class="form-input">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none border-r-2 border-gray-100 pr-3">
                                            <span class="text-gray-400 font-bold">ر.س</span>
                                        </div>
                                    </div>
                                @endif

                                @if($prop->is_hideable)
                                    @php
                                        $isPropHidden = $ad->adProperties->where('property_id', $prop->id)->first()?->is_hidden ?? false;
                                    @endphp
                                    <div class="mt-3 flex items-center gap-2">
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="properties_hidden[{{ $prop->id }}]" value="1" class="sr-only peer" {{ $isPropHidden ? 'checked' : '' }}>
                                            <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary"></div>
                                            <span class="mr-2 text-xs font-bold text-gray-500 select-none">إخفاء القيمة عن المستخدمين</span>
                                        </label>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
                @php $stepCounter++; @endphp
            @endif

            <!-- Step 1+: Dynamic Category Pages -->
            @foreach($category->pages as $index => $page)
                @if($page->properties->isNotEmpty())
                    <div class="step-container {{ $stepCounter == 1 ? 'active' : '' }}" data-step="{{ $stepCounter }}" data-title="{{ $page->name }}">
                        <div class="bg-white p-8 rounded-[32px] shadow-sm border border-gray-100 space-y-6">
                            <h2 class="text-2xl font-black text-gray-900 mb-6">{{ $page->name }}</h2>
                            @foreach($page->properties as $prop)
                                <div class="property-field">
                                    <label class="block text-gray-700 font-black mb-1">
                                        {{ $prop->label }}
                                        @if($prop->is_required) <span class="text-red-500">*</span> @endif
                                    </label>
                                    @if($prop->help_text)
                                        <p class="mb-3 text-sm text-gray-400 font-bold">{{ $prop->help_text }}</p>
                                    @elseif($prop->placeholder && ($prop->input_type == 'checkbox' || $prop->input_type == 'radio'))
                                        <p class="mb-3 text-sm text-gray-400 font-bold">{{ $prop->placeholder }}</p>
                                    @endif
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
                                    @elseif($prop->input_type == 'checkbox')
                                        @php 
                                            $selectedValues = array_map('trim', explode(',', $currentVal ?? ''));
                                        @endphp
                                        <div class="grid grid-cols-2 gap-4">
                                            @foreach($prop->options ?? [] as $option)
                                                <label class="flex items-center gap-3 p-4 border-2 border-gray-100 rounded-2xl cursor-pointer hover:border-primary transition-all">
                                                    <input type="checkbox" name="properties[{{ $prop->id }}][]" value="{{ $option }}" {{ in_array($option, $selectedValues) ? 'checked' : '' }} class="w-5 h-5 accent-primary rounded">
                                                    <span class="font-bold text-gray-700">{{ $option }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    @elseif($prop->input_type == 'textarea')
                                        <textarea name="properties[{{ $prop->id }}]" {{ $prop->is_required ? 'required' : '' }} placeholder="{{ $prop->placeholder }}" rows="4" class="form-input">{{ $currentVal }}</textarea>
                                    @elseif($prop->input_type == 'date')
                                        <input type="date" name="properties[{{ $prop->id }}]" value="{{ $currentVal }}" {{ $prop->is_required ? 'required' : '' }} class="form-input">
                                    @elseif($prop->input_type == 'price')
                                        <div class="relative">
                                            <input type="number" name="properties[{{ $prop->id }}]" value="{{ $currentVal }}" {{ $prop->is_required ? 'required' : '' }} placeholder="{{ $prop->placeholder }}" class="form-input">
                                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none border-r-2 border-gray-100 pr-3">
                                                <span class="text-gray-400 font-bold">ر.س</span>
                                            </div>
                                        </div>
                                    @endif

                                    @if($prop->is_hideable)
                                        @php
                                            $isPropHidden = $ad->adProperties->where('property_id', $prop->id)->first()?->is_hidden ?? false;
                                        @endphp
                                        <div class="mt-3 flex items-center gap-2">
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" name="properties_hidden[{{ $prop->id }}]" value="1" class="sr-only peer" {{ $isPropHidden ? 'checked' : '' }}>
                                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary"></div>
                                                <span class="mr-2 text-xs font-bold text-gray-500 select-none">إخفاء القيمة عن المستخدمين</span>
                                            </label>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @php $stepCounter++; @endphp
                @endif
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

            <!-- Dropdowns for City and District -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6" style="{{ $isRealEstate ? 'display: none;' : '' }}">
                <div>
                    <label class="block text-gray-700 font-black mb-2">المدينة <span class="text-red-500">*</span></label>
                    <select id="citySelect" name="city_id" class="form-input w-full" {{ $isRealEstate ? '' : 'required' }}>
                        <option value="">اختر المدينة</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 font-black mb-2">الحي <span class="text-red-500">*</span></label>
                    <select id="districtSelect" name="district_id" class="form-input w-full" {{ $isRealEstate ? '' : 'required' }}>
                        <option value="">اختر الحي</option>
                    </select>
                </div>
            </div>

            <!-- Map Picker Area (Required and shown only for Real Estate) -->
            <div class="space-y-4" id="mapPickerWrapper" style="{{ $isRealEstate ? '' : 'display: none;' }}">
                <div id="inspectionMap" style="height: 300px; border-radius: 24px;"></div>
                <div class="grid grid-cols-2 gap-4">
                    <input type="hidden" name="lat" id="latInput" value="{{ $ad->lat ?? ($isRealEstate ? '' : '24.7136') }}" {{ $isRealEstate ? 'required' : '' }}>
                    <input type="hidden" name="lng" id="lngInput" value="{{ $ad->lng ?? ($isRealEstate ? '' : '46.6753') }}" {{ $isRealEstate ? 'required' : '' }}>
                    <div class="col-span-2">
                        <label class="block text-gray-700 font-black mb-3">{{ $isRealEstate ? 'العنوان بالتفصيل (تلقائي من الخريطة)' : 'العنوان (تلقائي من الخريطة)' }}</label>
                        <input type="text" name="address" id="addressDisplay" value="{{ $ad->address }}" placeholder="اختر الموقع من الخريطة لتحديد العنوان تلقائياً" class="form-input bg-gray-50 text-gray-500 cursor-not-allowed" readonly>
                    </div>
                </div>
            </div>

            <!-- Date/Time Selection Area -->
            <div class="space-y-6 pt-4 border-t border-gray-50">
                <h3 class="text-xl font-black text-gray-900">متى تكون متاحاً للمعاينة؟</h3>
                
                <!-- Time Selection Type -->
                <div class="space-y-4">
                    <label class="block text-gray-700 font-black mb-1">طريقة تحديد وقت المعاينة</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div onclick="setInspectionTimeType('single')" id="btn_time_type_single" class="flex items-center justify-center gap-3 p-4 border-2 border-primary bg-blue-50/50 rounded-2xl cursor-pointer hover:border-primary transition-all text-primary font-bold text-center">
                            <input type="radio" id="radio_single" name="inspection_time_type" value="single" checked class="hidden">
                            <span class="text-base font-black">📅 موعد محدد (يوم وساعة)</span>
                        </div>
                        <div onclick="setInspectionTimeType('range')" id="btn_time_type_range" class="flex items-center justify-center gap-3 p-4 border-2 border-gray-100 rounded-2xl cursor-pointer hover:border-primary transition-all text-gray-500 font-bold text-center">
                            <input type="radio" id="radio_range" name="inspection_time_type" value="range" class="hidden">
                            <span class="text-base font-black">⏳ فترة زمنية (من - إلى)</span>
                        </div>
                    </div>
                </div>

                <!-- SINGLE TIME CONTAINER -->
                <div id="singleTimeContainer" class="relative">
                    <label class="block text-gray-700 font-black mb-3">اختر التاريخ والوقت</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none border-l-2 border-gray-100 pl-3">
                            <i class="fa-regular fa-calendar text-gray-400 text-xl"></i>
                        </div>
                        <input type="text" id="inspection_date_single" 
                               onfocus="(this.type='datetime-local'); this.min='{{ date('Y-m-d\TH:i') }}'; setTimeout(() => { this.showPicker ? this.showPicker() : null }, 50)" 
                               onblur="(this.value == '' ? this.type='text' : this.type='datetime-local')" 
                               value="{{ $ad->inspection_date ? \Carbon\Carbon::parse($ad->inspection_date)->format('Y-m-d\TH:i') : '' }}"
                               placeholder="أدخل تاريخ ووقت المعاينة" 
                               class="form-input text-gray-500 font-bold w-full !pr-16 text-right [&::-webkit-calendar-picker-indicator]:hidden cursor-pointer">
                    </div>
                </div>

                <!-- RANGE TIME CONTAINER -->
                <div id="rangeTimeContainer" class="hidden space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="space-y-2">
                            <label class="block text-gray-700 font-black">يوم المعاينة</label>
                            <input type="date" id="inspection_range_date" min="{{ date('Y-m-d') }}" class="form-input text-gray-500 font-bold">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-gray-700 font-black">من الساعة</label>
                            <select id="inspection_range_from" class="form-input font-bold text-gray-700">
                                <option value="09:00" selected>9:00 صباحاً</option>
                                <option value="10:00">10:00 صباحاً</option>
                                <option value="11:00">11:00 صباحاً</option>
                                <option value="12:00">12:00 مساءً</option>
                                <option value="13:00">1:00 مساءً</option>
                                <option value="14:00">2:00 مساءً</option>
                                <option value="15:00">3:00 مساءً</option>
                                <option value="16:00">4:00 مساءً</option>
                                <option value="17:00">5:00 مساءً</option>
                                <option value="18:00">6:00 مساءً</option>
                                <option value="19:00">7:00 مساءً</option>
                                <option value="20:00">8:00 مساءً</option>
                                <option value="21:00">9:00 مساءً</option>
                                <option value="22:00">10:00 مساءً</option>
                                <option value="23:00">11:00 مساءً</option>
                                <option value="00:00">12:00 صباحاً</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-gray-700 font-black">إلى الساعة</label>
                            <select id="inspection_range_to" class="form-input font-bold text-gray-700">
                                <option value="09:00">9:00 صباحاً</option>
                                <option value="10:00">10:00 صباحاً</option>
                                <option value="11:00">11:00 صباحاً</option>
                                <option value="12:00">12:00 مساءً</option>
                                <option value="13:00">1:00 مساءً</option>
                                <option value="14:00">2:00 مساءً</option>
                                <option value="15:00">3:00 مساءً</option>
                                <option value="16:00">4:00 مساءً</option>
                                <option value="17:00">5:00 مساءً</option>
                                <option value="18:00">6:00 مساءً</option>
                                <option value="19:00">7:00 مساءً</option>
                                <option value="20:00">8:00 مساءً</option>
                                <option value="21:00" selected>9:00 مساءً</option>
                                <option value="22:00">10:00 مساءً</option>
                                <option value="23:00">11:00 مساءً</option>
                                <option value="00:00">12:00 صباحاً</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Hidden input submitted to the database -->
                <input type="hidden" name="inspection_date" id="inspection_date_hidden">

                <div>
                    <label class="block text-gray-700 font-black mb-3">تفاصيل إضافية عن الموعد</label>
                    <textarea name="inspection_info" id="inspection_info_textarea" rows="3" placeholder="مثال: متاح من الساعة 4 عصراً حتى 9 مساءً، يرجى الاتصال قبل الحضور" class="form-input">{{ $ad->inspection_info }}</textarea>
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

            <!-- ENFAZ FIELD (SHARED FOR AUCTION & TENDER) -->
            <div id="enfazFields" class="{{ $ad->type != 'auction' && $ad->type != 'tender' ? 'hidden' : '' }} space-y-4">
                <label id="enfazLabel" class="block text-gray-700 font-black">
                    @if($ad->type == 'tender')
                        هل المناقصة عن طريق إنفاذ؟
                    @else
                        هل المزاد عن طريق إنفاذ؟
                    @endif
                </label>
                <div class="relative">
                    <select name="is_enfaz" onchange="toggleEnfazExtraFields()" class="form-input w-full appearance-none bg-white border-2 border-gray-100 rounded-2xl px-5 py-4 font-bold text-gray-700 outline-none focus:border-primary focus:ring-4 focus:ring-blue-50 transition-all cursor-pointer">
                        <option value="0" {{ ($ad->is_enfaz ?? 0) == 0 ? 'selected' : '' }}>لا</option>
                        <option value="1" {{ ($ad->is_enfaz ?? 0) == 1 ? 'selected' : '' }}>نعم</option>
                    </select>
                    <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                        <i class="fa-solid fa-chevron-down text-gray-400"></i>
                    </div>
                </div>

                <!-- Enfaz Extra Fields -->
                <div id="enfazExtraFields" class="hidden space-y-4 mt-4 p-4 bg-gray-50 rounded-2xl border border-gray-100">
                    <div class="space-y-2">
                        <label class="block text-gray-700 font-black">رقم الترخيص / قرار الإسناد <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="text" name="enfaz_license_number" id="enfaz_license_number_input" placeholder="أدخل رقم الترخيص أو قرار الإسناد" value="{{ $ad->enfaz_license_number }}" class="form-input">
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-gray-700 font-black">كراسة الشروط والمواصفات <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="file" name="enfaz_brochure" id="enfaz_brochure_input" accept=".pdf,image/*" class="form-input py-3">
                            <p class="text-xs text-gray-400 mt-1">المستندات المدعومة: PDF أو الصور (حد أقصى 20 ميجابايت)</p>
                            @if($ad->enfaz_brochure)
                                <div class="mt-2 text-sm text-gray-600 flex items-center gap-2">
                                    <span>الملف الحالي:</span>
                                    <a href="{{ asset($ad->enfaz_brochure) }}" target="_blank" class="text-blue-500 font-bold hover:underline flex items-center gap-1">
                                        <i class="fa-solid fa-file-pdf"></i> عرض كراسة الشروط
                                    </a>
                                </div>
                            @endif
                        </div>
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

            @if(!$isRealEstate)
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
            @endif
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
                        <textarea name="description" rows="4" class="form-input">{{ $ad->description }}</textarea>
                    </div>

                    <div class="space-y-3">
                        <label class="block text-gray-700 font-black">الصور الحالية</label>
                        <div id="currentImagesGrid" class="grid grid-cols-4 gap-4">
                            {{-- Main Image --}}
                            @if($ad->image)
                                <div class="relative aspect-square rounded-2xl overflow-hidden border-2 border-primary group" id="main-img-wrapper">
                                    <img src="{{ \Illuminate\Support\Str::startsWith($ad->image, 'http') ? $ad->image : '/' . ltrim($ad->image, '/') }}" class="w-full h-full object-cover" id="main-img-el">
                                    <div class="absolute top-1 right-1 bg-primary text-white text-[10px] px-2 py-0.5 rounded-full z-10 font-bold">الرئيسية</div>
                                    <button type="button"
                                        onclick="deleteMainImage({{ $ad->id }})"
                                        class="absolute top-1 left-1 w-6 h-6 bg-red-500 text-white rounded-full text-xs flex items-center justify-center z-10 opacity-0 group-hover:opacity-100 transition-opacity shadow"
                                        title="حذف الصورة الرئيسية">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>
                            @endif

                            {{-- Gallery Images --}}
                            @foreach($ad->images as $img)
                                @php $imgSrc = \Illuminate\Support\Str::startsWith($img->image, 'http') ? $img->image : '/' . ltrim($img->image, '/'); @endphp
                                <div class="relative aspect-square rounded-2xl overflow-hidden border border-gray-200 group gallery-img-item" id="gallery-img-{{ $img->id }}">
                                    <img src="{{ $imgSrc }}" class="w-full h-full object-cover">
                                    {{-- Delete button --}}
                                    <button type="button"
                                        onclick="deleteGalleryImage({{ $img->id }}, this)"
                                        class="absolute top-1 left-1 w-6 h-6 bg-red-500 text-white rounded-full text-xs flex items-center justify-center z-10 opacity-0 group-hover:opacity-100 transition-opacity shadow"
                                        title="حذف الصورة">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                    {{-- Set as main button --}}
                                    <button type="button"
                                        onclick="setMainImage({{ $ad->id }}, {{ $img->id }}, '{{ $imgSrc }}', this)"
                                        class="absolute bottom-1 right-1 bg-white/90 text-primary text-[10px] px-2 py-0.5 rounded-full font-bold z-10 opacity-0 group-hover:opacity-100 transition-opacity shadow border border-primary/20"
                                        title="تعيين كصورة رئيسية">
                                        <i class="fa-solid fa-star text-[9px]"></i> رئيسية
                                    </button>
                                </div>
                            @endforeach
                        </div>
                        <p class="text-xs text-gray-400 font-bold mt-1"><i class="fa-solid fa-circle-info ml-1"></i>مرر على الصورة لتظهر أزرار الحذف والتعيين كرئيسية</p>
                    </div>

                    <div class="space-y-3 pt-4">
                        <label class="block text-gray-700 font-black">الصور</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <button type="button" onclick="document.getElementById('imageInput').click()" class="w-full bg-primary text-white font-black py-4 rounded-xl flex items-center justify-center gap-2 hover:bg-blue-700 transition-all shadow-lg shadow-blue-200">
                                <i class="fa-solid fa-cloud-arrow-up text-xl"></i> أضف من جهازك
                            </button>
                            <button type="button" onclick="showImageUrlInput()" class="w-full border-2 border-primary text-primary font-black py-4 rounded-xl flex items-center justify-center gap-2 hover:bg-primary/5 transition-all">
                                <i class="fa-solid fa-link text-xl"></i> أضف من رابط (URL)
                            </button>
                        </div>
                        <input type="file" id="imageInput" name="images[]" multiple class="hidden" accept="image/*">
                        
                        <!-- Image URL input field (initially hidden) -->
                        <div id="imageUrlWrapper" class="hidden space-y-2 mt-3 bg-gray-50 p-4 rounded-2xl border border-gray-100">
                            <label class="block text-sm font-bold text-gray-700">أدخل رابط الصورة (URL):</label>
                            <div class="flex gap-2">
                                <input type="url" id="imageUrlInput" placeholder="https://example.com/image.jpg" class="form-input flex-1">
                                <button type="button" onclick="addImageUrlToList()" class="bg-primary text-white font-black px-6 rounded-xl hover:bg-blue-700 transition-all">إضافة</button>
                            </div>
                        </div>

                        <!-- Container for dynamic image urls hidden inputs -->
                        <div id="urlImagesContainer"></div>
                        
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
                                   onfocus="(this.type='datetime-local'); this.min='{{ date('Y-m-d\TH:i') }}';" 
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
                                   onfocus="(this.type='datetime-local'); this.min='{{ date('Y-m-d\TH:i') }}';" 
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
                                    <i class="fa-solid fa-circle-info"></i> يكون الإعلان في مقدمة الإعلانات ويحمل علامة ذهبية
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-1 font-black text-gray-800 text-lg">
                            <span>{{ \App\Models\Setting::where('key', 'promotion_price')->first()->value ?? 80 }}</span>
                            <img src="{{ asset('images/Vector-blue.svg') }}" class="w-4 h-4">
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
                <a href="{{ route('terms') }}" class="hover:text-white transition-colors">الشروط و الاحكام</a>
                <a href="{{ route('privacy') }}" class="hover:text-white transition-colors">سياسة الخصوصية</a>
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

    window.setInspectionTimeType = function(type) {
        const btnSingle = document.getElementById('btn_time_type_single');
        const btnRange = document.getElementById('btn_time_type_range');
        const singleContainer = document.getElementById('singleTimeContainer');
        const rangeContainer = document.getElementById('rangeTimeContainer');
        const radioSingle = document.getElementById('radio_single');
        const radioRange = document.getElementById('radio_range');

        if (type === 'single') {
            btnSingle.className = "flex items-center justify-center gap-3 p-4 border-2 border-primary bg-blue-50/50 rounded-2xl cursor-pointer hover:border-primary transition-all text-primary font-bold text-center";
            btnRange.className = "flex items-center justify-center gap-3 p-4 border-2 border-gray-100 rounded-2xl cursor-pointer hover:border-primary transition-all text-gray-500 font-bold text-center";
            singleContainer.classList.remove('hidden');
            rangeContainer.classList.add('hidden');
            radioSingle.checked = true;
        } else {
            btnRange.className = "flex items-center justify-center gap-3 p-4 border-2 border-primary bg-blue-50/50 rounded-2xl cursor-pointer hover:border-primary transition-all text-primary font-bold text-center";
            btnSingle.className = "flex items-center justify-center gap-3 p-4 border-2 border-gray-100 rounded-2xl cursor-pointer hover:border-primary transition-all text-gray-500 font-bold text-center";
            singleContainer.classList.add('hidden');
            rangeContainer.classList.remove('hidden');
            radioRange.checked = true;
        }
    };

    window.syncInspectionData = function() {
        const isRange = document.getElementById('radio_range').checked;
        const hiddenInput = document.getElementById('inspection_date_hidden');
        const infoTextarea = document.getElementById('inspection_info_textarea');
        
        if (isRange) {
            const rangeDate = document.getElementById('inspection_range_date').value;
            const rangeFrom = document.getElementById('inspection_range_from').value;
            const rangeTo = document.getElementById('inspection_range_to').value;
            
            const fromText = document.getElementById('inspection_range_from').options[document.getElementById('inspection_range_from').selectedIndex].text;
            const toText = document.getElementById('inspection_range_to').options[document.getElementById('inspection_range_to').selectedIndex].text;
            
            if (rangeDate && rangeFrom) {
                hiddenInput.value = rangeDate + ' ' + rangeFrom + ':00';
            } else {
                hiddenInput.value = '';
            }
            
            // Format inspection info
            let currentInfo = infoTextarea.value;
            currentInfo = currentInfo.replace(/\[فترة المعاينة:.*?\]/g, '').trim();
            infoTextarea.value = `[فترة المعاينة: من ${fromText} إلى ${toText}]` + (currentInfo ? '\n' + currentInfo : '');
        } else {
            const singleVal = document.getElementById('inspection_date_single').value;
            if (singleVal) {
                hiddenInput.value = singleVal.replace('T', ' ');
            } else {
                hiddenInput.value = '';
            }
            infoTextarea.value = infoTextarea.value.replace(/\[فترة المعاينة:.*?\]/g, '').trim();
        }
    };

    // Parser for edit view on page load
    window.addEventListener('DOMContentLoaded', () => {
        const infoTextarea = document.getElementById('inspection_info_textarea');
        if (infoTextarea) {
            const match = infoTextarea.value.match(/\[فترة المعاينة:\s*من\s*(.*?)\s*إلى\s*(.*?)\]/);
            if (match) {
                const fromText = match[1].trim();
                const toText = match[2].trim();
                
                // Toggle to range
                setInspectionTimeType('range');
                
                // Clean the info textarea
                infoTextarea.value = infoTextarea.value.replace(/\[فترة المعاينة:.*?\]/g, '').trim();
                
                // Populate selects
                const fromSelect = document.getElementById('inspection_range_from');
                const toSelect = document.getElementById('inspection_range_to');
                
                for (let option of fromSelect.options) {
                    if (option.text.trim() === fromText) {
                        option.selected = true;
                        break;
                    }
                }
                for (let option of toSelect.options) {
                    if (option.text.trim() === toText) {
                        option.selected = true;
                        break;
                    }
                }
                
                // Parse date from hidden/existing value
                const existingDateVal = `{!! $ad->inspection_date ? \Carbon\Carbon::parse($ad->inspection_date)->format('Y-m-d H:i:s') : '' !!}`;
                if (existingDateVal) {
                    const datePart = existingDateVal.split(' ')[0]; // YYYY-MM-DD
                    document.getElementById('inspection_range_date').value = datePart;
                }
            } else {
                // Single mode: existing datetime is in datetime-local format
                const existingDateVal = `{!! $ad->inspection_date ? \Carbon\Carbon::parse($ad->inspection_date)->format('Y-m-d H:i:s') : '' !!}`;
                if (existingDateVal) {
                    // Pre-fill hidden input too
                    document.getElementById('inspection_date_hidden').value = existingDateVal;
                }
            }
        }
    });

    // Location Dropdowns Initialization
    async function initLocationDropdowns() {
        const isRealEstate = {{ $isRealEstate ? 'true' : 'false' }};
        if (isRealEstate) return;
        const citySelect = document.getElementById('citySelect');
        const districtSelect = document.getElementById('districtSelect');
        if (!citySelect || !districtSelect) return;

        const initialDistrictId = {{ $ad->city_id ?? 'null' }};
        const initialCityId = {{ $ad->city && $ad->city->region_id ? $ad->city->region_id : 'null' }};

        // Fetch cities
        try {
            const response = await fetch('/api/cities');
            const result = await response.json();
            if (result.success && result.data) {
                result.data.forEach(city => {
                    const option = document.createElement('option');
                    option.value = city.id;
                    option.textContent = city.name;
                    if (initialCityId && city.id === initialCityId) {
                        option.selected = true;
                    }
                    citySelect.appendChild(option);
                });
            }
        } catch (error) {
            console.error('Error loading cities:', error);
        }

        // Load districts helper
        async function loadDistricts(cityId, selectedDistrictId = null) {
            districtSelect.innerHTML = '<option value="">اختر الحي</option>';
            if (!cityId) return;

            try {
                const response = await fetch(`/api/cities/${cityId}/districts`);
                const result = await response.json();
                if (result.success && result.data) {
                    result.data.forEach(district => {
                        const option = document.createElement('option');
                        option.value = district.id;
                        option.textContent = district.name;
                        if (selectedDistrictId && district.id === selectedDistrictId) {
                            option.selected = true;
                        }
                        districtSelect.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Error loading districts:', error);
            }
        }

        // If there's an initial city, load its districts and pre-select the district
        if (initialCityId) {
            await loadDistricts(initialCityId, initialDistrictId);
        }

        // On city change, load districts
        citySelect.addEventListener('change', async function() {
            await loadDistricts(this.value);
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        initLocationDropdowns();
    });

    // Map Initialization
    let map, marker;
    function initMap() {
        const isRealEstate = {{ $isRealEstate ? 'true' : 'false' }};
        if (!isRealEstate) return; // Only init map for real estate
        if (map) return;
        const lat = {{ $ad->lat ?? 24.7136 }};
        const lng = {{ $ad->lng ?? 46.6753 }};
        const mapOptions = {};
        
        map = L.map('inspectionMap', mapOptions).setView([lat, lng], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
        marker = L.marker([lat, lng], { draggable: true }).addTo(map);

        marker.on('dragend', function() {
            const pos = marker.getLatLng();
            document.getElementById('latInput').value = pos.lat;
            document.getElementById('lngInput').value = pos.lng;
            
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${pos.lat}&lon=${pos.lng}&accept-language=ar`)
                .then(r => r.json())
                .then(data => {
                    if (data && data.display_name) {
                        const isRealEstate = {{ $isRealEstate ? 'true' : 'false' }};
                        if (isRealEstate) {
                            document.getElementById('addressDisplay').value = data.display_name;
                        } else {
                            const addr = data.address || {};
                            const city = addr.city || addr.town || addr.village || addr.municipality || addr.state || '';
                            const neighborhood = addr.neighbourhood || addr.suburb || addr.quarter || addr.district || '';
                            let result = '';
                            if (city) result += city;
                            if (neighborhood) {
                                const cleanNeighborhood = neighborhood.replace(/^حي\s+/u, '');
                                result += (result ? '، ' : '') + 'حي ' + cleanNeighborhood;
                            }
                            document.getElementById('addressDisplay').value = result || data.display_name || '';
                        }
                    }
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

    nextBtn.onclick = () => {
        const currentStepContainer = document.querySelector(`.step-container[data-step="${currentStep}"]`);
        
        // Sync before validation if we are on inspection step
        if (currentStepContainer && currentStepContainer.dataset.title === 'تفاصيل المعاينة') {
            syncInspectionData();
        }

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
    prevBtn.onclick = () => { currentStep--; updateUI(); };

    let selectedFiles = [];
    let selectedUrls = [];

    function renderPreviews() {
        const preview = document.getElementById('imagePreview');
        preview.innerHTML = '';
        
        // 1. Render files
        selectedFiles.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(event) {
                const div = document.createElement('div');
                div.className = 'relative aspect-square rounded-2xl overflow-hidden border border-gray-100 shadow-sm group';
                div.innerHTML = `
                    <img src="${event.target.result}" class="w-full h-full object-cover">
                    <button type="button" onclick="removeSelectedFile(${index})" class="absolute top-1 left-1 w-6 h-6 bg-red-500 text-white rounded-full text-xs flex items-center justify-center z-10 opacity-0 group-hover:opacity-100 transition-all shadow hover:bg-red-600" title="حذف الصورة">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                `;
                preview.appendChild(div);
            }
            reader.readAsDataURL(file);
        });

        // 2. Render URLs
        selectedUrls.forEach((url, index) => {
            const div = document.createElement('div');
            div.className = 'relative aspect-square rounded-2xl overflow-hidden border border-gray-100 shadow-sm group';
            div.innerHTML = `
                <img src="${url}" class="w-full h-full object-cover" onerror="this.src='https://placehold.co/600x600?text=Invalid+Image+URL';">
                <button type="button" onclick="removeSelectedUrl(${index})" class="absolute top-1 left-1 w-6 h-6 bg-red-500 text-white rounded-full text-xs flex items-center justify-center z-10 opacity-0 group-hover:opacity-100 transition-all shadow hover:bg-red-600" title="حذف الصورة">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            `;
            preview.appendChild(div);
        });

        // 3. Update hidden inputs for URLs
        const urlContainer = document.getElementById('urlImagesContainer');
        urlContainer.innerHTML = '';
        selectedUrls.forEach(url => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'image_urls[]';
            input.value = url;
            urlContainer.appendChild(input);
        });
    }

    window.removeSelectedFile = function(index) {
        selectedFiles.splice(index, 1);
        renderPreviews();
    };

    window.removeSelectedUrl = function(index) {
        selectedUrls.splice(index, 1);
        renderPreviews();
    };

    window.showImageUrlInput = function() {
        const wrapper = document.getElementById('imageUrlWrapper');
        if (wrapper.classList.contains('hidden')) {
            wrapper.classList.remove('hidden');
            document.getElementById('imageUrlInput').focus();
        } else {
            wrapper.classList.add('hidden');
        }
    };

    window.addImageUrlToList = function() {
        const input = document.getElementById('imageUrlInput');
        const url = input.value.trim();
        if (url) {
            if (url.startsWith('http://') || url.startsWith('https://')) {
                selectedUrls.push(url);
                renderPreviews();
                input.value = '';
                document.getElementById('imageUrlWrapper').classList.add('hidden');
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'خطأ',
                    text: 'يرجى إدخال رابط صورة صحيح يبدأ بـ http:// أو https://',
                    confirmButtonText: 'حسناً',
                    confirmButtonColor: '#1A73E8'
                });
            }
        }
    };

    document.getElementById('imageInput').onchange = function(e) {
        if (e.target.files) {
            const files = Array.from(e.target.files);
            files.forEach(file => {
                if (!selectedFiles.some(f => f.name === file.name && f.size === file.size)) {
                    selectedFiles.push(file);
                }
            });
            renderPreviews();
            e.target.value = '';
        }
    };

    document.getElementById('adForm').addEventListener('submit', function(e) {
        syncInspectionData();
        if (selectedFiles.length > 0) {
            const dataTransfer = new DataTransfer();
            selectedFiles.forEach(file => {
                dataTransfer.items.add(file);
            });
            document.getElementById('imageInput').files = dataTransfer.files;
        }
    });

    window.handlePropFileChange = function(input, id) {
        const countText = document.getElementById('prop_file_text_' + id);
        if (input.files && input.files[0]) {
            countText.innerText = 'تم اختيار ملف جديد';
        }
    };
    
    updateUI();
</script>

<script>
    const adEditCsrfToken = '{{ csrf_token() }}';

    // ─── Delete a gallery image ────────────────────────────────────────────────
    window.deleteGalleryImage = function(imageId, btn) {
        Swal.fire({
            title: 'حذف الصورة؟',
            text: 'لن تتمكن من التراجع عن هذا الإجراء!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            cancelButtonColor: '#64748B',
            confirmButtonText: 'نعم، احذفها!',
            cancelButtonText: 'إلغاء',
        }).then((result) => {
            if (!result.isConfirmed) return;

            fetch(`/ads/images/${imageId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': adEditCsrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ _method: 'DELETE' })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('gallery-img-' + imageId)?.remove();
                    Swal.fire({ title: 'تم الحذف!', icon: 'success', timer: 1200, showConfirmButton: false });
                }
            })
            .catch(() => Swal.fire({ icon: 'error', title: 'حدث خطأ أثناء الحذف' }));
        });
    };

    // ─── Delete the main image ─────────────────────────────────────────────────
    window.deleteMainImage = function(adId) {
        Swal.fire({
            title: 'حذف الصورة الرئيسية؟',
            text: 'سيتم ترقية أول صورة من المعرض كصورة رئيسية إن وُجدت.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            cancelButtonColor: '#64748B',
            confirmButtonText: 'نعم، احذفها!',
            cancelButtonText: 'إلغاء',
        }).then((result) => {
            if (!result.isConfirmed) return;

            fetch(`/ads/${adId}/delete-main-image`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': adEditCsrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ _method: 'DELETE' })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const wrapper = document.getElementById('main-img-wrapper');
                    if (data.new_main) {
                        document.getElementById('main-img-el').src = data.new_main;
                    } else {
                        wrapper?.remove();
                    }
                    Swal.fire({ title: 'تم الحذف!', icon: 'success', timer: 1200, showConfirmButton: false });
                }
            })
            .catch(() => Swal.fire({ icon: 'error', title: 'حدث خطأ أثناء الحذف' }));
        });
    };

    // ─── Set a gallery image as the main image ─────────────────────────────────
    window.setMainImage = function(adId, imageId, imgSrc, btn) {
        Swal.fire({
            title: 'تعيين كصورة رئيسية؟',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#1A73E8',
            cancelButtonColor: '#64748B',
            confirmButtonText: 'نعم، عيّنها!',
            cancelButtonText: 'إلغاء',
        }).then((result) => {
            if (!result.isConfirmed) return;

            fetch(`/ads/${adId}/set-main-image`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': adEditCsrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ image_id: imageId })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    // Update main image src
                    const mainEl = document.getElementById('main-img-el');
                    if (mainEl) mainEl.src = data.new_main;

                    // Remove the promoted gallery card
                    document.getElementById('gallery-img-' + imageId)?.remove();

                    Swal.fire({ title: 'تم التعيين!', icon: 'success', timer: 1200, showConfirmButton: false });
                }
            })
            .catch(() => Swal.fire({ icon: 'error', title: 'حدث خطأ أثناء التعيين' }));
        });
    };

    window.toggleEnfazExtraFields = function() {
        const isEnfazSelect = document.querySelector('select[name="is_enfaz"]');
        if (!isEnfazSelect) return;
        const isEnfaz = isEnfazSelect.value === '1';
        const enfazExtraFields = document.getElementById('enfazExtraFields');
        const licenseInput = document.getElementById('enfaz_license_number_input');
        const brochureInput = document.getElementById('enfaz_brochure_input');
        const hasExistingBrochure = "{{ $ad->enfaz_brochure ? 'true' : 'false' }}" === 'true';
        
        if (isEnfaz) {
            if (enfazExtraFields) enfazExtraFields.classList.remove('hidden');
            if (licenseInput) licenseInput.setAttribute('required', 'required');
            if (brochureInput && !hasExistingBrochure) brochureInput.setAttribute('required', 'required');
        } else {
            if (enfazExtraFields) enfazExtraFields.classList.add('hidden');
            if (licenseInput) {
                licenseInput.removeAttribute('required');
            }
            if (brochureInput) {
                brochureInput.removeAttribute('required');
            }
        }
    };

    document.addEventListener("DOMContentLoaded", function() {
        toggleEnfazExtraFields();

        // Keep ad end date min locked to ad start date value
        const startDateInput = document.querySelector('input[name="start_date"]');
        if (startDateInput) {
            startDateInput.addEventListener('change', function() {
                const endDateInput = document.querySelector('input[name="end_date"]');
                if (endDateInput) {
                    endDateInput.min = this.value;
                    if (endDateInput.value && endDateInput.value < this.value) {
                        endDateInput.value = this.value;
                    }
                }
            });
            // trigger on load to set initial min if value exists
            if (startDateInput.value) {
                const endDateInput = document.querySelector('input[name="end_date"]');
                if (endDateInput) endDateInput.min = startDateInput.value;
            }
        }
    });
</script>
@endsection
