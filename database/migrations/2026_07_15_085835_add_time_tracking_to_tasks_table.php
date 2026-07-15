<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Tiempos en segundos
            $table->unsignedBigInteger('lead_time')->default(0)->after('status')->comment('Segundos desde created hasta done');
            $table->unsignedBigInteger('dev_time')->default(0)->after('lead_time')->comment('Segundos acumulados en in_progress');
            $table->unsignedBigInteger('review_time')->default(0)->after('dev_time')->comment('Segundos acumulados en in_review');

            // Contador de transiciones hacia atrás
            $table->unsignedInteger('backward_transitions')->default(0)->after('review_time')->comment('Veces que retrocede a estado anterior');

            // Timestamps de referencia
            $table->timestamp('entered_current_status_at')->nullable()->after('backward_transitions')->comment('Cuando entró al estado actual');
            $table->timestamp('completed_at')->nullable()->after('entered_current_status_at')->comment('Cuando se marcó como done');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn([
                'lead_time',
                'dev_time',
                'review_time',
                'backward_transitions',
                'entered_current_status_at',
                'completed_at',
            ]);
        });
    }
};