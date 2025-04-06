<?php

namespace App\Http\Requests\Governorate;

use Illuminate\Foundation\Http\FormRequest;

class StoreGovernorateRequest extends FormRequest
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
            'governorate_name_ar'=>'required',
            'governorate_name_en'=>'required'
        ];
    }
    public function messages()
    {
        return [
            'governorate_name_ar.required'=>'governorate_name_ar is required',
            'governorate_name_en.required'=>'governorate_name_en is required'
        ];
    }
}
