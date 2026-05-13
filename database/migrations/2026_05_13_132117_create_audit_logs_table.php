<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('actor_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('project_id')
                ->nullable()
                ->constrained('projects')
                ->nullOnDelete();

            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id');

            $table->string('action')->index();

            $table->json('before')->nullable();
            $table->json('after')->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamps();

            $table->index(['entity_type', 'entity_id']);
            $table->index(['project_id', 'created_at']);
            $table->index(['actor_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};