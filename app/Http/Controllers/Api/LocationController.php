<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Region;
use App\Models\City;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    protected function apiResponse($success, $message, $data = null, $code = 200)
    {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    public function regions()
    {
        $regions = Region::all();
        return $this->apiResponse(true, 'قائمة المناطق', $regions);
    }

    public function cities($regionId)
    {
        $cities = City::where('region_id', $regionId)->get();
        return $this->apiResponse(true, 'قائمة المدن', $cities);
    }

    public function allCities()
    {
        $cities = City::all();
        return $this->apiResponse(true, 'كل المدن', $cities);
    }

    public function updateLocation(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'address' => 'nullable|string'
        ]);

        $lat = $request->latitude;
        $lng = $request->longitude;
        $address = $request->address;

        // Auto-fetch address if not provided
        if (!$address) {
            try {
                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'User-Agent' => 'MazadApp/1.0'
                ])->get("https://nominatim.openstreetmap.org/reverse", [
                    'format' => 'json',
                    'lat' => $lat,
                    'lon' => $lng,
                    'accept-language' => 'ar'
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $addr = $data['address'] ?? [];
                    
                    $city = $addr['city'] ?? $addr['town'] ?? $addr['village'] ?? $addr['state'] ?? '';
                    $country = $addr['country'] ?? '';
                    
                    // Clean city name from "Province/Region" prefixes
                    $city = str_replace(['محافظة ', 'منطقة ', 'مدينة '], '', $city);
                    
                    if ($city && $country) {
                        $address = "$city، $country";
                    } else {
                        $address = $data['display_name'] ?? null;
                    }
                }
            } catch (\Exception $e) {
                // Fallback or ignore
            }
        }

        if (auth('sanctum')->check()) {
            $user = auth('sanctum')->user();
            $user->update([
                'latitude' => $lat,
                'longitude' => $lng,
                'address' => $address
            ]);
        }

        return $this->apiResponse(true, 'تم تحديث الموقع بنجاح', [
            'address' => $address
        ]);
    }
}
