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
        Schema::table('users', function (Blueprint $table) {
            $table->string('persona_inquiry_id')->nullable()->index();
            $table->enum('kyc_status', ['pending', 'approved', 'rejected', 'expired', 'pending_review'])->default('pending');
            $table->timestamp('kyc_completed_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['persona_inquiry_id']);
            $table->dropColumn(['persona_inquiry_id', 'kyc_status', 'kyc_completed_at']);
        });
    }
};
