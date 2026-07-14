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
        if (request()->has('redirect')) {
            session(['intended_url' => request('redirect')]);
        }
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
        if (request()->has('redirect')) {
            session(['intended_url' => request('redirect')]);
        }
        return view('auth.register-type');
    }

    public function showRegisterCompanyForm()
    {
        return view('auth.register-company');
    }

    public function showRegisterPartnerForm()
    {
        $categories = \App\Models\Category::whereNull('parent_id')->get();
        return view('auth.register-partner', compact('categories'));
    }

    public function register(Request $request)
    {
        if ($request->has('phone')) {
            $phone = $request->input('phone');
            if ($phone) {
                $phone = preg_replace('/[^0-9]/', '', $phone);
                while (str_starts_with($phone, '0')) {
                    $phone = substr($phone, 1);
                }
                if (str_starts_with($phone, '966')) {
                    $phone = substr($phone, 3);
                }
                while (str_starts_with($phone, '0')) {
                    $phone = substr($phone, 1);
                }
                $request->merge(['phone' => $phone]);
            }
        }

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

        $accountType = 'individual';
        if ($request->input('account_type') === 'partner') {
            $accountType = 'partner';
        } elseif ($request->has('company_name')) {
            $accountType = 'company';
        }

        $user = User::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'account_type' => $accountType,
            'company_name' => $validated['company_name'] ?? null,
            'cr_number' => $validated['cr_number'] ?? null,
        ]);


        session()->put('pending_user_id', $user->id);
        session(['is_registering' => true]);
        
        return redirect()->route('verify-otp');
    }

    public function login(Request $request)
    {
        if ($request->has('phone')) {
            $phone = $request->input('phone');
            if ($phone) {
                $phone = preg_replace('/[^0-9]/', '', $phone);
                while (str_starts_with($phone, '0')) {
                    $phone = substr($phone, 1);
                }
                if (str_starts_with($phone, '966')) {
                    $phone = substr($phone, 3);
                }
                while (str_starts_with($phone, '0')) {
                    $phone = substr($phone, 1);
                }
                $request->merge(['phone' => $phone]);
            }
        }

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
            if ($user->status === 'banned') {
                return back()->withErrors([
                    'phone' => 'تم إيقاف هذا الحساب من قبل الإدارة.',
                ])->onlyInput('phone');
            }

            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            // 🔒 New Device Login Detection (Web)
            $userAgent = $request->header('X-Device-Id') ?: ($request->input('device_id') ?: $request->userAgent() ?: 'unknown_device');
            $deviceSignature = md5($userAgent);

            $knownDevices = is_array($user->known_devices) ? $user->known_devices : [];

            if (!in_array($deviceSignature, $knownDevices)) {
                if (count($knownDevices) > 0) {
                    \App\Models\Notification::create([
                        'user_id' => $user->id,
                        'type' => 'alert',
                        'title' => 'تنبيه أمان: تسجيل دخول جديد',
                        'content' => "تم رصد تسجيل دخول جديد لحسابك من جهاز جديد في تاريخ " . now()->format('Y-m-d H:i') . ". إذا لم تكن أنت، يرجى تغيير كلمة المرور فوراً لتأمين حسابك.",
                        'status' => 'sent',
                        'read_at' => null
                    ]);
                }

                $knownDevices[] = $deviceSignature;
                $user->update([
                    'known_devices' => $knownDevices
                ]);
            }

            $redirectUrl = session()->pull('intended_url', route('home'));
            return redirect()->to($redirectUrl);
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
                if ($user->status === 'banned') {
                    session()->forget('pending_user_id');
                    return redirect()->route('login')->withErrors([
                        'phone' => 'تم إيقاف هذا الحساب من قبل الإدارة.',
                    ]);
                }

                $remember = session()->pull('remember_login', false);
                session()->forget('pending_user_id');

                Auth::login($user, $remember);
                $request->session()->regenerate();

                // 🔒 New Device Login Detection (Web)
                $userAgent = $request->header('X-Device-Id') ?: ($request->input('device_id') ?: $request->userAgent() ?: 'unknown_device');
                $deviceSignature = md5($userAgent);

                $knownDevices = is_array($user->known_devices) ? $user->known_devices : [];

                if (!in_array($deviceSignature, $knownDevices)) {
                    if (count($knownDevices) > 0) {
                        \App\Models\Notification::create([
                            'user_id' => $user->id,
                            'type' => 'alert',
                            'title' => 'تنبيه أمان: تسجيل دخول جديد',
                            'content' => "تم رصد تسجيل دخول جديد لحسابك من جهاز جديد في تاريخ " . now()->format('Y-m-d H:i') . ". إذا لم تكن أنت، يرجى تغيير كلمة المرور فوراً لتأمين حسابك.",
                            'status' => 'sent',
                            'read_at' => null
                        ]);
                    }

                    $knownDevices[] = $deviceSignature;
                    $user->update([
                        'known_devices' => $knownDevices
                    ]);
                }

                if (session('is_registering') && $user->account_type !== 'partner') {
                    session(['show_interests' => true]);
                }
                session()->forget('is_registering');
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
        
        $redirectUrl = session()->pull('intended_url', route('home'));
        $redirectText = $redirectUrl === route('home') ? 'الذهاب للرئيسية' : 'العودة للصفحة السابقة';
        
        return view('auth.verification-success', compact('redirectUrl', 'redirectText'));
    }

    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        if ($request->has('phone')) {
            $phone = $request->input('phone');
            if ($phone) {
                $phone = preg_replace('/[^0-9]/', '', $phone);
                while (str_starts_with($phone, '0')) {
                    $phone = substr($phone, 1);
                }
                if (str_starts_with($phone, '966')) {
                    $phone = substr($phone, 3);
                }
                while (str_starts_with($phone, '0')) {
                    $phone = substr($phone, 1);
                }
                $request->merge(['phone' => $phone]);
            }
        }

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
    public function updateInterests(Request $request)
    {
        $request->validate([
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:categories,id',
        ]);

        Auth::user()->interests()->sync($request->category_ids);
        session()->forget('show_interests');

        return redirect()->route('home')->with('success', 'تم تحديث اهتماماتك بنجاح');
    }
    public function skipInterests()
    {
        session()->forget('show_interests');
        return response()->json(['success' => true]);
    }
    public function saveServices(Request $request)
    {
        $request->validate([
            'services' => 'required|array',
            'services.*' => 'exists:services,id',
        ]);

        $user = Auth::user();

        $templates = \App\Models\Service::where('user_id', 1)
            ->whereIn('id', $request->services)
            ->get();

        if ($templates->count() !== count(array_unique($request->services))) {
            return back()->withErrors(['services' => 'إحدى الخدمات المختارة غير صالحة.']);
        }

        // Delete old services
        \App\Models\Service::where('user_id', $user->id)->delete();

        foreach ($templates as $tmpl) {
            \App\Models\Service::create([
                'user_id' => $user->id,
                'category_id' => $tmpl->category_id,
                'title' => $tmpl->title,
                'description' => $tmpl->description ?: 'خدمة احترافية مقدمة من الشريك كجزء من منصة مزاد.',
                'image' => $tmpl->getRawOriginal('image'),
                'price' => (float)$tmpl->price,
            ]);
        }

        return redirect()->route('home')->with('success', 'تم تفعيل وتحديد خدمات الشريك بنجاح! 🎉');
    }

    public function showPartnerInterestsForm()
    {
        $categories = \App\Models\Category::whereNull('parent_id')->get();
        return view('auth.register-partner-interests', compact('categories'));
    }

    public function savePartnerInterests(Request $request)
    {
        $request->validate([
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:categories,id',
        ]);

        $user = Auth::user();
        $user->interests()->sync($request->category_ids);

        session()->forget('show_interests');
        return redirect()->route('home')->with('success', 'تم تسجيل اهتماماتك وتنشيط حسابك كشريك بنجاح! 🎉');
    }

    public function showPartnerServicesForm()
    {
        $user = Auth::user();
        $categoryIds = $user->resolvedInterests()->pluck('id')->toArray();
        $services = \App\Models\Service::where('user_id', 1)
            ->whereIn('category_id', $categoryIds)
            ->get();

        return view('auth.register-partner-services', compact('services'));
    }

    public function savePartnerServices(Request $request)
    {
        $request->validate([
            'services' => 'required|array',
            'services.*' => 'exists:services,id',
        ]);

        $user = Auth::user();

        $templates = \App\Models\Service::where('user_id', 1)
            ->whereIn('id', $request->services)
            ->get();

        if ($templates->count() !== count(array_unique($request->services))) {
            return back()->withErrors(['services' => 'إحدى الخدمات المختارة غير صالحة.']);
        }

        // Delete old services to avoid duplication
        \App\Models\Service::where('user_id', $user->id)->delete();

        // Save selected services
        foreach ($templates as $tmpl) {
            \App\Models\Service::create([
                'user_id' => $user->id,
                'category_id' => $tmpl->category_id,
                'title' => $tmpl->title,
                'description' => $tmpl->description ?: 'خدمة احترافية مقدمة من الشريك كجزء من منصة مزاد.',
                'image' => $tmpl->getRawOriginal('image'),
                'price' => (float)$tmpl->price,
            ]);
        }

        session()->forget('show_interests');
        return redirect()->route('home')->with('success', 'تم تفعيل وتحديد خدمات الشريك بنجاح! 🎉');
    }
}
