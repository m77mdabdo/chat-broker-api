<?php

namespace App\Http\Controllers\Api\Category;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Category\IndexCategoryResource;
use App\Http\Resources\Api\Category\ShowCategoryResource;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class CategoryController extends Controller

{


    public function index(Request $request)
    {
        $categories = IndexCategoryResource::collection(Category::orderBy('created_at', 'desc')->get());
        return $categories;
    }
    public function show(Request $request)
    {
        $res = Product::where('category_id', $request->id)->with(['reviews', 'rent', 'swap', 'sell'])->withCount('reviews')->orderBy('created_at', 'desc')->paginate(10);
        return ShowCategoryResource::collection($res);
    }

    public function add(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('image')) {
            // Get file
            $image = $request->file('image');
            // unique name
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            // Move
            $image->move(public_path('images/categories'), $imageName);
        } else {
            return response([
                'message' => 'Image upload failed'
            ], 400);
        }

        $cat = Category::create([
            'title' => $request->title,
            'image' => $imageName,
        ]);

        if ($cat) {
            return response([
                'message' => 'Category added successfully'
            ], 200);
        } else {
            return response([
                'message' => 'Category creation failed'
            ], 500);
        }
    }


    public function delete($id)
    {
        $cat = Category::find($id);

        if ($cat) {
            $cat->delete();

            return response([
                'message' => 'Category deleted successfully'
            ], 200);
        } else {
            return response([
                'message' => 'Category not found'
            ], 404);
        }
    }
}
