<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            if (!Schema::hasColumn('forms', 'id_document')) {
                $table->string('id_document')->nullable();
            }
            
            if (!Schema::hasColumn('forms', 'passport_photo')) {
                $table->string('passport_photo')->nullable();
            }
            
            if (!Schema::hasColumn('forms', 'payslip')) {
                $table->string('payslip')->nullable();
            }
            
            if (!Schema::hasColumn('forms', 'signature')) {
                $table->text('signature')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            //
        });
    }
};
