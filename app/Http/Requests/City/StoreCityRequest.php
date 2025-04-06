<?php

namespace App\Http\Requests\City;

use Illuminate\Foundation\Http\FormRequest;

class StoreCityRequest extends FormRequest
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
            'governorate_id'=>'required',
            'city_name_ar'=>'required',
            'city_name_en'=>'required'
        ];
    }
    public function messages()
    {
        return [
            'governorate_id.required'=>'governorate_id is required',
            'city_name_ar.required'=>'city_name_ar is required',
            'city_name_en.required'=>'city_name_en is required'
        ];
    }
}
