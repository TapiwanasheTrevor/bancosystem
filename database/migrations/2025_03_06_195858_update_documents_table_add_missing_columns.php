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
        Schema::table('documents', function (Blueprint $table) {
            // Check and add missing columns based on errors in logs
            if (!Schema::hasColumn('documents', 'file_type')) {
                $table->string('file_type')->nullable()->after('path');
            }
            
            if (!Schema::hasColumn('documents', 'size')) {
                $table->integer('size')->nullable()->after('file_type');
            }
            
            if (!Schema::hasColumn('documents', 'document_type')) {
                $table->string('document_type')->nullable()->after('size');
            }
            
            if (!Schema::hasColumn('documents', 'agent_id')) {
                $table->foreignId('agent_id')->nullable()->after('document_type')
                    ->constrained('users')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('documents', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('agent_id')
                    ->constrained('users')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('documents', 'form_id')) {
                $table->foreignId('form_id')->nullable()->after('user_id')
                    ->constrained('forms')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn([
                'file_type',
                'size',
                'document_type',
                'agent_id',
                'user_id',
                'form_id'
            ]);
        });
    }
};
