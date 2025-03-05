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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('user');
            }
            
            if (!Schema::hasColumn('users', 'position')) {
                $table->string('position')->nullable(); // field_agent, office_agent, supervisor
            }
            
            if (!Schema::hasColumn('users', 'phone_number')) {
                $table->string('phone_number')->nullable();
            }
            
            if (!Schema::hasColumn('users', 'alternate_phone')) {
                $table->string('alternate_phone')->nullable();
            }
            
            if (!Schema::hasColumn('users', 'referral_code')) {
                $table->string('referral_code')->unique()->nullable();
            }
            
            if (!Schema::hasColumn('users', 'referred_by')) {
                $table->unsignedBigInteger('referred_by')->nullable();
                $table->foreign('referred_by')->references('id')->on('users')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('users', 'status')) {
                $table->integer('status')->default(1); // 1: active, 0: inactive
            }
            
            if (!Schema::hasColumn('users', 'referral_count')) {
                $table->integer('referral_count')->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
