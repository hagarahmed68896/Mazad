@extends('layouts.admin')

@section('title', 'تعديل بيانات الإشعار')

@section('content')
<div class="max-w-5xl mx-auto space-y-8 animate-in fade-in slide-in-from-bottom-6 duration-1000 pb-10">
    
    <!-- Title Section -->
    <div class="flex items-center gap-4 px-2">
        <a href="{{ route('admin.notifications.index') }}" class="w-10 h-10 bg-white shadow-sm border border-slate-100 rounded-xl flex items-center justify-center text-slate-400 hover:text-[#1A73E8] transition-all">
            <svg class="w-5 h-5 flex shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path></svg>
        </a>
        <h2 class="text-[28px] font-black text-[#1C1C1C]">تعديل بيانات الإشعار</h2>
    </div>

    <!-- Main Card -->
    <div class="bg-white rounded-[2.5rem] shadow-[0_20px_70px_-20px_rgba(0,0,0,0.05)] border border-slate-50 p-10">
        <form action="{{ route('admin.notifications.update', $notification->id) }}" method="POST" class="space-y-10" onsubmit="this.querySelector('button[type=submit]').disabled = true; this.querySelector('button[type=submit]').innerHTML = 'جاري الحفظ...';">
            @csrf
            @method('PUT')
            
            <div class="space-y-8">
                <h3 class="text-xl font-black text-[#1C1C1C]">بيانات الإشعار</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Notification Type Dropdown -->
                    <div x-data="{ open: false, selected: '{{ $notification->type }}', typeLabel: '{{ $notification->type == 'alert' ? 'تنبيه' : 'الإشعار' }}' }" class="space-y-3">
                        <label class="text-sm font-black text-slate-900 pr-2">نوع الإشعار</label>
                        <div class="relative mt-2">
                            <button @click="open = !open" type="button" class="w-full h-14 bg-slate-50 border border-slate-200 rounded-2xl px-6 flex items-center justify-between group hover:border-[#1A73E8] transition-all">
                                <span class="text-[#1A1A31] font-bold" x-text="typeLabel"></span>
                                <svg class="w-5 h-5 text-slate-300 group-hover:text-[#1A73E8] transition-transform duration-300" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <div x-show="open" @click.away="open = false" x-cloak class="absolute inset-x-0 mt-3 p-4 bg-white border border-slate-50 rounded-3xl shadow-2xl z-50 space-y-2">
                                <label class="flex items-center justify-between p-4 rounded-2xl cursor-pointer hover:bg-slate-50 transition-all" @click="selected = 'alert'; typeLabel = 'تنبيه'; open = false">
                                    <span class="text-sm font-black text-[#6F6F6F]">تنبيه</span>
                                    <div class="w-6 h-6 border-2 border-slate-200 rounded-full flex items-center justify-center transition-all" :class="selected == 'alert' ? 'border-[#1A73E8] bg-[#1A73E8]' : ''">
                                        <div class="w-2 h-2 bg-white rounded-full"></div>
                                    </div>
                                    <input type="radio" name="type" value="alert" class="hidden" x-model="selected">
                                </label>
                                <label class="flex items-center justify-between p-4 rounded-2xl cursor-pointer hover:bg-slate-50 transition-all border border-transparent" @click="selected = 'notification'; typeLabel = 'الإشعار'; open = false">
                                    <span class="text-sm font-black text-[#6F6F6F]">الإشعار</span>
                                    <div class="w-6 h-6 border-2 border-slate-200 rounded-full flex items-center justify-center transition-all" :class="selected === 'notification' ? 'border-[#1A73E8] bg-[#1A73E8]' : ''">
                                        <div class="w-2 h-2 bg-white rounded-full"></div>
                                    </div>
                                    <input type="radio" name="type" value="notification" class="hidden" x-model="selected">
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Notification Title -->
                    <div class="space-y-3">
                        <label class="text-sm font-black text-slate-900 pr-2">عنوان الإشعار</label>
                        <input type="text" name="title" value="{{ $notification->title }}" required placeholder="أدخل عنوان الإشعار" class="w-full mt-2 h-14 bg-slate-50 border border-slate-200 rounded-2xl px-6 text-sm font-bold text-[#1C1C1C] outline-none focus:border-[#1A73E8] focus:ring-4 focus:ring-blue-50 transition-all">
                    </div>
                </div>

                <!-- Notification Content -->
                <div class="space-y-3">
                    <label class="text-sm font-black text-slate-900 pr-2 ">محتوى الإشعار</label>
                    <textarea name="content" rows="4" required placeholder="أدخل محتوى الإشعار" class="w-full mt-2 bg-slate-50 border border-slate-200 rounded-2xl p-6 text-sm font-bold text-[#1C1C1C] outline-none focus:border-[#1A73E8] focus:ring-4 focus:ring-blue-50 transition-all resize-none">{{ $notification->content }}</textarea>
                </div>

                <!-- Status Dropdown -->
                <div x-data="{ open: false, selected: '{{ $notification->status }}', statusLabel: '{{ ['sent' => 'مرسل', 'scheduled' => 'مجدول', 'unsent' => 'غير مرسل'][$notification->status] }}' }" class="space-y-3">
                    <label class="text-sm font-black text-slate-900 pr-2">الحالة</label>
                    <div class="relative mt-2">
                        <button @click="open = !open" type="button" class="w-full h-14 bg-slate-50 border border-slate-200 rounded-2xl px-6 flex items-center justify-between group hover:border-[#1A73E8] transition-all">
                            <span class="text-[#1A1A31] font-bold" x-text="statusLabel"></span>
                            <svg class="w-5 h-5 text-slate-300 group-hover:text-[#1A73E8] transition-transform duration-300" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak class="absolute inset-x-0 mt-3 p-4 bg-white border border-slate-50 rounded-3xl shadow-2xl z-50 space-y-2">
                            @foreach(['sent' => 'مرسل', 'scheduled' => 'مجدول', 'unsent' => 'غير مرسل'] as $val => $label)
                            <label class="flex items-center justify-between p-4 rounded-2xl cursor-pointer hover:bg-slate-50 transition-all" @click="selected = '{{ $val }}'; statusLabel = '{{ $label }}'; open = false">
                                <span class="text-sm font-black text-[#6F6F6F]">{{ $label }}</span>
                                <div class="w-6 h-6 border-2 border-slate-200 rounded-full flex items-center justify-center transition-all" :class="selected === '{{ $val }}' ? 'border-[#1A73E8] bg-[#1A73E8]' : ''">
                                    <div class="w-2 h-2 bg-white rounded-full"></div>
                                </div>
                                <input type="radio" name="status" value="{{ $val }}" class="hidden" x-model="selected">
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-4 pt-6">
                <a href="{{ route('admin.notifications.index') }}" class="h-14 px-8 bg-slate-100 text-[#6F6F6F] rounded-2xl font-black text-lg flex items-center justify-center hover:bg-slate-200 transition-all">
                    إلغاء
                </a>
                <button type="submit" class="h-14 px-8 bg-[#1A73E8] text-white rounded-2xl font-black text-lg shadow-xl shadow-blue-100 hover:bg-blue-700 transition-all active:scale-95">
                    حفظ
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
