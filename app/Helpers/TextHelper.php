<?php

namespace App\Helpers;

class TextHelper
{
    public static function highlightMentions(string $text): string
    {
        return preg_replace_callback(
            '/@(\w+)/',
            function ($matches) {
                return '<strong class="text-blue-600">@' . htmlspecialchars($matches[1]) . '</strong>';
            },
            htmlspecialchars($text)
        );
    }
}