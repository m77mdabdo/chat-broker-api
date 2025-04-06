<?php

namespace App\Http\Resources\Api\Product;

use App\Models\Category;
use App\Models\Image;
use App\Models\Review;
use App\Models\Favorite;
use App\Models\Governorate;
use App\Models\OrderItem;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ShowProductResource extends JsonResource
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

        $user = $this['product']->user;
        $user ->image = config('app.image_base_url'). $user ->profile;

        $can_Review=0;
        $productId=$this['product']->id;
        $userId = $this['product']->user->id;
        $countProductbuy = OrderItem::whereHas('order', function ($query) use ($userId) {
                                                $query->where('user_id', $userId)
                                                      ->where('status', 'completed');
                                            })->count();
        $countProductReview = Review::where('product_id', $productId)
                                    ->where('user_id',$userId)
                                    ->count();


        if($countProductbuy>0 && $countProductReview >0){
            if($countProductbuy == $countProductReview){

                $can_Review=0;

            }elseif ($countProductbuy > $countProductReview) {

                $can_Review=1;

            }else{
                $can_Review=0;
            }
        }else{
            $can_Review=1;
        }

        return [
            'can_review' =>$can_Review,
            'product_id' => $this['product']->id,
            'sell' => $this['product']->sell,
            'swap' => $this['product']->swap,
            'rent' => $this['product']->rent,
            'category' => [
                'category' =>    $this['product']->category->catgory,
                'id' =>    $this['product']->category->id,
                'image' =>     config('app.category_base_url').$this['product']->category->image
            ],
            'title' => $this['product']->title,
            'desc' => $this['product']->desc,
            'available' => $this['product']->available,
            'is_favourite' => 0, // $this['product']->isFavorite($this['product']->id),
            'location' => $this['product']->location,
            'city' => $this['product']->city,
            'governorate' => $this['product']->city->governorate,

            'images' => $this['product']->images->map(function ($q) {
                return [
                    'id' => $q->id,
                    'image' => ($q->image != null) ? config('app.image_base_url') . "{$q->image}" : null,
                ];
            }),

            'videos' => $this['product']->videos->map(function ($q) {
                return [
                    'id' => $q->id,
                    'video' => ($q->video != null) ? config('app.video_base_url') . "{$q->video}" : null,
                ];
            }),

            'images360' => $this['product']->images360->map(function ($q) {
                return [
                    'id' => $q->id,
                    'images360' => ($q->image360 != null) ? config('app.image360_base_url') . "{$q->image360}" : null,
                ];
            }),


            'user' =>  $user,
            'total_rate' => ($this['product']->reviews->count() > 0) ? Review::ceil($this['product']->reviews->sum('rate') / $this['product']->reviews->count()) : 0,
            'reviews' => $this['product']->reviews->map(function ($q) {
                return [
                    'id' => $q->id,
                    'comment' => $q->comment,
                    'rate' => $q->rate,
                    'created_at' => $q->created_at,
                    'user' => [
                        'id' => $q->user->id,
                        'name' => $q->user->name,
                        'image' => ($q->user->profile != null) ? config('app.image_base_url') . $q->user->profile : null
                    ]
                ];
            }),
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
            //         'total_rate'=>($q->reviews->count() > 0)?Review::ceil($q->reviews->sum('rate')/$q->reviews->count()):0,
            //         'image'=>($q->images->count()>0)?Image::getImage("/lepgo/images/products/",$q->images()->first()->image):null
            //     ];
            // }),
        ];
    }
}
