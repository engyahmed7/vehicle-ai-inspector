<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarInspection extends Model
{
    protected $fillable = [
        'vin',
        'make',
        'model',
        'year',
        'odometer',
        'license_plate',
        'images', // stores all image paths
        'dashboard_info', // fuel, warnings, etc.
    ];

    protected $casts = [
        'images' => 'array', // to handle multiple image paths
        'dashboard_info' => 'array', // to handle structured dashboard info
    ];
}
