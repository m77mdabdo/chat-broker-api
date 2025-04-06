<?php

namespace App\Models;

use App\Models\Dashboard\ReviewUser;
use App\Models\Rent;
use App\Models\Sell;
use App\Models\Swap;
use app\Models\Favorite;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'desc',
        'category_id',
        'available',
        'location',
        'city_id',

    ];
    protected $guarded = [
        'id',
        'created_at',
        'updated_at'
    ];
    protected $hidden = ['created_at', 'updated_at'];


    public function user()
    {
        return $this->belongsTo(User::class,);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function images()
    {
        return $this->hasMany(Image::class);
    }

    public function videos()
    {
        return $this->hasMany(Video::class);
    }
    public function images360()
    {
        return $this->hasMany(Image360::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function sell()
    {
        return $this->hasOne(Sell::class);
    }

    public function rent()
    {
        return $this->hasOne(Rent::class);
    }

    public function swap()
    {
        return $this->hasOne(Swap::class);
    }

    public function isFavorite($id)
    {
        // $product = auth()->user()->favorites()->where('product_id', $id)->exists();
        // if($product){
        //     return intval(auth()->user()->favorites()->where('product_id', $id)->first()->id);
        // }else{
        //     return 0;
        // }
    }

    // public function reviewUser()
    // {
    //     return $this->hasMany(ReviewUser::class);
    // }
}
