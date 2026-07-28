<?php

namespace App\Models;

class Admin extends BaseModel
{
    protected static string $table = 'admins';

    public static function findByUsername(string $username): ?array
    {
        $results = static::where('username', $username);

        return $results[0] ?? null;
    }
}
