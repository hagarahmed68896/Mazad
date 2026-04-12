<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'phone' => ['required', 'string', 'regex:/^5[0-9]{8}$/'],
            'password' => ['required', 'string'],
        ], [
            'phone.regex' => 'رقم الجوال يجب أن يبدأ بـ 5 ويتكون من 9 أرقام.',
            'phone.required' => 'رقم الجوال مطلوب.',
            'password.required' => 'كلمة المرور مطلوبة.',
        ]);

        $phone = $credentials['phone'];

        $user = User::where('phone', $phone)->first();
        if ($user && app('hash')->check($credentials['password'], $user->password)) {
            session()->put('pending_user_id', $user->id);
            session()->put('remember_login', $request->boolean('remember'));
            
            return redirect()->route('admin.verify-otp');
        }

        return back()->withErrors([
            'phone' => __('The provided credentials do not match our records.'),
        ])->onlyInput('phone');
    }

    public function showVerifyOtpForm()
    {
        if (!session()->has('pending_user_id')) {
            return redirect()->route('admin.login');
        }
        return view('auth.verify-otp');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|array|size:4',
            'otp.*' => 'required|string|max:1'
        ]);

        $otpString = implode('', $request->otp);

        if ($otpString === '0000') {
            $userId = session('pending_user_id');
            $user = User::find($userId);

            if ($user) {
                $remember = session()->pull('remember_login', false);
                session()->forget('pending_user_id');

                Auth::login($user, $remember);
                $request->session()->regenerate();

                return redirect()->route('admin.verification.success');
            }
        }

        return back()->with('error', 'رمز التحقق غير صحيح');
    }

    public function showVerificationSuccess()
    {
        if (!Auth::check()) {
            return redirect()->route('admin.login');
        }
        return view('auth.verification-success');
    }

    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'string', 'regex:/^5[0-9]{8}$/'],
        ], [
            'phone.regex' => 'رقم الجوال يجب أن يبدأ بـ 5 ويتكون من 9 أرقام.',
            'phone.required' => 'رقم الجوال مطلوب.',
        ]);

        $phone = $request->phone;

        $user = User::where('phone', $phone)->first();

        if ($user) {
            session(['reset_phone' => $phone]);
            return redirect()->route('admin.reset-password');
        }

        return back()->withErrors([
            'phone' => 'رقم الجوال هذا غير مسجل لدينا.',
        ])->onlyInput('phone');
    }

    public function showResetPasswordForm()
    {
        return view('auth.reset-password');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $phone = session('reset_phone');

        if (!$phone) {
            return redirect()->route('admin.forgot-password')->withErrors([
                'phone' => 'انتهت الجلسة، يرجى إعادة المحاولة.',
            ]);
        }

        $user = User::where('phone', $phone)->first();

        if (!$user) {
            return redirect()->route('admin.forgot-password')->withErrors([
                'phone' => 'رقم الجوال غير مسجل لدينا.',
            ]);
        }

        $user->password = bcrypt($request->password);
        $user->save();

        session()->forget('reset_phone');

        return redirect()->route('admin.login')->with('success', 'تم إعادة تعيين كلمة المرور بنجاح. يمكنك تسجيل الدخول الآن.');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
