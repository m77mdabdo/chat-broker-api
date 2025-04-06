<?php

// namespace App\Http\Controllers\Dashboard;

// use App\Http\Controllers\Controller;
// use App\Models\Dashboard\ReviewUser;
// use Illuminate\Http\Request;

// class ReviewUserController extends Controller
// {
//     public function getLastFiveReviewsForSeller($sellerId) {
//         $reviews = ReviewUser::whereHas('product', function($query) use ($sellerId) {
//             $query->where('seller_id', $sellerId);
//         })
//         ->orderBy('created_at', 'desc')
//         ->take(5)
//         ->get();

//         return response()->json($reviews);
//     }
// }
