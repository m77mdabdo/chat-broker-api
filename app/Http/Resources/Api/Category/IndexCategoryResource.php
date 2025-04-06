<?php

namespace App\Http\Resources\Api\Category;
use App\Models\Image;

use Illuminate\Http\Resources\Json\JsonResource;

class IndexCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id'=>$this->id,
            'title'=>$this->title,
            'image'=>($this->image != " ")? config('app.category_base_url').$this->image:null,
        ];
    }
}
