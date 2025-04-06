<?php

namespace App\Http\Controllers\Api\Home;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Image;
use App\Models\Product;
use App\Models\Review;
use App\Services\Search;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $all_categories = Category::all(['id', 'title', 'image'])->map(function ($q) {
            return [
                'id' => $q->id,
                'title' => $q->title,
                'image' => ($q->image != " ") ? config('category_base_url') . $q->image : null
            ];
        });


        $offers = Product::whereHas('sell', function ($query) {
            $query->where('discount', '>', 0);
        })->orWhereHas('rent', function ($query) {
            $query->where('discount', '>', 0);
        })->orWhereHas('swap', function ($query) {
            $query->where('discount', '>', 0);
        })->with(['sell', 'rent', 'swap', 'city.governorate'])->withCount('reviews')->get()->map(function ($q) {
            return [
                'id' => $q->id,
                'title' => $q->title,
                'desc' => $q->desc,
                'sell' => $q->sell,
                'swap' => $q->swap,
                'rent' => $q->rent,
                'city' => $q->city->city_name_ar . " / " . $q->city->governorate->governorate_name_ar,
                'total_rate' => ($q->reviews->count() > 0) ? Review::ceil($q->reviews->sum('rate') / $q->reviews->count()) : 0,
                'image' => ($q->images->count() > 0) ? config('app.image_base_url') . $q->images()->first()->image : null

            ];
        })->sortByDesc('total_rate');

        $products = Product::whereHas('reviews', function ($query) {
            $query->where('rate', '>', 0);
        })->withCount('reviews')->inRandomOrder()->limit(10)->get()->map(function ($q) {
            return [
                'id' => $q->id,
                'title' => $q->title,
                'desc' => $q->desc,
                'rent' => $q->rent,
                'swap' => $q->swap,
                'sell' => $q->sell,
                'city' => $q->city->city_name_ar . " / " . $q->city->governorate->governorate_name_ar,
                'total_rate' => ($q->reviews->count() > 0) ? Review::ceil($q->reviews->sum('rate') / $q->reviews->count()) : 0,
                'image' => ($q->images->count() > 0) ? config('app.image_base_url') . $q->images()->first()->image : null
            ];
        })->sortByDesc('total_rate');


        return  [
            'categories' => $all_categories,
            'offers' => $offers->values(),
            'products' => $products->values()
        ];
    }
    public function search(Request $request, Search $search)
    {
        $products = $search->getResult($request);
        return $products;
    }

    public function search_auto_complete(Request $request)
    {
        if (mb_strlen($request->q, 'utf-8') <= 2) {
            $result = [];
        } else {
            $result = Product::where('title', 'like', '%' . $request->q . '%')
                ->orWhere('desc', 'like', '%' . $request->q . '%')->with(['reviews', 'sell', 'swap', 'rent', 'images', 'category', 'user', 'city.governorate'])->get()->map(function ($q) {
                    return [
                        'id' => $q->id,
                        'title' => $q->title,
                        'desc' => $q->desc,
                        'rent' => $q->rent,
                        'swap' => $q->swap,
                        'sell' => $q->sell,
                        'city' => $q->city->city_name_ar . " / " . $q->city->governorate->governorate_name_ar,
                        'total_rate' => ($q->reviews->count() > 0) ? Review::ceil($q->reviews->sum('rate') / $q->reviews->count()) : 0,
                        'image' => ($q->images->count() > 0) ? config('app.image_base_url') . $q->images()->first()->image : null

                    ];
                });
        }

        return response()->json([
            'count' => ($result != null) ? $result->count() : 0,
            'data' => $result
        ]);
    }
}
