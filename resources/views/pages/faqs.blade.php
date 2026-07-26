@extends('layouts.main')

@section('title', 'الأسئلة الشائعة | تمت')

@push('styles')
<style>
    .faq-accordion-item {
        transition: all 0.3s ease-in-out;
        border-radius: 20px;
        background: #F8FAFC;
    }
    .faq-accordion-item.active {
        background: #EBF3FC;
        box-shadow: 0 10px 15px -3px rgba(26, 115, 232, 0.05);
    }
    .faq-answer-wrap {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease-out;
    }
    .faq-accordion-item.active .faq-answer-wrap {
        max-height: 300px;
        transition: max-height 0.3s ease-in;
    }
    .faq-icon-toggle {
        transition: transform 0.3s ease;
    }
    .faq-accordion-item.active .faq-icon-toggle {
        transform: rotate(45deg);
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
            <span class="text-white bg-primary px-4 py-1.5 rounded-full text-xs">الأسئلة الشائعة</span>
        </div>

        <div class="text-center max-w-2xl mx-auto mb-12">
            <h1 class="text-3xl lg:text-4xl font-black text-gray-900 mb-4">الأسئلة الشائعة</h1>
            <p class="text-gray-500 font-bold text-base">اعثر على إجابات للاستفسارات الشائعة حول منتجاتنا وخدماتنا.</p>
        </div>

        <!-- Accordions List -->
        <div class="max-w-4xl mx-auto space-y-4">
            @foreach($faqs as $faq)
                <div class="faq-accordion-item border border-gray-100 p-6 cursor-pointer" onclick="toggleFaq(this)">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-black text-gray-900">{{ $faq->question }}</h3>
                        <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-primary shadow-sm faq-icon-toggle">
                            <i class="fa-solid fa-plus text-sm"></i>
                        </div>
                    </div>
                    <div class="faq-answer-wrap">
                        <div class="pt-4 text-gray-600 font-bold text-sm leading-relaxed border-t border-gray-200/50 mt-4">
                            {!! nl2br(e($faq->answer)) !!}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

    </div>
</div>

@push('scripts')
<script>
    function toggleFaq(el) {
        const isActive = el.classList.contains('active');
        
        // Close all other FAQs
        document.querySelectorAll('.faq-accordion-item').forEach(item => {
            item.classList.remove('active');
            const icon = item.querySelector('.faq-icon-toggle i');
            if (icon) {
                icon.className = 'fa-solid fa-plus text-sm';
            }
        });

        // Toggle current FAQ
        if (!isActive) {
            el.classList.add('active');
            const icon = el.querySelector('.faq-icon-toggle i');
            if (icon) {
                icon.className = 'fa-solid fa-minus text-sm';
            }
        }
    }
</script>
@endpush
@endsection
