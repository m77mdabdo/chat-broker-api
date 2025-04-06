<?php

namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\AddImageRequest;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateImageRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\Api\Product\IndexProductResource;
use App\Http\Resources\Api\Product\OffersResource;
use App\Http\Resources\Api\Product\ShowProductResource;
use App\Http\Resources\Api\Product\ShowUpdateProductResource;
use App\Models\Image;
use App\Models\Order;
use App\Models\Price;
use App\Models\Product;
use App\Models\Favorite;
use App\Models\Review;
use App\Repositories\Product\ProductRepositoryInterface;
use App\Repositories\S3\UploadFileRepositoryInterface;
use App\Models\Sell;
use App\Models\Rent;
use App\Models\Swap;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Http;

class ProductController extends Controller
{
    private $productRepository;
    private $uploadFileRepository;

    public function __construct(ProductRepositoryInterface $productRepository, UploadFileRepositoryInterface $uploadFileRepository)
    {
        $this->productRepository = $productRepository;
        $this->uploadFileRepository = $uploadFileRepository;
    }

    public function index(Request $request)
    {
        $products = IndexProductResource::collection(Product::withCount('reviews')->with(['sell', 'rent', 'swap', 'reviews'])->orderBy('created_at', 'desc')->paginate(10));
        return $products;
    }

    public function getRecommendations(Request $request)
    {
        $userId = auth()->user()->id;

        $products = Product::with('reviews')->get();
        $productsData = $products->map(function ($product) {
            $totalRate = $product->reviews->avg('rate') ?? 0;
            $users = $product->reviews->map(function ($review) {
                return [
                    'user_id' => $review->user_id,
                    'rating' => $review->rate,
                ];
            })->toArray();

            return [
                'product_title' => $product->title,
                'product_id' => $product->id,
                'rating' => $totalRate,
                'users' => $users,
            ];
        })->toArray();
        // dd($productsData);
        try {
            $response = Http::post('https://recommendation-api-23.onrender.com/recommend?user_id=' . $userId, [
                'user_id' => $userId,
                'products' => $productsData
            ]);

            if ($response->failed()) {

                $randomProducts = Product::inRandomOrder()->limit(10)->with(['reviews', 'sell', 'swap', 'rent'])->get()->map(function ($q) {
                    $totalRate = $q->reviews->avg('rate') ?? 0;
                    return [
                        'id' => $q->id,
                        'title' => $q->title,
                        'desc' => $q->desc,
                        'sell' => $q->sell,
                        'swap' => $q->swap,
                        'rent' => $q->rent,
                        'city' => $q->city->city_name_ar . " / " . $q->city->governorate->governorate_name_ar,
                        'total_rate' => $totalRate,
                        'image' => ($q->images->count() > 0) ? config('app.image_base_url') . $q->images()->first()->image : null
                    ];
                });
                return response()->json(['products' => $randomProducts], 200);
            }

            $recommendedProducts = $response->json();
            $productIds = array_column($recommendedProducts, 'product_id');
            $recommendedProductData = Product::whereIn('id', $productIds)->with(['reviews', 'sell', 'swap', 'rent'])->get()->map(function ($q) {
                $totalRate = ($q->reviews->count() > 0) ? Review::ceil($q->reviews->sum('rate') / $q->reviews->count()) : 0;
                return [
                    'id' => $q->id,
                    'title' => $q->title,
                    'desc' => $q->desc,
                    'sell' => $q->sell,
                    'swap' => $q->swap,
                    'rent' => $q->rent,
                    'city' => $q->city->city_name_ar . " / " . $q->city->governorate->governorate_name_ar,
                    'total_rate' => $totalRate,
                    'num_Reviews' => $q->reviews->count(),
                    'image' => ($q->images->count() > 0) ? config('app.image_base_url') . $q->images()->first()->image : null
                ];
            });

            /*

            [
            product_title: "this is a title",
            product_id : 5,
            users :[
            {user_id: 5, rating:1},
            {user_id: 2, rating:4},
                  ]
            ]
            */

            if ($recommendedProductData->isEmpty()) {
                $randomProducts = Product::inRandomOrder()->limit(10)->with(['reviews', 'sell', 'swap', 'rent'])->get()->map(function ($q) {
                    $totalRate = $q->reviews->avg('rate') ?? 0;
                    return [
                        'id' => $q->id,
                        'title' => $q->title,
                        'desc' => $q->desc,
                        'sell' => $q->sell,
                        'swap' => $q->swap,
                        'rent' => $q->rent,
                        'city' => $q->city->city_name_ar . " / " . $q->city->governorate->governorate_name_ar,
                        'total_rate' => $totalRate,
                        'image' => ($q->images->count() > 0) ? config('app.image_base_url') . $q->images()->first()->image : null
                    ];
                });
                return response()->json(['products' => $randomProducts], 200);
            }

            return response()->json($recommendedProductData, 200);
        } catch (\Exception $e) {
            Log::error('Error fetching recommendations: ' . $e->getMessage());
            $randomProducts = Product::inRandomOrder()->limit(10)->with(['reviews', 'sell', 'swap', 'rent'])->get()->map(function ($q) {
                $totalRate = $q->reviews->avg('rate') ?? 0;
                return [
                    'id' => $q->id,
                    'title' => $q->title,
                    'desc' => $q->desc,
                    'sell' => $q->sell,
                    'swap' => $q->swap,
                    'rent' => $q->rent,
                    'city' => $q->city->city_name_ar . " / " . $q->city->governorate->governorate_name_ar,
                    'total_rate' => $totalRate,
                    'image' => ($q->images->count() > 0) ? config('app.image_base_url') . $q->images()->first()->image : null
                ];
            });
            return response()->json(['products' => $randomProducts], 200);
        }
    }

    public function offers()
    {

        $allOffers = Product::whereHas('sell', function ($query) {
            $query->where('discount', '>', 0);
        })->orWhereHas('rent', function ($query) {
            $query->where('discount', '>', 0);
        })->orWhereHas('swap', function ($query) {
            $query->where('discount', '>', 0);
        })->with(['sell', 'rent', 'swap', 'city.governorate'])->withCount('reviews')->get();

        $allOffers->transform(function ($q) {
            return [
                'id' => $q->id,
                'title' => $q->title,
                'desc' => $q->desc,
                'sell' => $q->sell && $q->sell->discount == 0 ? null :  $q->sell,
                'swap' => $q->swap && $q->swap->discount == 0 ? null :  $q->swap,
                'rent' => $q->rent && $q->rent->discount == 0 ? null :  $q->rent,
                'total_rate' => ($q->reviews->count() > 0) ? Review::ceil($q->reviews->sum('rate') / $q->reviews->count()) : null,
                'image' => ($q->images->count() > 0) ? config('app.image_base_url') . $q->images()->first()->image : null

            ];
        });
        $res = [
            // 'most_offers' => $products->values(),
            'offers' => $allOffers
        ];
        return $res;
    }

    public function show($id)
    {
        $product = Product::where('id', $id)
            ->withCount('reviews')
            ->with(['reviews', 'sell', 'swap', 'rent', 'images', 'category', 'user', 'city.governorate'])
            ->firstOrfail();

        // $related_products = Product::where('id','<>',$id)
        // ->Where('category_id',$product->category_id)
        // ->withCount('reviews')
        // ->with('reviews')
        // ->limit(10)
        // ->get();



        return new ShowProductResource([
            'product' => $product,
        ]);
    }

    public function showUpdatedProduct($id)
    {
        $product = Product::where('id', $id)
            ->withCount('reviews')
            ->with('reviews')
            ->firstOrfail();

        $res = [
            'product' => $product,
        ];
        return new ShowUpdateProductResource($res);
    }

    public function store(StoreProductRequest $request)
    {

        // if(count($request->images) >= 3){

        //     return response()->json([
        //         'message'=>"You can't add more than 3 images !"
        //     ],422);
        // }


        //   $request->validate([
        //  'type' => 'required|in:sell,rent,swap',
        //  'product_id' => 'required',
        //  'price_id' => 'required',
        //  ]);
        // Log::info('Request Data: ', $request->all());

        // Validate the input data
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'desc' => 'required|string',
            'category_id' => 'required|integer',
            'available' => 'required|boolean',
            'location' => 'required|string',
            'city_id' => 'required|integer',
            'for_renting' => 'required|boolean',
            'rent_discount' => 'numeric',
            'rent_amount' => 'numeric',
            'duration' => 'nullable|integer',
            'enum_durations' => 'nullable|string',
            'conditions' => 'string',
            'for_swapping' => 'required|boolean',
            'swap_discount' => 'numeric',
            'swap_amount' => 'numeric',
            'swap_with' => 'nullable|string',
            'for_selling' => 'required|boolean',
            'sell_discount' => 'numeric',
            'sell_amount' => 'numeric',
            'images.*' => 'required|mimes:jpg,jpeg,png,bmp|max:5000',
            'images360.*' => 'required|mimes:jpg,jpeg,png,bmp|max:9000',
            'videos.*' => 'required|mimes:mp4|max:40000',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        if (count($request->images) > 3) {
            return response()->json([
                'message' => "You can't add more than 3 images !"
            ], 422);
        }

        if ($request->for_selling) {
            if (is_null($request->sell_amount)) {
                return response([
                    'message' => 'for_selling is true so you must provide sell_amount'
                ], 400);
            }
        }

        if ($request->for_renting) {
            if (
                is_null($request->rent_amount)
                || is_null($request->conditions)
                || is_null($request->enum_durations)
                || is_null($request->duration)
            ) {
                return response([
                    'message' => 'for_renting is true so you must provide rent_amount, duration, enum_durations and conditions',
                    'data' => [
                        'conditions' => $request->conditions,
                        'enum_durations' => $request->enum_durations,
                        'duration' => $request->duration,
                        'rent_amount' => $request->rent_amount,
                    ]
                ], 400);
            }
        }

        if ($request->for_swapping) {
            if (
                is_null($request->swap_amount)
                || is_null($request->swap_with)
            ) {
                return response([
                    'message' => 'for_swapping is true so you must provide swap_amount and swap_with'
                ], 400);
            }
        }

        $product = Product::create([
            'title' => $request->title,
            'desc' => $request->desc,
            'category_id' => $request->category_id,
            'available' => $request->available,
            'user_id' => auth()->user()->id,
            'location' => $request->location,
            'conditions' => 'dummy conditions not used',
            'city_id' => $request->city_id,
        ]);


        $sell_product = null;
        $rent_product = null;
        $swap_product = null;
        if ($request->for_selling) {
            $sell_product = Sell::create([
                'product_id' => $product->id,
                'amount' => $request->sell_amount,
                'discount' => $request->sell_discount > 0 ? $request->sell_discount : 0,
            ]);
        }

        if ($request->for_renting) {
            $rent_product  = Rent::create([
                'product_id' => $product->id,
                'amount' => $request->rent_amount,
                'discount' => $request->rent_discount ?? 0,
                'conditions' =>  $request->conditions,
                'enum_durations' =>  $request->enum_durations,
                'duration' =>  $request->duration,
            ]);
        }

        if ($request->for_swapping) {
            $swap_product = Swap::create([
                'product_id' => $product->id,
                'amount' => $request->swap_amount,
                'discount' => $request->swap_discount > 0 ? $request->swap_discount : 0,
                'swap_with' =>  $request->swap_with,
            ]);
        }






        $addNewProduct = $this->productRepository->addNewProduct($request, $product);

        // $newProduct = $product->fresh();
        return response()->json([
            'message' => 'Product created successfully.',
            'product' => $addNewProduct,
            'sell_product' => $sell_product,
            'rent_product' => $rent_product,
            'swap_product' => $swap_product,
        ]);
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'desc' => 'nullable|string',
            'category_id' => 'required|integer|exists:categories,id',
            'conditions' => 'nullable|string',
            'available' => 'required|boolean',
            'location' => 'nullable|string',
            'city_id' => 'required|integer|exists:cities,id',
            'sell_details.amount' => 'nullable|numeric',
            'sell_details.discount' => 'nullable|numeric',
            'rent_details.amount' => 'nullable|numeric',
            'rent_details.discount' => 'nullable|numeric',
            'rent_details.conditions' => 'nullable|string',
            'rent_details.enum_durations' => 'nullable|string',
            'rent_details.duration' => 'nullable|string',
            'swap_details.amount' => 'nullable|numeric',
            'swap_details.discount' => 'nullable|numeric',
            'swap_details.swap_with' => 'nullable|string',
        ]);

        $product = Product::where('id', $id)
            ->where('user_id', auth()->user()->id)
            ->firstOrFail();

        $product->update([
            'title' => $validatedData['title'],
            'desc' => $validatedData['desc'] ?? $product->desc,
            'category_id' => $validatedData['category_id'],
            'conditions' => $validatedData['conditions'] ?? $product->conditions,
            'available' => $validatedData['available'],
            'location' => $validatedData['location'] ?? $product->location,
            'city_id' => $validatedData['city_id'],
        ]);

        optional($product->sell)->update([
            'amount' => $validatedData['sell_details']['amount'] ?? $product->sell->amount,
            'discount' => $validatedData['sell_details']['discount'] ?? $product->sell->discount,
        ]);

        optional($product->rent)->update([
            'amount' => $validatedData['rent_details']['amount'] ?? $product->rent->amount,
            'discount' => $validatedData['rent_details']['discount'] ?? $product->rent->discount,
            'conditions' => $validatedData['rent_details']['conditions'] ?? $product->rent->conditions,
            'enum_durations' => $validatedData['rent_details']['enum_durations'] ?? $product->rent->enum_durations,
            'duration' => $validatedData['rent_details']['duration'] ?? $product->rent->duration,
        ]);

        optional($product->swap)->update([
            'amount' => $validatedData['swap_details']['amount'] ?? $product->swap->amount,
            'discount' => $validatedData['swap_details']['discount'] ?? $product->swap->discount,
            'swap_with' => $validatedData['swap_details']['swap_with'] ?? $product->swap->swap_with,
        ]);

        return response()->json([
            'message' => 'You have successfully updated the product!'
        ], 200);
    }


    public function destroy(Request $request)
    {
        $product = Product::where([
            'id' => $request->id,
            'user_id' => auth()->user()->id
        ])->firstOrfail();
        $favs = Favorite::where('product_id', $product->id)->get();
        if ($favs) {
            foreach ($favs as $fav) {
                $fav->delete();
            }
        }

        $product->delete();
        return response([
            'message' => 'You delete product Successful !'
        ], 200);
    }

    public function editProductImages($id)
    {
        $product = Product::findOrfail($id);
        if ($product->user->id == auth()->user()->id) {
            $product_images = Image::where('product_id', $id)->get()->map(function ($q) {
                return [
                    'id' => $q->id,
                    ///TODO
                    'image' => ($q->image != null) ? config('app.image_base_url') . $q->image : null
                ];
            });
        } else {
            return response([
                'message' => 'You not Authorized !'
            ], 403);
        }
        return response([
            'images' => $product_images,
        ], 200);
    }

    public function add_image(AddImageRequest $request)
    {
        try {
            $product = Product::findOrFail($request->id);

            if ($product->user->id != auth()->user()->id) {
                return response([
                    'message' => 'You are not authorized!'
                ], 403);
            }

            if (count($request->images) + $product->images()->count() > 3) {
                return response()->json(['message' => "You can't add more than 3 images!"], 422);
            }

            foreach ($request->images as $image) {

                $imageName = $this->uploadFileRepository->upload($image);

                Image::create([
                    'image' => $imageName,
                    'product_id' => $product->id,
                ]);
            }

            return response([
                'message' => 'You have successfully added a new image!'
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response([
                'message' => 'Product not found!'
            ], 404);
        } catch (\Exception $e) {
            return response([
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy_image(Request $request)
    {
        $image = Image::findOrfail($request->id);
        if ($image->product->user->id == auth()->user()->id) {
            $image->delete();
        } else {
            return response([
                'message' => 'You not Authorized !'
            ], 403);
        }

        return response([
            'message' => 'You have delete image !'
        ], 200);
    }

    public function get360Image($id)
    {
        try {
            $product = Product::findOrFail($id);


            $imageRecord = $product->images360()->first();

            if (!$imageRecord || !$imageRecord->image360) {
                return response()->json(['message' => '360-degree image not found for this product'], 404);
            }

            $imageUrl = config('app.image360_base_url') . $imageRecord->image360;
            return view('pannellum.show', compact('imageUrl'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Product not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function getUserCompletedPaidOrderItems($userId)
    {
        // all completed orders for the user to track what paid
        $completedOrders = Order::where('user_id', $userId)
            ->where('status', 'completed')
            ->get();

        $orderItems = [];

        foreach ($completedOrders as $order) {
            foreach ($order->items as $item) {
                $product = Product::find($item->product_id);
                $item->product_id = $product;
                $orderItems[] = $item;
            }
        }
        $product = Product::find($item->product_id);

        return response()->json($orderItems);
    }
}
