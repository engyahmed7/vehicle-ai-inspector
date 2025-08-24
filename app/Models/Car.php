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
        'user_id',
        'vin',
        'vehicle_info',
        'make',
        'model',
        'year',
        'body_class',
        'vehicle_type',
        'fuel_type',
        'transmission_style',
        'vehicle_age_eligible',
        'insurance_details',
        'mvr_details',
        'images_data',
    ];


    protected $casts = [
        'registration_expiry' => 'date',
        'vehicle_info' => 'array',
        'insurance_details' => 'array',
        'mvr_details' => 'array',
        'images_data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }
}
