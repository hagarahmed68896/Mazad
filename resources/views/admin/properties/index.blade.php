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
                        mode: 'preview', 
                        showAdvanced: false,
                        title: '{{ addslashes($property->label) }}',
                        placeholder: '{{ addslashes($property->placeholder) }}',
                        helpText: '{{ addslashes($property->help_text) }}',
                        type: '{{ $property->input_type ?: 'text' }}',
                        req: '{{ $property->is_required ? '1' : '0' }}',
                        options: {{ json_encode($opts) }},
                     }">
                    
                    <!-- Block Header Row -->
                    <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-200 bg-[#F1F3F5] rounded-t-2xl cursor-pointer hover:bg-slate-200 transition-colors"
                         @click="if(isExpanded && mode === 'preview') { isExpanded = false; } else { isExpanded = true; mode = 'preview'; }">
                        <!-- Grid Right (Drag) -->
                        <div class="w-10 text-slate-400 cursor-move shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <rect x="3" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="1.8" fill="none"/>
                                <rect x="14" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="1.8" fill="none"/>
                                <rect x="3" y="14" width="7" height="7" rx="1" stroke="currentColor" stroke-width="1.8" fill="none"/>
                                <rect x="14" y="14" width="7" height="7" rx="1" stroke="currentColor" stroke-width="1.8" fill="none"/>
                            </svg>
                        </div>

                        <!-- Centered Title -->
                        <span class="flex-1 text-center text-sm font-black text-[#1C1C1C]" x-text="title || 'عنوان حقل الادخال'"></span>

                        <!-- Actions (Pencil & Trash) -->
                        <div class="w-20 flex items-center justify-end gap-1 shrink-0" @click.stop>
                            <!-- Edit Pencil -->
                            <button @click.stop="if(isExpanded && mode === 'edit') { isExpanded = false; } else { isExpanded = true; mode = 'edit'; }"
                                    class="p-1.5 text-slate-400 hover:text-[#1A73E8] hover:bg-white rounded-lg transition-all flex items-center justify-center"
                                    :class="isExpanded && mode === 'edit' ? 'bg-[#1A73E8] text-white shadow-sm' : ''">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                            </button>

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
                    </div>

                    <!-- Fields / Edit Form -->
                    <div class="px-5 py-6" x-show="isExpanded" x-transition x-cloak>
                        
                        <!-- 1. STATIC PREVIEW MODE (Read Only) -->
                        <div x-show="mode === 'preview'" class="space-y-3">
                            <!-- Label Above the Input (Text Right) -->
                            <div class="px-1 flex items-center justify-between">
                                <label class="text-sm font-black text-[#1C1C1C]" x-text="helpText"></label>
                                <button @click="mode = 'edit'" class="text-[10px] text-[#1A73E8] hover:underline font-black">تعديل</button>
                            </div>
                            
                            <!-- Static Mockup Input with Placeholder inside -->
                            <div class="relative group">
                                <template x-if="['text', 'number', 'price', 'date'].includes(type)">
                                    <div class="w-full px-5 py-4 bg-[#F8F9FA] border border-slate-200 rounded-xl text-sm font-bold text-slate-400 select-none shadow-sm text-right"
                                         x-text="placeholder || ('أدخل ' + title + '...')"></div>
                                </template>

                                <template x-if="type === 'radio' || type === 'checkbox' || type === 'select'">
                                    <div class="flex flex-wrap gap-3 p-3 bg-white border border-slate-200 rounded-xl">
                                        <template x-for="(opt, oIdx) in options" :key="oIdx">
                                            <div class="flex items-center gap-2 px-3 py-1.5 bg-slate-50 rounded-lg">
                                                <div class="w-3 h-3 rounded-full border border-slate-300" :class="type === 'checkbox' ? 'rounded-none' : ''"></div>
                                                <span class="text-[11px] font-bold text-slate-500" x-text="opt || 'خيار'"></span>
                                            </div>
                                        </template>
                                    </div>
                                </template>

                                <template x-if="type === 'file'">
                                    <div class="p-8 border-2 border-dashed border-slate-100 rounded-2xl text-center bg-white flex flex-col items-center">
                                         <svg class="w-8 h-8 text-slate-200 mb-2" fill="currentColor" viewBox="0 0 24 24"><path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM14 13v4h-4v-4H7l5-5 5 5h-3z"/></svg>
                                         <span class="text-xs font-black text-slate-300">معاينة لحقل رفع الملفات</span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- 2. FULL EDIT MODE (The Form) -->
                        <div x-show="mode === 'edit'">
                            <form action="{{ route('admin.categories.properties.update', [$category->id, $property->id]) }}" method="POST">
                                @csrf @method('PUT')
                                
                                    <div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm space-y-8">
                                        <!-- Label Edit -->
                                        <div>
                                            <label class="block text-sm font-black text-[#6F6F6F] mb-3 text-right">عنوان حقل الادخال</label>
                                            <input type="text" name="label" x-model="title" required
                                                   class="w-full px-5 py-4 bg-[#F8F9FA] border border-slate-200 rounded-xl text-sm font-bold text-[#1C1C1C]">
                                        </div>

                                        <!-- Placeholder Edit -->
                                        <div>
                                            <label class="block text-sm font-black text-[#6F6F6F] mb-3 text-right">النص التوضيحي (Placeholder)</label>
                                            <input type="text" name="placeholder" x-model="placeholder"
                                                   class="w-full px-5 py-4 bg-[#F8F9FA] border border-slate-200 rounded-xl text-sm font-bold text-[#1C1C1C]">
                                        </div>

                                        <!-- Toggle Advanced -->
                                        <button type="button" @click="showAdvanced = !showAdvanced" class="text-xs font-black text-[#1A73E8] flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" :class="showAdvanced ? 'rotate-180' : ''"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                            <span>خيارات متقدمة</span>
                                        </button>

                                        <div x-show="showAdvanced" x-transition class="space-y-8 pt-6 border-t border-slate-50">
                                            <!-- نص المساعدة -->
                                            <div>
                                                <label class="block text-sm font-black text-[#6F6F6F] mb-3 text-right">نص المساعدة</label>
                                                <input type="text" name="help_text" x-model="helpText" class="w-full px-5 py-4 bg-white border border-slate-200 rounded-xl text-sm font-bold text-[#1C1C1C]">
                                            </div>

                                            <!-- النوع والحالة -->
                                            <div class="grid grid-cols-2 gap-6">
                                                <!-- Type Selector -->
                                                <div class="relative" x-data="{ openT: false }">
                                                    <label class="block text-xs font-black text-[#6F6F6F] mb-3 text-right">النوع</label>
                                                    <div @click="openT = !openT" class="w-full px-5 py-4 bg-white border border-slate-200 rounded-xl flex justify-between items-center cursor-pointer">
                                                        <span class="text-xs font-bold" x-text="getTypeLabel(type)"></span>
                                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                                    </div>
                                                    <div x-show="openT" @click.away="openT = false" class="absolute z-30 w-full mt-1 bg-white border border-slate-100 rounded-xl shadow-xl p-2 max-h-48 overflow-y-auto">
                                                        @foreach(['text'=>'نصوص','number'=>'أرقام','price'=>'أسعار','radio'=>'اختيار واحد','checkbox'=>'اختيار متعدد','date'=>'تاريخ','file'=>'ملف','map'=>'خرائط'] as $v => $n)
                                                        <label class="flex items-center gap-2 p-2 hover:bg-slate-50 rounded-lg cursor-pointer">
                                                            <input type="radio" name="input_type" value="{{ $v }}" x-model="type" @change="openT = false" class="hidden">
                                                            <div class="w-3 h-3 rounded-full border-2 border-slate-200" :class="type === '{{ $v }}' ? 'border-[#1A73E8]' : ''"></div>
                                                            <span class="text-xs font-bold">{{ $n }}</span>
                                                        </label>
                                                        @endforeach
                                                    </div>
                                                </div>

                                                <!-- Status Selector -->
                                                <div class="relative" x-data="{ openS: false }">
                                                    <label class="block text-xs font-black text-[#6F6F6F] mb-3 text-right">الحالة</label>
                                                    <div @click="openS = !openS" class="w-full px-5 py-4 bg-white border border-slate-200 rounded-xl flex justify-between items-center cursor-pointer">
                                                        <span class="text-xs font-bold" x-text="getStatusLabel(req)"></span>
                                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                                    </div>
                                                    <div x-show="openS" @click.away="openS = false" class="absolute z-30 w-full mt-1 bg-white border border-slate-100 rounded-xl shadow-xl p-2">
                                                        <label class="flex items-center gap-2 p-2 hover:bg-slate-50 rounded-lg cursor-pointer">
                                                            <input type="radio" name="is_required" value="1" x-model="req" @change="openS = false" class="hidden">
                                                            <div class="w-3 h-3 rounded-full border-2 border-slate-200" :class="req === '1' ? 'border-[#1A73E8]' : ''"></div>
                                                            <span class="text-xs font-bold">إلزامي</span>
                                                        </label>
                                                        <label class="flex items-center gap-2 p-2 hover:bg-slate-50 rounded-lg cursor-pointer">
                                                            <input type="radio" name="is_required" value="0" x-model="req" @change="openS = false" class="hidden">
                                                            <div class="w-3 h-3 rounded-full border-2 border-slate-200" :class="req === '0' ? 'border-[#1A73E8]' : ''"></div>
                                                            <span class="text-xs font-bold">اختياري</span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Options Edit -->
                                            <div x-show="['checkbox','radio','select'].includes(type)" x-transition class="pt-2">
                                                <label class="block text-sm font-black text-[#6F6F6F] mb-4 text-right">قيم الاختيارات</label>
                                                <input type="hidden" name="options" :value="options.filter(o => o.trim() !== '').join(',')">
                                                <div class="space-y-3">
                                                    <template x-for="(opt, optIdx) in options" :key="optIdx">
                                                        <div class="flex items-center gap-2">
                                                            <input type="text" x-model="options[optIdx]" class="flex-1 px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm font-bold">
                                                            <button type="button" @click="options.splice(optIdx, 1)" class="text-red-400 hover:text-red-610"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                                                        </div>
                                                    </template>
                                                    <button type="button" @click="options.push('')" class="text-[10px] font-black p-1 text-[#1A73E8] hover:underline">+ خيار جديد</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Footer Btns -->
                                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                                        <button type="button" @click="isExpanded = false" class="px-8 py-2.5 bg-slate-100 text-slate-500 rounded-xl text-sm font-black">إلغاء</button>
                                        <button type="submit" class="px-10 py-2.5 bg-[#1A73E8] text-white rounded-xl text-sm font-black shadow-md">حفظ</button>
                                    </div>
                                </div>
                            </form>
                        </div>

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
