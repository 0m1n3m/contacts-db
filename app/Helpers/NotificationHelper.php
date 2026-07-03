<?php

namespace App\Helpers;

class NotificationHelper
{
    public static function getBadgeClass(string $type): string
    {
        return match($type) {
            'mention' => 'bg-purple-100 text-purple-800',
            'assignment' => 'bg-blue-100 text-blue-800',
            'upload' => 'bg-green-100 text-green-800',
            'status_change' => 'bg-orange-100 text-orange-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}