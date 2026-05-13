<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_assignments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('task_id')
                ->constrained('tasks')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('assigned_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('accepted_at')->nullable()->index();

            $table->timestamps();

            $table->unique(['task_id', 'user_id']);
            $table->index(['user_id', 'accepted_at']);
            $table->index(['task_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_assignments');
    }
};