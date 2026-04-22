<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function contacts()
    {
        $settings = Setting::where('group', 'contacts')->pluck('value', 'key')->toArray();
        return view('admin.settings.contacts', compact('settings'));
    }

    public function updateContacts(Request $request)
    {
        $request->validate([
            'phone' => 'required|digits:9',
            'email' => 'required|email',
        ]);

        $data = $request->except('_token');
        
        foreach ($data as $key => $value) {
            // If it's the social links array, encode it
            if ($key === 'social_links') {
                $value = json_encode($value);
            }
            
            Setting::updateOrCreate(
                ['key' => $key],
                ['group' => 'contacts', 'value' => $value]
            );
        }
        return back()->with('success', 'تم تحديث وسائل التواصل بنجاح');
    }

    public function ads()
    {
        $settings = Setting::where('group', 'ads')->pluck('value', 'key')->toArray();
        return view('admin.settings.ads', compact('settings'));
    }

    public function updateAds(Request $request)
    {
        $data = $request->except('_token');
        
        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['group' => 'ads', 'value' => $value]
            );
        }

        return back()->with('success', 'تم تحديث إعدادات الإعلانات بنجاح');
    }

    public function financial()
    {
        $settings = Setting::where('group', 'financial')->pluck('value', 'key')->toArray();
        return view('admin.settings.financial', compact('settings'));
    }

    public function updateFinancial(Request $request)
    {
        $request->validate([
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'vat_rate'        => 'nullable|numeric|min:0|max:100',
            'admin_fees'      => 'nullable|numeric|min:0',
            'transfer_fees'   => 'nullable|numeric|min:0',
            'promotion_price' => 'nullable|numeric|min:0',
        ]);

        foreach ($request->except('_token') as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['group' => 'financial', 'value' => $value]
            );
        }

        return back()->with('success', 'تم تحديث إعدادات النظام المالي بنجاح');
    }
}

