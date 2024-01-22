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
}
