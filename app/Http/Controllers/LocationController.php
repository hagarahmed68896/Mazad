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

        if (Auth::check()) {
            $user = Auth::user();
            $user->update([
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'address' => $request->address,
            ]);
        } else {
            // Store in session for guests
            session([
                'user_latitude' => $request->latitude,
                'user_longitude' => $request->longitude,
                'user_address' => $request->address,
            ]);
        }

        return response()->json([
            'success' => true, 
            'address' => $request->address,
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
            ]);
        }
        
        session()->forget(['user_latitude', 'user_longitude', 'user_address']);

        return back()->with('success', 'تم مسح الموقع وإعادة التعيين بنجاح');
    }
}
