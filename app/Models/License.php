<?php

namespace App\Models;

class License extends BaseModel
{
    protected static string $table = 'licenses';

    public static function findByToken(string $token): ?array
    {
        $results = static::where('token', $token);

        return $results[0] ?? null;
    }
}
