@extends('layouts.main')

@section('title', 'أقسام المنصة | تمت')

@section('content')
<main class="container py-12 mx-auto min-h-screen">
    <!-- Breadcrumbs -->
    <div class="flex items-center gap-2 mb-8 text-sm font-semibold text-slate-500" dir="rtl">
        <a href="{{ route('home') }}" class="hover:text-primary transition-colors">الرئيسية</a>
        <i class="fa-solid fa-chevron-left text-xs text-slate-300"></i>
        <span class="text-slate-800">أقسام المنصة</span>
    </div>

    <!-- Header Section -->
    <div class="text-center mb-16" dir="rtl">
        <h1 class="text-4xl md:text-5xl font-black text-slate-900 mb-4 tracking-tight leading-tight">
            تصفح أقسام المنصة
        </h1>
        <p class="text-slate-500 font-medium max-w-xl mx-auto text-base md:text-lg">
            اكتشف آلاف المزادات والعروض الحية المصنفة بدقة لتجد ما تبحث عنه بسهولة وسرعة
        </p>
    </div>

    <!-- Categories Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 md:gap-8" dir="rtl">
        @foreach($categories as $category)
            <a href="{{ route('categories.show', $category->id) }}" class="group bg-white rounded-[2.5rem] p-6 border border-slate-100 hover:border-blue-100 hover:shadow-[0_24px_48px_-15px_rgba(26,115,232,0.08)] hover:-translate-y-1.5 transition-all duration-300 flex flex-col justify-between h-full">
                <div>
                    <!-- Image Wrapper -->
                    <div class="aspect-[4/3] rounded-3xl overflow-hidden bg-slate-50 flex items-center justify-center p-6 mb-6 group-hover:scale-98 transition-all duration-300 relative">
                        <img src="{{ $category->image }}" alt="{{ $category->name }}" class="max-h-full max-w-full object-contain group-hover:scale-105 transition-transform duration-500">
                        <!-- Subtle hover overlay -->
                        <div class="absolute inset-0 bg-primary/0 group-hover:bg-primary/[0.01] transition-colors duration-300"></div>
                    </div>

                    <!-- Category Info -->
                    <div class="text-center px-2">
                        <h3 class="text-lg md:text-xl font-black text-slate-800 group-hover:text-primary transition-colors duration-300">
                            {{ $category->name }}
                        </h3>
                        
                        <!-- Subcategories preview -->
                        @if($category->subCategories->count() > 0)
                            <div class="mt-4 flex flex-wrap gap-1.5 justify-center">
                                @foreach($category->subCategories->take(3) as $sub)
                                    <span class="text-[10px] md:text-xs font-bold bg-slate-50 text-slate-500 group-hover:bg-blue-50 group-hover:text-primary/80 px-2 py-0.5 rounded-lg transition-colors duration-300">
                                        {{ $sub->name }}
                                    </span>
                                @endforeach
                                @if($category->subCategories->count() > 3)
                                    <span class="text-[10px] md:text-xs font-bold bg-slate-50 text-slate-400 group-hover:bg-blue-50 group-hover:text-primary/60 px-1.5 py-0.5 rounded-lg transition-colors duration-300">
                                        + {{ $category->subCategories->count() - 3 }}
                                    </span>
                                @endif
                            </div>
                        @else
                            <p class="text-slate-400 text-xs font-semibold mt-3">تصفح المزادات</p>
                        @endif
                    </div>
                </div>

                <!-- Footer Card Action -->
                <div class="mt-6 pt-4 border-t border-slate-50 flex items-center justify-center gap-1.5 text-xs font-bold text-slate-400 group-hover:text-primary transition-colors duration-300">
                    <span>عرض الإعلانات</span>
                    <i class="fa-solid fa-arrow-left text-[10px] transform group-hover:-translate-x-1 transition-transform duration-300"></i>
                </div>
            </a>
        @endforeach
    </div>
</main>
@endsection
