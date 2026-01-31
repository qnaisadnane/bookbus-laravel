<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    protected $fillable = [
        'nom',
        'description'];

    public function programme(){
        return $this->hasMany(Programme::class);
    }
    public function etape(){
        return $this->hasMany(Etape::class);
    }
}
