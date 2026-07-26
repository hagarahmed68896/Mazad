@extends('layouts.main')

@section('title', 'الشروط والأحكام | تمت')

@push('styles')
<style>
    .policy-sidebar-link {
        transition: all 0.3s ease;
    }
    .policy-sidebar-link.active {
        background-color: #EBF3FC;
        color: #1A73E8 !important;
        border-right: 4px solid #1A73E8;
    }
    .policy-section {
        scroll-margin-top: 100px;
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
            <span class="text-white bg-primary px-4 py-1.5 rounded-full text-xs">الشروط والأحكام</span>
        </div>

        <div class="text-center max-w-2xl mx-auto mb-12">
            <h1 class="text-3xl lg:text-4xl font-black text-gray-900 mb-4">الشروط والأحكام</h1>
            <p class="text-gray-500 font-bold text-base">نحرص في منصتنا على توفير بيئة آمنة وموثوقة لجميع المستخدمين. تحتوي هذه الصفحة على الشروط والأحكام التي تنظم استخدام المنصة وتحفظ حقوق كل مستخدم.</p>
        </div>

        <!-- Layout grid -->
        <div class="flex flex-col lg:flex-row gap-8">

            <!-- Sidebar (Right) -->
            <div class="w-full lg:w-1/3 order-1 lg:order-2">
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 sticky top-24">
                    <h3 class="text-lg font-black text-gray-900 mb-4 text-right border-b border-gray-100 pb-3">فهرس الشروط</h3>
                    <div class="flex flex-col space-y-2 text-right">
                        <a href="#section-1" class="policy-sidebar-link active block px-4 py-3 text-gray-600 font-bold hover:bg-gray-50 rounded-xl">1. استخدام المنصة</a>
                        <a href="#section-2" class="policy-sidebar-link block px-4 py-3 text-gray-600 font-bold hover:bg-gray-50 rounded-xl">2. إنشاء الحساب</a>
                        <a href="#section-3" class="policy-sidebar-link block px-4 py-3 text-gray-600 font-bold hover:bg-gray-50 rounded-xl">3. حقوق الملكية الفكرية</a>
                        <a href="#section-4" class="policy-sidebar-link block px-4 py-3 text-gray-600 font-bold hover:bg-gray-50 rounded-xl">4. استخدام البيانات</a>
                        <a href="#section-5" class="policy-sidebar-link block px-4 py-3 text-gray-600 font-bold hover:bg-gray-50 rounded-xl">5. إنهاء الحساب</a>
                    </div>
                </div>
            </div>

            <!-- Content Area (Left) -->
            <div class="w-full lg:w-2/3 order-2 lg:order-1 space-y-6">
                <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 space-y-8 text-right">
                    
                    <!-- Section 1 -->
                    <div id="section-1" class="policy-section space-y-3">
                        <h2 class="text-xl font-black text-gray-900">1. استخدام المنصة</h2>
                        <p class="text-gray-600 font-bold text-sm leading-relaxed">
                            باستخدامك للمنصة، فإنك توافق على استخدام الخدمات بطريقة قانونية ومسؤولة ووفقاً للسياسات المعلنة. يُمنع استخدام المنصة بأي شكل يلحق الضرر بالخوادم أو بالأنظمة الأمنية المخصصة للمنصة أو بالبنية التحتية.
                        </p>
                    </div>

                    <hr class="border-gray-100">

                    <!-- Section 2 -->
                    <div id="section-2" class="policy-section space-y-3">
                        <h2 class="text-xl font-black text-gray-900">2. إنشاء الحساب</h2>
                        <p class="text-gray-600 font-bold text-sm leading-relaxed">
                            يتعين على المستخدم تقديم معلومات دقيقة وصحيحة ومحدثة عند التسجيل في المنصة. تقع على عاتق المستخدم كامل المسؤولية في الحفاظ على سرية معلومات حسابه وكلمة المرور، ويُمنع منعاً باتاً مشاركة بيانات الدخول مع أي طرف ثالث تحت أي ظرف من الظروف.
                        </p>
                    </div>

                    <hr class="border-gray-100">

                    <!-- Section 3 -->
                    <div id="section-3" class="policy-section space-y-3">
                        <h2 class="text-xl font-black text-gray-900">3. حقوق الملكية الفكرية</h2>
                        <p class="text-gray-600 font-bold text-sm leading-relaxed">
                            جميع المحتويات والنصوص والتصاميم والشعارات والأيقونات والبرمجيات المتوافرة ضمن المنصة هي ملكية فكرية حصرية ومحمية بموجب حقوق النشر والعلامات التجارية المحلية والدولية. لا يحق لأي شخص نسخ أو استخدام هذه المحتويات دون إذن كتابي مسبق.
                        </p>
                    </div>

                    <hr class="border-gray-100">

                    <!-- Section 4 -->
                    <div id="section-4" class="policy-section space-y-3">
                        <h2 class="text-xl font-black text-gray-900">4. استخدام البيانات</h2>
                        <p class="text-gray-600 font-bold text-sm leading-relaxed">
                            نحتفظ بالحق في استخدام البيانات والمعلومات غير الشخصية التي يتم جمعها بطرق تلقائية أو التي يقدمها المستخدم بغرض تحسين جودة الخدمات، وتطوير المنتجات، وتخصيص التنبيهات واهتمامات المزايدة الفعالة مع الحفاظ الكامل على أمان البيانات الشخصية وتشفيرها.
                        </p>
                    </div>

                    <hr class="border-gray-100">

                    <!-- Section 5 -->
                    <div id="section-5" class="policy-section space-y-3">
                        <h2 class="text-xl font-black text-gray-900">5. إنهاء الحساب</h2>
                        <p class="text-gray-600 font-bold text-sm leading-relaxed">
                            يحق لإدارة المنصة وبموجب تقديرها الخاص تعليق أو إنهاء حساب المستخدم أو حظر وصوله للمنصة والخدمات في حال ثبوت انتهاك بنود الشروط والأحكام أو سياسة الخصوصية، أو ممارسة المزايدات الوهمية والعشوائية، أو إساءة الاستخدام.
                        </p>
                    </div>

                </div>
            </div>

        </div>

    </div>
</div>

@push('scripts')
<script>
    // Smooth scrolling & active state highlights based on scroll position
    document.querySelectorAll('.policy-sidebar-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetEl = document.querySelector(targetId);
            
            if (targetEl) {
                targetEl.scrollIntoView({ behavior: 'smooth' });
                
                document.querySelectorAll('.policy-sidebar-link').forEach(item => item.classList.remove('active'));
                this.classList.add('active');
            }
        });
    });

    window.addEventListener('scroll', function() {
        const scrollPos = window.scrollY + 120;
        const sections = document.querySelectorAll('.policy-section');
        const links = document.querySelectorAll('.policy-sidebar-link');
        
        sections.forEach((section, index) => {
            if (scrollPos >= section.offsetTop && scrollPos < (section.offsetTop + section.offsetHeight)) {
                links.forEach(item => item.classList.remove('active'));
                links[index].classList.add('active');
            }
        });
    });
</script>
@endpush
@endsection
