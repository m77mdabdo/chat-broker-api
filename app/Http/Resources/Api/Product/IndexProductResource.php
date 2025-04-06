<?php

namespace App\Http\Resources\Api\Product;

use App\Models\Image;
use App\Models\Review;
use Illuminate\Http\Resources\Json\JsonResource;

class IndexProductResource extends JsonResource
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
            'id'=>$this->id,
            'title'=>$this->title,
            'desc'=>$this->desc,
            'sell'=>$this->sell,
            'swap'=>$this->swap,
            'rent'=>$this->rent,
            'city'=>$this->city->city_name_ar . " / " .$this->city->governorate->governorate_name_ar,
            'total_rate'=>($this->reviews->count() > 0)?Review::ceil($this->reviews->sum('rate')/$this->reviews->count()):0,
            'image' => ($this->images->count() > 0) ? config('app.image_base_url').$this->images()->first()->image : null,
        ];
    }

}
