@extends('layouts.main')

@section('title', ($ad->type == 'tender' ? 'سجل المناقصات' : ($ad->type == 'direct_purchase' ? 'سجل العروض' : 'سجل المزايدات')) . ' | ' . $ad->title)

@push('styles')
<style>
    .bids-history-page {
        background-color: #F8FAFC;
        min-height: 80vh;
        padding: 3rem 0;
        direction: rtl;
        text-align: right;
        font-family: 'Cairo', sans-serif;
    }

    .breadcrumbs-container {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 2rem;
    }

    .breadcrumbs-pill {
        background-color: #1A73E8;
        border-radius: 12px;
        color: white !important;
        padding: 0.6rem 1.2rem;
        font-size: 0.85rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none !important;
        box-shadow: 0 4px 12px rgba(26, 115, 232, 0.15);
        transition: all 0.2s ease;
    }

    .breadcrumbs-pill:hover {
        background-color: #1557B0;
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(26, 115, 232, 0.25);
    }

    .breadcrumbs-separator {
        color: #94A3B8;
        font-size: 0.8rem;
        font-weight: bold;
    }

    .breadcrumbs-text {
        font-size: 0.85rem;
        font-weight: 700;
        color: #64748B;
        padding: 0.6rem 0;
    }

    .page-title {
        font-size: 2.25rem;
        font-weight: 900;
        color: #1E293B;
        margin-bottom: 2rem;
    }

    /* Table styling */
    .bids-card {
        background: white;
        border: 1px solid #E2E8F0;
        border-radius: 28px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.02);
    }

    .bids-table {
        width: 100%;
        border-collapse: collapse;
        text-align: right;
    }

    .bids-table th {
        background-color: #F8FAFC;
        padding: 1.25rem 1.5rem;
        font-weight: 800;
        font-size: 0.95rem;
        color: #475569;
        border-bottom: 1.5px solid #F1F5F9;
    }

    .bids-table td {
        padding: 1.25rem 1.5rem;
        font-weight: 700;
        font-size: 0.95rem;
        color: #334155;
        border-bottom: 1px solid #F1F5F9;
        vertical-align: middle;
    }

    .bids-table tr:last-child td {
        border-bottom: none;
    }

    .bids-table tr:hover td {
        background-color: #F8FAFC;
    }

    .bidder-name {
        color: #1A73E8;
        font-weight: 800;
    }

    .bid-amount {
        font-weight: 900;
        color: #1A73E8;
        display: flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 1.05rem;
    }

    .bid-time {
        color: #64748B;
        font-size: 0.9rem;
    }

    .empty-state {
        text-align: center;
        padding: 5rem 2rem;
    }

    .empty-text {
        font-weight: 800;
        font-size: 1.25rem;
        color: #64748B;
        margin-top: 1rem;
    }
</style>
@endpush

@section('content')
<div class="bids-history-page">
    <div class="container mx-auto px-4">
        <!-- Breadcrumbs matching screenshot exactly -->
        <div class="breadcrumbs-container">
            <a href="{{ route('home') }}" class="breadcrumbs-pill">
                <span>الرئيسية</span>
            </a>
            <span class="breadcrumbs-separator"><i class="fa-solid fa-chevron-left"></i></span>
            <a href="{{ route('bids-offers.index') }}" class="breadcrumbs-pill">
                <span>المزادات و العروض</span>
            </a>
            <span class="breadcrumbs-separator"><i class="fa-solid fa-chevron-left"></i></span>
            <span class="breadcrumbs-text text-slate-500 font-extrabold max-w-[200px] truncate" title="{{ $ad->title }}">{{ $ad->title }}</span>
        </div>

        <!-- Page Title -->
        <h1 class="page-title">
            @if($ad->type == 'tender')
                سجل المناقصات
            @elseif($ad->type == 'direct_purchase')
                سجل العروض
            @else
                سجل المزايدات
            @endif
        </h1>

        <!-- Bids Card -->
        <div class="bids-card">
            @if($bids->isEmpty())
                <div class="empty-state">
                    <div class="mx-auto flex items-center justify-center text-slate-300 mb-4" style="width: 100px; height: 100px; background: #F8FAFC; border-radius: 50%; border: 4px solid #F1F5F9;">
                        <i class="fa-solid fa-gavel text-5xl"></i>
                    </div>
                    <h3 class="empty-text">
                        @if($ad->type == 'tender')
                            لا توجد مناقصات مسجلة على هذا الإعلان حالياً
                        @elseif($ad->type == 'direct_purchase')
                            لا توجد عروض مسجلة على هذا الإعلان حالياً
                        @else
                            لا توجد مزايدات مسجلة على هذا الإعلان حالياً
                        @endif
                    </h3>
                </div>
            @else
                <table class="bids-table">
                    <thead>
                        <tr>
                            <th>الاسم</th>
                            <th>المبلغ</th>
                            <th>الوقت</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bids as $bid)
                            @php
                                $formattedDate = $bid->created_at->format('Y/m/d - h:i:s a');
                                $formattedDate = str_replace(['am', 'pm'], ['ص', 'م'], $formattedDate);
                            @endphp
                            <tr>
                                <td class="bidder-name">
                                    {{ $bid->masked_name }}
                                    @if($loop->first)
                                        <i class="fa-solid fa-trophy text-amber-500 mr-2" title="العرض الأعلى"></i>
                                    @endif
                                </td>
                                <td>
                                    <div class="bid-amount">
                                        <span>{{ number_format($bid->amount) }}</span>
                                        <x-riyal-icon class="w-5 h-5 text-blue-600" />
                                    </div>
                                </td>
                                <td class="bid-time">{{ $formattedDate }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="p-6 bg-slate-50 border-t border-slate-100 flex justify-center">
                    {{ $bids->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
