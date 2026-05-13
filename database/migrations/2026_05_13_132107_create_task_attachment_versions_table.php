<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_attachment_versions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('task_attachment_id')
                ->constrained('task_attachments')
                ->cascadeOnDelete();

            $table->unsignedInteger('version');

            $table->foreignId('uploaded_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('disk', 50)->default('local');
            $table->string('path');

            $table->string('original_name');
            $table->string('mime', 100)->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->string('checksum', 64)->nullable();

            $table->timestamps();

            $table->unique(['task_attachment_id', 'version']);
            $table->index(['task_attachment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_attachment_versions');
    }
};