<?php

use App\Http\Controllers\Api\Auth\AuthContoller;
use App\Http\Middleware\CheckUserRole;
use App\Http\Middleware\CheckAuthorizationHeader;
use App\Http\Controllers\Api\Category\CategoryController;
use App\Http\Controllers\Api\City\CityController;
use App\Http\Controllers\Api\Favorite\FavoriteController;
use App\Http\Controllers\Api\Home\HomeController;
use App\Http\Controllers\Api\APIRecommedationController;
use App\Http\Controllers\Api\Payment\PaymentController;
use App\Http\Controllers\Api\Product\ProductController;
use App\Http\Controllers\Api\Governorate\GovernorateController;
use App\Http\Controllers\Api\Review\ReviewController;
use App\Http\Controllers\Api\Auth\VerificationController;
use App\Http\Controllers\Recommendation;
use App\Http\Controllers\ReviewUserController;
use App\Models\Image;
use App\Models\User;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v1')->group(function () {
    Route::get('/recommend', [Recommendation::class, 'recommendation'])->middleware('check.auth.header');

    Route::group(['middleware' => ['auth:sanctum']], function () {
        Route::prefix('users')->group(function () {
            Route::get("myproducts", [AuthContoller::class, 'myproducts']);
            Route::get("get_user_profile/{id}", [AuthContoller::class, 'getUserProfile']);
            Route::post("logout", [AuthContoller::class, 'logout']);
            Route::put("update_password", [AuthContoller::class, 'updatePassword']);
            Route::post("update_img", [AuthContoller::class, 'updateProfileImage']);
            Route::put("update_details", [AuthContoller::class, 'updateDetails']);
            Route::get('verfiy-email', [VerificationController::class, 'OTPGentrator']);
            Route::post('verfiy-email/otp', [VerificationController::class, 'verfiy']);
            Route::get('/profile/{id}', function ($id) {
                $user = User::findOrFail($id);

                return response()->json([
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'phone_number' => $user->phone_number,
                        'email' => $user->email,
                        'is_verified' => intval($user->is_verified),
                        'image' => ($user->profile != null) ? config('app.image_base_url') . $user->profile : null,
                        'city' => ($user->city) ? $user->city->city_name_ar . " / " . $user->city->governorate->governorate_name_ar : null,
                    ],
                ]);
            });
            Route::get('/check_user', function () {
                return response()->json([
                    'message' => 'User is authenticated'
                ], 200);
            });
        });

        Route::prefix('products')->group(function () {
            Route::get('/recommend', [ProductController::class, 'getRecommendations']);


            Route::get('/', [ProductController::class, 'index']);
            Route::get('/{id}', [ProductController::class, 'show']);
            Route::get('/user/{id}/completed-orders-products', [ProductController::class, 'getUserCompletedPaidOrderItems']);
            Route::get('info-update/{id}', [ProductController::class, 'showUpdatedProduct']);
            // Route::middleware('is_verified')->group(function () {
            Route::post('/store', [ProductController::class, 'store'])->middleware(CheckUserRole::class);
            Route::put('/update/{id}', [ProductController::class, 'update'])->middleware(CheckUserRole::class);
            Route::delete('/{id}', [ProductController::class, 'destroy'])->middleware(CheckUserRole::class);
            Route::prefix('images')->group(function () {
                Route::get('/{id}', [ProductController::class, 'editProductImages'])->middleware(CheckUserRole::class);
                Route::post('/store', [ProductController::class, 'add_image'])->middleware(CheckUserRole::class);
                Route::delete('/{id}', [ProductController::class, 'destroy_image'])->middleware(CheckUserRole::class);
            });
            // });
        });


        //still test payment
        Route::controller(PaymentController::class)->group(function () {
            Route::post('stripe/checkout', 'stripeCheckout')->name('stripe.checkout');
        });




        Route::prefix('reviews')->group(function () {
            Route::post('/store', [ReviewController::class, 'store'])->middleware(CheckUserRole::class);
            Route::delete('/{id}', [ReviewController::class, 'destroy'])->middleware(CheckUserRole::class);
        });
        Route::prefix('favorites')->group(function () {
            Route::get('/', [FavoriteController::class, 'index']);
            Route::post('/store', [FavoriteController::class, 'store'])->middleware(CheckUserRole::class);
            Route::delete('/{id}', [FavoriteController::class, 'destroy']);
        });




        //searching

    });
    Route::prefix('users')->group(function () {
        Route::post("login", [AuthContoller::class, 'login']);
        Route::post("register", [AuthContoller::class, 'register']);
        Route::post("register-admin", [AuthContoller::class, 'registerAdmin']);
        Route::post("forgot-password", [AuthContoller::class, 'forgetPassword']);
    });
    Route::get('/search', [HomeController::class, 'search']);
    Route::get('/search/auto_complete', [HomeController::class, 'search_auto_complete']);




    Route::get('/offers', [ProductController::class, 'offers']);
    Route::get('/home', [HomeController::class, 'index']);

    Route::get('/governorates', [GovernorateController::class, 'index']);
    Route::post('/governorates/add', [GovernorateController::class, 'store']);

    Route::prefix('cities')->group(function () {
        Route::get('/{id}', [CityController::class, 'getCityByGov']);
        Route::post('/add', [CityController::class, 'store']);
    });

    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index']);
        Route::get('/{id}', [ProductController::class, 'show']);
    });

    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::get('/{id}', [CategoryController::class, 'show'])->middleware(CheckUserRole::class);
        Route::post('/add', [CategoryController::class, 'add'])->middleware(CheckUserRole::class);
    });


    Route::prefix('dashboard')->group(function () {


        // Route::get('/seller/{sellerId}/reviews', [ReviewUserController::class, 'getLastFiveReviewsForSeller']);
    });
    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::get('/{id}', [CategoryController::class, 'show']);
        Route::post('/add', [CategoryController::class, 'add']);
        Route::delete('/delete/{id}', [CategoryController::class, 'delete']);
    });


    Route::get('stripe/checkout/success', [PaymentController::class, 'stripeCheckoutSuccess'])->name('stripe.checkout.success');
});
