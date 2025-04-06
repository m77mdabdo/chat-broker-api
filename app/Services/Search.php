<?php

namespace App\Services;

use App\Models\Image;
use App\Models\Product;
use App\Models\Review;

Class Search
{

    public function getResult($request){
        if($request->q && !$request->city_id && !$request->cat_id && !($request->price_from && $request->price_to)){
            $products= Product::where('title', 'Like', '%' . $request->q . '%')
            ->withCount('reviews')->with('reviews')->with('price')->with('city')->paginate(10);
        }
        elseif(!$request->q && $request->city_id && !$request->cat_id && !($request->price_from && $request->price_to)){
            $products = Product::whereHas('city',function($q) use ($request){
                $q->where('id', $request->city_id);
            })->withCount('reviews')->with('reviews')->with('price')->with('city')->paginate(10);        }

        elseif(!$request->q && !$request->city_id && $request->cat_id && !($request->price_from && $request->price_to)){
            $products = Product::where('category_id',$request->cat_id)->withCount('reviews')->with('reviews')->with('price')->with('city')->paginate(10);
        }

        elseif(!$request->q && !$request->city_id && !$request->cat_id && ($request->price_from && $request->price_to)){
            $products = Product::whereHas('price',function($q) use ($request){
                $q->where('amount', '>=', $request->price_from)->where('amount', '<=', $request->price_to);
            })->withCount('reviews')->with('reviews')->with('price')->with('city')->paginate(10);
        }

        elseif($request->q && $request->city_id && !$request->cat_id && !($request->price_from && $request->price_to)){
                $products= Product::where('title', 'Like', '%' . $request->q . '%')->whereHas('city',function($q) use ($request){
                    $q->where('id', $request->city_id);
                })->withCount('reviews')->with('price')->paginate(10);
        }

        elseif($request->q && !$request->city_id && $request->cat_id && !($request->price_from && $request->price_to)){
            $products= Product::where('title', 'Like', '%' . $request->q . '%')->where('category_id',$request->cat_id)
            ->withCount('reviews')->with('price')->paginate(10);
        }

        elseif(!$request->q && $request->city_id && !$request->cat_id && ($request->price_from && $request->price_to)){
            $products = Product::whereHas('city',function($q) use ($request){
                $q->where('id', $request->city_id);
            })->whereHas('price',function($q) use ($request){
                $q->where('amount', '>=', $request->price_from)
                ->where('amount', '<=', $request->price_to);

            })->withCount('reviews')->with('reviews')->with('price')->with('city')->paginate(10);
        }

        elseif(!$request->q && !$request->city_id && $request->cat_id && ($request->price_from && $request->price_to)){
            $products = Product::whereHas('category',function($q) use ($request){
                $q->where('id', $request->cat_id);
            })->whereHas('price',function($q) use ($request){
                $q->where('amount', '>=', $request->price_from)
                ->where('amount', '<=', $request->price_to);
            })->withCount('reviews')->with('reviews')->with('price')->with('city')->paginate(10);
        }

        elseif($request->q && !$request->city_id && !$request->cat_id && ($request->price_from && $request->price_to)){
            $products = Product::where('title', 'Like', '%' . $request->q . '%')
            ->whereHas('price',function($q) use ($request){
                $q->where('amount', '>=', $request->price_from)
                ->where('amount', '<=', $request->price_to);
            })->withCount('reviews')->with('reviews')->with('price')->with('city')->paginate(10);
        }elseif($request->q && $request->city_id && !$request->cat_id && ($request->price_from && $request->price_to)){
            $products = Product::where('title', 'Like', '%' . $request->q . '%')
            ->whereHas('price',function($q) use ($request){
                $q->where('amount', '>=', $request->price_from)
                ->where('amount', '<=', $request->price_to);

            })->withCount('reviews')->with('reviews')->with('price')->with('city')->paginate(10);
        }
        else{
            $products = Product::withCount('reviews')->with('reviews')->with('price')->with('city')->paginate(10);
        }
        

        $products->transform(function($q) {
            return [
                'id'=>$q->id,
                'title'=>$q->title,
                'desc'=>$q->desc,
                'amount'=>$q->price->amount,
                'duration'=>$q->price->duration,
                'discount'=>$q->price->discount,
                'city'=>$q->city->city_name_ar . " / " .$q->city->governorate->governorate_name_ar,
                'total_rate'=>($q->reviews->count() > 0 )?Review::ceil($q->reviews->sum('rate')/$q->reviews->count()):0,
                'image'=>($q->images->count()>0)?Image::getImage("/lepgo/images/products/",$q->images()->first()->image):0
            ];
        })->sortByDesc('city');
        $products->appends($request->all());
        return [
            'result'=>$products
        ];


    }


}
