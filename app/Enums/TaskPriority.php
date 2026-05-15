<?php

namespace App\Enums;

enum TaskPriority: string
{
    case Critical = 'critical';
    case High = 'high';
    case Normal = 'normal';
    case Low = 'low';
}