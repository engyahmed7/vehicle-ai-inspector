<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    protected $fillable = [
        'image_url',
        'cloudinary_id',
        'license_plate',
        'odometer',
        'fuel_level',
    ];


    protected $casts = [
        'registration_expiry' => 'date',
    ];
}
