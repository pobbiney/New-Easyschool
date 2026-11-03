<?php

namespace App\Support;

class MediaUrl
{
    public static function resolve(?string $path): ?string
    {
        if ($path === null || trim($path) === '') {
            return null;
        }

        $path = trim($path);

        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        if (str_starts_with($path, '//')) {
            return $path;
        }

        return '/'.ltrim($path, '/');
    }
}
