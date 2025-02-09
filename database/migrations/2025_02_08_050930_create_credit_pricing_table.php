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
        Schema::create('credit_pricings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id');
            $table->integer('months'); // 3, 6, 9, 12
            $table->decimal('interest', 5, 2);
            $table->decimal('final_price', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('credit_pricing', function (Blueprint $table) {
            //
        });
    }
};
