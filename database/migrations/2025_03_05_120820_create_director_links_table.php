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
        Schema::create('director_links', function (Blueprint $table) {
            $table->id();
            $table->string('token', 64)->unique();
            $table->string('form_id');
            $table->string('business_name')->nullable();
            $table->json('form_data')->nullable();
            $table->json('business_details')->nullable();
            $table->unsignedInteger('director_position')->default(1);
            $table->unsignedInteger('total_directors')->default(1);
            $table->boolean('is_final_director')->default(false);
            $table->boolean('is_completed')->default(false);
            $table->timestamp('expires_at');
            $table->timestamps();
            
            $table->index('token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('director_links');
    }
};
