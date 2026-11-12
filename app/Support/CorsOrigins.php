<?php

namespace App\Support;

use InvalidArgumentException;

final class CorsOrigins
{
    public static function parse(string $value): array
    {
        $origins = [];
        foreach (explode(',', $value) as $entry) {
            $origin = rtrim(trim($entry), '/');
            if ($origin === '') continue;
            $parts = parse_url($origin);
            if (str_contains($origin, '*') || ! filter_var($origin, FILTER_VALIDATE_URL)
                || ! is_array($parts) || ! isset($parts['scheme'], $parts['host'])
                || ! in_array(strtolower($parts['scheme']), ['http','https'], true)
                || array_diff(array_keys($parts), ['scheme','host','port'])) {
                throw new InvalidArgumentException('CORS_ALLOWED_ORIGINS doit contenir uniquement des origines HTTP(S) exactes, séparées par des virgules, sans joker, chemin ni identifiants.');
            }
            $origins[] = strtolower($origin);
        }
        return array_values(array_unique($origins));
    }
}
