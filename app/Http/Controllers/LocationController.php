<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LocationController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'latitude' => 'required',
            'longitude' => 'required',
            'address' => 'required',
        ]);

        $cityId = null;
        if ($request->filled('city')) {
            $cleanCity = str_replace(['محافظة ', 'منطقة ', 'مدينة ', 'حي '], '', $request->city);
            $cleanCity = trim(explode('،', $cleanCity)[0]);
            $matchedCity = \App\Models\City::where('name', 'like', '%' . $cleanCity . '%')->first();
            if ($matchedCity) {
                $cityId = $matchedCity->id;
            }
        }

        if (!$cityId && $request->filled('address')) {
            $cities = \App\Models\City::all();
            foreach ($cities as $city) {
                if (mb_stripos($request->address, $city->name) !== false) {
                    $cityId = $city->id;
                    break;
                }
            }
        }

        if (!$cityId && $request->filled('address')) {
            $regions = \App\Models\Region::all();
            foreach ($regions as $region) {
                if (mb_stripos($request->address, $region->name) !== false) {
                    $defaultCity = \App\Models\City::where('region_id', $region->id)->first();
                    if ($defaultCity) {
                        $cityId = $defaultCity->id;
                        break;
                    }
                }
            }
        }

        if (Auth::check()) {
            $user = Auth::user();
            $user->update([
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'address' => $request->address,
                'city_id' => $cityId,
            ]);
        } else {
            // Store in session for guests
            session([
                'user_latitude' => $request->latitude,
                'user_longitude' => $request->longitude,
                'user_address' => $request->address,
                'user_city_id' => $cityId,
            ]);
        }

        return response()->json([
            'success' => true, 
            'address' => $request->address,
            'city_id' => $cityId,
            'message' => 'تم تحديث الموقع بنجاح'
        ]);
    }

    public function clear()
    {
        if (Auth::check()) {
            $user = Auth::user();
            $user->update([
                'latitude' => null,
                'longitude' => null,
                'address' => null,
                'city_id' => null,
            ]);
        }
        
        session()->forget(['user_latitude', 'user_longitude', 'user_address', 'user_city_id']);

        $previousUrl = url()->previous();
        $redirectUrl = preg_replace('/\?.*/', '', $previousUrl); // Strip all query parameters

        return redirect($redirectUrl)->with('success', 'تم مسح الموقع وإعادة التعيين بنجاح');
    }
}
