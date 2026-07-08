@extends('layouts.admin')

@section('title', 'تفاصيل التذكرة')

@section('content')
<div x-data="{ 
    modalWarning: false, 
    modalSuspend: false, 
    modalReject: false 
}" class="w-full space-y-6 animate-in fade-in slide-in-from-bottom-6 duration-1000 pb-20 relative text-right" dir="rtl">
    
    <!-- Title with Arrow -->
    <div class="flex items-center gap-4 mb-4">
        <a href="{{ route('admin.tickets.index') }}" class="text-slate-800 hover:text-blue-600 transition-colors">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <h2 class="text-3xl font-black text-[#1C1C1C]">{{ $ticket->ticket_number }}</h2>
    </div>

    <!-- Basic Info Card -->
    <div class="bg-white rounded-[35px] p-8 shadow-[0_15px_50px_-25px_rgba(0,0,0,0.06)] border border-slate-50">
        <div class="mb-12">
            <h3 class="text-2xl font-black text-[#1C1C1C]">بيانات التذكرة</h3>
        </div>

        <div class="space-y-4">
            <!-- Row: User Name -->
            <div class="flex items-center">
                <div class="flex items-center gap-4 ml-4">
                    <div class="w-12 h-12 bg-[#E8F0FE] text-[#1A73E8] rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </div>
                    <span class="text-lg font-bold text-slate-500 whitespace-nowrap">اسم المستخدم:</span>
                </div>
                <div class="text-lg font-black text-[#1C1C1C]">{{ $ticket->user?->name ?? 'اسم المستخدم' }}</div>
            </div>

            <!-- Row: Account Type -->
            <div class="flex items-center">
                <div class="flex items-center gap-4 ml-4">
                    <div class="w-12 h-12 bg-[#E8F0FE] text-[#1A73E8] rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    </div>
                    <span class="text-lg font-bold text-slate-500 whitespace-nowrap">نوع الحساب:</span>
                </div>
                <div class="text-lg font-black text-[#1C1C1C]">
                    @php
                        $accTypes = ['individual' => 'فرد', 'company' => 'شركة', 'representative' => 'مندوب', 'partner' => 'شريك'];
                        $accType = $ticket->user?->account_type ?? 'individual';
                    @endphp
                    {{ $accTypes[$accType] ?? $accType }}
                </div>
            </div>

            <!-- Row: Phone -->
            <div class="flex items-center">
                <div class="flex items-center gap-4 ml-4">
                    <div class="w-12 h-12 bg-[#E8F0FE] text-[#1A73E8] rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                    </div>
                    <span class="text-lg font-bold text-slate-500 whitespace-nowrap">رقم الجوال:</span>
                </div>
                <div class="text-lg font-black text-[#1C1C1C]" dir="ltr">+{{ $ticket->phone ?? $ticket->user?->phone ?? '0123456789' }}</div>
            </div>

            <!-- Row: Type -->
            <div class="flex items-center">
                <div class="flex items-center gap-4 ml-4">
                    <div class="w-12 h-12 bg-[#E8F0FE] text-[#1A73E8] rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <span class="text-lg font-bold text-slate-500 whitespace-nowrap">نوع التذكرة:</span>
                </div>
                <div class="text-lg font-black text-[#1C1C1C]">{{ $ticket->type == 'report' ? 'بلاغ' : 'استفسار' }}</div>
            </div>

            <!-- Row: Reported -->
            @if($ticket->type == 'report')
            <div class="flex items-center">
                <div class="flex items-center gap-4 ml-4">
                    <div class="w-12 h-12 bg-[#E8F0FE] text-[#1A73E8] rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </div>
                    <span class="text-lg font-bold text-slate-500 whitespace-nowrap">المُبلغ عنه:</span>
                </div>
                <div class="text-lg font-black text-[#1C1C1C]">{{ $ticket->reported_about ?? 'اسم المستخدم' }}</div>
            </div>
            @endif

            <!-- Row: Status -->
            <div class="flex items-center">
                <div class="flex items-center gap-4 ml-4">
                    <div class="w-12 h-12 bg-[#E8F0FE] text-[#1A73E8] rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    </div>
                    <span class="text-lg font-bold text-slate-500 whitespace-nowrap">الحالة:</span>
                </div>
                <div>
                    @if($ticket->status == 'resolved')
                        <span class="px-5 py-1.5 bg-[#E7F7F1] text-[#10B981] rounded-full text-[15px] font-black">تم الحل</span>
                    @elseif($ticket->status == 'under_review')
                        <span class="px-5 py-1.5 bg-slate-100 text-[#6F6F6F] rounded-full text-[15px] font-black opacity-60">قيد المراجعة</span>
                    @else
                        <span class="px-5 py-1.5 bg-rose-50 text-rose-500 rounded-full text-[15px] font-black">مرفوضة</span>
                    @endif
                </div>
            </div>

            <!-- Row: Date -->
            <div class="flex items-center">
                <div class="flex items-center gap-4 ml-4">
                    <div class="w-12 h-12 bg-[#E8F0FE] text-[#1A73E8] rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <span class="text-lg font-bold text-slate-500 whitespace-nowrap">التاريخ:</span>
                </div>
                <div class="text-lg font-black text-[#1C1C1C]">{{ $ticket->created_at->format('Y/m/d') }}</div>
            </div>
        </div>
    </div>

    <!-- Description Card -->
    <div class="bg-white rounded-[35px] p-8 shadow-[0_15px_50px_-25px_rgba(0,0,0,0.06)] border border-slate-50">
        <div class="mb-8">
            <h3 class="text-2xl font-black text-[#1C1C1C]">وصف المشكلة</h3>
        </div>
        <div class=" bg-[#F1F3F5] rounded-[30px] text-slate-500 font-bold leading-[2.2] text-[15px]">
            {{ $ticket->description }}
        </div>
    </div>

    @if($ticket->status != 'resolved')
        @if($ticket->type == 'report')
            <!-- Actions Buttons for Reports -->
            <div class="flex items-center justify-start gap-4 pt-10 flex-wrap">
                <button @click="modalWarning = true" class="h-14 px-8 rounded-2xl font-black transition-all active:scale-95 text-[15px] hover:opacity-90" style="background-color: #1B4066; color: white; box-shadow: 0 10px 15px -3px rgba(27, 64, 102, 0.3);">
                    إرسال تنبيه للمستخدم
                </button>
                <button @click="modalSuspend = true" class="h-14 px-8 rounded-2xl font-black transition-all active:scale-95 text-[15px] hover:bg-blue-50" style="border: 2px solid #1B4066; color: #1B4066; background-color: transparent;">
                    إيقاف الحساب مؤقتًا
                </button>
                <button @click="modalReject = true" class="h-14 px-8 rounded-2xl font-black transition-all active:scale-95 text-[15px] hover:opacity-90" style="background-color: #9F1239; color: white; box-shadow: 0 10px 15px -3px rgba(159, 18, 57, 0.3);">
                    رفض الطلب
                </button>
            </div>
        @else
            <!-- Reply Form for Inquiries -->
            <div class="bg-white rounded-[35px] p-8 shadow-[0_15px_50px_-25px_rgba(0,0,0,0.06)] border border-slate-50">
                <div class="mb-8">
                    <h3 class="text-2xl font-black text-[#1C1C1C]">الرد</h3>
                </div>
                <form action="{{ route('admin.tickets.update', $ticket->id) }}" method="POST" class="space-y-6">
                    @csrf @method('PUT')
                    <input type="hidden" name="action" value="reply">
                    <textarea name="note" rows="5" required class="w-full p-8 bg-[#F8FAFC] border-none rounded-[30px] font-bold text-slate-600 focus:ring-2 focus:ring-blue-100 transition-all resize-none shadow-sm" placeholder="اكتب ردك هنا..."></textarea>
                    <div class="flex justify-start">
                        <button type="submit" class="h-14 px-12 whitespace-nowrap bg-[#1A73E8] text-white rounded-2xl font-black shadow-xl shadow-blue-100 hover:bg-blue-700 transition-all active:scale-95 text-[16px] w-auto min-w-[160px]" style="padding: 0 3rem;">
                            إرسال الرد
                        </button>
                    </div>
                </form>
            </div>
        @endif
    @else
        <!-- Action Logs Section -->
        <div class="bg-white rounded-[35px] p-4 shadow-[0_15px_50px_-25px_rgba(0,0,0,0.06)] border border-slate-50 space-y-8">
            <div>
                <h3 class="text-2xl font-black text-[#1C1C1C]">سجل الإجراءات</h3>
            </div>
            
            <div class="space-y-4">
                @foreach($ticket->logs as $log)
                    @php
                        $isReply = $log->action == 'reply';
                        $isSuspend = $log->action == 'suspension';
                        $isWarning = $log->action == 'warning';
                        $borderColor = $isReply ? 'border-[#1A73E8]' : ($isSuspend ? 'border-rose-600' : 'border-amber-400');
                    @endphp
                    <div class="relative  bg-[#F1F3F5] rounded-[30px] border-r-8 {{ $borderColor }}">
                        <div class="flex justify-between items-start">
                            <div class="flex items-center gap-4">
                                @if($isReply)
                                    <div class="w-10 h-10 bg-[#1A73E8] rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </div>
                                @elseif($isSuspend)
                                    <div class="w-10 h-10 bg-rose-500 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                    </div>
                                @else
                                    <div class="w-10 h-10 bg- rounded-full flex items-center justify-center font-black">!</div>
                                @endif
                                <h4 class="text-xl font-black text-[#1C1C1C]">
                                    @if($isReply) الرد علي الاستفسار
                                    @elseif($isSuspend) إيقاف الحساب مؤقتًا
                                    @elseif($isWarning) إرسال تنبيه للمستخدم
                                    @else تم حل التذكرة
                                    @endif
                                </h4>
                            </div>
                            <span class="text-sm font-bold text-slate-400" dir="rtl">{{ $log->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="mt-4 text-slate-500 font-bold leading-relaxed text-[15px]">
                            {{ str_replace([' عبر التطبيق.', ' عبر التطبيق', ' عبر نموذج اتصل بنا.', ' عبر نموذج اتصل بنا'], '', $log->note) }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Modals (Only for Reports) -->
    @if($ticket->type == 'report')
    <!-- Modal: Send Warning -->
    <div x-show="modalWarning" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm overflow-y-auto">
        <form action="{{ route('admin.tickets.update', $ticket->id) }}" method="POST" @click.away="modalWarning = false" x-show="modalWarning" x-transition class="bg-white w-full max-w-lg rounded-[32px] p-10 relative shadow-2xl">
            @csrf @method('PUT')
            <input type="hidden" name="action" value="warning">
            <button type="button" @click="modalWarning = false" class="absolute top-8 left-8 text-slate-400 hover:text-slate-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
            <div class="text-center space-y-4 mb-8">
                <h3 class="text-2xl font-black text-[#1C1C1C]">إرسال تنبيه للمستخدم</h3>
                <p class="text-[15px] font-bold text-slate-400 leading-relaxed px-4">سيتم إرسال تنبيه رسمي للمستخدم بخصوص هذه الشكوى. يرجى كتابة ملاحظاتك لتوضيح الإجراء.</p>
            </div>
            <div class="space-y-6">
                <div class="space-y-3 text-right">
                    <label class="text-[17px] font-black text-slate-800">الملاحظات</label>
                    <textarea name="note" rows="4" required class="w-full p-5 bg-[#F8FAFC] border-none rounded-2xl font-bold text-slate-600 focus:ring-2 focus:ring-blue-100 transition-all resize-none" placeholder="يرجى كتابة ملاحظاتك هنا..."></textarea>
                </div>
                <div class="flex items-center gap-4 pt-4">
                    <button type="submit" class="flex-1 h-14 bg-[#1A73E8] text-white rounded-2xl font-black shadow-lg shadow-blue-100 hover:bg-blue-700 transition-all active:scale-95 text-[15px]">ارسال</button>
                    <button type="button" @click="modalWarning = false" class="flex-1 h-14 bg-[#F1F3F5] text-[#6F6F6F] rounded-2xl font-black hover:bg-slate-200 transition-all active:scale-95 text-[15px]">إلغاء</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Modal: Suspend Account -->
    <div x-show="modalSuspend" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm overflow-y-auto">
        <form action="{{ route('admin.tickets.update', $ticket->id) }}" method="POST" @click.away="modalSuspend = false" x-show="modalSuspend" x-transition class="bg-white w-full max-w-lg rounded-[32px] p-10 relative shadow-2xl">
            @csrf @method('PUT')
            <input type="hidden" name="action" value="suspension">
            <button type="button" @click="modalSuspend = false" class="absolute top-8 left-8 text-slate-400 hover:text-slate-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
            <div class="text-center space-y-4 mb-8">
                <h3 class="text-2xl font-black text-[#1C1C1C]">إيقاف الحساب مؤقتًا</h3>
                <p class="text-[15px] font-bold text-slate-400 leading-relaxed px-4">سيتم إيقاف حساب المستخدم المُبلغ عنه مؤقتًا بخصوص هذه الشكوى. يرجى كتابة ملاحظاتك لتوضيح الإجراء.</p>
            </div>
            <div class="space-y-6">
                <div class="space-y-3 text-right">
                    <label class="text-[17px] font-black text-slate-800">الملاحظات</label>
                    <textarea name="note" rows="4" required class="w-full p-5 bg-[#F8FAFC] border-none rounded-2xl font-bold text-slate-600 focus:ring-2 focus:ring-blue-100 transition-all resize-none" placeholder="يرجى كتابة ملاحظاتك هنا..."></textarea>
                </div>
                <div class="flex items-center gap-4 pt-4">
                    <button type="submit" class="flex-1 h-14 bg-[#1A73E8] text-white rounded-2xl font-black shadow-lg shadow-blue-100 hover:bg-blue-700 transition-all active:scale-95 text-[15px]">تأكيد الإيقاف</button>
                    <button type="button" @click="modalSuspend = false" class="flex-1 h-14 bg-[#F1F3F5] text-[#6F6F6F] rounded-2xl font-black hover:bg-slate-200 transition-all active:scale-95 text-[15px]">إلغاء</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Modal: Reject Request -->
    <div x-show="modalReject" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm overflow-y-auto">
        <form action="{{ route('admin.tickets.update', $ticket->id) }}" method="POST" @click.away="modalReject = false" x-show="modalReject" x-transition class="bg-white w-full max-w-lg rounded-[32px] p-10 relative shadow-2xl">
            @csrf @method('PUT')
            <input type="hidden" name="status" value="rejected">
            <button type="button" @click="modalReject = false" class="absolute top-8 left-8 text-slate-400 hover:text-slate-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
            <div class="text-center space-y-8 py-4">
                <h3 class="text-3xl font-black text-[#1C1C1C]">رفض الطلب</h3>
                <p class="text-xl font-bold text-slate-400">هل أنت متأكد أنك تريد رفض الطلب؟</p>
                <div class="flex items-center gap-4 pt-4">
                    <button type="submit" class="flex-1 h-14 bg-[#EF4444] text-white rounded-2xl font-black shadow-lg shadow-red-100 hover:bg-red-700 transition-all active:scale-95 text-[15px]">تأكيد الرفض</button>
                    <button type="button" @click="modalReject = false" class="flex-1 h-14 bg-[#F1F3F5] text-[#6F6F6F] rounded-2xl font-black hover:bg-slate-200 transition-all active:scale-95 text-[15px]">تراجع</button>
                </div>
            </div>
        </form>
    </div>
    @endif
</div>
@endsection
