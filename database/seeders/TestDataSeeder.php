<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $carOwner = \App\Models\User::where('role', 'car_owner')->first();

        $car = \App\Models\Car::create([
            'user_id' => $carOwner->id,
            'image_url' => 'https://example.com/car1.jpg',
            'cloudinary_id' => 'sample_car_1',
            'license_plate' => 'ABC-123',
            'odometer' => 50000,
            'fuel_level' => 75,
        ]);

        $car2 = \App\Models\Car::create([
            'user_id' => $carOwner->id,
            'image_url' => 'https://example.com/car2.jpg',
            'cloudinary_id' => 'sample_car_2',
            'license_plate' => 'XYZ-789',
            'odometer' => 25000,
            'fuel_level' => 60,
        ]);
    }
}
