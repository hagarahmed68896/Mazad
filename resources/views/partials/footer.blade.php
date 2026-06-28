<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            
            <!-- Column 1: Logo & Info -->
            <div class="footer-col footer-col-logo" style="text-align: right;">
                <div class="footer-logo">
                    @include('partials.logo')
                </div>
                <p>منصة موثوقة للمزادات والشراء الفوري، تجمعك بفرص مميزة على السيارات والعقارات وغيرها.<br>شارك، زايد، أو اشترِ بكل سهولة وأمان.</p>
                <div class="social-links" style="justify-content: flex-start;">
                    @php
                        $socialLinks = [];
                        if (!empty($settings['social_links'])) {
                            $decoded = json_decode($settings['social_links'], true);
                            if (is_array($decoded)) {
                                $socialLinks = $decoded;
                            }
                        }
                    @endphp
                    
                    @foreach($socialLinks as $link)
                        @if(!empty($link['url']))
                            @if($link['platform'] === 'فيسبوك')
                                <a href="{{ $link['url'] }}" class="social-icon" target="_blank"><i class="fa-brands fa-facebook-f"></i></a>
                            @elseif($link['platform'] === 'إنستغرام')
                                <a href="{{ $link['url'] }}" class="social-icon" target="_blank"><i class="fa-brands fa-instagram"></i></a>
                            @elseif($link['platform'] === 'تويتر')
                                <a href="{{ $link['url'] }}" class="social-icon" target="_blank">
                                    <svg xmlns="http://www.w3.org/2000/svg" height="16" width="16" viewBox="0 0 512 512" fill="currentColor"><path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"/></svg>
                                </a>
                            @elseif($link['platform'] === 'لينكدان')
                                <a href="{{ $link['url'] }}" class="social-icon" target="_blank"><i class="fa-brands fa-linkedin-in"></i></a>
                            @elseif($link['platform'] === 'الموقع')
                                <a href="{{ $link['url'] }}" class="social-icon" target="_blank"><i class="fa-solid fa-globe"></i></a>
                            @endif
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Column 2: Quick Links -->
            <div class="footer-col">
                <h4>روابط سريعة</h4>
                <ul>
                    <li><a href="{{ route('home') }}">الرئيسية</a></li>
                    <li><a href="{{ route('search', ['type' => 'auction']) }}">مزادات</a></li>
                    <li><a href="{{ route('search', ['type' => 'direct_purchase']) }}">شراء فوري</a></li>
                    <li><a href="{{ route('search', ['type' => 'tender']) }}">مناقصات</a></li>
                    <li><a href="javascript:void(0)" onclick="openAddAdModal()">إضافة إعلان</a></li>
                </ul>
            </div>

            <!-- Column 3: Categories -->
            <div class="footer-col">
                <h4>الفئات</h4>
                <ul>
                    @php
                        $footerCategories = \App\Models\Category::whereNull('parent_id')->take(6)->get();
                    @endphp
                    @forelse($footerCategories as $category)
                        <li><a href="{{ route('search', ['category' => $category->id]) }}">{{ $category->name }}</a></li>
                    @empty
                        <li><a href="{{ route('search', ['category' => 1]) }}">سيارات</a></li>
                        <li><a href="{{ route('search', ['category' => 2]) }}">عقارات</a></li>
                        <li><a href="{{ route('search', ['category' => 3]) }}">الكترونيات</a></li>
                    @endforelse
                </ul>
            </div>

            <!-- Column 4: Contact Us -->
            <div class="footer-col">
                <h4>تواصل معنا</h4>
                <ul class="contact-list">
                    <li>
                        <a href="tel:{{ $settings['phone'] ?? '+966 50 123 4567' }}">
                                                    <i class="fa-solid fa-phone" style="transform: scaleX(-1);"></i>
   
                        <span dir="ltr">{{ $settings['phone'] ?? '+966 50 123 4567' }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="mailto:{{ $settings['email'] ?? 'support@yourapp.sa' }}">
                                                    <i class="fa-regular fa-envelope"></i>
   
                        <span>{{ $settings['email'] ?? 'support@yourapp.sa' }}</span>
                        </a>
                    </li>
                </ul>
            </div>

        </div>
        <div class="footer-bottom">
        
            <div class="footer-bottom-links">
                <a href="{{ route('terms') }}">الشروط والأحكام</a>
                <a href="{{ route('privacy') }}">سياسة الخصوصية</a>
                <a href="{{ route('faqs') }}">الأسئلة الشائعة</a>
                @auth
                    <a href="{{ route('inquiries.my') }}">استفساراتي</a>
                @endauth
            </div>
                <div class="footer-copyright">
                {{ $settings['copyright'] ?? '©2025 copyright' }}
            </div>
        </div>
    </div>
</footer>
