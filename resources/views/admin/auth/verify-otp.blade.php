@extends('layouts.admin-auth')

@section('title', 'تحقق من الرمز - لوحة تحكم مزاد')

@push('styles')
<style>
    .otp-input {
        width: 60px;
        height: 65px;
        background: #F1F5F9;
        border: 2px solid transparent;
        border-radius: 16px;
        text-align: center;
        font-size: 1.5rem;
        font-weight: 800;
        color: #1E293B;
        transition: all 0.3s;
    }
    .otp-input:focus {
        background: white;
        border-color: #1A73E8;
        box-shadow: 0 0 0 4px rgba(26, 115, 232, 0.1);
        outline: none;
    }
</style>
@endpush

@section('content')
    <div class="auth-header">
        <h1>رمز التحقق</h1>
        <p>تم إرسال رمز التحقق إلى رقم جوالك المسجل. يرجى إدخاله للمتابعة.</p>
    </div>

    @if(session('error'))
        <div style="margin-bottom: 2rem; padding: 1.25rem; background: #FFF1F2; border: 1px solid #FFE4E6; border-radius: 16px; color: #E11D48; font-size: 0.95rem; font-weight: 600; text-align: center;">
            {{ session('error') }}
        </div>
    @endif

    <form id="otpForm" action="{{ route('admin.post-verify') }}" method="POST" class="space-y-8">
        @csrf
        <div style="display: flex; justify-content: center; gap: 1rem; direction: ltr;">
            <input type="text" name="otp[]" maxlength="1" autofocus class="otp-input" inputmode="numeric">
            <input type="text" name="otp[]" maxlength="1" class="otp-input" inputmode="numeric">
            <input type="text" name="otp[]" maxlength="1" class="otp-input" inputmode="numeric">
            <input type="text" name="otp[]" maxlength="1" class="otp-input" inputmode="numeric">
        </div>

        <button type="submit" class="action-btn" style="margin-top: 3rem;">تحقق الآن</button>
    </form>

    <div style="margin-top: 2rem; text-align: center;">
        <p style="color: var(--text-muted); font-size: 0.95rem; font-weight: 600;">
            لم يصلك الرمز؟ <a href="#" style="color: #1A73E8; text-decoration: none;">إعادة الإرسال</a>
        </p>
    </div>
@endsection

@push('scripts')
<script>
    const inputs = document.querySelectorAll('.otp-input');
    inputs.forEach((input, index) => {
        input.addEventListener('input', (e) => {
            if (e.target.value.length === 1 && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !e.target.value && index > 0) {
                inputs[index - 1].focus();
            }
        });
    });
</script>
@endpush
