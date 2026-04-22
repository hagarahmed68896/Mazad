@extends('layouts.admin')

@section('title', 'الملف الشخصي')

@section('content')
<div class="max-w-4xl mx-auto space-y-8 animate-in fade-in slide-in-from-bottom-6 duration-1000 pb-10" x-data="{ 
    tab: 'info',
    showAvatarModal: false,
    imageUrl: null,
    previewImage(event) {
        const file = event.target.files[0];
        if (file) {
            this.imageUrl = URL.createObjectURL(file);
        }
    }
}">
    
    <!-- Title -->
    <div>
        <h2 class="text-[32px] font-black text-[#1C1C1C]">الملف الشخصي</h2>
    </div>

    <!-- User Header Card -->
    <div class="bg-white rounded-[2.5rem] p-8 shadow-[0_20px_70px_-20px_rgba(0,0,0,0.05)] border border-slate-50 flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="flex items-center gap-6">
            <!-- Avatar -->
            <div class="relative group">
                <div class="w-24 h-24 bg-[#1A73E8] rounded-[2rem] flex items-center justify-center text-white text-4xl font-black shadow-xl shadow-blue-100 transition-transform group-hover:scale-105 duration-300 overflow-hidden">
                    @if(Auth::user()->avatar)
                        <img src="{{ asset(Auth::user()->avatar) }}" class="w-full h-full object-cover">
                    @else
                        {{ substr(Auth::user()->name, 0, 1) }}
                    @endif
                </div>
            </div>
            <!-- Name & Meta -->
            <div class="space-y-1 text-right md:text-right">
                <h3 class="text-2xl font-black text-[#1C1C1C]">{{ Auth::user()->name }}</h3>
                <p class="text-slate-400 font-bold">الآدمن</p>
            </div>
        </div>

        <!-- Header Actions -->
        <div class="flex items-center gap-3">
            <button @click="showAvatarModal = true" class="px-6 py-3.5 bg-[#1A73E8] text-white rounded-xl font-black text-sm flex items-center gap-2 hover:bg-blue-700 transition-all shadow-lg shadow-blue-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                تعديل الصورة
            </button>
            <button class="px-6 py-3.5 bg-slate-100 text-slate-400 rounded-xl font-black text-sm hover:bg-rose-50 hover:text-rose-500 transition-all">
                حذف الصورة
            </button>
        </div>
    </div>

    <!-- Interface Switching Tabs -->
    <div class="flex items-center gap-3 relative z-10">
        <button @click="tab = 'info'" 
                :class="tab === 'info' ? 'bg-[#1A73E8] text-white shadow-xl shadow-blue-100' : 'bg-white text-slate-400 border border-slate-100'"
                class="px-8 py-4 rounded-2xl font-black text-sm transition-all duration-300">
            بيانات الحساب
        </button>
        <button @click="tab = 'password'" 
                :class="tab === 'password' ? 'bg-[#1A73E8] text-white shadow-xl shadow-blue-100' : 'bg-white text-slate-400 border border-slate-100'"
                class="px-8 py-4 rounded-2xl font-black text-sm transition-all duration-300">
            كلمة المرور
        </button>
    </div>

    <!-- Avatar Upload Modal -->
    <template x-teleport="body">
        <div x-show="showAvatarModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div x-show="showAvatarModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="showAvatarModal = false"></div>
            
            <div x-show="showAvatarModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0" class="relative bg-white rounded-[2.5rem] w-full max-w-md p-10 shadow-2xl border border-slate-100">
                <div class="flex flex-col items-center text-center gap-8">
                    <div class="w-20 h-20 bg-blue-50 rounded-3xl flex items-center justify-center text-[#1A73E8] shadow-inner">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    
                    <div class="space-y-2">
                        <h3 class="text-2xl font-black text-slate-800">تحديث الصورة الشخصية</h3>
                        <p class="text-slate-500 font-bold leading-relaxed">اختر صورة جديدة لملفك الشخصي. يفضل أن تكون مربعة وبصيغة PNG أو JPG.</p>
                    </div>

                    <form action="{{ route('admin.profile.update-avatar') }}" method="POST" enctype="multipart/form-data" class="w-full space-y-6">
                        @csrf
                        <!-- Preview Area -->
                        <div class="relative group cursor-pointer" @click="$refs.avatarInput.click()">
                            <div class="w-full h-48 bg-slate-50 border-2 border-dashed border-slate-200 rounded-[2rem] flex items-center justify-center overflow-hidden transition-all group-hover:border-[#1A73E8] group-hover:bg-blue-50/30">
                                <template x-if="!imageUrl">
                                    <div class="flex flex-col items-center gap-2 text-slate-400">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                        <span class="font-bold text-sm">اضغط لاختيار صورة</span>
                                    </div>
                                </template>
                                <template x-if="imageUrl">
                                    <img :src="imageUrl" class="w-full h-full object-cover">
                                </template>
                            </div>
                            <input type="file" name="avatar" x-ref="avatarInput" class="hidden" accept="image/*" @change="previewImage">
                        </div>

                        <div class="flex items-center gap-4 w-full pt-2">
                            <button type="button" @click="showAvatarModal = false; imageUrl = null" class="flex-1 px-6 py-4 bg-slate-100 text-slate-500 rounded-2xl font-black hover:bg-slate-200 transition-all active:scale-95">إلغاء</button>
                            <button type="submit" class="flex-1 px-6 py-4 bg-[#1A73E8] text-white rounded-2xl font-black shadow-xl shadow-blue-500/20 hover:scale-[1.02] active:scale-95 transition-all">حفظ الصورة</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>

    <!-- Sections Content -->
    <div class="bg-white rounded-[2.5rem] shadow-[0_20px_70px_-20px_rgba(0,0,0,0.05)] border border-slate-50 overflow-hidden">
        
        <!-- Info Form -->
        <div x-show="tab === 'info'" x-transition class="p-10">
            <form action="{{ route('admin.profile.update') }}" method="POST" class="space-y-8">
                @csrf
                <div class="space-y-6">
                    <!-- Full Name -->
                    <div class="space-y-3 text-right">
                        <label class="text-sm font-black text-slate-900 pr-2">الاسم الكامل</label>
                        <input type="text" name="name" value="{{ $user->name }}" required placeholder="أدخل الاسم كامل"
                               class="w-full mt-2 h-14 bg-slate-50 border border-slate-200 rounded-2xl px-6 text-sm font-bold text-[#1C1C1C] outline-none focus:border-[#1A73E8] focus:ring-4 focus:ring-blue-50 transition-all">
                    </div>

                    <!-- Mobile Number -->
                    <div class="space-y-3 text-right">
                        <label class="text-sm font-black text-slate-900 pr-2">رقم الجوال</label>
                        <div class="relative mt-2" dir="ltr">
                            <input type="text" name="phone" value="{{ $user->phone }}" 
                                   maxlength="9"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                   placeholder="5xxxxxxxx"
                                   class="w-full h-14 bg-slate-50 border {{ $errors->has('phone') ? 'border-rose-500' : 'border-slate-200' }} rounded-2xl pl-4 pr-20 text-sm font-bold text-[#1C1C1C] outline-none focus:border-[#1A73E8] focus:ring-4 focus:ring-blue-50 transition-all text-left">
                            <div class="absolute inset-y-0 right-0 px-4 flex items-center border-r border-slate-200 bg-slate-100/50 rounded-r-2xl text-slate-400 font-black text-sm">
                                966+
                            </div>
                        </div>
                        @error('phone')
                            <p class="text-rose-500 text-xs font-bold pt-1 pr-2 text-right" dir="rtl">يجب أن يتكون رقم الجوال من 9 أرقام</p>
                        @enderror
                    </div>

                    <!-- Email Address -->
                    <div class="space-y-3 text-right">
                        <label class="text-sm font-black text-slate-900 pr-2">البريد الإلكتروني</label>
                        <input type="email" name="email" value="{{ $user->email }}" required placeholder="أدخل البريد الإلكتروني"
                               class="w-full mt-2 h-14 bg-slate-50 border border-slate-200 rounded-2xl px-6 text-sm font-bold text-[#1C1C1C] outline-none focus:border-[#1A73E8] focus:ring-4 focus:ring-blue-50 transition-all">
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="flex items-center justify-end gap-4 pt-6">
                    <button type="button" class="px-8 py-4 bg-slate-100 text-slate-400 rounded-2xl font-black text-lg hover:bg-slate-200 transition-all">
                        إلغاء
                    </button>
                    <button type="submit" class="px-8 py-4 bg-[#1A73E8] text-white rounded-2xl font-black text-lg shadow-xl shadow-blue-100 hover:bg-blue-700 transition-all">
                        حفظ
                    </button>
                </div>
            </form>
        </div>

        <!-- Password Form -->
        <div x-show="tab === 'password'" x-transition x-cloak class="p-10">
            <form action="{{ route('admin.profile.password') }}" method="POST" class="space-y-8">
                @csrf
                <div class="grid grid-cols-1  gap-8">
                    <!-- Current Password -->
                    <div class="space-y-3 text-right md:col-span-2">
                        <label class="text-sm font-black text-slate-900 pr-2">كلمة المرور الحالية</label>
                        <input type="password" name="current_password" required placeholder="********"
                               class="w-full mt-2 h-14 bg-slate-50 border border-slate-200 rounded-2xl px-6 text-sm font-bold text-[#1C1C1C] outline-none focus:border-[#1A73E8] focus:ring-4 focus:ring-blue-50 transition-all">
                    </div>

                    <!-- New Password -->
                    <div class="space-y-3 text-right">
                        <label class="text-sm font-black text-slate-900 pr-2">كلمة المرور الجديدة</label>
                        <input type="password" name="password" required placeholder="********"
                               class="w-full mt-2 h-14 bg-slate-50 border border-slate-200 rounded-2xl px-6 text-sm font-bold text-[#1C1C1C] outline-none focus:border-[#1A73E8] focus:ring-4 focus:ring-blue-50 transition-all">
                    </div>

                    <!-- Confirm Password -->
                    <div class="space-y-3 text-right">
                        <label class="text-sm font-black text-slate-900 pr-2">تأكيد كلمة المرور</label>
                        <input type="password" name="password_confirmation" required placeholder="********"
                               class="w-full mt-2 h-14 bg-slate-50 border border-slate-200 rounded-2xl px-6 text-sm font-bold text-[#1C1C1C] outline-none focus:border-[#1A73E8] focus:ring-4 focus:ring-blue-50 transition-all">
                    </div>
                </div>

                <div class="flex items-center justify-end pt-6">
                    <button type="submit" class="px-8 py-4 bg-[#1A73E8] text-white rounded-2xl font-black text-lg shadow-xl shadow-emerald-100 hover:bg-emerald-600 transition-all">
                        تحديث كلمة المرور
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
