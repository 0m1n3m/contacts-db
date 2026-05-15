<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Created = 'created';
    case Accepted = 'accepted';
    case InProgress = 'in_progress';
    case InReview = 'in_review';
    case Done = 'done';
}