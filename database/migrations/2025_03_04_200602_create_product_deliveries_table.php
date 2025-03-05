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
        Schema::create('product_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('tracking_number')->unique();
            $table->enum('status', [
                'pending', 
                'processing', 
                'dispatched', 
                'in_transit',
                'at_station',
                'out_for_delivery',
                'delivered',
                'delayed',
                'cancelled'
            ])->default('pending');
            $table->string('current_location')->nullable();
            $table->text('status_notes')->nullable();
            $table->timestamp('estimated_delivery_date')->nullable();
            $table->timestamp('actual_delivery_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_deliveries');
    }
};
