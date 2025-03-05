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
        Schema::create('delivery_status_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_delivery_id')->constrained()->onDelete('cascade');
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
            ]);
            $table->string('location')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_status_updates');
    }
};
