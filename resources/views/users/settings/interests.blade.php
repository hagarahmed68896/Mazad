@extends('layouts.main')

@section('title', 'اهتماماتي | مزاد')

@push('styles')
<style>
    .interest-item {
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid #F1F5F9;
        border-radius: 24px;
        padding: 1.75rem 1rem;
        position: relative;
        background: #F8FAFC;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    .interest-item:hover {
        transform: translateY(-4px);
        border-color: #CBD5E1;
        box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.05);
    }
    .interest-item.selected {
        border-color: #1A73E8 !important;
        background-color: #F0F7FF !important;
        transform: translateY(-4px);
        box-shadow: 0 10px 15px -3px rgba(26, 115, 232, 0.15);
    }
    .interest-item.selected .check-badge {
        opacity: 1 !important;
    }
    .interest-item.selected span {
        color: #1A73E8 !important;
    }
</style>
@endpush

@section('content')
<div class="bg-gray-50 min-h-screen py-10">
    <div class="container mx-auto px-4 lg:px-12 max-w-4xl">
        
        <!-- Breadcrumb -->
        <div class="flex items-center gap-2 text-sm font-bold text-gray-500 mb-6 justify-start" dir="rtl">
            <a href="{{ route('home') }}" class="hover:text-primary transition-colors">الرئيسية</a>
            <i class="fa-solid fa-chevron-left text-xs"></i>
            <span class="text-white bg-primary px-3 py-1 rounded-full text-xs">اهتماماتي</span>
        </div>

        <!-- Main Card Container -->
        <div class="bg-white rounded-[32px] p-8 md:p-12 shadow-sm border border-gray-100 text-center" dir="rtl">
            
            <!-- Header Text -->
            <div class="max-w-2xl mx-auto mb-10">
                <h1 class="text-3xl font-black text-gray-900 mb-3">اهتماماتي</h1>
                <h2 class="text-xl font-bold text-gray-800 mb-4">وش الفئات اللي تهمك؟</h2>
                <p class="text-gray-500 text-sm md:text-base font-medium leading-relaxed">
                    اختر المزادات اللي حاب تتابعها أول بأول، ونخلي باقي الفئات متاحة لك متى ما حبيت تبحث عنها.
                </p>
            </div>

            <!-- Form -->
            <form action="{{ route('profile.interests.update') }}" method="POST">
                @csrf
                @method('PUT')
                
                @if($errors->has('category_ids') || $errors->has('category_ids.*'))
                    <div class="bg-red-50 text-red-600 font-bold p-4 rounded-2xl mb-6 text-right text-sm">
                        {{ $errors->first('category_ids') ?: 'يرجى اختيار فئة اهتمام واحدة على الأقل.' }}
                    </div>
                @endif

                <!-- Interests Grid -->
                <div class="interests-grid grid grid-cols-2 md:grid-cols-3 gap-6 mb-10">
                    @foreach($categories as $category)
                        @php
                            $isSelected = Auth::user()->interests->contains($category->id);
                        @endphp
                        <div class="interest-item {{ $isSelected ? 'selected' : '' }}" onclick="toggleProfileInterest(this)">
                            <input type="checkbox" name="category_ids[]" value="{{ $category->id }}" {{ $isSelected ? 'checked' : '' }} class="hidden">
                            <div class="interest-icon h-20 w-20 flex items-center justify-center mb-4">
                                <img src="{{ $category->image ? (Str::startsWith($category->image, 'uploads/') ? asset($category->image) : asset('uploads/' . $category->image)) : 'https://mazad-admin.com/uploads/categories/placeholder.png' }}" alt="{{ $category->name }}" class="max-h-full max-w-full object-contain">
                            </div>
                            <span class="text-sm md:text-base font-black text-gray-700 select-none transition-colors" style="color: {{ $isSelected ? '#1A73E8' : '#475569' }}">{{ $category->name }}</span>
                            <div class="check-badge absolute top-4 left-4 text-primary transition-opacity duration-200 {{ $isSelected ? 'opacity-100' : 'opacity-0' }}" style="color: #1A73E8;">
                                <i class="fa-solid fa-circle-check text-xl"></i>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row justify-center gap-4 pt-6 border-t border-gray-100 max-w-md mx-auto">
                    <button type="submit" class="w-full sm:flex-1 bg-primary hover:bg-blue-700 text-white font-black py-4 px-12 rounded-2xl transition-all text-sm md:text-base shadow-md shadow-blue-100">
                        حفظ التغييرات
                    </button>
                    <a href="{{ route('profile.index') }}" class="w-full sm:flex-1 bg-gray-100 hover:bg-gray-200 text-gray-600 font-black py-4 px-8 rounded-2xl transition-all text-sm md:text-base flex items-center justify-center">
                        إلغاء
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function toggleProfileInterest(element) {
        const checkbox = element.querySelector('input[type="checkbox"]');
        checkbox.checked = !checkbox.checked;
        element.classList.toggle('selected');
        
        const badge = element.querySelector('.check-badge');
        const title = element.querySelector('span');
        
        if (checkbox.checked) {
            badge.classList.remove('opacity-0');
            badge.classList.add('opacity-100');
            element.style.borderColor = '#1A73E8';
            element.style.backgroundColor = '#F0F7FF';
            title.style.color = '#1A73E8';
        } else {
            badge.classList.remove('opacity-100');
            badge.classList.add('opacity-0');
            element.style.borderColor = '#F1F5F9';
            element.style.backgroundColor = '#F8FAFC';
            title.style.color = '#475569';
        }
    }

    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'نجاح',
            text: "{{ session('success') }}",
            timer: 2000,
            showConfirmButton: false,
            customClass: {
                popup: 'rounded-[32px]'
            }
        });
    @endif
</script>
@endpush
