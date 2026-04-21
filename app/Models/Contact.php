<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $casts = [
        'use_for_events' => 'boolean',
        'potential_speaker' => 'boolean',
        'emails' => 'array',
        'phones' => 'array',
        'organisation_types' => 'array',
        'keywords' => 'array',
    ];
}
