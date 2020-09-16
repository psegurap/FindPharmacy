<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pharmacy extends Model
{
    //Information about the table related to this Model
    protected $table = "pharmacies";
    protected $fillable = ['name', 'address', 'city', 'state', 'zip', 'latitude', 'longitude'];

    public $timestamps = false;
}
