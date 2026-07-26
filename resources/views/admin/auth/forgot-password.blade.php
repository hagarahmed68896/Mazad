@extends('layouts.admin-auth')

@section('title', 'نسيت كلمة المرور - لوحة تحكم تَمَّت')

@section('content')
    <div class="auth-header">
        <div class="auth-header-text">
            <h1>نسيت كلمة المرور؟</h1>
            <p>أدخل رقم جوالك المسجل وسنرسل لك رمز تحقق لإعادة تعيين كلمة المرور.</p>
        </div>
        <div class="auth-logo">
            <a href="/">
                @include('partials.logo', ['height' => 'h-16'])
            </a>
        </div>
    </div>

    @if($errors->any())
        <div style="margin-bottom: 2rem; padding: 1.25rem; background: #FFF1F2; border: 1px solid #FFE4E6; border-radius: 16px; color: #E11D48; font-size: 0.95rem; font-weight: 600; text-align: center;">
            {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('admin.post-forgot-password') }}" method="POST">
        @csrf
        <div class="input-group">
            <label class="label-text">رقم الجوال</label>
            <div class="input-control-wrapper">
                <input type="text" name="phone" value="{{ old('phone') }}" required inputmode="numeric" maxlength="9" pattern="^5[0-9]{8}$" oninput="this.value = this.value.replace(/[^0-9]/g, ''); if(this.value.length > 0 && this.value[0] !== '5') this.value = '';" placeholder="أدخل رقم الجوال" class="input-control">
                <div class="prefix-box">966+</div>
            </div>
        </div>

        <button type="submit" class="action-btn" style="margin-top: 2rem;">إرسال الرمز</button>
    </form>

    <div style="margin-top: 3rem; text-align: center;">
        <p style="color: var(--text-muted); font-size: 0.95rem; font-weight: 600;">
            تذكرت كلمة المرور؟ <a href="{{ route('admin.login') }}" style="color: #1A73E8; text-decoration: none; font-weight: 700;">تسجيل الدخول</a>
        </p>
    </div>
@endsection
