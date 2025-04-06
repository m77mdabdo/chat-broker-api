<?php

namespace App\Http\Controllers\Api\Favorite;

use App\Http\Controllers\Controller;
use App\Http\Requests\Favorite\StoreFavoriteRequest;
use App\Http\Resources\Api\Favorite\IndexFavoriteResource;
use App\Models\Favorite;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\Review;
use PDO;

class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        $products =    Favorite::where('user_id', auth()->user()->id)->with(['products.city', 'products.city.governorate', 'products.reviews', 'products.sell', 'products.swap', 'products.rent', 'products.images'])->orderBy('created_at', 'desc')->paginate(10);
        if (count($products) > 0) {

            $favoritesData = $products->map(function ($product) {
                if ($product) {

                    return [
                        'id' => $product->id,
                        'product_id' => $product->products->id,
                        'title' => $product->products->title,
                        'sell' => $product->products->sell,
                        'swap' => $product->products->swap,
                        'rent' => $product->products->rent,
                        'city' => $product->products->city->city_name_ar . " / " . $product->products->city->governorate->governorate_name_ar,
                        'total_rate' => ($product->products->reviews->count() > 0) ? Review::ceil($product->products->reviews->sum('rate') / $product->products->reviews->count()) : 0,
                        'image' => ($product->products->images->count() > 0) ? config('app.image_base_url') . $product->products->images()->first()->image : null

                    ];
                }
            })->toArray();
            return $favoritesData;
        }
        return [];
    }

    public function store(StoreFavoriteRequest $request)
    {
        $existingFavorite = Favorite::where('user_id', auth()->user()->id)
            ->where('product_id', $request->product_id)
            ->first();
        if ($existingFavorite) {

            return response()->json(['message' => 'Favorite already exists'], 200);
        }
        $product = Product::where('id', $request->product_id)
        ->whereNull('deleted_at')
        ->first();
        if ($product) {

            $favorite = Favorite::create([
                'user_id' => auth()->user()->id,
                'product_id' => $request->product_id,
            ]);
            
            
            $favoriteResource = new IndexFavoriteResource($favorite);
            
            return response([
                'message' => 'You add a product to favorite Successfully !',
                'data' => $favoriteResource,
            ], 200);
        }else{
            return response([
                'message' => 'no product to add to fav',
            ], 200);
            
        }
    }
    public function destroy(Request $request)
    {
        $productId = $request->id;

        $fav = Favorite::where(['product_id' => $productId, 'user_id' => auth()->user()->id])->get();

        if (count($fav)) {

            Favorite::destroy($fav[0]->id);
            return response([
                'message' => 'Favorite deleted successfully!'
            ], 200);
        }

        return response([
            'message' => 'no fav product to delete'
        ], 200);
    }
}
