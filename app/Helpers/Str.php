<?php

namespace App\Helpers;

class Str
{
    public static function random(int $length = 32): string
    {
        return bin2hex(random_bytes((int) ceil($length / 2)));
    }

    public static function slug(string $value): string
    {
        $map = ['ç' => 'c', 'ğ' => 'g', 'ı' => 'i', 'ö' => 'o', 'ş' => 's', 'ü' => 'u',
                'Ç' => 'c', 'Ğ' => 'g', 'İ' => 'i', 'Ö' => 'o', 'Ş' => 's', 'Ü' => 'u'];

        $value = strtr($value, $map);
        $value = strtolower($value);
        $value = preg_replace('/[^a-z0-9]+/', '-', $value);

        return trim($value, '-');
    }

    public static function limit(string $value, int $limit = 100, string $end = '...'): string
    {
        if (mb_strlen($value) <= $limit) {
            return $value;
        }

        return mb_substr($value, 0, $limit) . $end;
    }

    public static function code(int $groups = 3, int $groupLength = 4): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $parts = [];

        for ($g = 0; $g < $groups; $g++) {
            $part = '';
            for ($i = 0; $i < $groupLength; $i++) {
                $part .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
            $parts[] = $part;
        }

        return implode('-', $parts);
    }
}
