<?php

namespace App\Http\Resources\Api\Product;

use App\Models\Category;
use App\Models\Image;
use App\Models\Review;
use App\Models\Favorite;
use App\Models\Governorate;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ShowUpdateProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {


       // $product = $this['product'];
        // $category = Category::find($product->category_id);
        // $category->image = config('app.category_base_url') . $category->image;

        // $city = $product->city;
        // $governorate = Governorate::find($city->governorate_id);


        return [

            'title' => $this['product']->title,
            'desc' => $this['product']->desc,
            'category_id' => $this['product']->category_id,
            'city_id' => $this['product']->city_id,
            'available' => $this['product']->available,
            'location' => $this['product']->location,
            'images' => $this['product']->images->map(function ($image) {
                return [
                    'id' => $image->id,
                    'image' => config('app.image_base_url') . $image->image,
                ];
            }),
            'sell_id' => $this['product']->sell->id ?? null,
            'sell_amount' => $this['product']->sell->amount ?? null,
            'sell_discount' => $this['product']->sell->discount ?? null,

            'swap_id' => $this['product']->swap->id ?? null,
            'swap_amount' => $this['product']->swap->amount ?? null,
            'swap_discount' => $this['product']->swap->discount ?? null,
            'swap_with' => $this['product']->swap->swap_with ?? null,

            'rent_id' => $this['product']->rent->id ?? null,
            'rent_amount' => $this['product']->rent->amount ?? null,
            'rent_discount' => $this['product']->rent->discount ?? null,
            'conditions' => $this['product']->rent->conditions ?? null,
            'enum_durations' => $this['product']->rent->enum_durations ?? null,
            'duration' => $this['product']->rent->duration ?? null,


            // 'related_products'=>$this['related_products']->map(function($q){
            //     return [
            //         'id'=>$q->id,
            //         'title'=>$q->title,
            //         'desc'=>$q->desc,
            //         'amount'=>$q->price->amount,
            //         'duration'=>$q->price->duration,
            //         'enum_durations'=>$q->price->enum_durations,
            //         'discount'=>$q->price->discount,
            //         'city'=>$q->city->city_name_ar . " / " .$q->city->governorate->governorate_name_ar,
            //         'total_rate'=>($q->reviews->count() > 0)?Review::ceil($q->reviews->sum('rate')/$q->reviews_count):0,
            //         'image'=>($q->images->count()>0)?Image::getImage("/lepgo/images/products/",$q->images()->first()->image):null
            //     ];
            // }),
        ];
    }
}
