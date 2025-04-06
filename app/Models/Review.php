<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = [
        'product_id',
        'rate',
        'user_id',
        'comment',
    ];
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }
    public function product(){
        return $this->belongsTo(Product::class,'product_id');
    }
    public static function ceil($num){
        if($num > intval($num)+0.5){
            return intval($num)+1;
        }else if($num == intval($num)){
            return intval($num);
        }else{
            return intval($num)+0.5;
        }

    }
  
}
