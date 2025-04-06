<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
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
        return [
            // 'title' => 'required|string|max:255',
            // 'desc' => 'required|string',
            // 'category_id' => 'required|integer',
            // 'conditions' => 'required|string',
            // 'available' => 'required|boolean',
            // 'location' => 'required|string',
            // 'city_id' => 'required|integer',
            // 'sell_amount' => 'nullable|numeric',
            // 'sell_discount' => 'nullable|numeric',
            // 'rent_amount' => 'nullable|numeric',
            // 'rent_discount' => 'nullable|numeric',
            // 'enum_durations' => 'nullable|string',
            // 'duration' => 'nullable|integer',
            // 'swap_amount' => 'nullable|numeric',
            // 'swap_discount' => 'nullable|numeric',
            // 'swap_with' => 'nullable|string',

            'images.*' => 'required|mimes:jpg,jpeg,png,bmp|max:20000'
        ];
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


            'amount.required'=>'Amount is required',
            'duration.required'=>'Duration is required',
            'discount.required'=>'Discount must be numeric',


        ];
    }
}
