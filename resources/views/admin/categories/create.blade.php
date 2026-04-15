@extends('layouts.admin')

@section('title', 'إضافة فئة')

@section('content')
<div class="container-fluid mx-auto px-4 sm:px-6 lg:px-8 py-8" dir="rtl" x-data="categoryForm()">
    
    <!-- Title & Back -->
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('admin.categories.index') }}" class="p-2 bg-white border border-slate-200 rounded-xl text-slate-400 hover:text-[#1A73E8] transition-all">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path></svg>
        </a>
        <h1 class="text-2xl font-black text-[#1C1C1C]">إضافة فئة</h1>
    </div>

    <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf

        <!-- Main Data Card -->
        <div class="bg-white rounded-3xl p-8 border border-slate-100 shadow-sm space-y-8">
            <h2 class="text-lg font-black text-[#1C1C1C] border-b border-slate-50 pb-4">بيانات الفئة</h2>

            <!-- Image Upload -->
            <div class="space-y-4">
                <label class="text-sm font-black text-[#6F6F6F]">الصورة</label>
                <div @dragover.prevent="dragOver = true" 
                     @dragleave.prevent="dragOver = false"
                     @drop.prevent="handleDrop($event)"
                     :class="dragOver ? 'border-[#1A73E8] bg-blue-50' : 'border-slate-200 bg-slate-50'"
                     class="relative h-64 border-2 border-dashed rounded-3xl transition-all flex flex-col items-center justify-center gap-4 group">
                    
                    <input type="file" name="image" id="category_image" class="hidden" accept="image/*" @change="handleFileSelect($event)">
                    
                    <template x-if="!imagePreview">
                        <div class="flex flex-col items-center gap-4">
                            <div class="w-16 h-16 bg-white rounded-2xl shadow-sm flex items-center justify-center text-[#1A73E8]">
<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-cloud-upload" viewBox="0 0 16 16">
  <path fill-rule="evenodd" d="M4.406 1.342A5.53 5.53 0 0 1 8 0c2.69 0 4.923 2 5.166 4.579C14.758 4.804 16 6.137 16 7.773 16 9.569 14.502 11 12.687 11H10a.5.5 0 0 1 0-1h2.688C13.979 10 15 8.988 15 7.773c0-1.216-1.02-2.228-2.313-2.228h-.5v-.5C12.188 2.825 10.328 1 8 1a4.53 4.53 0 0 0-2.941 1.1c-.757.652-1.153 1.438-1.153 2.055v.448l-.445.049C2.064 4.805 1 5.952 1 7.318 1 8.785 2.23 10 3.781 10H6a.5.5 0 0 1 0 1H3.781C1.708 11 0 9.366 0 7.318c0-1.763 1.266-3.223 2.942-3.593.143-.863.698-1.723 1.464-2.383"/>
  <path fill-rule="evenodd" d="M7.646 4.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 5.707V14.5a.5.5 0 0 1-1 0V5.707L5.354 7.854a.5.5 0 1 1-.708-.708z"/>
</svg>                            </div>
                            <div class="text-center">
                                <p class="text-sm font-black text-[#1C1C1C]">اسحب الملف هنا</p>
                                <p class="text-xs font-bold text-slate-400 mt-1">أو</p>
                            </div>
                            <button type="button" @click="document.getElementById('category_image').click()" class="px-8 py-2.5 bg-white border border-[#1A73E8] text-[#1A73E8] rounded-xl text-sm font-black hover:bg-blue-50 transition-all">تصفح الملفات</button>
                        </div>
                    </template>

                    <template x-if="imagePreview">
                        <div class="relative w-full h-full p-4 flex items-center justify-center">
                            <img :src="imagePreview" class="max-h-[200px] w-auto object-contain rounded-2xl" alt="Preview">
                            <button type="button" @click="removeImage()" class="absolute top-4 left-4 p-2 bg-red-500 text-white rounded-full shadow-lg hover:bg-red-600 transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Name -->
            <div class="space-y-4">
                <label class="text-sm font-black text-[#6F6F6F]">اسم الفئة الرئيسية</label>
                <input type="text" name="name" required placeholder="أدخل اسم الفئة الرئيسية"
                       class="w-full px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-[15px] font-bold text-[#1C1C1C] placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#1A73E8]/10 focus:border-[#1A73E8] transition-all">
            </div>

            <!-- Sub Categories Repeater -->
            <div class="space-y-4">
                <label class="text-sm font-black text-[#6F6F6F]">اسم الفئة الفرعية</label>
                <div class="space-y-3">
                    <template x-for="(sub, index) in subCategories" :key="index">
                        <div class="flex items-center gap-3">
                            <input type="text" name="sub_categories[]" x-model="subCategories[index]" placeholder="أدخل اسم الفئة الفرعية"
                                   class="flex-1 px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-[15px] font-bold text-[#1C1C1C] placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#1A73E8]/10 focus:border-[#1A73E8] transition-all">
                            <button type="button" @click="removeSubCategory(index)" x-show="subCategories.length > 1"
                                    class="p-4 bg-red-50 text-red-500 rounded-2xl hover:bg-red-100 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                            <button type="button" x-show="index === subCategories.length - 1" @click="addSubCategory()"
                                    class="px-8 py-4 bg-[#1A73E8] text-white rounded-2xl font-black shadow-lg hover:shadow-xl transition-all active:scale-95">أضف</button>
                        </div>
                    </template>
                </div>
            </div>

            <div class="space-y-4 relative" :class="open ? 'z-50' : 'z-10'" x-data="{ 
                open: false, 
                selected: '{{ old('ad_type', 'ads') }}',
                get label() { 
                    if(this.selected === 'ads') return 'إعلانات';
                    if(this.selected === 'tenders') return 'مناقصات';
                    return 'حدد نوع الإعلانات';
                }
            }">
                <label class="text-sm font-black text-[#6F6F6F]">نوع الإعلانات</label>
                <div class="relative">
                    <button type="button" @click="open = !open" 
                            class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl text-[15px] font-bold text-[#1C1C1C] flex items-center justify-between focus:outline-none focus:ring-4 focus:ring-[#1A73E8]/5 focus:border-[#1A73E8] transition-all"
                            :class="open ? 'border-[#1A73E8] bg-white' : 'border-slate-50'">
                        <span x-text="label"></span>
                        <svg :class="open ? 'rotate-180 text-[#1A73E8]' : 'text-slate-400'" class="w-5 h-5 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>

                    <div x-show="open" @click.away="open = false" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-4"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="absolute z-[100] w-full mt-3 bg-white border border-slate-100 rounded-3xl shadow-[0_20px_50px_-12px_rgba(0,0,0,0.15)] p-2 space-y-1">
                        <label @click="selected = 'ads'; open = false" class="flex items-center justify-between p-4 bg-white hover:bg-slate-50 rounded-2xl cursor-pointer group transition-all">
                            <span class="text-[15px] font-black group-hover:text-[#1A73E8]">إعلانات</span>
                            <input type="radio" name="ad_type" value="ads" x-model="selected" class="w-6 h-6 text-[#1A73E8] focus:ring-[#1A73E8] border-slate-200">
                        </label>
                        <label @click="selected = 'tenders'; open = false" class="flex items-center justify-between p-4 bg-white hover:bg-slate-50 rounded-2xl cursor-pointer group transition-all">
                            <span class="text-[15px] font-black group-hover:text-[#1A73E8]">مناقصات</span>
                            <input type="radio" name="ad_type" value="tenders" x-model="selected" class="w-6 h-6 text-[#1A73E8] focus:ring-[#1A73E8] border-slate-200">
                        </label>
                    </div>
                </div>
            </div>

            <!-- Two Columns for Numeric Values -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Deposit -->
                <div class="space-y-4">
                    <label class="text-sm font-black text-[#6F6F6F]">قيمة العربون</label>
                    <div class="relative">
                        <input type="number" name="deposit_value" step="0.01" placeholder="أدخل قيمة العربون"
                               class="w-full pl-20 pr-4 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-[15px] font-bold text-[#1C1C1C] placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#1A73E8]/10 focus:border-[#1A73E8] transition-all">
                        <div class="absolute inset-y-0 left-0 pl-6 flex items-center pointer-events-none border-r border-slate-200 my-3 pr-4">
                            <span class="text-xs font-black text-slate-400">ريال</span>
                        </div>
                    </div>
                </div>

                <!-- Tax -->
                <div class="space-y-4">
                    <label class="text-sm font-black text-[#6F6F6F]">القيمة الضريبية</label>
                    <div class="relative">
                        <input type="number" name="tax_value" step="0.01" placeholder="أدخل القيمة الضريبية"
                               class="w-full pl-20 pr-4 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-[15px] font-bold text-[#1C1C1C] placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#1A73E8]/10 focus:border-[#1A73E8] transition-all">
                        <div class="absolute inset-y-0 left-0 pl-6 flex items-center pointer-events-none border-r border-slate-200 my-3 pr-4">
                            <span class="text-xs font-black text-slate-400">ريال</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pages Section -->
        <div class="bg-white rounded-3xl p-8 border border-slate-100 shadow-sm space-y-6">
            <h2 class="text-lg font-black text-[#1C1C1C] border-b border-slate-50 pb-4">صفحات الفئة</h2>
            <div class="space-y-4">
                <label class="text-sm font-black text-[#6F6F6F]">اسم الصفحة</label>
                <div class="space-y-3">
                    <template x-for="(page, index) in pages" :key="index">
                        <div class="flex items-center gap-3">
                            <input type="text" name="pages[]" x-model="pages[index]" placeholder="أدخل اسم الصفحة"
                                   class="flex-1 px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-[15px] font-bold text-[#1C1C1C] placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#1A73E8]/10 focus:border-[#1A73E8] transition-all">
                            <button type="button" @click="removePage(index)" x-show="pages.length > 1"
                                    class="p-4 bg-red-50 text-red-500 rounded-2xl hover:bg-red-100 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                            <button type="button" x-show="index === pages.length - 1" @click="addPage()"
                                    class="px-8 py-4 bg-[#1A73E8] text-white rounded-2xl font-black shadow-lg hover:shadow-xl transition-all active:scale-95">أضف</button>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Services Section -->
        <div class="bg-white rounded-3xl p-8 border border-slate-100 shadow-sm space-y-6">
            <h2 class="text-lg font-black text-[#1C1C1C] border-b border-slate-50 pb-4">خدمات الفئة</h2>
            <div class="space-y-4">
                <label class="text-sm font-black text-[#6F6F6F]">اسم الخدمة</label>
                <div class="space-y-3">
                    <template x-for="(service, index) in services" :key="index">
                        <div class="flex items-center gap-3">
                            <input type="text" name="services[]" x-model="services[index]" placeholder="أدخل اسم الخدمة"
                                   class="flex-1 px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-[15px] font-bold text-[#1C1C1C] placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#1A73E8]/10 focus:border-[#1A73E8] transition-all">
                            <button type="button" @click="removeService(index)" x-show="services.length > 1"
                                    class="p-4 bg-red-50 text-red-500 rounded-2xl hover:bg-red-100 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                            <button type="button" x-show="index === services.length - 1" @click="addService()"
                                    class="px-8 py-4 bg-[#1A73E8] text-white rounded-2xl font-black shadow-lg hover:shadow-xl transition-all active:scale-95">أضف</button>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-4 pb-12">
            <a href="{{ route('admin.categories.index') }}" class="inline-flex items-center justify-center px-8 py-4 bg-slate-200 text-slate-600 rounded-2xl font-black hover:bg-slate-300 transition-all">إلغاء</a>
            <button type="submit" class="px-8 py-4 bg-[#1A73E8] text-white rounded-2xl font-black shadow-[0_10px_30px_-10px_rgba(26,115,232,0.5)] hover:bg-blue-700 transition-all active:scale-95">إضافة فئة</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    function categoryForm() {
        return {
            dragOver: false,
            imagePreview: null,
            subCategories: [''],
            pages: [''],
            services: [''],
            
            handleFileSelect(event) {
                const file = event.target.files[0];
                if (file) {
                    this.previewImage(file);
                }
            },
            
            handleDrop(event) {
                this.dragOver = false;
                const file = event.dataTransfer.files[0];
                if (file) {
                    const input = document.getElementById('category_image');
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    input.files = dataTransfer.files;
                    this.previewImage(file);
                }
            },
            
            previewImage(file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.imagePreview = e.target.result;
                };
                reader.readAsDataURL(file);
            },
            
            removeImage() {
                this.imagePreview = null;
                document.getElementById('category_image').value = '';
            },
            
            addSubCategory() {
                this.subCategories.push('');
            },
            
            removeSubCategory(index) {
                this.subCategories.splice(index, 1);
            },
            
            addPage() {
                this.pages.push('');
            },
            
            removePage(index) {
                this.pages.splice(index, 1);
            },
            
            addService() {
                this.services.push('');
            },
            
            removeService(index) {
                this.services.splice(index, 1);
            }
        }
    }
</script>
@endpush
