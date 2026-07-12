<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Region;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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
        $cities = Region::all();
        return $this->apiResponse(true, 'كل المدن', $cities);
    }

    public function allDistricts()
    {
        $districts = City::all();
        return $this->apiResponse(true, 'كل الأحياء', $districts);
    }

    public function cityDistricts($cityId)
    {
        $districts = City::where('region_id', $cityId)->get();
        return $this->apiResponse(true, 'قائمة الأحياء', $districts);
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

        $city_name = null;
        $region_name = null;

        // Auto-fetch address if not provided
        if (!$address) {
            // 1. Try Nominatim (OpenStreetMap)
            try {
                $response = Http::withoutVerifying()->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
                ])->timeout(5)->get("https://nominatim.openstreetmap.org/reverse", [
                    'format' => 'json',
                    'lat' => $lat,
                    'lon' => $lng,
                    'accept-language' => 'ar'
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $addr = $data['address'] ?? [];
                    
                    $city_name = $addr['city'] ?? $addr['town'] ?? $addr['village'] ?? '';
                    $region_name = $addr['state'] ?? $addr['province'] ?? '';
                    
                    if (!$city_name) {
                        $city_name = $region_name;
                    }
                    
                    // Clean prefixes
                    $city_name = str_replace(['محافظة ', 'منطقة ', 'مدينة '], '', $city_name);
                    $region_name = str_replace(['منطقة ', 'محافظة '], '', $region_name);
                    
                    $address = $data['display_name'] ?? null;
                }
            } catch (\Exception $e) {
                // Ignore and fall back to Photon
            }

            // 2. Try Photon (Komoot) as a secondary fallback
            if (!$address) {
                try {
                    $response = Http::withoutVerifying()->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
                    ])->timeout(5)->get("https://photon.komoot.io/reverse", [
                        'lat' => $lat,
                        'lon' => $lng
                    ]);

                    if ($response->successful()) {
                        $data = $response->json();
                        $feature = $data['features'][0] ?? null;
                        if ($feature) {
                            $props = $feature['properties'] ?? [];
                            $city_name = $props['city'] ?? $props['county'] ?? '';
                            $region_name = $props['state'] ?? '';
                            
                            $city_name = str_replace(['محافظة ', 'منطقة ', 'مدينة '], '', $city_name);
                            $region_name = str_replace(['منطقة ', 'محافظة '], '', $region_name);
                            
                            $addressParts = array_filter([
                                $props['name'] ?? null,
                                $props['city'] ?? $props['county'] ?? null,
                                $props['state'] ?? null,
                                $props['country'] ?? null
                            ]);
                            $address = implode('، ', $addressParts);
                        }
                    }
                } catch (\Exception $e) {
                    // Ignore
                }
            }

            // 3. Ultimate Fallback (Never return null)
            if (!$address) {
                $address = "موقع حدده المستخدم ($lat, $lng)";
            }
        } else {
            // Address was already provided, let's try to extract city and region from it
            $parts = array_map('trim', explode('،', $address));
            if (count($parts) >= 1) {
                $city_name = str_replace(['محافظة ', 'منطقة ', 'مدينة '], '', $parts[0]);
            }
            if (count($parts) >= 2) {
                $region_name = str_replace(['منطقة ', 'محافظة '], '', $parts[1]);
            }
        }

        // Match with local City and Region databases to get IDs
        $city_id = null;
        $region_id = null;

        if ($city_name) {
            $matchedCity = City::where('name', 'like', '%' . $city_name . '%')->first();
            if ($matchedCity) {
                $city_id = $matchedCity->id;
                $region_id = $matchedCity->region_id;
                
                // Keep the exact DB name if possible
                $city_name = $matchedCity->name;
                if ($matchedCity->region) {
                    $region_name = $matchedCity->region->name;
                }
            }
        }

        if (!$region_id && $region_name) {
            $matchedRegion = Region::where('name', 'like', '%' . $region_name . '%')->first();
            if ($matchedRegion) {
                $region_id = $matchedRegion->id;
                $region_name = $matchedRegion->name;
            }
        }

        // Region-to-district fallback: if district (city_id) is null but city (region_id) is matched, assign a default district
        if (!$city_id && $region_id) {
            $defaultCity = City::where('region_id', $region_id)->first();
            if ($defaultCity) {
                $city_id = $defaultCity->id;
            }
        }

        // Update authenticated user's location and city
        if (auth('sanctum')->check()) {
            $user = auth('sanctum')->user();
            $updateData = [
                'latitude' => $lat,
                'longitude' => $lng,
                'address' => $address
            ];

            if ($city_id) {
                $updateData['city_id'] = $city_id;
            }

            $user->update($updateData);
        }

        return $this->apiResponse(true, 'تم تحديث الموقع بنجاح', [
            'address' => $address
        ]);
    }
}
