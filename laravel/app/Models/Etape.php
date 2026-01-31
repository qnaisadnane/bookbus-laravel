<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Etape extends Model
{
    protected $fillable = [
        'ordre'];

    public function segment(){
        return $this->belongTo(Segment::class);
    }
    public function route(){
        return $this->belongTo(Route::class);
    }
}
