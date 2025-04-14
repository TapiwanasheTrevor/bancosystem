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
        // Add field for tracking if an inventory item is assigned to a field team
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->unsignedBigInteger('team_id')->nullable()->after('warehouse_id');
            $table->foreign('team_id')->references('id')->on('agent_teams')->onDelete('set null');
            $table->timestamp('assigned_to_team_at')->nullable();
            $table->timestamp('returned_from_team_at')->nullable();
        });
        
        // Add table for tracking returns from field teams
        Schema::create('team_stock_returns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id');
            $table->foreign('team_id')->references('id')->on('agent_teams')->onDelete('cascade');
            $table->unsignedBigInteger('processed_by');
            $table->foreign('processed_by')->references('id')->on('users')->onDelete('cascade');
            $table->string('return_number')->unique();
            $table->string('status'); // pending, processed, rejected
            $table->text('notes')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
        
        // Add table for tracking individual items in a return
        Schema::create('team_stock_return_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('return_id');
            $table->foreign('return_id')->references('id')->on('team_stock_returns')->onDelete('cascade');
            $table->unsignedBigInteger('inventory_item_id');
            $table->foreign('inventory_item_id')->references('id')->on('inventory_items')->onDelete('cascade');
            $table->integer('quantity');
            $table->string('condition'); // good, damaged, missing
            $table->text('notes')->nullable();
            $table->timestamps();
        });
        
        // Add table for credit notes
        Schema::create('credit_notes', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->unique();
            $table->unsignedBigInteger('return_id');
            $table->foreign('return_id')->references('id')->on('team_stock_returns')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->string('reason');
            $table->string('status'); // pending, approved, processed
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_notes');
        Schema::dropIfExists('team_stock_return_items');
        Schema::dropIfExists('team_stock_returns');
        
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
            $table->dropColumn(['team_id', 'assigned_to_team_at', 'returned_from_team_at']);
        });
    }
};
