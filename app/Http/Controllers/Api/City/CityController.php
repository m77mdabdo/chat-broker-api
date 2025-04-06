<?php

namespace App\Http\Controllers\Api\City;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\City\IndexAllGovResource;
use App\Http\Resources\Api\City\IndexCityResource;
use App\Models\City;
use App\Models\Governorate;
use Illuminate\Http\Request;
use App\Http\Requests\City\StoreCityRequest;

class CityController extends Controller
{


    public function getAllGov(){
        $allGov = Governorate::orderBy('governorate_name_ar','asc')->get()->map(function($q){
            return [
                'id'=>$q->id,
                'name'=>$q->governorate_name_ar
            ];
        });

        return IndexAllGovResource::collection($allGov);
    }
    public function getCityByGov($id){
        $allCityByid = City::where('governorate_id',$id)->orderBy('city_name_ar','asc')->get()->map(function($q){
                return [
                    'id'=>$q->id,
                    'name_ar'=>$q->city_name_ar,
                    'name_en'=>$q->city_name_en,
                ];
        });
        return IndexCityResource::collection($allCityByid);

    }

    public function store(StoreCityRequest $request)
    {

        $newCity = City::create([

            'city_name_ar' => $request->city_name_ar,
            'city_name_en' => $request->city_name_en,
            'governorate_id' => $request->governorate_id
        ]);
        return response([
            'message' => 'added city Successfully !'
        ], 200);
    }
}
