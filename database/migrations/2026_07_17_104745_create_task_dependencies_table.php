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
        Schema::create('task_dependencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete(); // tarea que tiene dependencia
            $table->foreignId('dependent_task_id')->constrained('tasks')->cascadeOnDelete(); // tarea de la que depende
            $table->enum('type', ['depends_on', 'blocks', 'relates_to'])->default('depends_on');
            $table->timestamps();
            $table->unique(['task_id', 'dependent_task_id', 'type']); // evitar duplicados
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_dependencies');
    }
};
