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

        $validator = Validator::make($request->all(), [
            'phone' => ['required', 'regex:/^5[0-9]{8}$/'],
            'password' => 'required|string',
        ], [
            'phone.required' => 'رقم الجوال مطلوب.',
            'phone.regex' => 'رقم الجوال يجب أن يبدأ بـ 5 ويتكون من 9 أرقام.',
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

        // 🔒 New Device Login Detection
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

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => ['required', 'regex:/^5[0-9]{8}$/', 'unique:users,phone'],
            'password' => 'required|string|min:8',
            'account_type' => 'required|in:individual,company,partner',
            'terms' => 'required|accepted',
            'company_name' => 'required_if:account_type,company|string|max:255',
            'commercial_register' => 'required_if:account_type,company|string|max:255',
        ], [
            'name.required' => 'الاسم مطلوب.',
            'email.required' => 'البريد الإلكتروني مطلوب.',
            'email.email' => 'صيغة البريد الإلكتروني غير صحيحة.',
            'email.unique' => 'البريد الإلكتروني مسجل مسبقاً.',
            'phone.required' => 'رقم الجوال مطلوب.',
            'phone.regex' => 'رقم الجوال يجب أن يبدأ بـ 5 ويتكون من 9 أرقام.',
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

        $userAgent = $request->header('X-Device-Id') ?: ($request->input('device_id') ?: $request->userAgent() ?: 'unknown_device');
        $deviceSignature = md5($userAgent);

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'account_type' => $request->account_type,
            'status' => 'active',
            'known_devices' => [$deviceSignature],
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
        $request->validate(['phone' => ['required', 'regex:/^5[0-9]{8}$/']]);
        
        // Logic to send OTP via SMS (Taqnyat or other)
        // For now, returning success assuming 0000 is the OTP
        return $this->apiResponse(true, 'تم إرسال رمز التحقق بنجاح');
    }

    /**
     * Verify OTP API
     */
    public function verifyOtp(Request $request)
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

        $validator = Validator::make($request->all(), [
            'phone' => ['required', 'regex:/^5[0-9]{8}$/'],
            'otp' => 'required|string|size:4',
        ], [
            'phone.required' => 'رقم الجوال مطلوب.',
            'phone.regex' => 'رقم الجوال يجب أن يبدأ بـ 5 ويتكون من 9 أرقام.',
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
        $request->validate(['phone' => ['required', 'regex:/^5[0-9]{8}$/']], [
            'phone.required' => 'رقم الجوال مطلوب.',
            'phone.regex' => 'رقم الجوال يجب أن يبدأ بـ 5 ويتكون من 9 أرقام.',
        ]);
        
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

        $validator = Validator::make($request->all(), [
            'phone' => ['required', 'regex:/^5[0-9]{8}$/'],
            'otp' => 'required|string|size:4',
            'password' => 'required|string|min:8',
        ], [
            'phone.required' => 'رقم الجوال مطلوب.',
            'phone.regex' => 'رقم الجوال يجب أن يبدأ بـ 5 ويتكون من 9 أرقام.',
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
     * Get user current interests and all available categories
     */
    public function getInterests(Request $request)
    {
        $categories = \App\Models\Category::select('id', 'name', 'image')->get();
        $userInterests = $request->user()->interests()->pluck('categories.id')->toArray();

        return $this->apiResponse(true, 'اهتمامات المستخدم والتصنيفات المتاحة', [
            'categories' => $categories->map(function($category) use ($userInterests) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'image' => $category->image ? (\Illuminate\Support\Str::startsWith($category->image, 'uploads/') ? asset($category->image) : asset('uploads/' . $category->image)) : 'https://mazad-admin.com/uploads/categories/placeholder.png',
                    'is_selected' => in_array($category->id, $userInterests)
                ];
            })
        ]);
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

    /**
     * Update Profile API
     */
    public function updateProfile(Request $request)
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

        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => ['required', 'regex:/^5[0-9]{8}$/', 'unique:users,phone,' . $user->id],
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'address' => 'nullable|string|max:500',
            'bio' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ], [
            'name.required' => 'الاسم مطلوب.',
            'phone.required' => 'رقم الجوال مطلوب.',
            'phone.regex' => 'رقم الجوال يجب أن يبدأ بـ 5 ويتكون من 9 أرقام.',
            'phone.unique' => 'رقم الجوال مسجل مسبقاً لحساب آخر.',
            'email.required' => 'البريد الإلكتروني مطلوب.',
            'email.email' => 'صيغة البريد الإلكتروني غير صحيحة.',
            'email.unique' => 'البريد الإلكتروني مسجل مسبقاً لحساب آخر.',
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(false, 'فشل التحقق من البيانات', $validator->errors(), 422);
        }

        $data = $request->only(['name', 'phone', 'email', 'address', 'bio', 'latitude', 'longitude']);
        $user->update($data);

        return $this->apiResponse(true, 'تم تحديث البيانات الشخصية بنجاح', $user);
    }

    /**
     * Update Password API
     */
    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'current_password.required' => 'كلمة المرور الحالية مطلوبة.',
            'password.required' => 'كلمة المرور الجديدة مطلوبة.',
            'password.min' => 'يجب ألا تقل كلمة المرور الجديدة عن 8 أحرف.',
            'password.confirmed' => 'تأكيد كلمة المرور الجديدة غير متطابق.',
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(false, 'فشل التحقق من البيانات', $validator->errors(), 422);
        }

        if (!Hash::check($request->current_password, $user->password)) {
            return $this->apiResponse(false, 'كلمة المرور الحالية غير صحيحة', null, 400);
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return $this->apiResponse(true, 'تم تحديث كلمة المرور بنجاح');
    }

    /**
     * Update Bank Information API
     */
    public function updateBank(Request $request)
    {
        $user = $request->user();

        // Clean spaces and capitalize IBAN before validation
        if ($request->filled('bank_iban')) {
            $request->merge([
                'bank_iban' => strtoupper(str_replace(' ', '', $request->bank_iban))
            ]);
        }

        $validator = Validator::make($request->all(), [
            'bank_name' => 'nullable|string|max:255',
            'bank_account_name' => 'nullable|string|max:255',
            'bank_account_number' => ['nullable', 'string', 'regex:/^[0-9]{10,20}$/'],
            'bank_iban' => ['nullable', 'string', 'regex:/^SA[0-9]{22}$/'],
            'bank_address' => 'nullable|string|max:500',
            'bank_swift_code' => ['nullable', 'string', 'regex:/^[A-Z]{6}[A-Z0-9]{2}([A-Z0-9]{3})?$/'],
        ], [
            'bank_account_number.regex' => 'رقم الحساب يجب أن يحتوي على أرقام فقط وبطول بين 10 و 20 رقماً.',
            'bank_iban.regex' => 'رقم الآيبان غير صحيح. يجب أن يبدأ بـ SA ويليه 22 رقماً.',
            'bank_swift_code.regex' => 'السويفت كود (Swift Code) غير صحيح. يجب أن يتكون من 8 أو 11 حرفاً ورقماً بالصيغة الصحيحة.',
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(false, 'فشل التحقق من البيانات', $validator->errors(), 422);
        }

        $user->update($request->only(['bank_name', 'bank_account_name', 'bank_account_number', 'bank_iban', 'bank_address', 'bank_swift_code']));

        return $this->apiResponse(true, 'تم تحديث البيانات البنكية بنجاح', [
            'bank_name' => $user->bank_name,
            'bank_account_name' => $user->bank_account_name,
            'bank_account_number' => $user->bank_account_number,
            'bank_iban' => $user->bank_iban,
            'bank_address' => $user->bank_address,
            'bank_swift_code' => $user->bank_swift_code,
        ]);
    }

    /**
     * Update Avatar API
     */
    public function updateAvatar(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ], [
            'avatar.required' => 'صورة الملف الشخصي مطلوبة.',
            'avatar.image' => 'يجب اختيار ملف صورة صالح.',
            'avatar.max' => 'الحد الأقصى لحجم الصورة هو 2 ميجابايت.',
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(false, 'فشل التحقق من البيانات', $validator->errors(), 422);
        }

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            $destinationPath = public_path('uploads/avatars');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }
            
            $file->move($destinationPath, $filename);

            // Delete old avatar if exists
            if ($user->avatar && str_contains($user->avatar, '/uploads/avatars/')) {
                $oldPath = public_path(ltrim($user->avatar, '/'));
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }

            $user->update([
                'avatar' => '/uploads/avatars/' . $filename
            ]);
        }

        return $this->apiResponse(true, 'تم تحديث الصورة الشخصية بنجاح', [
            'avatar' => asset($user->avatar)
        ]);
    }

    /**
     * Update Cover API
     */
    public function updateCover(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'cover' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
        ], [
            'cover.required' => 'صورة الغلاف مطلوبة.',
            'cover.image' => 'يجب اختيار ملف صورة صالح.',
            'cover.max' => 'الحد الأقصى لحجم صورة الغلاف هو 5 ميجابايت.',
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(false, 'فشل التحقق من البيانات', $validator->errors(), 422);
        }

        if ($request->hasFile('cover')) {
            $file = $request->file('cover');
            $filename = time() . '_cover_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            $destinationPath = public_path('uploads/covers');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }
            
            $file->move($destinationPath, $filename);

            // Delete old cover if exists
            if ($user->cover && str_contains($user->cover, '/uploads/covers/')) {
                $oldPath = public_path(ltrim($user->cover, '/'));
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }

            $user->update([
                'cover' => '/uploads/covers/' . $filename
            ]);
        }

        return $this->apiResponse(true, 'تم تحديث صورة الغلاف بنجاح', [
            'cover' => asset($user->cover)
        ]);
    }

    /**
     * Get User Notification Settings API
     */
    public function getNotificationSettings(Request $request)
    {
        $user = $request->user();
        return $this->apiResponse(true, 'إعدادات الإشعارات الحالية', [
            'all_notifications' => (bool) $user->all_notifications,
            'bid_notifications' => (bool) $user->bid_notifications,
            'bid_approaching_limit_notifications' => (bool) $user->bid_approaching_limit_notifications,
            'bid_approaching_limit_percentage' => (int) $user->bid_approaching_limit_percentage,
            'bid_reached_limit_notifications' => (bool) $user->bid_reached_limit_notifications,
            'auction_update_notifications' => (bool) $user->auction_update_notifications,
            'favorite_auction_notifications' => (bool) $user->favorite_auction_notifications,
            'winning_bid_notifications' => (bool) $user->winning_bid_notifications,
        ]);
    }

    /**
     * Update User Notification Settings API
     */
    public function updateNotificationSettings(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'all_notifications' => 'nullable|boolean',
            'bid_notifications' => 'nullable|boolean',
            'bid_approaching_limit_notifications' => 'nullable|boolean',
            'bid_approaching_limit_percentage' => 'nullable|integer|in:70,80,90',
            'bid_reached_limit_notifications' => 'nullable|boolean',
            'auction_update_notifications' => 'nullable|boolean',
            'favorite_auction_notifications' => 'nullable|boolean',
            'winning_bid_notifications' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(false, 'فشل التحقق من البيانات', $validator->errors(), 422);
        }

        $user->update($request->only([
            'all_notifications',
            'bid_notifications',
            'bid_approaching_limit_notifications',
            'bid_approaching_limit_percentage',
            'bid_reached_limit_notifications',
            'auction_update_notifications',
            'favorite_auction_notifications',
            'winning_bid_notifications',
        ]));

        return $this->apiResponse(true, 'تم تحديث إعدادات الإشعارات بنجاح', [
            'all_notifications' => (bool) $user->all_notifications,
            'bid_notifications' => (bool) $user->bid_notifications,
            'bid_approaching_limit_notifications' => (bool) $user->bid_approaching_limit_notifications,
            'bid_approaching_limit_percentage' => (int) $user->bid_approaching_limit_percentage,
            'bid_reached_limit_notifications' => (bool) $user->bid_reached_limit_notifications,
            'auction_update_notifications' => (bool) $user->auction_update_notifications,
            'favorite_auction_notifications' => (bool) $user->favorite_auction_notifications,
            'winning_bid_notifications' => (bool) $user->winning_bid_notifications,
        ]);
    }

    /**
     * Update FCM Token API
     */
    public function updateFcmToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fcm_token' => 'required|string',
        ], [
            'fcm_token.required' => 'رمز الجهاز (FCM Token) مطلوب.',
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(false, 'فشل التحقق من البيانات', $validator->errors(), 422);
        }

        $request->user()->update([
            'fcm_token' => $request->fcm_token
        ]);

        return $this->apiResponse(true, 'تم تحديث رمز الجهاز (FCM Token) بنجاح');
    }
}
