<?php

namespace App\Http\Resources\Api\Favorite;

use App\Models\Image;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Review;

class IndexFavoriteResource extends JsonResource

{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        if ($this->id) {
            return [
                'id' => $this->id,
                'product_id' => $this->products->id,
                'title' => $this->products->title,
                'sell' => $this->products->sell,
                'swap' => $this->products->swap,
                'rent' => $this->products->rent,
                'city' => $this->products->city->city_name_ar . " / " . $this->products->city->governorate->governorate_name_ar,
                'total_rate' => ($this->products->reviews->count() > 0) ? Review::ceil($this->products->reviews->sum('rate') / $this->products->reviews->count()) : 0,
                'image' => ($this->products->images->count() > 0) ? config('app.image_base_url') . $this->products->images()->first()->image : null
            ];
        }else{
            return [];
        }
    }
}
