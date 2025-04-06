<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;
    protected $fillable = [
        'city_name_ar',
        'city_name_en',
        'governorate_id'
    ];

    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    public function governorate(){
        return $this->belongsTo(Governorate::class);
    }
}
