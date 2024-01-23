<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
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
    public function scopePopular(Builder $query , $from =null, $to= null): Builder | QueryBuilder
    {
        //where there is fn its error function
        // this key is used to refer to the private method of the class
        return  $query->withCount([
                        "reviews"=> fn (Builder $q) => $this->dateRangeFilter($q,$from,$to) 
                        ])
                        ->orderBy("reviews_count", "desc");
    }

    public function scopeHighestRated(Builder $query,$from =null, $to= null ): Builder | QueryBuilder
    {
        return $query->withAvg([
                        "reviews"=> fn (Builder $q) => $this->dateRangeFilter($q,$from,$to) 
                        ],"rating")
                    ->orderBy("reviews_avg_rating","desc");
    }
    public function scopeMinReviews(Builder $query, int $minReviews):Builder | QueryBuilder{
        return $query->having("reviews_count", "=>", $minReviews);
    }

    private function dateRangeFilter(Builder $query, $from = null, $to = null ){
        if($from && !$to){
            $query->where("created_at",">=", $from);
        }elseif(!$from && $to){
            $query->where("created_at","<=",$to);
        }elseif($from && $to){
            $query->whereBetween("created_at", [$from,$to]);
        }
    }

}
