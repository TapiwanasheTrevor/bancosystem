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
        // Add catalog_type column to categories table if it doesn't exist
        if (!Schema::hasColumn('categories', 'catalog_type')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->enum('catalog_type', ['microbiz', 'hirepurchase'])->default('microbiz')->after('parent_id');
            });
        }
        
        // Try to create a composite unique index if possible
        try {
            Schema::table('categories', function (Blueprint $table) {
                $table->unique(['name', 'catalog_type'], 'categories_name_catalog_type_unique');
            });
        } catch (\Exception $e) {
            // Index might already exist or can't be created, we'll just continue
        }
        
        // Add catalog_type column to products table if it doesn't exist
        if (!Schema::hasColumn('products', 'catalog_type')) {
            Schema::table('products', function (Blueprint $table) {
                $table->enum('catalog_type', ['microbiz', 'hirepurchase'])->default('microbiz')->after('image');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove catalog_type column from categories table
        Schema::table('categories', function (Blueprint $table) {
            // First drop the composite unique constraint
            $table->dropUnique(['name', 'catalog_type']);
            
            // Add back the simple unique constraint on name
            $table->unique(['name']);
            
            // Remove the catalog_type column
            $table->dropColumn('catalog_type');
        });
        
        // Remove catalog_type column from products table
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('catalog_type');
        });
    }
};
