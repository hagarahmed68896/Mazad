@extends('layouts.admin')

@section('title', 'تعديل بيانات الخصائص')

@section('content')
<div class="container-fluid mx-auto px-4 sm:px-6 lg:px-8 py-8" dir="rtl">

    <!-- Header & Title -->
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('admin.categories.index') }}" class="p-2 bg-white border border-slate-200 rounded-xl text-slate-400 hover:text-[#1A73E8] transition-all">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path></svg>
        </a>
        <h1 class="text-2xl font-black text-[#1C1C1C]">تعديل بيانات الخصائص</h1>
    </div>

    @if(session('success'))
        <div class="max-w-4xl px-5 py-4 bg-green-50 border border-green-200 rounded-2xl text-green-700 font-bold mb-6 text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="mb-6 max-w-4xl px-5 py-4 bg-red-50 border border-red-200 rounded-2xl text-red-700 font-bold text-sm space-y-1">
            @foreach($errors->all() as $error)
                <p>• {{ $error }}</p>
            @endforeach
        </div>
    @endif

    <!-- Main Form Container -->
    <div class="max-w-4xl bg-[#F8F9FA] border border-slate-100 rounded-3xl p-6 shadow-sm relative" x-data="propertiesIndex()">
        
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-lg font-black text-[#1C1C1C] text-right">بيانات الخصائص</h2>
            <!-- أضف حقل إدخال -> redirects to create page -->
            <a href="{{ route('admin.categories.properties.create', $category->id) }}"
               class="flex items-center gap-2 px-4 py-2.5 bg-[#1A73E8] text-white rounded-xl text-sm font-black shadow-sm hover:bg-blue-700 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                أضف حقل ادخال
            </a>
        </div>
        
        <div class="space-y-6" id="sortable-list">

            <!-- الفئة -->
            <div class="ns-ignore">
                <label class="block text-sm font-black text-[#6F6F6F] mb-2 text-right">الفئة</label>
                <input type="text" readonly value="{{ $category->name }}"
                       class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-500 cursor-not-allowed">
            </div>

            <!-- اسم الصفحة -->
            <div class="ns-ignore">
                <label class="block text-sm font-black text-[#6F6F6F] mb-2 text-right">اسم الصفحة</label>
                <select disabled
                        class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-400 cursor-not-allowed transition-all">
                    <option>حدد اسم الصفحة</option>
                </select>
                <p class="text-xs text-slate-400 mt-2 font-bold select-none">* لإضافة خصائص لصفحة معينة، يمكنك استخدام زر الإضافة.</p>
            </div>

            <!-- Loop Properties -->
            @forelse($properties as $property)
                @php
                    // Parse options
                    $opts = is_array($property->options) ? $property->options : json_decode($property->options, true) ?? [];
                    if (empty($opts)) $opts = [''];
                @endphp
                
                <div class="bg-[#F8F9FA] rounded-2xl border border-slate-200 property-item" 
                     data-id="{{ $property->id }}"
                     x-data="{ 
                        isExpanded: false,
                        title: '{{ addslashes($property->label) }}',
                        type: '{{ $property->input_type ?: 'text' }}',
                        req: '{{ $property->is_required ? '1' : '0' }}',
                        options: {{ json_encode($opts) }},
                     }">
                    
                    <!-- Block Header Row -->
                    <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-200 bg-[#F1F3F5] rounded-t-2xl cursor-pointer hover:bg-slate-200 transition-colors"
                         @click="isExpanded = !isExpanded">
                        <!-- Grid Right -->
                        <div class="text-slate-400 cursor-move">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <rect x="3" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="1.8" fill="none"/>
                                <rect x="14" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="1.8" fill="none"/>
                                <rect x="3" y="14" width="7" height="7" rx="1" stroke="currentColor" stroke-width="1.8" fill="none"/>
                                <rect x="14" y="14" width="7" height="7" rx="1" stroke="currentColor" stroke-width="1.8" fill="none"/>
                            </svg>
                        </div>

                        <span class="text-sm font-black text-[#1C1C1C]" x-text="title || 'عنوان حقل الادخال'"></span>

                        <!-- Trash -->
                        <form action="{{ route('admin.categories.properties.destroy', [$category->id, $property->id]) }}" method="POST" @click.stop class="m-0 p-0">
                            @csrf @method('DELETE')
                            <button type="submit" onclick="return confirm('تأكيد مسح الخاصية؟')"
                                    class="p-1.5 text-slate-400 hover:text-red-500 hover:bg-white rounded-lg transition-all flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                    </div>

                    <!-- Fields / Edit Form -->
                    <div class="px-5 py-6 space-y-4" x-show="isExpanded" x-transition x-cloak>
                        <form action="{{ route('admin.categories.properties.update', [$category->id, $property->id]) }}" method="POST">
                            @csrf @method('PUT')
                            <div class="space-y-4">
                                <!-- عنوان حقل الادخال -->
                                <div class="flex items-center">
                                    <label class="w-32 shrink-0 text-sm font-black text-[#6F6F6F] text-right">عنوان حقل الادخال</label>
                                    <input type="text" name="label" x-model="title" required
                                           class="flex-1 px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm font-bold text-[#1C1C1C] placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#1A73E8]/10 focus:border-[#1A73E8] transition-all">
                                </div>

                                <!-- النص التوضيحي -->
                                <div class="flex items-center">
                                    <label class="w-32 shrink-0 text-sm font-black text-[#6F6F6F] text-right">النص التوضيحي</label>
                                    <input type="text" name="placeholder" value="{{ $property->placeholder }}"
                                           class="flex-1 px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm font-bold text-[#1C1C1C] placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#1A73E8]/10 focus:border-[#1A73E8] transition-all">
                                </div>

                                <!-- نص المساعدة -->
                                <div class="flex items-center">
                                    <label class="w-32 shrink-0 text-sm font-black text-[#6F6F6F] text-right">نص المساعدة</label>
                                    <input type="text" name="help_text" value="{{ $property->help_text }}"
                                           class="flex-1 px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm font-bold text-[#1C1C1C] placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#1A73E8]/10 focus:border-[#1A73E8] transition-all">
                                </div>

                                <!-- نوع حقل الادخال -->
                                <div class="flex items-start">
                                    <label class="w-32 shrink-0 text-sm font-black text-[#6F6F6F] pt-3 text-right">نوع حقل الادخال</label>
                                    <div class="flex-1 relative" x-data="{ openType: false }">
                                        <div @click="openType = !openType"
                                             class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl flex justify-between items-center cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#1A73E8]/10 focus:border-[#1A73E8] transition-all">
                                            <span class="text-sm font-bold text-[#1C1C1C]" x-text="getTypeLabel(type)"></span>
                                            <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="openType ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        </div>
                                        <div x-show="openType" @click.away="openType = false" x-transition x-cloak
                                             class="absolute z-10 w-full mt-2 bg-white border border-slate-200 rounded-xl shadow-lg p-4 space-y-3">
                                            @foreach(['text'=>'النصوص','number'=>'الأرقام','price'=>'الأسعار','radio'=>'اختيار واحد','checkbox'=>'اختيار متعدد','date'=>'التواريخ والأوقات','file'=>'الملفات والصور','map'=>'الموقع الجغرافي'] as $val => $name)
                                            <label class="flex items-center gap-3 cursor-pointer">
                                                <input type="radio" name="input_type" value="{{ $val }}" x-model="type" @change="openType = false" class="w-4 h-4 text-[#1A73E8] border-slate-300 focus:ring-[#1A73E8]">
                                                <span class="text-sm font-bold text-[#1C1C1C]">{{ $name }}</span>
                                            </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <!-- قيم الاختيارات -->
                                <div x-show="['checkbox','select','radio'].includes(type)" x-transition class="flex items-start">
                                    <label class="w-32 shrink-0 text-sm font-black text-[#6F6F6F] pt-3 text-right">قيم الاختيارات</label>
                                    <div class="flex-1 flex gap-2">
                                        <input type="hidden" name="options" :value="options.filter(o => o.trim() !== '').join(',')">
                                        <template x-for="(opt, optIdx) in options" :key="optIdx">
                                            <div class="flex-1 flex items-center mb-2">
                                                <input type="text" x-model="options[optIdx]" placeholder="أدخل هنا"
                                                       class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm font-bold text-[#1C1C1C] placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#1A73E8]/10 focus:border-[#1A73E8]">
                                            </div>
                                        </template>
                                        <button type="button" @click="options.push('')"
                                                class="px-5 py-3 h-[46px] bg-[#1A73E8] text-white rounded-xl text-sm font-black hover:bg-blue-700 transition-all shrink-0">
                                            أضف
                                        </button>
                                    </div>
                                </div>

                                <!-- الحالة -->
                                <div class="flex items-start">
                                    <label class="w-32 shrink-0 text-sm font-black text-[#6F6F6F] pt-3 text-right">الحالة</label>
                                    <div class="flex-1 relative" x-data="{ openStatus: false }">
                                        <div @click="openStatus = !openStatus"
                                             class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl flex justify-between items-center cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#1A73E8]/10 focus:border-[#1A73E8] transition-all">
                                            <span class="text-sm font-bold text-[#1C1C1C]" x-text="getStatusLabel(req)"></span>
                                            <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="openStatus ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        </div>
                                        <div x-show="openStatus" @click.away="openStatus = false" x-transition x-cloak
                                             class="absolute bottom-full mb-2 w-full bg-white border border-slate-200 rounded-xl shadow-lg p-4 space-y-3 z-10">
                                            <label class="flex items-center gap-3 cursor-pointer">
                                                <input type="radio" name="is_required" value="1" x-model="req" @change="openStatus = false" class="w-4 h-4 text-[#1A73E8] border-slate-300 focus:ring-[#1A73E8]">
                                                <span class="text-sm font-bold text-[#1C1C1C]">إلزامي</span>
                                            </label>
                                            <label class="flex items-center gap-3 cursor-pointer">
                                                <input type="radio" name="is_required" value="0" x-model="req" @change="openStatus = false" class="w-4 h-4 text-[#1A73E8] border-slate-300 focus:ring-[#1A73E8]">
                                                <span class="text-sm font-bold text-[#1C1C1C]">اختياري</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- حفظ / إلغاء -->
                                <div class="flex items-center justify-end gap-3 pt-4">
                                    <button type="button" @click="isExpanded = false"
                                            class="px-8 py-2.5 bg-slate-200 text-slate-600 rounded-xl text-sm font-black hover:bg-slate-300 transition-all">
                                        إلغاء
                                    </button>
                                    <button type="submit"
                                            class="px-8 py-2.5 bg-[#1A73E8] text-white rounded-xl text-sm font-black hover:bg-blue-700 transition-all">
                                        حفظ
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @empty
                <div class="py-12 text-center border-2 border-dashed border-slate-200 rounded-2xl bg-white">
                    <p class="text-slate-400 font-bold mb-3">لا توجد خصائص، جرب إضافة خاصية جديدة</p>
                </div>
            @endforelse

        </div>
    </div>
</div>

@push('scripts')
<script>
    function propertiesIndex() {
        return {
            getTypeLabel(val) {
                const types = {
                    'text': 'النصوص',
                    'number': 'الأرقام',
                    'price': 'الأسعار',
                    'radio': 'اختيار واحد',
                    'checkbox': 'اختيار متعدد',
                    'date': 'التواريخ والأوقات',
                    'file': 'الملفات والصور',
                    'map': 'الموقع الجغرافي'
                };
                return types[val] || 'حدد نوع حقل الادخال';
            },

            getStatusLabel(val) {
                if(val === '1' || val === true) return 'إلزامي';
                if(val === '0' || val === false) return 'اختياري';
                return 'حدد الحالة';
            }
        }
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var el = document.getElementById('sortable-list');
        var sortable = Sortable.create(el, {
            handle: '.cursor-move',
            animation: 150,
            filter: '.ns-ignore',
            onEnd: function () {
                let order = [];
                document.querySelectorAll('.property-item').forEach((item) => {
                    order.push(item.getAttribute('data-id'));
                });

                fetch("{{ route('admin.categories.properties.reorder', $category->id) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ order: order })
                }).then(res => res.json()).then(data => {
                    if(data.success) {
                        // Order saved silently
                    }
                }).catch(err => console.error(err));
            }
        });
    });
</script>
@endpush
@endsection
