@extends('layouts.main')

@section('title', 'استفساراتي | مزاد')

@push('styles')
<style>
    .inquiry-card {
        border-radius: 24px;
        transition: all 0.3s ease;
    }
    .inquiry-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px -5px rgba(0,0,0,0.03);
    }
</style>
@endpush

@section('content')
<div class="bg-gray-50 min-h-screen py-10" dir="rtl">
    <div class="container mx-auto px-4 lg:px-24">

        <!-- Breadcrumb & Header -->
        <div class="flex items-center justify-center gap-2 text-sm font-bold text-gray-500 mb-6">
            <a href="{{ route('home') }}" class="hover:text-primary transition-colors">الرئيسية</a>
            <i class="fa-solid fa-chevron-left text-xs"></i>
            <span class="text-white bg-primary px-4 py-1.5 rounded-full text-xs">استفساراتي</span>
        </div>

        <div class="text-center max-w-2xl mx-auto mb-12">
            <h1 class="text-3xl lg:text-4xl font-black text-gray-900 mb-4">استفساراتي</h1>
            <p class="text-gray-500 font-bold text-base">نحن هنا لمساعدتك، يمكنك متابعة جميع استفساراتك وتلقي الردود من فريق الدعم الفني.</p>
        </div>

        <!-- Success Alert -->
        @if(session('success'))
            <div class="max-w-4xl mx-auto mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl p-4 text-right font-bold text-sm flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-lg text-emerald-500"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <div class="max-w-4xl mx-auto space-y-6">
            @forelse($tickets as $ticket)
                <div class="bg-white inquiry-card p-6 border border-gray-100 shadow-sm text-right">
                    
                    <!-- Top section: Avatar & Meta -->
                    <div class="flex items-start justify-between flex-wrap gap-3 mb-4">
                        <div class="flex items-center gap-3">
                            <!-- Avatar circle -->
                            <div class="w-12 h-12 bg-primary/10 text-primary font-black rounded-full flex items-center justify-center text-lg shadow-inner">
                                {{ mb_substr(Auth::user()->name ?? 'م', 0, 1) }}
                            </div>
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="text-base font-black text-gray-900">{{ str_replace(' (تطبيق)', '', $ticket->reported_about ?? 'استفسار عام') }}</h3>
                                    <span class="bg-blue-50 text-primary px-2.5 py-0.5 rounded-full text-xs font-mono font-bold">{{ $ticket->ticket_number }}</span>
                                </div>
                                <p class="text-gray-400 font-bold text-xs mt-1">
                                    الدعم والمساعدة - 
                                    @if($ticket->created_at)
                                        {{ $ticket->created_at->diffForHumans() }}
                                    @else
                                        منذ ساعة
                                    @endif
                                </p>
                            </div>
                        </div>

                        <!-- Status badge -->
                        <div>
                            @if($ticket->status === 'resolved')
                                <span class="bg-emerald-50 text-emerald-700 border border-emerald-200 px-3.5 py-1.5 rounded-full text-xs font-black flex items-center gap-1.5">
                                    <i class="fa-solid fa-check text-xs"></i> تم الحل
                                </span>
                            @elseif($ticket->status === 'under_review')
                                <span class="bg-amber-50 text-amber-700 border border-amber-200 px-3.5 py-1.5 rounded-full text-xs font-black flex items-center gap-1.5">
                                    <i class="fa-solid fa-circle-notch fa-spin text-xs"></i> قيد المراجعة
                                </span>
                            @else
                                <span class="bg-gray-50 text-gray-700 border border-gray-200 px-3.5 py-1.5 rounded-full text-xs font-black flex items-center gap-1.5">
                                    مفتوحة
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Question Body -->
                    <div class="bg-gray-50/50 rounded-2xl p-5 border border-gray-100/50 mb-4">
                        <h4 class="text-sm font-black text-gray-800 mb-1.5">نص الاستفسار:</h4>
                        <p class="text-gray-600 font-bold text-sm leading-relaxed">{{ $ticket->description }}</p>
                    </div>

                    <!-- Answer / Team Response section -->
                    @if($ticket->status === 'resolved' || !empty($ticket->notes))
                        <div class="bg-blue-50/30 rounded-2xl p-5 border border-blue-100/50 text-right">
                            <div class="flex items-center gap-2 mb-2 text-primary">
                                <i class="fa-solid fa-user-tie text-sm"></i>
                                <h4 class="text-sm font-black">رد فريق الدعم والمساعدة:</h4>
                            </div>
                            <p class="text-gray-700 font-bold text-sm leading-relaxed">
                                {{ $ticket->notes ?? 'نشكرك على تواصلك معنا، تم مراجعة طلبك واتخاذ اللازم بنجاح.' }}
                            </p>
                        </div>
                    @else
                        <div class="bg-amber-50/20 rounded-2xl p-5 border border-amber-100/30 text-right flex items-center gap-2 text-amber-800">
                            <i class="fa-solid fa-clock text-sm text-amber-600"></i>
                            <p class="font-bold text-xs">طلبك قيد المتابعة حالياً من قبل فريق الدعم الفني، وسنقوم بالرد عليك في أقرب وقت.</p>
                        </div>
                    @endif

                </div>
            @empty
                <div class="bg-white rounded-3xl p-12 text-center border border-gray-100 shadow-sm">
                    <div class="w-20 h-20 bg-blue-50 text-primary rounded-full flex items-center justify-center text-3xl mx-auto mb-6">
                        <i class="fa-solid fa-clipboard-question"></i>
                    </div>
                    <h3 class="text-lg font-black text-gray-900 mb-2">لا توجد استفسارات سابقة</h3>
                    <p class="text-gray-400 font-bold text-sm mb-6">لم تقم بإرسال أي طلبات دعم فني أو استفسارات بعد.</p>
                    <a href="{{ route('contact') }}" class="inline-block bg-primary hover:bg-blue-700 text-white font-black px-8 py-3 rounded-xl text-sm shadow-md shadow-blue-100 transition-colors">
                        تواصل معنا الآن
                    </a>
                </div>
            @endforelse

            @if($tickets->hasPages())
                <div class="mt-8 flex justify-center">
                    <x-custom-pagination :paginator="$tickets" />
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
