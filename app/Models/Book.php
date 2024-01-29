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

    public function scopeWithReviewsCount(Builder $query , $from =null, $to= null): Builder | QueryBuilder
    {
        return  $query->withCount([
            "reviews"=> fn (Builder $q) => $this->dateRangeFilter($q,$from,$to) 
            ]);
    }
    public function scopeWithAvgRating(Builder $query , $from =null, $to= null): Builder | QueryBuilder
    {
        return $query->withAvg([
            "reviews"=> fn (Builder $q) => $this->dateRangeFilter($q,$from,$to) 
            ],"rating");
    }

    //Aggreations on Ralations
    public function scopePopular(Builder $query , $from =null, $to= null): Builder | QueryBuilder
    {
        //where there is fn its error function
        // this key is used to refer to the private method of the class
        return  $query->withReviewsCount()
                        ->orderBy("reviews_count", "desc");
    }

    public function scopeHighestRated(Builder $query,$from =null, $to= null ): Builder | QueryBuilder
    {
        return $query->withAvgRating()
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

    public function scopePopularLastMonth(Builder $query) : Builder | QueryBuilder{
        return $query->popular(now()->subMonth(), now())
                        ->highestRated(now()->subMonth(), now())
                        ->minReviews(2);
    }

    public function scopePopularLast6Months(Builder $query) : Builder | QueryBuilder{
        return $query->popular(now()->subMonths(6), now())
                        ->highestRated(now()->subMonths(6), now())
                        ->minReviews(5);
    }

    public function scopeHighestRatedLastMonth(Builder $query) : Builder | QueryBuilder{
        return $query->highestRated(now()->subMonth(), now())
                        ->popular(now()->subMonth(), now())
                        ->minReviews(2);
    }

    public function scopeHighestRatedLast6Months(Builder $query) : Builder | QueryBuilder{
        return $query->highestRated(now()->subMonths(6), now())
                        ->popular(now()->subMonths(6), now())
                        ->minReviews(5);
    }


    protected static function booted(){

        static::updated(fn (Book $book) => cache()->forget("book:". $book->id));
        static::deleted(fn (Book $book) => cache()->forget("book:". $book->id));
     }
}
