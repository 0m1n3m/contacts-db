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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();

            $table->string('contact_category')->index();
            $table->string('relationship_status')->index();

            $table->boolean('use_for_events')->default(false)->index();
            $table->boolean('potential_speaker')->default(false)->index();

            $table->string('organisation_name')->nullable()->index();

            $table->string('first_name')->nullable()->index();
            $table->string('last_name')->nullable()->index();
            $table->string('job_title')->nullable();

            $table->json('emails')->nullable();              // ["a@b.com","b@c.com"]
            $table->json('phones')->nullable();              // ["+34 ...", "+1 ..."]

            $table->string('country')->nullable()->index();

            $table->json('organisation_types')->nullable();  // ["NGO","University"]
            $table->json('keywords')->nullable();            // ["climate","policy"]

            $table->string('relevant_project_programme')->nullable();
            $table->text('expertise_speaking_topics')->nullable();

            $table->string('stakeholder_type')->nullable()->index();
            $table->text('comment')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
