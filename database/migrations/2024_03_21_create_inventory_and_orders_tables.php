<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Create agents table
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // field, online, office
            $table->string('phone');
            $table->string('email')->unique();
            $table->string('employee_number')->unique();
            $table->decimal('commission_rate', 5, 2)->default(5.00); // 5% default commission
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Create purchase_orders table
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('application_number');
            $table->string('status'); // pending, approved, delivered
            $table->decimal('total_amount', 10, 2);
            $table->string('supplier')->default('Seven Hundred Nine Hundred Pvt Ltd');
            $table->timestamps();
        });

        // Create purchase_order_items table
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->onDelete('cascade');
            $table->string('product_id');
            $table->integer('quantity');
            $table->decimal('cost_price', 10, 2);
            $table->string('status'); // pending, in_stock, delivered
            $table->timestamps();
        });

        // Create inventory table
        Schema::create('inventory', function (Blueprint $table) {
            $table->id();
            $table->string('product_id');
            $table->integer('quantity');
            $table->decimal('cost_price', 10, 2);
            $table->string('supplier')->default('Seven Hundred Nine Hundred Pvt Ltd');
            $table->string('location');
            $table->string('status'); // available, reserved, damaged, missing
            $table->timestamps();
        });

        // Create commissions table
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained();
            $table->string('application_number');
            $table->decimal('amount', 10, 2);
            $table->decimal('percentage', 5, 2);
            $table->string('period'); // YYYY-MM format
            $table->string('status'); // pending, approved, paid
            $table->timestamps();
        });

        // Create allowances table
        Schema::create('allowances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained();
            $table->decimal('amount', 10, 2);
            $table->string('period'); // YYYY-MM format
            $table->string('status'); // pending, approved, paid
            $table->timestamps();
        });

        // Create cost_buildups table
        Schema::create('cost_buildups', function (Blueprint $table) {
            $table->id();
            $table->string('product_id');
            $table->decimal('base_cost', 10, 2);
            $table->json('variables'); // Store variable costs as JSON
            $table->decimal('final_price', 10, 2);
            $table->timestamps();
        });

        // Create credit_notes table
        Schema::create('credit_notes', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number');
            $table->foreignId('purchase_order_id')->constrained();
            $table->decimal('amount', 10, 2);
            $table->string('reason');
            $table->string('status'); // pending, approved, processed
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('credit_notes');
        Schema::dropIfExists('cost_buildups');
        Schema::dropIfExists('allowances');
        Schema::dropIfExists('commissions');
        Schema::dropIfExists('inventory');
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('agents');
    }
};
