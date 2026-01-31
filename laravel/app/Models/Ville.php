<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ville extends Model
{
    protected $fillable = [
        'jour_depart',
        'heure_depart',
        'heure_arrivee',];

    public function segment(){
        return $this->belongTo(Segment::class);
    }
    public function route(){
        return $this->belongTo(Route::class);
    }
}
