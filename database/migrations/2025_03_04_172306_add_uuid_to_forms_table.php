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
        Schema::table('forms', function (Blueprint $table) {
            // Check if column doesn't exist before adding (UUID serves as reference number)
            if (!Schema::hasColumn('forms', 'uuid')) {
                $table->uuid('uuid')->nullable()->after('id')->index()->comment('Reference number for application tracking');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
