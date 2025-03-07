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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('supervisor_id')->nullable()->after('referred_by')->constrained('users')->onDelete('set null');
            $table->string('team_role')->nullable()->after('role');
            $table->decimal('commission_rate', 5, 2)->nullable()->after('status');
            $table->boolean('is_team_lead')->default(false)->after('status');
            $table->date('team_joined_date')->nullable()->after('status');
        });

        Schema::create('agent_teams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('team_lead_id')->constrained('users')->onDelete('cascade');
            $table->text('description')->nullable();
            $table->date('formed_date');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('agent_team_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('agent_teams')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('joined_date');
            $table->date('left_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['team_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_team_members');
        Schema::dropIfExists('agent_teams');
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['supervisor_id']);
            $table->dropColumn([
                'supervisor_id',
                'team_role',
                'commission_rate',
                'is_team_lead',
                'team_joined_date'
            ]);
        });
    }
};
