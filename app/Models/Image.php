<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Image extends Model
{
    use HasFactory;
    protected $fillable = [
        'image',
        'product_id',
        'object_id'
    ];
    protected $guarded = [
        'id',
        'created_at',
        'updated_at'
    ];

    public function product(){
        return $this->belongsTo(Product::class);
    }

    public static function getImage($path, $first) {
        $fullPath = $path . '/' . $first;
        return Storage::disk('public')->get($fullPath);
    }

}
