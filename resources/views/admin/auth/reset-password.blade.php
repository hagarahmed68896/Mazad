@extends('layouts.admin-auth')

@section('title', 'إعادة تعيين كلمة المرور - لوحة تحكم تَمَّت')

@section('content')
    <div class="auth-header">
        <div class="auth-header-text">
            <h1>كلمة مرور جديدة</h1>
            <p>يرجى إدخال كلمة المرور الجديدة الخاصة بك وتأكيدها للمتابعة.</p>
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

    <form action="{{ route('admin.post-reset-password') }}" method="POST">
        @csrf
        <div class="input-group">
            <label class="label-text">كلمة المرور الجديدة</label>
            <div class="input-control-wrapper">
                <input type="password" name="password" id="password" required placeholder="أدخل كلمة المرور الجديدة" class="input-control">
                <button type="button" class="eye-toggle" onclick="togglePassword('password', 'eye-1')">
                    <i class="fa-regular fa-eye-slash" id="eye-1"></i>
                </button>
            </div>
        </div>

        <div class="input-group">
            <label class="label-text">تأكيد كلمة المرور</label>
            <div class="input-control-wrapper">
                <input type="password" name="password_confirmation" id="password_confirmation" required placeholder="تأكيد كلمة المرور" class="input-control">
                <button type="button" class="eye-toggle" onclick="togglePassword('password_confirmation', 'eye-2')">
                    <i class="fa-regular fa-eye-slash" id="eye-2"></i>
                </button>
            </div>
        </div>

        <button type="submit" class="action-btn" style="margin-top: 2rem;">حفظ كلمة المرور</button>
    </form>
@endsection

@push('scripts')
<script>
    function togglePassword(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        }
    }
</script>
@endpush
