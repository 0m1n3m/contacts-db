<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();

            // Nullable: office/internal tasks not tied to a project
            $table->foreignId('project_id')
                ->nullable()
                ->constrained('projects')
                ->nullOnDelete();

            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('title')->index();
            $table->longText('description')->nullable();

            $table->enum('priority', ['critical', 'high', 'normal', 'low'])
                ->default('normal')
                ->index();

            $table->enum('status', ['created', 'accepted', 'in_progress', 'in_review', 'done'])
                ->default('created')
                ->index();

            $table->timestamp('due_at')->nullable()->index();
            $table->timestamp('last_activity_at')->nullable()->index();
            $table->timestamp('last_due_soon_reminded_at')->nullable();

            $table->timestamps();

            $table->index(['project_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};