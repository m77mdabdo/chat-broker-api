<?php

namespace App\Http\Resources\Api\Product;

use App\Models\Image;
use App\Models\Review;
use Illuminate\Http\Resources\Json\JsonResource;

class OffersResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'most_offers'=>$this['most_offers']->map(function($q){
                return [
                    'id'=>$q->id,
                    'title'=>$q->title,
                    'desc'=>$q->desc,
                    'amount'=>$q->price->amount,
                    'duration'=>$q->price->duration,
                    'enum_durations'=>$q->price->enum_durations,
                    'discount'=>$q->price->discount,
                    'total_rate'=>($q->reviews->count() > 0)?Review::ceil($q->reviews->sum('rate')/$q->reviews->count()):null,
                    'city'=>$q->city->city_name_ar . " / " .$q->city->governorate->governorate_name_ar,
                    'image'=>($q->images->count()>0)?'https://chat-broker-api.azurewebsites.net/images/'.$q->images()->first()->image:null

                    ];
            }),
            'all_offers'=>$this['offers']->transform(function($q){
                return [
                    'id'=>$q->id,
                    'title'=>$q->title,
                    'desc'=>$q->desc,
                    'amount'=>$q->price->amount,
                    'duration'=>$q->price->duration,
                    'enum_durations'=>$q->price->enum_durations,
                    'discount'=>$q->price->discount,
                     'city'=>$q->city->city_name_ar . " / " .$q->city->governorate->governorate_name_ar,
                    'total_rate'=>($q->reviews->count() > 0)?Review::ceil($q->reviews->sum('rate')/$q->reviews->count()):null,
                    'image'=>($q->images->count()>0)? config('app.image_base_url').$q->images()->first()->image:null


                    ];
            }),


        ];
    }
}
