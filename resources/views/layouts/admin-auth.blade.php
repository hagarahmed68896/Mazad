<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'لوحة تحكم تَمَّت')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #2075e3ff 0%, #0e3c81ff 100%);
            --glass-bg: rgba(255, 255, 255, 0.95);
            --text-main: #1E293B;
            --text-muted: #64748B;
        }

        body { 
            font-family: 'IBM Plex Sans Arabic', sans-serif; 
            background: #F8FAFC;
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
        }

        .login-page-wrapper {
            display: flex;
            width: 100%;
            height: 100vh;
            background: white;
        }

        /* Hero Side (Right in RTL) */
        .hero-side {
            width: 55%;
            background: var(--primary-gradient);
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 4rem;
            color: white;
            overflow: hidden;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            text-align: center;
            max-width: 500px;
        }

        .hero-title {
            font-family: 'Outfit', sans-serif;
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            letter-spacing: -1px;
            line-height: 1.2;
        }

        .hero-description {
            font-size: 1.15rem;
            opacity: 0.9;
            line-height: 1.8;
            margin-bottom: 3rem;
        }

        /* Form Side (Left in RTL) */
        .auth-side {
            width: 45%;
            padding: 4rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            z-index: 10;
        }

        .logo-box {
            margin-bottom: 4rem;
        }

        .auth-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1.5rem;
            margin-bottom: 2.5rem;
            text-align: right;
        }

        .auth-header-text {
            flex: 1;
        }

        .auth-header h1 {
            font-size: 2.25rem;
            font-weight: 700;
            color: var(--text-main);
            margin: 0 0 0.5rem 0;
        }

        .auth-header p {
            color: var(--text-muted);
            font-size: 1.05rem;
            line-height: 1.6;
            margin: 0;
        }

        .auth-logo {
            flex-shrink: 0;
        }

        .input-group {
            margin-bottom: 1.75rem;
            text-align: right;
        }

        .label-text {
            display: block;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 0.75rem;
            font-size: 0.95rem;
        }

        .input-control-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            background: #F1F5F9;
            border: 2px solid transparent;
            border-radius: 16px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .input-control-wrapper:focus-within {
            background: white;
            border-color: #1A73E8;
            box-shadow: 0 0 0 4px rgba(26, 115, 232, 0.1);
        }

        .input-control {
            width: 100%;
            padding: 1.1rem 1.5rem;
            border: none;
            background: transparent;
            font-family: inherit;
            font-size: 1rem;
            outline: none;
            color: var(--text-main);
            font-weight: 500;
        }

        .prefix-box {
            padding: 0 1.5rem;
            color: #94A3B8;
            font-weight: 700;
            border-left: 1px solid #E2E8F0;
            height: 24px;
            display: flex;
            align-items: center;
            direction: ltr;
        }

        .action-btn {
            width: 100%;
            padding: 1.25rem;
            background: var(--primary-gradient);
            color: white;
            border: none;
            border-radius: 16px;
            font-size: 1.15rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 10px 25px rgba(26, 115, 232, 0.3);
            margin-top: 1rem;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(26, 115, 232, 0.4);
        }

        .action-btn:disabled {
            background: #CBD5E1;
            box-shadow: none;
            cursor: not-allowed;
            transform: none;
        }

        .eye-toggle {
            padding: 0 1.25rem;
            color: #94A3B8;
            cursor: pointer;
            background: none;
            border: none;
            font-size: 1.1rem;
            transition: color 0.2s;
        }

        /* Dashboard Mockup */
        .dashboard-mockup {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            transform: perspective(1000px) rotateX(2deg);
        }

        .mock-card {
            background: white;
            border-radius: 12px;
            padding: 1rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            border: 1px solid #F1F5F9;
            text-align: right;
        }

        .mock-title {
            font-size: 0.8rem;
            font-weight: 800;
            color: #1E293B;
            margin-bottom: 0.25rem;
        }

        .mock-value {
            font-size: 1.1rem;
            font-weight: 900;
            color: #1E293B;
            margin-bottom: 0.5rem;
        }

        .mock-badge {
            font-size: 0.65rem;
            background: #DCFCE7;
            color: #16A34A;
            padding: 0.1rem 0.4rem;
            border-radius: 4px;
            margin-right: 0.5rem;
        }

        /* Chart Visuals */
        .line-chart-visual {
            height: 60px;
            background: linear-gradient(0deg, rgba(26,115,232,0.05) 0%, transparent 100%);
            border-bottom: 2px solid #1A73E8;
            position: relative;
            margin-top: 1rem;
        }
        .line-point {
            position: absolute;
            width: 6px;
            height: 6px;
            background: white;
            border: 2px solid #1A73E8;
            border-radius: 50%;
        }

        .bar-chart-visual {
            display: flex;
            align-items: flex-end;
            gap: 4px;
            height: 60px;
            margin-top: 1rem;
        }
        .bar-col {
            flex: 1;
            background: #1A73E8;
            border-radius: 2px 2px 0 0;
            min-height: 10px;
        }

        .donut-visual {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: 10px solid #22C55E;
            border-top-color: #EF4444;
            border-right-color: #F59E0B;
            margin: 1rem auto 0;
        }

        @media (max-width: 992px) {
            .login-page-wrapper { flex-direction: column; height: auto; }
            .hero-side { width: 100%; padding: 3rem 2rem; min-height: 400px; }
            .auth-side { width: 100%; padding: 4rem 2rem; }
            .hero-title { font-size: 2.25rem; }
        }

        @media (max-width: 576px) {
            .auth-header {
                flex-direction: column-reverse;
                align-items: flex-start;
                gap: 1rem;
            }
            .auth-logo {
                margin-bottom: 0.5rem;
            }
        }
    </style>
    @stack('styles')
</head>
<body>

    <div class="login-page-wrapper">
        <!-- Hero Side (Right in RTL) -->
        <div class="hero-side">
            <div class="hero-content">
                <h1 class="hero-title">لوحة تحكم تَمَّت</h1>
                <p class="hero-description">نظام متكامل لإدارة الإعلانات، الحسابات والمنتجات بكفاءة وبأعلى معايير الأمان.</p>
                
                <div class="dashboard-mockup">
                    <!-- Card 1: Ads -->
                    <div class="mock-card">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span class="mock-badge">0.43%+</span>
                            <div class="mock-title">إجمالي الإعلانات</div>
                        </div>
                        <div class="mock-value">850</div>
                        <div class="line-chart-visual">
                            <div class="line-point" style="left: 20%; bottom: 40%;"></div>
                            <div class="line-point" style="left: 50%; bottom: 70%;"></div>
                            <div class="line-point" style="left: 80%; bottom: 50%;"></div>
                        </div>
                    </div>

                    <!-- Card 2: Users -->
                    <div class="mock-card">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span class="mock-badge">0.43%+</span>
                            <div class="mock-title">إجمالي المستخدمين</div>
                        </div>
                        <div class="mock-value">10,000</div>
                        <div class="bar-chart-visual">
                            <div class="bar-col" style="height: 40%; opacity: 0.3;"></div>
                            <div class="bar-col" style="height: 60%; opacity: 0.6;"></div>
                            <div class="bar-col" style="height: 100%;"></div>
                            <div class="bar-col" style="height: 70%; opacity: 0.8;"></div>
                        </div>
                    </div>

                    <!-- Card 3: Revenue -->
                    <div class="mock-card">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span class="mock-badge">0.43%+</span>
                            <div class="mock-title">إجمالي الإيرادات</div>
                        </div>
                        <div class="mock-value">900,000 ر.س</div>
                        <div class="bar-chart-visual" style="gap: 8px;">
                            <div class="bar-col" style="height: 100%; width: 20%; background: #1A73E8;"></div>
                            <div class="bar-col" style="height: 100%; width: 20%; background: #1A73E8; opacity: 0.5;"></div>
                            <div class="bar-col" style="height: 100%; width: 20%; background: #1A73E8; opacity: 0.3;"></div>
                        </div>
                    </div>

                    <!-- Card 4: Status -->
                    <div class="mock-card">
                        <div class="mock-title">حالة الإعلانات</div>
                        <div class="donut-visual"></div>
                        <div style="display: flex; justify-content: center; gap: 5px; margin-top: 0.5rem; font-size: 0.6rem; font-weight: 700;">
                            <span style="color: #22C55E;">● قائم</span>
                            <span style="color: #F59E0B;">● قيد المراجعة</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Side (Left in RTL) -->
        <div class="auth-side">
            <div style="max-width: 440px; width: 100%; margin: 0 auto;">
                @yield('content')

                <div style="margin-top: 3rem; text-align: center;">
                    <p style="color: var(--text-muted); font-size: 0.9rem; font-weight: 500;">
                        جميع الحقوق محفوظة لمنصة تَمَّت © 2025
                    </p>
                </div>
            </div>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
