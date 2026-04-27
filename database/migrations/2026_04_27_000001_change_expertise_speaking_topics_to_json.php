<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1) add temp json column
        Schema::table('contacts', function (Blueprint $table) {
            $table->json('expertise_speaking_topics_json')->nullable();
        });

        // 2) migrate data from old text column -> json array
        $contacts = DB::table('contacts')->select('id', 'expertise_speaking_topics')->get();

        foreach ($contacts as $c) {
            $raw = (string) ($c->expertise_speaking_topics ?? '');
            $raw = trim($raw);

            if ($raw === '') {
                $arr = null;
            } else {
                // split by ; and clean
                $parts = array_values(array_filter(array_map(function ($x) {
                    $x = trim((string) $x);
                    return $x === '' ? null : $x;
                }, preg_split('/\s*;\s*/', $raw) ?: [])));

                $arr = count($parts) ? $parts : null;
            }

            DB::table('contacts')
                ->where('id', $c->id)
                ->update(['expertise_speaking_topics_json' => $arr ? json_encode($arr) : null]);
        }

        // 3) drop old column (SQLite requires table rebuild under the hood; Laravel handles it)
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn('expertise_speaking_topics');
        });

        // 4) rename temp column to original name
        Schema::table('contacts', function (Blueprint $table) {
            $table->renameColumn('expertise_speaking_topics_json', 'expertise_speaking_topics');
        });
    }

    public function down(): void
    {
        // reverse: create text column, flatten json array into ';' string, drop json col, rename back

        Schema::table('contacts', function (Blueprint $table) {
            $table->text('expertise_speaking_topics_text')->nullable();
        });

        $contacts = DB::table('contacts')->select('id', 'expertise_speaking_topics')->get();

        foreach ($contacts as $c) {
            $raw = $c->expertise_speaking_topics;

            $text = null;
            if ($raw !== null) {
                $arr = json_decode((string) $raw, true);
                if (is_array($arr)) {
                    $arr = array_values(array_filter(array_map(fn ($x) => trim((string)$x), $arr)));
                    $text = count($arr) ? implode(';', $arr) : null;
                } else {
                    $text = trim((string) $raw) ?: null;
                }
            }

            DB::table('contacts')
                ->where('id', $c->id)
                ->update(['expertise_speaking_topics_text' => $text]);
        }

        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn('expertise_speaking_topics');
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->renameColumn('expertise_speaking_topics_text', 'expertise_speaking_topics');
        });
    }
};