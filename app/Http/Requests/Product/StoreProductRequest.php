<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [];
        // return [
        //     'title' => 'required|string|max:255',
        //     'desc' => 'required|string',
        //     'category_id' => 'required|integer',
        //     'available' => 'required|boolean',
        //     'location' => 'required|string',
        //     'city_id' => 'required|integer',
        //     'for_renting' => 'required|boolean',
        //     'rent_discount' => 'numeric',
        //     'rent_amount' => 'numeric',
        //     'duration' => 'nullable|integer',
        //     'enum_durations' => 'nullable|string',
        //     'conditions' => 'string',
        //     'for_swapping' => 'required|boolean',
        //     'swap_discount' => 'numeric',
        //     'swap_amount' => 'numeric',
        //     'swap_with' => 'nullable|string',
        //     'for_selling' => 'required|boolean',
        //     'sell_discount' => 'numeric',
        //     'sell_amount' => 'numeric',

        //     'images' => 'required',
        //     'images.*' => 'required|mimes:jpg,jpeg,png,bmp,mp4|max:2000'
        // ];
    }
    public function messages()
    {
        return [
            'title.required'=>'Title is required',
            'desc.required'=>'Description is required',
            'conditions.required'=>'Conditions is required',
            'category_id.required'=>'Category is required',
            'available.required'=>'Available is required',
            'location.required'=>'Location is required',

            'category_id.integer'=>'Category is integer',
            'available.boolean'=>'Available is boolean',
            'images.required' => 'Please at least upload an image',
            'images.*.required' => 'Please at least upload an image',
            'images.*.mimes' => 'Only jpeg, png, jpg and bmp images are allowed',
            'images.*.max' => 'Sorry! Maximum allowed size for an image is 2MB',



        ];
    }
}
