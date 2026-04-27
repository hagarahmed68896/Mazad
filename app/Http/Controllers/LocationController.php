<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LocationController extends Controller
{
    public function update(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'latitude' => 'required',
            'longitude' => 'required',
            'address' => 'required',
        ]);

        $user = Auth::user();
        $user->update([
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'address' => $request->address,
        ]);

        return response()->json([
            'success' => true, 
            'address' => $request->address,
            'message' => 'تم تحديث الموقع بنجاح'
        ]);
    }
}
