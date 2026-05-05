<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Standardized JSON Response
     */
    protected function apiResponse($success, $message, $data = null, $code = 200)
    {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    /**
     * Login API
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'password' => 'required|string',
        ], [
            'phone.required' => 'رقم الجوال مطلوب.',
            'password.required' => 'كلمة المرور مطلوبة.',
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(false, 'بيانات غير مكتملة', $validator->errors(), 422);
        }

        $user = User::where('phone', $request->phone)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->apiResponse(false, 'بيانات الدخول غير صحيحة', null, 401);
        }

        if ($user->status !== 'active') {
            return $this->apiResponse(false, 'هذا الحساب معطل أو بانتظار التفعيل', null, 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->apiResponse(true, 'تم تسجيل الدخول بنجاح', [
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Register API
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|unique:users,phone',
            'password' => 'required|string|min:8',
            'account_type' => 'required|in:individual,company',
            'terms' => 'required|accepted',
            'company_name' => 'required_if:account_type,company|string|max:255',
            'commercial_register' => 'required_if:account_type,company|string|max:255',
        ], [
            'name.required' => 'الاسم مطلوب.',
            'email.required' => 'البريد الإلكتروني مطلوب.',
            'email.email' => 'صيغة البريد الإلكتروني غير صحيحة.',
            'email.unique' => 'البريد الإلكتروني مسجل مسبقاً.',
            'phone.required' => 'رقم الجوال مطلوب.',
            'phone.unique' => 'رقم الجوال مسجل مسبقاً.',
            'password.required' => 'كلمة المرور مطلوبة.',
            'password.min' => 'كلمة المرور يجب أن لا تقل عن 8 أحرف.',
            'account_type.required' => 'نوع الحساب مطلوب.',
            'terms.required' => 'يجب الموافقة على الشروط والأحكام.',
            'company_name.required_if' => 'اسم الشركة مطلوب عند التسجيل كشركة.',
            'commercial_register.required_if' => 'رقم السجل التجاري مطلوب عند التسجيل كشركة.',
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(false, 'فشل التحقق من البيانات', $validator->errors(), 422);
        }

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'account_type' => $request->account_type,
            'status' => 'active',
        ];

        if ($request->account_type === 'company') {
            $userData['company_name'] = $request->company_name;
            $userData['commercial_register'] = $request->commercial_register;
        }

        $user = User::create($userData);
        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->apiResponse(true, 'تم إنشاء الحساب بنجاح', [
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Send OTP API
     */
    public function sendOtp(Request $request)
    {
        $request->validate(['phone' => 'required|string']);
        
        // Logic to send OTP via SMS (Taqnyat or other)
        // For now, returning success assuming 0000 is the OTP
        return $this->apiResponse(true, 'تم إرسال رمز التحقق بنجاح');
    }

    /**
     * Verify OTP API
     */
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'otp' => 'required|string|size:4',
        ], [
            'phone.required' => 'رقم الجوال مطلوب.',
            'otp.required' => 'رمز التحقق مطلوب.',
            'otp.size' => 'يجب إدخال 4 أرقام لرمز التحقق.',
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(false, 'فشل التحقق', $validator->errors(), 422);
        }

        if ($request->otp === '0000') {
            return $this->apiResponse(true, 'تم التحقق من الرمز بنجاح');
        }

        return $this->apiResponse(false, 'رمز التحقق غير صحيح', null, 400);
    }

    /**
     * Forgot Password API
     */
    public function forgotPassword(Request $request)
    {
        $request->validate(['phone' => 'required|string'], ['phone.required' => 'رقم الجوال مطلوب.']);
        
        $user = User::where('phone', $request->phone)->first();
        if (!$user) {
            return $this->apiResponse(false, 'رقم الجوال غير مسجل لدينا', null, 404);
        }

        // Logic to send reset OTP
        return $this->apiResponse(true, 'تم إرسال رمز استعادة كلمة المرور');
    }

    /**
     * Reset Password API
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'otp' => 'required|string|size:4',
            'password' => 'required|string|min:8',
        ], [
            'phone.required' => 'رقم الجوال مطلوب.',
            'otp.required' => 'رمز التحقق مطلوب.',
            'password.required' => 'كلمة المرور الجديدة مطلوبة.',
            'password.min' => 'كلمة المرور يجب أن لا تقل عن 8 أحرف.',
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(false, 'فشل التحقق', $validator->errors(), 422);
        }

        if ($request->otp !== '0000') {
            return $this->apiResponse(false, 'رمز التحقق غير صحيح', null, 400);
        }

        $user = User::where('phone', $request->phone)->first();
        if (!$user) {
            return $this->apiResponse(false, 'المستخدم غير موجود', null, 404);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return $this->apiResponse(true, 'تم تغيير كلمة المرور بنجاح');
    }

    /**
     * Logout API
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->apiResponse(true, 'تم تسجيل الخروج بنجاح');
    }

    /**
     * Profile API
     */
    public function profile(Request $request)
    {
        return $this->apiResponse(true, 'بيانات الملف الشخصي', $request->user());
    }
    /**
     * Update User Interests
     */
    public function updateInterests(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:categories,id',
        ], [
            'category_ids.required' => 'يرجى اختيار تصنيف واحد على الأقل.',
            'category_ids.array' => 'تنسيق البيانات غير صحيح.',
            'category_ids.*.exists' => 'أحد التصنيفات المختارة غير موجود في قاعدة البيانات.',
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(false, 'فشل التحقق', $validator->errors(), 422);
        }

        $request->user()->interests()->sync($request->category_ids);

        return $this->apiResponse(true, 'تم تحديث الاهتمامات بنجاح');
    }
}
