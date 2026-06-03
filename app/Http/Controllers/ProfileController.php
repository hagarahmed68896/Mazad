<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        return view('users.settings.profile');
    }

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

        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => ['required', 'regex:/^5[0-9]{8}$/', 'unique:users,phone,' . $user->id],
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'address' => 'nullable|string|max:500',
            'bio' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'cover' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
        ], [
            'phone.regex' => 'رقم الجوال يجب أن يبدأ بـ 5 ويتكون من 9 أرقام.',
        ]);

        $data = $request->only(['name', 'phone', 'email', 'address', 'bio', 'latitude', 'longitude']);

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

            $data['cover'] = '/uploads/covers/' . $filename;
        }

        $user->update($data);

        return redirect()->back()->with('success', 'تم حفظ بيانات الحساب بنجاح.');
    }

    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
        ]);

        $user = Auth::user();

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            // Save directly to public folder (not storage)
            $destinationPath = public_path('uploads/avatars');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }
            
            $file->move($destinationPath, $filename);

            // Delete old avatar if it exists in public path
            if ($user->avatar && str_contains($user->avatar, '/uploads/avatars/')) {
                $oldPath = public_path(ltrim($user->avatar, '/'));
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }

            $avatarUrl = '/uploads/avatars/' . $filename;
            $user->update(['avatar' => $avatarUrl]);

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث الصورة بنجاح',
                'avatar_url' => asset($avatarUrl)
            ]);
        }

        return response()->json(['success' => false, 'message' => 'لم يتم إرسال الصورة'], 400);
    }

    public function updateBank(Request $request)
    {
        $user = Auth::user();

        // Normalize spaces and convert IBAN to uppercase before validating
        if ($request->filled('bank_iban')) {
            $request->merge([
                'bank_iban' => strtoupper(str_replace(' ', '', $request->bank_iban))
            ]);
        }

        $request->validate([
            'bank_name' => 'nullable|string|max:255',
            'bank_account_name' => 'nullable|string|max:255',
            'bank_account_number' => ['nullable', 'string', 'regex:/^[0-9]{10,20}$/'],
            'bank_iban' => ['nullable', 'string', 'regex:/^SA[0-9]{22}$/'],
            'bank_address' => 'nullable|string|max:500',
            'bank_swift_code' => ['nullable', 'string', 'regex:/^[A-Z]{6}[A-Z0-9]{2}([A-Z0-9]{3})?$/'],
        ], [
            'bank_account_number.regex' => 'رقم الحساب يجب أن يحتوي على أرقام فقط وبطول بين 10 و 20 رقماً.',
            'bank_iban.regex' => 'رقم الآيبان غير صحيح. يجب أن يبدأ بـ SA ويليه 22 رقماً (مثال: SA801234...).',
            'bank_swift_code.regex' => 'السويفت كود (Swift Code) غير صحيح. يجب أن يتكون من 8 أو 11 حرفاً ورقماً بالصيغة الصحيحة.',
        ]);

        $user->update($request->only(['bank_name', 'bank_account_name', 'bank_account_number', 'bank_iban', 'bank_address', 'bank_swift_code']));

        return redirect()->back()->with('success', 'تم حفظ الحساب البنكي بنجاح.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'كلمة المرور الحالية غير صحيحة.']);
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return redirect()->back()->with('success', 'تم تغيير كلمة المرور بنجاح.');
    }

    public function interestsIndex()
    {
        // Category views shared in AppServiceProvider, but let's fetch here just in case or for cleaner explicit flow
        $categories = \App\Models\Category::whereNull('parent_id')->get();
        return view('users.settings.interests', compact('categories'));
    }

    public function updateInterests(Request $request)
    {
        $request->validate([
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:categories,id',
        ], [
            'category_ids.required' => 'يرجى اختيار اهتمام واحد على الأقل.',
        ]);

        Auth::user()->interests()->sync($request->category_ids);

        return redirect()->route('profile.interests.index')->with('success', 'تم حفظ اهتماماتك بنجاح.');
    }

    public function notificationsIndex()
    {
        $user = Auth::user();
        return view('users.settings.notifications', compact('user'));
    }

    public function updateNotifications(Request $request)
    {
        $user = Auth::user();

        // Validate percentage
        $request->validate([
            'bid_approaching_limit_percentage' => 'nullable|integer|in:70,80,90',
        ]);

        // Standard request might send explicit boolean or checkboxes.
        // Let's support both checkbox presence and raw boolean parameters (useful for AJAX)
        $data = [
            'all_notifications' => $request->has('all_notifications') ? ($request->input('all_notifications') === 'on' || $request->input('all_notifications') == true) : false,
            'bid_notifications' => $request->has('bid_notifications') ? ($request->input('bid_notifications') === 'on' || $request->input('bid_notifications') == true) : false,
            'bid_approaching_limit_notifications' => $request->has('bid_approaching_limit_notifications') ? ($request->input('bid_approaching_limit_notifications') === 'on' || $request->input('bid_approaching_limit_notifications') == true) : false,
            'bid_approaching_limit_percentage' => $request->input('bid_approaching_limit_percentage', $user->bid_approaching_limit_percentage ?? 70),
            'bid_reached_limit_notifications' => $request->has('bid_reached_limit_notifications') ? ($request->input('bid_reached_limit_notifications') === 'on' || $request->input('bid_reached_limit_notifications') == true) : false,
            'auction_update_notifications' => $request->has('auction_update_notifications') ? ($request->input('auction_update_notifications') === 'on' || $request->input('auction_update_notifications') == true) : false,
            'favorite_auction_notifications' => $request->has('favorite_auction_notifications') ? ($request->input('favorite_auction_notifications') === 'on' || $request->input('favorite_auction_notifications') == true) : false,
            'winning_bid_notifications' => $request->has('winning_bid_notifications') ? ($request->input('winning_bid_notifications') === 'on' || $request->input('winning_bid_notifications') == true) : false,
        ];

        $user->update($data);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم تحديث إعدادات الإشعارات بنجاح.',
                'settings' => $data
            ]);
        }

        return redirect()->back()->with('success', 'تم تحديث إعدادات الإشعارات بنجاح.');
    }
}
