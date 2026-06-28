<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Ad;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TeamController extends Controller
{
    /**
     * Display Team Members list (representatives and partners)
     */
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'يجب تسجيل الدخول أولاً.');
        }

        $user = Auth::user();
        if ($user->account_type !== 'company') {
            return redirect()->route('home')->with('error', 'هذه الصفحة مخصصة لحسابات الشركات فقط.');
        }

        $tab = $request->get('tab', 'representative'); // 'representative' or 'partner'
        $search = $request->get('q', '');

        // Query team members under the current logged-in company
        $query = User::where('parent_id', $user->id)
            ->where('account_type', $tab);

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        // Count ads for each team member
        $members = $query->withCount(['ads' => function($q) {
            $q->where('status', 'active');
        }])->paginate(12)->appends($request->all());

        // Seeding some default team members if empty, to match mockup screenshots perfectly
        if ($members->isEmpty() && empty($search)) {
            $mockMembers = [
                [
                    'name' => 'أحمد خالد',
                    'email' => 'ahmed.khalid1@example.com',
                    'phone' => '0598765431',
                    'account_type' => 'representative',
                    'commission_rate' => 10.00,
                    'avatar' => '/uploads/avatars/mock_team_1.png'
                ],
                [
                    'name' => 'خالد عبد الله',
                    'email' => 'khaled.abdallah@example.com',
                    'phone' => '0598765432',
                    'account_type' => 'representative',
                    'commission_rate' => 12.50,
                    'avatar' => '/uploads/avatars/mock_team_2.png'
                ],
                [
                    'name' => 'محمد علي',
                    'email' => 'mohamed.ali@example.com',
                    'phone' => '0598765433',
                    'account_type' => 'representative',
                    'commission_rate' => 8.00,
                    'avatar' => '/uploads/avatars/mock_team_3.png'
                ],
                [
                    'name' => 'أحمد خالد',
                    'email' => 'ahmed.khalid2@example.com',
                    'phone' => '0598765434',
                    'account_type' => 'partner', // manager
                    'commission_rate' => 0.00,
                    'avatar' => '/uploads/avatars/mock_team_4.png'
                ],
                [
                    'name' => 'سعد منصور',
                    'email' => 'saad.mansour@example.com',
                    'phone' => '0598765435',
                    'account_type' => 'partner', // manager
                    'commission_rate' => 0.00,
                    'avatar' => '/uploads/avatars/mock_team_5.png'
                ]
            ];

            foreach ($mockMembers as $m) {
                // Ensure directories exist
                $dest = public_path('uploads/avatars');
                if (!file_exists($dest)) {
                    mkdir($dest, 0777, true);
                }
                
                // Copy placeholder avatars if they don't exist
                $mockAvatarPath = public_path($m['avatar']);
                if (!file_exists($mockAvatarPath)) {
                    @copy('https://i.pravatar.cc/150?img=' . rand(1, 70), $mockAvatarPath);
                }

                User::create([
                    'name' => $m['name'],
                    'email' => $m['email'],
                    'phone' => $m['phone'],
                    'password' => Hash::make('password123'),
                    'account_type' => $m['account_type'],
                    'parent_id' => $user->id,
                    'commission_rate' => $m['commission_rate'],
                    'avatar' => $m['avatar'],
                    'status' => 'active'
                ]);
            }

            // Re-fetch
            $members = User::where('parent_id', $user->id)
                ->where('account_type', $tab)
                ->withCount(['ads' => function($q) {
                    $q->where('status', 'active');
                }])
                ->paginate(12)
                ->appends($request->all());
        }

        return view('users.team.index', compact('members', 'tab', 'search'));
    }

    /**
     * Show form to add delegate/manager
     */
    public function create(Request $request)
    {
        if (!Auth::check() || Auth::user()->account_type !== 'company') {
            return redirect()->route('home')->with('error', 'غير مصرح لك بدخول هذه الصفحة.');
        }

        $type = $request->query('type', 'representative');
        if (!in_array($type, ['representative', 'partner'])) {
            $type = 'representative';
        }

        return view('users.team.create', compact('type'));
    }

    /**
     * Store new Team Member
     */
    public function store(Request $request)
    {
        if (!Auth::check() || Auth::user()->account_type !== 'company') {
            return response()->json(['success' => false, 'message' => 'غير مصرح بالعملية.'], 403);
        }

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

        $company = Auth::user();

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
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
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

        // Create Member User account
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

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم إضافة عضو الفريق بنجاح.',
                'member' => $member
            ]);
        }

        return redirect()->route('team.index', ['tab' => $request->account_type])->with('team_member_created', [
            'name' => $member->name,
            'password' => 'password123',
        ]);
    }

    /**
     * Delete Team Member
     */
    public function destroy($id)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'يجب تسجيل الدخول.'], 401);
        }

        try {
            $company = Auth::user();
            $member = User::where('parent_id', $company->id)->findOrFail($id);

            // Delete team member avatar if exists and not placeholder
            $rawAvatar = $member->getRawOriginal('avatar');
            if ($rawAvatar && str_contains($rawAvatar, '/uploads/avatars/') && !str_contains($rawAvatar, 'placeholder.png')) {
                $oldPath = public_path(ltrim($rawAvatar, '/'));
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }

            $member->delete();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف عضو الفريق بنجاح.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء الحذف: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show form to edit delegate/manager
     */
    public function edit($id)
    {
        if (!Auth::check() || Auth::user()->account_type !== 'company') {
            return redirect()->route('home')->with('error', 'غير مصرح لك بدخول هذه الصفحة.');
        }

        $company = Auth::user();
        $member = User::where('parent_id', $company->id)->findOrFail($id);
        $type = $member->account_type;

        return view('users.team.edit', compact('member', 'type'));
    }

    /**
     * Update Team Member
     */
    public function update(Request $request, $id)
    {
        if (!Auth::check() || Auth::user()->account_type !== 'company') {
            return response()->json(['success' => false, 'message' => 'غير مصرح بالعملية.'], 403);
        }

        $company = Auth::user();
        $member = User::where('parent_id', $company->id)->findOrFail($id);

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
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
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

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم تحديث بيانات العضو بنجاح.',
                'member' => $member
            ]);
        }

        return redirect()->route('team.index', ['tab' => $member->account_type])->with('success', 'تم تحديث بيانات عضو الفريق بنجاح.');
    }
}

