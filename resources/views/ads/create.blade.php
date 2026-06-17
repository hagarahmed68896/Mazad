@extends('layouts.main')

@section('title', 'ما الذي تريد بيعه؟ | مزاد')

@section('content')
<!-- Override Navbar and Footer for this specific page -->
<style>
    .navbar, .categories-nav, .footer { display: none !important; }
    body { background-color: #F8FAFC; }
    
    .category-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        width: 100%;
        max-width: 180px;
        margin: 0 auto;
    }
    .category-card:hover {
        transform: translateY(-8px);
    }
    .category-card.active .icon-box {
        background-color: #1A73E8 !important;
        border-color: #1A73E8 !important;
    }
    .category-card.active .icon-box img {
        /* Removed filter to keep original image colors */
    }
    .category-card.active .cat-name {
        color: #1A73E8 !important;
    }

    .icon-box {
        width: 100%;
        aspect-ratio: 1/1;
        background-color: white;
        border-radius: 24px;
        border: 1px solid #F1F5F9;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1.25rem; /* Reduced padding from 2rem (p-8) to 1.25rem (p-5) */
        margin-bottom: 1rem;
        transition: all 0.3s ease;
        box-shadow: 0 10px 25px rgba(0,0,0,0.02);
    }

    .btn-continue:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* Login-style Footer */
    .custom-login-footer {
        background-color: #1557B0;
        background-image: linear-gradient(135deg, #1557B0 0%, #0a3066 100%);
        position: relative;
        overflow: hidden;
        padding: 2rem 0;
        margin-top: auto;
    }
    .custom-login-footer::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        width: 350px;
        height: 100%;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none"><polygon points="0,0 70,0 0,100" fill="rgba(14, 62, 126, 0.4)"/><polygon points="0,20 40,0 0,60" fill="rgba(14, 62, 126, 0.6)"/></svg>');
        background-size: cover;
        z-index: 1;
    }
    .custom-login-footer .container {
        position: relative;
        z-index: 2;
    }
</style>

<div class="min-h-screen flex flex-col">
    <!-- Custom Header -->
    <header class="bg-white py-6 shadow-sm">
        <div class="container flex justify-between items-center">
            <div class="logo">
                <a href="/">
                    @include('partials.logo', ['size' => 'text-4xl'])
                </a>
            </div>
            <a href="/" class="px-6 py-2 border border-gray-200 rounded-xl font-bold text-gray-600 hover:bg-gray-50 transition-all">خروج</a>

        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow container py-16 flex flex-col items-center">
        <div class="text-center mb-16">
            <h1 class="text-4xl font-black text-gray-900 mb-4">ما الذي تريد بيعه؟</h1>
            <p class="text-gray-500 font-bold text-lg">اختر الفئة المناسبة للإعلان الذي تريد إضافته.</p>
        </div>

        <form action="{{ route('ads.create.details') }}" method="GET" id="categoryForm" class="w-full max-w-6xl">
            <input type="hidden" name="type" value="{{ $type }}">
            <input type="hidden" name="category" id="selectedCategoryId" value="">

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6 mb-20 justify-items-center">
                @foreach($categories as $category)
                    <div onclick="selectCategory('{{ $category->id }}', this)" 
                         class="category-card flex flex-col items-center group">
                        <div class="icon-box">
                            @php
                                $imgUrl = $category->image ?: 'https://cdn-icons-png.flaticon.com/512/711/711701.png';
                            @endphp
                            <img src="{{ $imgUrl }}" alt="{{ $category->name }}" class="w-full h-full object-contain" onerror="this.src='https://cdn-icons-png.flaticon.com/512/711/711701.png'">
                        </div>
                        <span class="cat-name text-base font-black text-gray-800 transition-colors text-center">{{ $category->name }}</span>
                    </div>
                @endforeach
            </div>

            <div class="flex justify-center">
                <button type="submit" id="continueBtn" disabled class="btn-continue w-full max-w-2xl bg-primary text-white py-5 rounded-2xl text-xl font-black shadow-xl shadow-blue-200 hover:bg-blue-700 transition-all transform active:scale-[0.98]">
                    متابعة
                </button>
            </div>
        </form>
    </main>

    <!-- Login-style Footer -->
    <footer class="custom-login-footer">
        <div class="container flex flex-col md:flex-row justify-between items-center text-white/80 font-bold text-sm">
            <div class="flex gap-8 mb-4 md:mb-0">
                <a href="{{ route('terms') }}" class="hover:text-white transition-colors">الشروط و الاحكام</a>
                <a href="{{ route('privacy') }}" class="hover:text-white transition-colors">سياسة الخصوصية</a>
            </div>
            <div dir="ltr">
                {{ $settings['copyright'] ?? '©2025 copyright' }}
            </div>
        </div>
    </footer>
</div>

<script>
    function selectCategory(id, element) {
        // Remove active class from all
        document.querySelectorAll('.category-card').forEach(card => card.classList.remove('active'));
        
        // Add to clicked
        element.classList.add('active');
        
        // Set hidden input
        document.getElementById('selectedCategoryId').value = id;
        
        // Enable button
        document.getElementById('continueBtn').disabled = false;
    }
</script>
@endsection

