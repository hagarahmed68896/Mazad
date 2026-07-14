@extends('layouts.admin')

@section('title', 'إضافة خصائص')

@section('content')
<!-- Delete Confirmation Modal -->
<div x-data="{ show: false, targetIndex: null }"
     x-on:open-delete-modal.window="show = true; targetIndex = $event.detail.index"
     x-show="show"
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center"
     style="display:none">

    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="show = false"></div>

    <!-- Modal Box -->
    <div class="relative bg-white rounded-2xl shadow-2xl p-8 w-full max-w-sm mx-4 text-center"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95">

        <!-- Icon -->
        <div class="flex items-center justify-center w-16 h-16 bg-red-100 rounded-full mx-auto mb-4">
            <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
        </div>

        <h3 class="text-lg font-black text-[#1C1C1C] mb-2">حذف الخاصية</h3>
        <p class="text-sm text-slate-500 font-medium mb-6">هل أنت متأكد من حذف هذه الخاصية؟<br>لا يمكن التراجع عن هذا الإجراء.</p>

        <div class="flex gap-3 justify-center" dir="rtl">
            <button type="button"
                    @click="show = false"
                    class="flex-1 px-5 py-2.5 bg-slate-100 text-slate-600 rounded-xl text-sm font-black hover:bg-slate-200 transition-all">
                إلغاء
            </button>
            <button type="button"
                    @click="$dispatch('confirm-delete', { index: targetIndex }); show = false"
                    class="flex-1 px-5 py-2.5 bg-red-500 text-white rounded-xl text-sm font-black hover:bg-red-600 transition-all">
                نعم، احذف
            </button>
        </div>
    </div>
</div>

<div class="container-fluid mx-auto px-4 sm:px-6 lg:px-8 py-8" dir="rtl" x-data="propertiesBuilder()" x-on:confirm-delete.window="removeProperty($event.detail.index)">

    <!-- Header -->
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('admin.categories.properties.index', $category->id) }}" class="p-2 bg-white border border-slate-200 rounded-xl text-slate-400 hover:text-[#1A73E8] transition-all">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path></svg>
        </a>
        <h1 class="text-2xl font-black text-[#1C1C1C]">إضافة خصائص</h1>
    </div>

    @if($errors->any())
        <div class="mb-6 max-w-4xl px-5 py-4 bg-red-50 border border-red-200 rounded-2xl text-red-700 font-bold text-sm space-y-1">
            @foreach($errors->all() as $error)
                <p>• {{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form action="{{ route('admin.categories.properties.store', $category->id) }}" method="POST">
        @csrf

        <div class="max-w-4xl bg-[#F8F9FA] border border-slate-100 rounded-3xl p-6 shadow-sm relative">

        <div class="flex justify-between items-center mb-6">
                <h2 class="text-lg font-black text-[#1C1C1C] text-right">بيانات الخصائص</h2>
            <!-- أضف حقل إدخال Button at top left -->
            <button type="button" @click="addProperty()"
                    class="flex items-center gap-2 px-4 py-2.5 bg-[#1A73E8] text-white rounded-xl text-sm font-black shadow-sm hover:bg-blue-700 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                أضف خاصية
            </button>
</div>
          
            
            <div class="space-y-6">

                <!-- الفئة -->
                <div>
                    <label class="block text-sm font-black text-[#6F6F6F] mb-2 text-right">الفئة</label>
                    <input type="text" readonly value="{{ $category->name }}"
                           class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-500 cursor-not-allowed">
                </div>

                <!-- اسم الصفحة -->
                <div>
                    <label class="block text-sm font-black text-[#6F6F6F] mb-2 text-right">اسم الصفحة</label>
                    <select name="category_page_id"
                            class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-xl text-sm font-bold text-[#1C1C1C] focus:outline-none focus:ring-2 focus:ring-[#1A73E8]/10 focus:border-[#1A73E8] transition-all cursor-pointer">
                        <option value="" selected>خصائص عامة </option>
                        @foreach($pages as $page)
                            <option value="{{ $page->id }}">{{ $page->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Property Blocks Loop -->
                <template x-for="(prop, index) in properties" :key="prop.uid">
                    <div class="bg-[#F8F9FA] rounded-2xl border border-slate-200">
                        
                        <!-- Block Header Row -->
                        <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-200 bg-[#F1F3F5] rounded-t-2xl cursor-pointer hover:bg-slate-200 transition-colors"
                             @click="prop.isExpanded = !prop.isExpanded">
                            <!-- Grid Right -->
                            <div class="text-slate-400 cursor-move">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <rect x="3" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="1.8" fill="none"/>
                                    <rect x="14" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="1.8" fill="none"/>
                                    <rect x="3" y="14" width="7" height="7" rx="1" stroke="currentColor" stroke-width="1.8" fill="none"/>
                                    <rect x="14" y="14" width="7" height="7" rx="1" stroke="currentColor" stroke-width="1.8" fill="none"/>
                                </svg>
                            </div>

                            <span class="text-sm font-black text-[#1C1C1C]" x-text="prop.label || 'عنوان حقل الادخال'"></span>

                            <!-- Trash -->
                            <button type="button" @click.stop="$dispatch('open-delete-modal', { index: index })"
                                    class="p-1.5 text-slate-400 hover:text-red-500 hover:bg-white rounded-lg transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Fields -->
                        <div class="px-5 py-6 space-y-4" x-show="prop.isExpanded" x-transition>

                            <!-- عنوان حقل الادخال -->
                            <div class="flex items-center">
                                <label class="w-32 shrink-0 text-sm font-black text-[#6F6F6F] text-right">عنوان حقل الادخال</label>
                                <input type="text"
                                       :name="`properties[${index}][label]`"
                                       x-model="prop.label"
                                       placeholder="أدخل عنوان حقل الادخال"
                                       required
                                       class="flex-1 px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm font-bold text-[#1C1C1C] placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#1A73E8]/10 focus:border-[#1A73E8] transition-all">
                            </div>

                            <!-- النص التوضيحي -->
                            <div class="flex items-center">
                                <label class="w-32 shrink-0 text-sm font-black text-[#6F6F6F] text-right">النص التوضيحي</label>
                                <input type="text"
                                       :name="`properties[${index}][placeholder]`"
                                       x-model="prop.placeholder"
                                       placeholder="أدخل النص التوضيحي"
                                       class="flex-1 px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm font-bold text-[#1C1C1C] placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#1A73E8]/10 focus:border-[#1A73E8] transition-all">
                            </div>

                            <!-- نص المساعدة -->
                            <div class="flex items-center">
                                <label class="w-32 shrink-0 text-sm font-black text-[#6F6F6F] text-right">نص المساعدة</label>
                                <input type="text"
                                       :name="`properties[${index}][help_text]`"
                                       x-model="prop.help_text"
                                       placeholder="أدخل نص المساعدة"
                                       class="flex-1 px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm font-bold text-[#1C1C1C] placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#1A73E8]/10 focus:border-[#1A73E8] transition-all">
                            </div>

                            <!-- نوع حقل الادخال -->
                            <div class="flex items-start">
                                <label class="w-32 shrink-0 text-sm font-black text-[#6F6F6F] pt-3 text-right">نوع حقل الادخال</label>
                                <div class="flex-1 relative" x-data="{ openType: false }">
                                    <div @click="openType = !openType"
                                         class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl flex justify-between items-center cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#1A73E8]/10 focus:border-[#1A73E8] transition-all">
                                        <span class="text-sm font-bold text-[#1C1C1C]" x-text="getTypeLabel(prop.input_type)"></span>
                                        <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="openType ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </div>
                                    
                                    <div x-show="openType" @click.away="openType = false" x-transition x-cloak
                                         class="absolute z-10 w-full mt-2 bg-white border border-slate-200 rounded-xl shadow-lg p-4 space-y-3">
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" :name="`properties[${index}][input_type]`" value="text" x-model="prop.input_type" @change="openType = false" class="w-4 h-4 text-[#1A73E8] border-slate-300 focus:ring-[#1A73E8]">
                                            <span class="text-sm font-bold text-[#1C1C1C]">النصوص</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" :name="`properties[${index}][input_type]`" value="number" x-model="prop.input_type" @change="openType = false" class="w-4 h-4 text-[#1A73E8] border-slate-300 focus:ring-[#1A73E8]">
                                            <span class="text-sm font-bold text-[#1C1C1C]">الأرقام</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" :name="`properties[${index}][input_type]`" value="price" x-model="prop.input_type" @change="openType = false" class="w-4 h-4 text-[#1A73E8] border-slate-300 focus:ring-[#1A73E8]">
                                            <span class="text-sm font-bold text-[#1C1C1C]">الأسعار</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" :name="`properties[${index}][input_type]`" value="radio" x-model="prop.input_type" @change="openType = false" class="w-4 h-4 text-[#1A73E8] border-slate-300 focus:ring-[#1A73E8]">
                                            <span class="text-sm font-bold text-[#1C1C1C]">اختيار واحد</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" :name="`properties[${index}][input_type]`" value="select" x-model="prop.input_type" @change="openType = false" class="w-4 h-4 text-[#1A73E8] border-slate-300 focus:ring-[#1A73E8]">
                                            <span class="text-sm font-bold text-[#1C1C1C]">قائمة منسدلة</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" :name="`properties[${index}][input_type]`" value="checkbox" x-model="prop.input_type" @change="openType = false" class="w-4 h-4 text-[#1A73E8] border-slate-300 focus:ring-[#1A73E8]">
                                            <span class="text-sm font-bold text-[#1C1C1C]">اختيار متعدد</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" :name="`properties[${index}][input_type]`" value="date" x-model="prop.input_type" @change="openType = false" class="w-4 h-4 text-[#1A73E8] border-slate-300 focus:ring-[#1A73E8]">
                                            <span class="text-sm font-bold text-[#1C1C1C]">التواريخ والأوقات</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" :name="`properties[${index}][input_type]`" value="file" x-model="prop.input_type" @change="openType = false" class="w-4 h-4 text-[#1A73E8] border-slate-300 focus:ring-[#1A73E8]">
                                            <span class="text-sm font-bold text-[#1C1C1C]">الملفات والصور</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" :name="`properties[${index}][input_type]`" value="map" x-model="prop.input_type" @change="openType = false" class="w-4 h-4 text-[#1A73E8] border-slate-300 focus:ring-[#1A73E8]">
                                            <span class="text-sm font-bold text-[#1C1C1C]">الموقع الجغرافي</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- قيم الاختيارات (for multi/select types) -->
                            <div x-show="['checkbox','select','radio'].includes(prop.input_type)" x-transition class="flex items-start">
                                <label class="w-32 shrink-0 text-sm font-black text-[#6F6F6F] pt-3 text-right">قيم الاختيارات</label>
                                <div class="flex-1 flex gap-2">
                                    <template x-for="(opt, optIdx) in prop.options" :key="optIdx">
                                        <div class="flex-1 flex items-center mb-2">
                                            <input type="text"
                                                   :name="`properties[${index}][options][]`"
                                                   x-model="prop.options[optIdx]"
                                                   placeholder="أدخل قيم الاختيارات"
                                                   class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm font-bold text-[#1C1C1C] placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#1A73E8]/10 focus:border-[#1A73E8] transition-all">
                                        </div>
                                    </template>
                                    <button type="button" @click="addOption(index)"
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
                                        <span class="text-sm font-bold text-[#1C1C1C]" x-text="getStatusLabel(prop.is_required)"></span>
                                        <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="openStatus ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </div>
                                    
                                    <div x-show="openStatus" @click.away="openStatus = false" x-transition x-cloak
                                         class="absolute bottom-full mb-2 w-full bg-white border border-slate-200 rounded-xl shadow-lg p-4 space-y-3 z-10">
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" :name="`properties[${index}][is_required]`" value="1" x-model="prop.is_required" @change="openStatus = false" class="w-4 h-4 text-[#1A73E8] border-slate-300 focus:ring-[#1A73E8]">
                                            <span class="text-sm font-bold text-[#1C1C1C]">إلزامي</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" :name="`properties[${index}][is_required]`" value="0" x-model="prop.is_required" @change="openStatus = false" class="w-4 h-4 text-[#1A73E8] border-slate-300 focus:ring-[#1A73E8]">
                                            <span class="text-sm font-bold text-[#1C1C1C]">اختياري</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- إمكانية الإخفاء -->
                            <div class="flex items-start">
                                <label class="w-32 shrink-0 text-sm font-black text-[#6F6F6F] pt-3 text-right">إمكانية الإخفاء</label>
                                <div class="flex-1 relative" x-data="{ openHideable: false }">
                                    <div @click="openHideable = !openHideable"
                                         class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl flex justify-between items-center cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#1A73E8]/10 focus:border-[#1A73E8] transition-all">
                                        <span class="text-sm font-bold text-[#1C1C1C]" x-text="getHideableLabel(prop.is_hideable)"></span>
                                        <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="openHideable ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </div>
                                    
                                    <div x-show="openHideable" @click.away="openHideable = false" x-transition x-cloak
                                         class="absolute bottom-full mb-2 w-full bg-white border border-slate-200 rounded-xl shadow-lg p-4 space-y-3 z-10">
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" :name="`properties[${index}][is_hideable]`" value="1" x-model="prop.is_hideable" @change="openHideable = false" class="w-4 h-4 text-[#1A73E8] border-slate-300 focus:ring-[#1A73E8]">
                                            <span class="text-sm font-bold text-[#1C1C1C]">مسموح للمعلن بإخفائها عن المستخدمين</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" :name="`properties[${index}][is_hideable]`" value="0" x-model="prop.is_hideable" @change="openHideable = false" class="w-4 h-4 text-[#1A73E8] border-slate-300 focus:ring-[#1A73E8]">
                                            <span class="text-sm font-bold text-[#1C1C1C]">غير مسموح بالإخفاء (تظهر دائماً)</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- حفظ / إلغاء -->
                            <div class="flex items-center justify-end gap-3 pt-4">
                                <button type="button" @click="removeProperty(index)"
                                        class="px-8 py-2.5 bg-slate-200 text-slate-600 rounded-xl text-sm font-black hover:bg-slate-300 transition-all">
                                    إلغاء
                                </button>
                                <button type="button"
                                        @click="if (prop.label.trim()) { prop.isExpanded = false; } else { $el.closest('.rounded-2xl').querySelector('input[required]').reportValidity(); }"
                                        class="px-8 py-2.5 bg-[#1A73E8] text-white rounded-xl text-sm font-black hover:bg-blue-700 transition-all">
                                    حفظ
                                </button>
                            </div>
                        </div>
                    </div>
                </template>


            
            <div class="mt-8 text-center pt-4">
                <button type="submit"
                        class="w-full flex items-center justify-center gap-2 py-4 bg-[#1A73E8] text-white rounded-xl text-sm font-black shadow-lg hover:bg-blue-700 transition-all">
                    حفظ
                </button>
            </div>
        </div>

    </form>
</div>

@push('scripts')
<script>
    function propertiesBuilder() {
        let uid = 0;
        return {
            properties: [],

            addProperty() {
                this.properties.push({
                    uid: ++uid,
                    label: '',
                    placeholder: '',
                    help_text: '',
                    input_type: '',
                    is_required: '',
                    is_hideable: '0',
                    options: [''],
                    isExpanded: true
                });
            },

            removeProperty(index) {
                this.properties.splice(index, 1);
            },

            addOption(propIndex) {
                this.properties[propIndex].options.push('');
            },

            removeOption(propIndex, optIdx) {
                if (this.properties[propIndex].options.length > 1) {
                    this.properties[propIndex].options.splice(optIdx, 1);
                }
            },
            
            getTypeLabel(val) {
                const types = {
                    'text': 'النصوص',
                    'number': 'الأرقام',
                    'price': 'الأسعار',
                    'radio': 'اختيار واحد',
                    'select': 'قائمة منسدلة',
                    'checkbox': 'اختيار متعدد',
                    'date': 'التواريخ والأوقات',
                    'file': 'الملفات والصور',
                    'map': 'الموقع الجغرافي'
                };
                return types[val] || 'حدد نوع حقل الادخال';
            },

            getStatusLabel(val) {
                if(val === '1') return 'إلزامي';
                if(val === '0') return 'اختياري';
                return 'حدد الحالة';
            },

            getHideableLabel(val) {
                if(val === '1' || val === 1 || val === true) return 'مسموح للمعلن بإخفائها عن المستخدمين';
                return 'غير مسموح بالإخفاء (تظهر دائماً)';
            }
        }
    }
</script>
@endpush
@endsection
