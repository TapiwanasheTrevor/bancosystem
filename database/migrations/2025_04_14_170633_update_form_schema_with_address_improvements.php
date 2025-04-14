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
        // Add swift branches table for delivery points
        Schema::create('swift_branches', function (Blueprint $table) {
            $table->id();
            $table->string('branch_name');
            $table->string('branch_code');
            $table->string('province');
            $table->string('district')->nullable();
            $table->string('address');
            $table->string('contact_person')->nullable();
            $table->string('contact_number')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        
        // Add address components to forms table
        Schema::table('forms', function (Blueprint $table) {
            $table->string('address_type')->nullable(); // urban, rural
            $table->string('house_number')->nullable();
            $table->string('street_name')->nullable();
            $table->string('town_city')->nullable();
            $table->string('district')->nullable();
            $table->string('province')->nullable();
            $table->string('ward')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('swift_branches');
        
        Schema::table('forms', function (Blueprint $table) {
            $table->dropColumn([
                'address_type',
                'house_number',
                'street_name',
                'town_city',
                'district',
                'province',
                'ward'
            ]);
        });
    }
};
