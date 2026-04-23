<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = [
        'contact_category',
        'relationship_status',
        'use_for_events',
        'potential_speaker',
        'organisation_name',
        'first_name',
        'last_name',
        'job_title',
        'emails',
        'phones',
        'country',
        'organisation_types',
        'keywords',
        'relevant_project_programme',
        'expertise_speaking_topics',
        'stakeholder_type',
        'comment',
    ];

    // Recomendado si en DB guardas JSON/arrays
    protected $casts = [
        'emails' => 'array',
        'phones' => 'array',
        'organisation_types' => 'array',
        'keywords' => 'array',
        'use_for_events' => 'boolean',
        'potential_speaker' => 'boolean',
    ];
}
