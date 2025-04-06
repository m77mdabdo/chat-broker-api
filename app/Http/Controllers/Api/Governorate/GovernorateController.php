<?php

namespace App\Http\Controllers\Api\Governorate;

use App\Http\Controllers\Controller;
use App\Http\Requests\Governorate\StoreGovernorateRequest;
use App\Models\City;
use App\Models\Governorate;
use Illuminate\Http\Request;

class GovernorateController extends Controller
{
    public function index (){
        $governorates = Governorate::all()->map(function($governorate) {
            $cities = City::where('governorate_id', $governorate->id)
                            ->orderBy('city_name_ar', 'asc')
                            ->get()
                            ->map(function($city) {
                                return [
                                    'id' => $city->id,
                                    'name_ar' => $city->city_name_ar,
                                    'name_en' => $city->city_name_en,
                                ];
                            });
            return [
                'id' => $governorate->id,
                'name_ar' => $governorate->governorate_name_ar,
                'name_en' => $governorate->governorate_name_en,
                'cities' => $cities,
            ];
        });

            $durationOptions =['hour','day','week','month','year'];
        return response()->json(['data'=>$governorates,'durationOptions'=> $durationOptions], 200);
    }

    public function store( StoreGovernorateRequest $request)
    {

        $newGovernorate = Governorate::create([
            'governorate_name_ar' => $request->governorate_name_ar,
            'governorate_name_en' => $request->governorate_name_en,
        ]);

        return response([
            'message' => 'added governorate Successfully !'
        ], 200);
    }
}
