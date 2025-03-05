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
            // Extract key fields for querying
            $table->string('applicant_name')->nullable()->index()->after('form_name');
            $table->string('applicant_id_number')->nullable()->index()->after('applicant_name');
            $table->string('applicant_phone')->nullable()->after('applicant_id_number');
            $table->string('applicant_email')->nullable()->after('applicant_phone');
            $table->string('employer')->nullable()->index()->after('applicant_email');
            $table->decimal('loan_amount', 10, 2)->nullable()->index()->after('employer');
            $table->integer('loan_term_months')->nullable()->after('loan_amount');
            $table->date('loan_start_date')->nullable()->after('loan_term_months');
            $table->date('loan_end_date')->nullable()->after('loan_start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->dropColumn([
                'applicant_name',
                'applicant_id_number',
                'applicant_phone',
                'applicant_email',
                'employer',
                'loan_amount',
                'loan_term_months',
                'loan_start_date',
                'loan_end_date',
            ]);
        });
    }
};