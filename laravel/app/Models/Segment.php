<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Segement extends Model
{
    protected $fillable = [
        'tarif',
        'duree_estimee',
        'distance_km'
    ];
    public function reservation(){
        return $this->hasMany(Booking::class);
    }
    public function bus(){
        return $this->belongTo(Bus::class);
    }
    public function programme(){
        return $this->hasMany(Bus::class);
    }
}
