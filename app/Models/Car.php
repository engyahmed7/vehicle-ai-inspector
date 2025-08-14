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
    ];


    protected $casts = [
        'registration_expiry' => 'date',
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
