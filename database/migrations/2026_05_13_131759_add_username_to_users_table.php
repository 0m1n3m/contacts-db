<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // nullable to avoid breaking existing rows; you can backfill later.
            $table->string('username')->nullable()->after('name');

            $table->unique('username');
            $table->index('username');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropIndex(['username']);
            $table->dropColumn('username');
        });
    }
};