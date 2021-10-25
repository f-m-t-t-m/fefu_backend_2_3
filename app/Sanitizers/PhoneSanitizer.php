<?php

namespace App\Sanitizers;
use function preg_replace;

class PhoneSanitizer
{
    public static function sanitize(?string $value) : ?string {
        if ($value === null) {
            return null;
        }
        return preg_replace('/((\+7)|7|8){1} \((\d{3})\) (\d{2})-(\d{2})-(\d{3})/', '7$3$4$5$6', $value);
    }
}
