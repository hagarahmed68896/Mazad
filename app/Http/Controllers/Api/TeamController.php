<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class TeamController extends Controller
{
    /**
     * Get list of team members (representatives/managers)
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        if ($user->account_type !== 'company') {
            return response()->json([
                'success' => false,
                'message' => 'هذه العملية مخصصة لحسابات الشركات فقط.'
            ], 403);
        }

        $tab = $request->get('tab', 'representative'); // 'representative' or 'partner'
        $search = $request->get('q', '');

        $query = User::where('parent_id', $user->id)
            ->where('account_type', $tab);

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        $members = $query->withCount(['ads' => function($q) {
            $q->where('status', 'active');
        }])->paginate(12)->appends($request->all());

        return response()->json([
            'success' => true,
            'message' => 'قائمة أعضاء الفريق',
            'data' => $members
        ]);
    }

    /**
     * Store a new team member (representative/manager)
     */
    public function store(Request $request)
    {
        $company = auth()->user();
        if ($company->account_type !== 'company') {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح بالعملية. مخصص للشركات فقط.'
            ], 403);
        }

        // Phone normalization
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
            'account_type' => 'required|in:representative,partner',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ], [
            'name.required' => 'الاسم الكامل مطلوب.',
            'email.required' => 'البريد الإلكتروني مطلوب.',
            'email.email' => 'صيغة البريد الإلكتروني غير صحيحة.',
            'email.unique' => 'البريد الإلكتروني مسجل مسبقاً لمستخدم آخر.',
            'phone.required' => 'رقم الجوال مطلوب.',
            'phone.regex' => 'رقم الجوال يجب أن يبدأ بـ 5 ويتكون من 9 أرقام.',
            'phone.unique' => 'رقم الجوال مسجل مسبقاً لمستخدم آخر.',
            'account_type.required' => 'نوع العضو مطلوب.',
            'commission_rate.numeric' => 'نسبة العموله يجب أن تكون رقمية.',
            'commission_rate.min' => 'نسبة العموله يجب ألا تقل عن 0.',
            'commission_rate.max' => 'نسبة العموله يجب ألا تزيد عن 100.',
            'avatar.image' => 'يجب اختيار ملف صورة صالح.',
            'avatar.max' => 'حجم الصورة يجب ألا يتجاوز 2 ميجابايت.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $avatarUrl = null;
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = time() . '_team_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            $destinationPath = public_path('uploads/avatars');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }
            
            $file->move($destinationPath, $filename);
            $avatarUrl = '/uploads/avatars/' . $filename;
        }

        $member = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make('password123'), // Default password
            'account_type' => $request->account_type,
            'parent_id' => $company->id,
            'commission_rate' => $request->commission_rate ?? 0.00,
            'avatar' => $avatarUrl,
            'status' => 'active'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إضافة عضو الفريق بنجاح. كلمة المرور الافتراضية: password123',
            'data' => $member
        ], 201);
    }

    /**
     * Show a specific team member
     */
    public function show($id)
    {
        $company = auth()->user();
        if ($company->account_type !== 'company') {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح بالعملية.'
            ], 403);
        }

        $member = User::where('parent_id', $company->id)->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'تفاصيل عضو الفريق',
            'data' => $member
        ]);
    }

    /**
     * Update a team member's details
     */
    public function update(Request $request, $id)
    {
        $company = auth()->user();
        if ($company->account_type !== 'company') {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح بالعملية.'
            ], 403);
        }

        $member = User::where('parent_id', $company->id)->findOrFail($id);

        // Phone normalization
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
            'email' => 'required|email|unique:users,email,' . $member->id,
            'phone' => ['required', 'regex:/^5[0-9]{8}$/', 'unique:users,phone,' . $member->id],
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ], [
            'name.required' => 'الاسم الكامل مطلوب.',
            'email.required' => 'البريد الإلكتروني مطلوب.',
            'email.email' => 'صيغة البريد الإلكتروني غير صحيحة.',
            'email.unique' => 'البريد الإلكتروني مسجل مسبقاً لمستخدم آخر.',
            'phone.required' => 'رقم الجوال مطلوب.',
            'phone.regex' => 'رقم الجوال يجب أن يبدأ بـ 5 ويتكون من 9 أرقام.',
            'phone.unique' => 'رقم الجوال مسجل مسبقاً لمستخدم آخر.',
            'commission_rate.numeric' => 'نسبة العموله يجب أن تكون رقمية.',
            'commission_rate.min' => 'نسبة العموله يجب ألا تقل عن 0.',
            'commission_rate.max' => 'نسبة العموله يجب ألا تزيد عن 100.',
            'avatar.image' => 'يجب اختيار ملف صورة صالح.',
            'avatar.max' => 'حجم الصورة يجب ألا يتجاوز 2 ميجابايت.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ];

        if ($member->account_type === 'representative') {
            $data['commission_rate'] = $request->commission_rate ?? 0.00;
        }

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = time() . '_team_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            $destinationPath = public_path('uploads/avatars');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }
            
            $file->move($destinationPath, $filename);
            
            // Delete old avatar if exists and not default placeholder
            if ($member->avatar && str_contains($member->avatar, '/uploads/avatars/') && !str_contains($member->avatar, 'placeholder.png')) {
                $oldPath = public_path(ltrim($member->avatar, '/'));
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }
            
            $data['avatar'] = '/uploads/avatars/' . $filename;
        }

        $member->update($data);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث بيانات العضو بنجاح.',
            'data' => $member
        ]);
    }

    /**
     * Delete a team member
     */
    public function destroy($id)
    {
        $company = auth()->user();
        if ($company->account_type !== 'company') {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح بالعملية.'
            ], 403);
        }

        $member = User::where('parent_id', $company->id)->findOrFail($id);

        // Delete team member avatar if exists and not placeholder
        if ($member->avatar && str_contains($member->avatar, '/uploads/avatars/') && !str_contains($member->avatar, 'placeholder.png')) {
            $oldPath = public_path(ltrim($member->avatar, '/'));
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
        }

        $member->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف عضو الفريق بنجاح.'
        ]);
    }
}
