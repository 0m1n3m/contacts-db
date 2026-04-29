<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_invitations', function (Blueprint $table) {
            $table->id();

            $table->string('email')->unique();
            $table->string('role')->default('viewer')->index();

            // Guardamos el token hasheado (no el token plano)
            $table->string('token_hash', 64)->unique();

            $table->timestamp('expires_at')->index();
            $table->timestamp('accepted_at')->nullable()->index();

            $table->foreignId('invited_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            // Opcional: para evitar invites duplicados "activos" por email
            $table->index(['email', 'accepted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_invitations');
    }
};