<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Setting;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        $settings = Setting::pluck('value', 'key');
        return view('admin.auth.login', compact('settings'));
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

        if (!$user || $user->email !== 'admin@mazad.com') {
            return back()->withErrors([
                'phone' => 'عذراً، هذا الحساب لا يملك صلاحيات دخول لوحة التحكم.',
            ])->onlyInput('phone');
        }

        if (app('hash')->check($credentials['password'], $user->password)) {
            session()->put('pending_user_id', $user->id);
            session()->put('remember_login', $request->boolean('remember'));
            
            return redirect()->route('admin.verify-otp');
        }

        return back()->withErrors([
            'phone' => 'بيانات الاعتماد المقدمة لا تتطابق مع سجلاتنا.',
        ])->onlyInput('phone');
    }

    public function showVerifyOtpForm()
    {
        if (!session()->has('pending_user_id')) {
            return redirect()->route('admin.login');
        }
        return view('admin.auth.verify-otp');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|array|size:4',
            'otp.*' => 'required|string|max:1'
        ], [
            'otp.required' => 'رمز التحقق مطلوب.',
            'otp.size' => 'يجب إدخال 4 أرقام لرمز التحقق.',
            'otp.*.required' => 'يرجى إدخال جميع أرقام الرمز.',
        ]);

        $otpString = implode('', $request->otp);

        if ($otpString === '0000') {
            $userId = session('pending_user_id');
            $user = User::find($userId);

            if ($user && $user->email === 'admin@mazad.com') {
                $remember = session()->pull('remember_login', false);
                session()->forget('pending_user_id');

                Auth::guard('admin')->login($user, $remember);
                $request->session()->regenerate();

                Auth::login($user);
                return redirect()->route('admin.dashboard');
            }
        }

        return back()->with('error', 'رمز التحقق غير صحيح');
    }

    public function showVerificationSuccess()
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login');
        }
        
        $redirectUrl = route('admin.dashboard');
        $redirectText = 'الذهاب للوحة التحكم';
        
        return view('admin.auth.verification-success', compact('redirectUrl', 'redirectText'));
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }

    // Add remaining methods if needed (forgot password etc.)
    public function showForgotPasswordForm()
    {
        return view('admin.auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'string', 'regex:/^5[0-9]{8}$/'],
        ], [
            'phone.required' => 'رقم الجوال مطلوب.',
            'phone.regex' => 'رقم الجوال يجب أن يبدأ بـ 5 ويتكون من 9 أرقام.',
        ]);
        $user = User::where('phone', $request->phone)->where('email', 'admin@mazad.com')->first();
        if ($user) {
            session(['reset_phone' => $request->phone]);
            return redirect()->route('admin.reset-password');
        }
        return back()->withErrors(['phone' => 'رقم الجوال غير صحيح أو ليس للأدمن.']);
    }

    public function showResetPasswordForm()
    {
        return view('admin.auth.reset-password');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed'
        ], [
            'password.required' => 'كلمة المرور الجديدة مطلوبة.',
            'password.min' => 'يجب أن لا تقل كلمة المرور عن 8 أحرف.',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق.',
        ]);
        $phone = session('reset_phone');
        $user = User::where('phone', $phone)->where('email', 'admin@mazad.com')->first();
        if ($user) {
            $user->password = bcrypt($request->password);
            $user->save();
            session()->forget('reset_phone');
            return redirect()->route('admin.reset-success');
        }
        return redirect()->route('admin.forgot-password');
    }

    public function showResetSuccess()
    {
        return view('admin.auth.reset-success');
    }
}
