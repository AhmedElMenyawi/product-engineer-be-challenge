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
        Schema::table('tasks', function (Blueprint $table) {
            $table->index('status', 'idx_tasks_status');
            $table->index('priority', 'idx_tasks_priority');
            $table->index('team_id', 'idx_tasks_team_id');
            $table->index('assigned_to', 'idx_tasks_assigned_to');
            $table->index('created_by', 'idx_tasks_created_by');
            
            $table->index(['team_id', 'status'], 'idx_tasks_team_status');
            $table->index(['assigned_to', 'status'], 'idx_tasks_assigned_status');
            $table->index(['created_by', 'created_at'], 'idx_tasks_creator_created');
            
            $table->index('created_at', 'idx_tasks_created_at');
            $table->index('start_time', 'idx_tasks_start_time');
            $table->index('end_time', 'idx_tasks_end_time');
            
            $table->index(['created_at', 'status'], 'idx_tasks_created_status');
            $table->index(['start_time', 'end_time'], 'idx_tasks_time_range');
        });

        Schema::table('task_histories', function (Blueprint $table) {
            $table->index('task_id', 'idx_task_histories_task_id');
            $table->index('user_id', 'idx_task_histories_user_id');
            $table->index('action', 'idx_task_histories_action');
            $table->index('created_at', 'idx_task_histories_created_at');
            
            $table->index(['task_id', 'created_at'], 'idx_task_histories_task_created');
            $table->index(['user_id', 'created_at'], 'idx_task_histories_user_created');
            $table->index(['action', 'created_at'], 'idx_task_histories_action_created');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('email', 'idx_users_email');
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->index('name', 'idx_teams_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex('idx_tasks_status');
            $table->dropIndex('idx_tasks_priority');
            $table->dropIndex('idx_tasks_team_id');
            $table->dropIndex('idx_tasks_assigned_to');
            $table->dropIndex('idx_tasks_created_by');
            $table->dropIndex('idx_tasks_team_status');
            $table->dropIndex('idx_tasks_assigned_status');
            $table->dropIndex('idx_tasks_creator_created');
            $table->dropIndex('idx_tasks_created_at');
            $table->dropIndex('idx_tasks_start_time');
            $table->dropIndex('idx_tasks_end_time');
            $table->dropIndex('idx_tasks_created_status');
            $table->dropIndex('idx_tasks_time_range');
        });

        Schema::table('task_histories', function (Blueprint $table) {
            $table->dropIndex('idx_task_histories_task_id');
            $table->dropIndex('idx_task_histories_user_id');
            $table->dropIndex('idx_task_histories_action');
            $table->dropIndex('idx_task_histories_created_at');
            $table->dropIndex('idx_task_histories_task_created');
            $table->dropIndex('idx_task_histories_user_created');
            $table->dropIndex('idx_task_histories_action_created');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_email');
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->dropIndex('idx_teams_name');
        });
    }
};
