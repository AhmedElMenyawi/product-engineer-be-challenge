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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('task_token')->unique();
            $table->string('title');
            $table->text('description');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null'); 
            $table->foreignId('created_by')->constrained('users');
            $table->enum('status', ['pending', 'in_progress', 'completed','cancelled','on_hold'])->default('pending')->index();
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium')->index();
            $table->foreignId('team_id')->constrained('teams');
            $table->timestamp('start_time')->nullable()->index();
            $table->timestamp('end_time')->nullable()->index();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
