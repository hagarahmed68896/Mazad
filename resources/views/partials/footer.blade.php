<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            
            <!-- Column 1: Logo & Info -->
            <div class="footer-col footer-col-logo" style="text-align: right;">
                <div class="footer-logo">
                    Logo
                </div>
                <p>منصة موثوقة للمزادات والشراء الفوري، تجمعك بفرص مميزة على السيارات والعقارات وغيرها.<br>شارك، زايد، أو اشترِ بكل سهولة وأمان.</p>
                <div class="social-links" style="justify-content: flex-start;">
                    @if(!empty($settings['youtube']))
                        <a href="{{ $settings['youtube'] }}" class="social-icon"><i class="fa-brands fa-youtube"></i></a>
                    @endif
                    @if(!empty($settings['twitter']))
                        <a href="{{ $settings['twitter'] }}" class="social-icon">
                            <!-- X icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" height="16" width="16" viewBox="0 0 512 512" fill="currentColor"><path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"/></svg>
                        </a>
                    @endif
                    @if(!empty($settings['instagram']))
                        <a href="{{ $settings['instagram'] }}" class="social-icon"><i class="fa-brands fa-instagram"></i></a>
                    @endif
                    @if(!empty($settings['facebook']))
                        <a href="{{ $settings['facebook'] }}" class="social-icon"><i class="fa-brands fa-facebook-f"></i></a>
                    @endif
                </div>
            </div>

            <!-- Column 2: Quick Links -->
            <div class="footer-col">
                <h4>روابط سريعة</h4>
                <ul>
                    <li><a href="#">الرئيسية</a></li>
                    <li><a href="#">مزادات</a></li>
                    <li><a href="#">شراء فوري</a></li>
                    <li><a href="#">مناقصات</a></li>
                    <li><a href="#">إضافة إعلان</a></li>
                </ul>
            </div>

            <!-- Column 3: Categories -->
            <div class="footer-col">
                <h4>الفئات</h4>
                <ul>
                    <li><a href="#">سيارات</a></li>
                    <li><a href="#">عقارات</a></li>
                    <li><a href="#">الكترونيات</a></li>
                    <li><a href="#">كتب</a></li>
                    <li><a href="#">كاميرات وصور</a></li>
                    <li><a href="#">ادوات مكتبية</a></li>
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
                <a href="#">الشروط و الاحكام</a>
                <a href="#">سياسة الخصوصية</a>
            </div>
                <div class="footer-copyright">
                {{ $settings['copyright'] ?? '©2025 copyright' }}
            </div>
        </div>
    </div>
</footer>
