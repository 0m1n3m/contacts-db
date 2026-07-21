<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_comment_mentions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('task_comment_id')
                ->constrained('task_comments')
                ->cascadeOnDelete();

            // Denormalized for fast visibility queries
            $table->foreignId('task_id')
                ->constrained('tasks')
                ->cascadeOnDelete();

            $table->foreignId('mentioned_user_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->timestamps();

            $table->unique(['task_comment_id', 'mentioned_user_id']);
            $table->index(['mentioned_user_id']);
            $table->index(['task_id', 'mentioned_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_comment_mentions');
    }
};