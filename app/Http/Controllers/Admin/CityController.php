<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Region;
use Illuminate\Http\Request;

class CityController extends Controller
{
    public function index(Request $request)
    {
        $query = Region::withCount(['cities', 'users', 'ads'])
            ->with(['cities' => function($q) {
                $q->withCount('users');
            }]);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $regions = $query->latest()->paginate(12);

        return view('admin.cities.index', compact('regions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:regions,name',
            'areas' => 'nullable|array',
            'areas.*' => 'required|string|max:255',
        ]);

        $region = Region::create(['name' => $request->name]);

        if ($request->has('areas')) {
            foreach ($request->areas as $areaName) {
                $region->cities()->create(['name' => $areaName]);
            }
        }

        return back()->with('success', 'تمت إضافة المدينة بنجاح');
    }

    public function update(Request $request, Region $region)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:regions,name,' . $region->id,
        ]);

        $region->update(['name' => $request->name]);

        return back()->with('success', 'تم تحديث بيانات المدينة بنجاح');
    }

    public function destroy(Region $region)
    {
        $region->delete();

        return back()->with('success', 'تم حذف المدينة بنجاح');
    }

    /** 
     * Area (City) Management Logic 
     */

    public function addArea(Request $request, Region $region)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $area = $region->cities()->create(['name' => $request->name]);

        if ($request->ajax()) {
            return response()->json([
                'status' => true,
                'data' => array_merge($area->toArray(), ['users_count' => 0])
            ]);
        }

        return back()->with('success', 'تمت إضافة المنطقة بنجاح');
    }

    public function destroyArea(Request $request, City $city)
    {
        $city->delete();

        if ($request->ajax()) {
            return response()->json(['status' => true]);
        }

        return back()->with('success', 'تم حذف المنطقة بنجاح');
    }
}
