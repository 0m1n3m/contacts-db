<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_attachments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('task_id')
                ->constrained('tasks')
                ->cascadeOnDelete();

            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('label')->nullable(); // e.g. "Design", "Specs"

            $table->timestamps();

            $table->index(['task_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_attachments');
    }
};