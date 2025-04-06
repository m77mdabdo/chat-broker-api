<?php

namespace App\Http\Controllers\Api\Review;

use App\Http\Controllers\Controller;
use App\Http\Requests\Review\StoreReviewRequest;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
  /*  public function __construct(){
      $this->middleware('CheckUserRole')->only(['store','destroy']);
    } */
    public function store(StoreReviewRequest $request){
        if(Review::where(['product_id'=> $request->product_id , 'user_id'=> auth()->user()->id ])->exists()){
            return response([
                'message'=>'You have reviewed already !'
            ],404);
        }
        $newReview = Review::create([
            'product_id'=>$request->product_id,
            'rate'=>$request->rate,
            'comment'=>$request->comment,
            'user_id'=>auth()->user()->id,
        ]);

        $path =config('app.image_base_url');
        $image=$newReview->user->profile;
        $imageWithPath=$path .$image;

        $userData = [
            'id' => $newReview->user->id,
            'name' => $newReview->user->name,
            'image' => $imageWithPath,
        ];

        return response([
            'review' => [
            'id' => $newReview->id,
            'product_id' => $newReview->product_id,
            'rate' => $newReview->rate,
            'comment' => $newReview->comment,
            'user' => $userData,
            ],
            'message'=>'You add a Review Successfully !'
        ],200);

    }
    public function destroy($id){
        $review = Review::where(['id'=> $id, 'user_id'=> Auth()->user()->id])
        ->firstOrFail();
        $review->delete();
        return response([
            'message'=>'You delete your review Successful !',
        ],200);
    }
}
