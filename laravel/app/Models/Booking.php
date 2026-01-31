<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'id_client',
        'id_segment',
        'date_reservation',
        'status',
        'siege_numero'];

    public function client(){
        return $this->belongTo(Client::class);
    }
    public function segment(){
        return $this->belongTo(Segment::class);
    }
}
