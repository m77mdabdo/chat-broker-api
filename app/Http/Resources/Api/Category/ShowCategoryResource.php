<?php

namespace App\Http\Resources\Api\Category;

use App\Models\Category;
use App\Models\Image;
use App\Models\Review;
use Illuminate\Http\Resources\Json\JsonResource;

class ShowCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $category = Category::find($request->id);
        $category->image = config('app.category_base_url'). $category->image;

        return [
            'id'=>$this->id,
            'category_id' =>$category,
            'title'=>$this->title,
            'desc'=>$this->desc,
            'rent'=>$this->rent,
            'swap'=>$this->swap,
            'sell'=>$this->sell,

            'city'=>$this->city->city_name_ar . " / " .$this->city->governorate->governorate_name_ar,
            'total_rate'=>($this->reviews->count() > 0)?Review::ceil($this->reviews->sum('rate')/$this->reviews->count()):0,
            'image'=>($this->images->count()>0)? config('app.image_base_url').$this->images()->first()->image:null,
        ];
    }
}
