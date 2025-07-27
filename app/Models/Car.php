<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    protected $fillable = [
        'vin',
        'make',
        'model',
        'year',
        'odometer',
        'plate_number',
        'registration_expiry',
        'image_path',
    ];

    protected $casts = [
        'registration_expiry' => 'date',
    ];
}
