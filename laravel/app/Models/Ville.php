<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ville extends Model
{
    use HasFactory;

    protected $table = 'ville';

    protected $fillable = [
        'name',
    ];

    /**
     * Get all stations in this city
     */
    public function stations(): HasMany
    {
        return $this->hasMany(Station::class, 'city_id');
    }
}
