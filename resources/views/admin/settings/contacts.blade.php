@extends('layouts.admin')

@section('title', 'وسائل التواصل')

@section('content')
<div class="max-w-4xl mx-auto space-y-8 animate-in fade-in slide-in-from-bottom-6 duration-1000 pb-10">
    
    <!-- Title -->
    <div>
        <h2 class="text-[32px] font-black text-[#1C1C1C]">وسائل التواصل</h2>
    </div>

    <form action="{{ route('admin.settings.contacts.update') }}" method="POST" class="space-y-8" 
          x-data="{ 
            links: {{ isset($settings['social_links']) ? $settings['social_links'] : '[]' }},
            newPlatform: 'فيسبوك',
            newLink: '',
            addLink() {
                if (this.newLink.trim()) {
                    this.links.push({ platform: this.newPlatform, url: this.newLink });
                    this.newLink = '';
                }
            },
            removeLink(index) {
                this.links.splice(index, 1);
            }
          }">
        @csrf
        
        <div class="bg-white rounded-[2.5rem] p-10 shadow-[0_20px_70px_-20px_rgba(0,0,0,0.05)] border border-slate-50 space-y-10">
            
            <!-- Basic Contacts -->
            <div class="space-y-8">
                <!-- Mobile Number -->
                <div class="space-y-3 text-right">
                    <label class="text-sm font-black text-slate-900 pr-2">رقم الجوال</label>
                    <div class="relative mt-2">
                        <input type="text" name="phone" value="{{ $settings['phone'] ?? '' }}" 
                               maxlength="9"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                               placeholder="5xxxxxxxx"
                               class="w-full h-14 bg-slate-50 border {{ $errors->has('phone') ? 'border-rose-500' : 'border-slate-200' }} rounded-2xl  pl-4 text-sm font-bold text-[#1C1C1C] outline-none focus:border-[#1A73E8] focus:ring-4 focus:ring-blue-50 text-left">
                        <div class="absolute inset-y-0 right-0 px-5 flex items-center border-r border-slate-200 bg-slate-100/50 rounded-r-2xl text-slate-400 font-black text-sm">
                            966+
                        </div>
                    </div>
                    @error('phone')
                        <p class="text-rose-500 text-xs font-bold pt-1 pr-2">يجب أن يتكون رقم الجوال من 9 أرقام (مثال: 5xxxxxxxx)</p>
                    @enderror
                </div>

                <!-- Email Address -->
                <div class="space-y-3 text-right mb-4">
                    <label class="text-sm font-black text-slate-900 pr-2">البريد الإلكتروني</label>
                    <input type="email" name="email" value="{{ $settings['email'] ?? '' }}" placeholder="أدخل البريد الإلكتروني"
                           class="w-full mt-2 h-14 bg-slate-50 border border-slate-200 rounded-2xl px-6 text-sm font-bold text-[#1C1C1C] outline-none focus:border-[#1A73E8] focus:ring-4 focus:ring-blue-50 transition-all text-right">
                </div>
            </div>

            <div class="w-full h-px bg-slate-50"></div>

            <!-- Social Media Links -->
            <div class="space-y-3">
                <label class="text-sm font-black text-slate-900 pr-2 block text-right">روابط منصات التواصل الاجتماعي</label>
                
                <!-- Add New Link Row -->
                <div class="flex items-center gap-4">
                    <!-- Add Button -->
                    <button type="button" @click="addLink()" class="h-14 px-8 bg-[#1A73E8] text-white rounded-2xl font-black text-sm shadow-lg shadow-blue-100 hover:bg-blue-700 transition-all shrink-0">
                        أضف
                    </button>

                    <!-- Link Input -->
                    <div class="flex-1">
                        <input type="text" x-model="newLink" placeholder="أدخل رابط المنصة"
                               class="w-full h-14 bg-slate-50 border border-slate-200 rounded-2xl px-6 text-sm font-bold text-[#1C1C1C] outline-none focus:border-[#1A73E8] transition-all text-right">
                    </div>

                    <!-- Platform Dropdown -->
                    <div class="w-64 relative" x-data="{ open: false }">
                        <button type="button" @click="open = !open" 
                                class="w-full h-14 bg-slate-50 border border-slate-200 rounded-2xl px-6 flex items-center justify-between group hover:border-[#1A73E8] transition-all">
                            <span class="text-slate-400 font-bold" x-text="newPlatform">اختر المنصة</span>
                            <svg class="w-5 h-5 text-slate-300 group-hover:text-[#1A73E8] transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        
                        <div x-show="open" @click.away="open = false" x-cloak 
                             class="absolute inset-x-0 mt-3 p-4 bg-white border border-slate-100 rounded-[2rem] shadow-2xl z-50 space-y-2">
                            @foreach(['فيسبوك', 'إنستغرام', 'تويتر', 'لينكدان', 'الموقع'] as $platform)
                            <label class="flex items-center justify-between p-4 rounded-2xl cursor-pointer hover:bg-slate-50 transition-all" 
                                   @click="newPlatform = '{{ $platform }}'; open = false">
                                <span class="text-sm font-bold text-[#6F6F6F]">{{ $platform }}</span>
                                <div class="w-5 h-5 border-2 border-slate-200 rounded-full flex items-center justify-center transition-all" 
                                     :class="newPlatform === '{{ $platform }}' ? 'border-[#1A73E8] bg-[#1A73E8]' : ''">
                                    <div class="w-1.5 h-1.5 bg-white rounded-full"></div>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Existing Links List -->
                <div class="space-y-4">
                    <template x-for="(link, index) in links" :key="index">
                        <div class="flex items-center gap-4 animate-in fade-in slide-in-from-top-2">
                            <!-- Trash Button -->
                            <button type="button" @click="removeLink(index)" class="w-14 h-14 bg-rose-50 text-rose-500 rounded-2xl flex items-center justify-center hover:bg-rose-500 hover:text-white transition-all shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>

                            <!-- Link Display -->
                            <div class="flex-1 h-14 bg-slate-50 border border-slate-200 rounded-2xl px-6 flex items-center text-sm font-bold text-[#1C1C1C]">
                                <input type="hidden" :name="`social_links[${index}][platform]`" :value="link.platform">
                                <input type="hidden" :name="`social_links[${index}][url]`" :value="link.url">
                                <span x-text="link.url"></span>
                            </div>

                            <!-- Platform Label -->
                            <div class="w-64 h-14 bg-slate-50 border border-slate-200 rounded-2xl px-6 flex items-center justify-between text-slate-400 font-bold">
                                <span x-text="link.platform"></span>
                                <svg class="w-5 h-5 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="w-full h-px bg-slate-50"></div>

            <!-- Copyright -->
            <div class="space-y-3 text-right mt-2">
                <label class="text-sm font-black text-slate-900 pr-2">حقوق النشر</label>
                <input type="text" name="copyright" value="{{ $settings['copyright'] ?? '' }}" placeholder="© 2025 جميع الحقوق محفوظة"
                       class="w-full mt-2 h-14 bg-slate-50 border border-slate-200 rounded-2xl px-6 text-sm font-bold text-[#1C1C1C] outline-none focus:border-[#1A73E8] transition-all text-right">
            </div>

            <!-- Footer Actions -->
            <div class="flex items-center justify-end gap-4 pt-4">
                <a href="{{ route('admin.dashboard') }}" class="px-8 py-4 bg-slate-100 text-[#6F6F6F] rounded-2xl font-black text-lg hover:bg-slate-200 transition-all text-center">
                    إلغاء
                </a>
                <button type="submit" class="px-8 py-4 bg-[#1A73E8] text-white rounded-2xl font-black text-lg shadow-xl shadow-blue-100 hover:bg-blue-700 transition-all">
                    حفظ
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
