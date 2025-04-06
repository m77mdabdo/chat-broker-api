<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Price extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = [
        'amount',
        'duration',
        'discount',
        'product_id',
        'enum_durations'

    ];
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

}
