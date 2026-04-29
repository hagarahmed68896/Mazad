<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Setting;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        $settings = Setting::pluck('value', 'key');
        $loginRoute = route('login.post');
        return view('auth.login', compact('settings', 'loginRoute'));
    }

    public function showRegisterIndividualForm()
    {
        return view('auth.register-individual');
    }

    public function showRegisterTypeForm()
    {
        return view('auth.register-type');
    }

    public function showRegisterCompanyForm()
    {
        return view('auth.register-company');
    }

    public function register(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'phone' => ['required', 'string', 'regex:/^5[0-9]{8}$/', 'unique:users,phone'],
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
        ];

        if ($request->has('company_name')) {
            $rules['company_name'] = 'required|string|max:255';
            $rules['cr_number'] = 'required|string|max:255';
        }

        $validated = $request->validate($rules);

        $user = User::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'type' => $request->has('company_name') ? 'company' : 'individual',
            'company_name' => $validated['company_name'] ?? null,
            'cr_number' => $validated['cr_number'] ?? null,
        ]);

        session()->put('pending_user_id', $user->id);
        
        return redirect()->route('verify-otp');
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
            
            return redirect()->route('verify-otp');
        }

        return back()->withErrors([
            'phone' => 'بيانات الاعتماد المقدمة لا تتطابق مع سجلاتنا.',
        ])->onlyInput('phone');
    }

    public function showVerifyOtpForm()
    {
        if (!session()->has('pending_user_id')) {
            return redirect()->route('login');
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

                session()->flash('show_interests', true);
                return redirect()->route('verification.success');
            }
        }

        return back()->with('error', 'رمز التحقق غير صحيح');
    }

    public function showVerificationSuccess()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        
        $redirectUrl = route('home');
        $redirectText = 'الذهاب للرئيسية';
        
        return view('auth.verification-success', compact('redirectUrl', 'redirectText'));
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
            return redirect()->route('reset-password');
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
            return redirect()->route('forgot-password')->withErrors([
                'phone' => 'انتهت الجلسة، يرجى إعادة المحاولة.',
            ]);
        }

        $user = User::where('phone', $phone)->first();

        if (!$user) {
            return redirect()->route('forgot-password')->withErrors([
                'phone' => 'رقم الجوال غير مسجل لدينا.',
            ]);
        }

        $user->password = bcrypt($request->password);
        $user->save();

        session()->forget('reset_phone');

        return redirect()->route('reset-success');
    }

    public function showResetSuccess()
    {
        return view('auth.reset-success');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
