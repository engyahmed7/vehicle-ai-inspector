<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('car_inspections', function (Blueprint $table) {
            $table->id();
            $table->json('front')->nullable();
            $table->json('rear')->nullable();
            $table->json('left')->nullable();
            $table->json('right')->nullable();
            $table->json('dashboard')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('car_inspections');
    }
};
