@extends('layouts.admin-auth')

@section('title', 'تسجيل الدخول - لوحة تحكم تَمَّت')

@section('content')
    <div class="auth-header">
        <div class="auth-header-text">
            <h1>تسجيل الدخول</h1>
            <p>أدخل بياناتك للوصول إلى لوحة تحكم وإدارة النظام بكفاءة.</p>
        </div>
        <div class="auth-logo">
            <a href="/">
                @include('partials.logo', ['height' => 'h-16'])
            </a>
        </div>
    </div>

    @if($errors->any())
        <div style="margin-bottom: 2rem; padding: 1.25rem; background: #FFF1F2; border: 1px solid #FFE4E6; border-radius: 16px; color: #E11D48; font-size: 0.95rem; font-weight: 600; text-align: center; display: flex; align-items: center; gap: 0.75rem; justify-content: center;">
            <i class="fa-solid fa-circle-exclamation"></i>
            {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('admin.post-login') }}" method="POST">
        @csrf
        <div class="input-group">
            <label class="label-text">رقم الجوال</label>
            <div class="input-control-wrapper">
                <input type="text" name="phone" value="{{ old('phone') }}" required inputmode="numeric" maxlength="9" pattern="^5[0-9]{8}$" oninput="this.value = this.value.replace(/[^0-9]/g, ''); if(this.value.length > 0 && this.value[0] !== '5') this.value = '';" placeholder="أدخل رقم الجوال" class="input-control">
                <div class="prefix-box">966+</div>
            </div>
        </div>

        <div class="input-group">
            <label class="label-text">كلمة المرور</label>
            <div class="input-control-wrapper">
                <input type="password" name="password" id="password" required placeholder="أدخل كلمة المرور" class="input-control">
                <button type="button" class="eye-toggle" onclick="togglePassword()">
                    <i class="fa-regular fa-eye-slash" id="eye-icon"></i>
                </button>
            </div>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2.5rem;">
            <a href="{{ route('admin.forgot-password') }}" style="color: #1A73E8; font-size: 0.95rem; font-weight: 700; text-decoration: none; border-bottom: 2px solid transparent; transition: all 0.3s;" onmouseover="this.style.borderBottomColor='#1A73E8'" onmouseout="this.style.borderBottomColor='transparent'">نسيت كلمة المرور؟</a>
            
            <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer; color: var(--text-muted); font-size: 0.95rem; font-weight: 600;">
                <span>تذكرني</span>
                <div style="position: relative; width: 20px; height: 20px;">
                    <input type="checkbox" name="remember" style="width: 20px; height: 20px; accent-color: #1A73E8; cursor: pointer;">
                </div>
            </label>
        </div>

        <button type="submit" class="action-btn">دخول لوحة التحكم</button>
    </form>
@endsection

@push('scripts')
<script>
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eye-icon');
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.classList.remove('fa-eye-slash');
            eyeIcon.classList.add('fa-eye');
        } else {
            passwordInput.type = 'password';
            eyeIcon.classList.remove('fa-eye');
            eyeIcon.classList.add('fa-eye-slash');
        }
    }
</script>
@endpush
