<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    // method added
    public function reviews(){
        return $this->hasMany(Review::class);
    }

    //this are the local query scope
    //the builder should be from use ...\eloquent\builder
    public function scopeTitle(Builder $query, string $title): Builder
    {
        return $query->where('title', 'LIKE', '%'. $title.'%');
    }


    //Aggreations on Ralations
    public function scopePopular(Builder $query ):Builder{
        return  $query->withCount("reviews")
                        ->orderBy("reviews_count", "desc");
    }

    public function scopeHighestRated(Builder $query ):Builder
    {
        return $query->withAvg("reviews","rating")
                    ->orderBy("reviews_avg_rating","desc");
    }

}
