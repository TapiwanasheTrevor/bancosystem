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
        {
            Schema::table('forms', function (Blueprint $table) {
                $table->json('form_values')->nullable()->after('type');
                $table->json('questionnaire_data')->nullable()->after('form_values');
                $table->dropColumn('schema');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->dropColumn('form_values');
            $table->dropColumn('questionnaire_data');
            $table->json('schema')->after('type');
        });
    }
};
