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
            // Check if 'referral' exists and 'referral_code' doesn't
            if (Schema::hasColumn('users', 'referral') && !Schema::hasColumn('users', 'referral_code')) {
                // Rename the column from 'referral' to 'referral_code'
                $table->renameColumn('referral', 'referral_code');
            } 
            // If 'referral_code' doesn't exist at all, create it
            else if (!Schema::hasColumn('users', 'referral_code')) {
                $table->string('referral_code')->unique()->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Rename back if 'referral_code' exists
            if (Schema::hasColumn('users', 'referral_code')) {
                $table->renameColumn('referral_code', 'referral');
            }
        });
    }
};
