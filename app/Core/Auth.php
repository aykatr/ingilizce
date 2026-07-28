<?php

namespace App\Core;

use App\Models\Admin;

class Auth
{
    private const SESSION_KEY = 'admin_id';

    public static function login(int|string $adminId): void
    {
        Session::regenerate();
        Session::put(self::SESSION_KEY, $adminId);
    }

    public static function logout(): void
    {
        Session::forget(self::SESSION_KEY);
        Session::destroy();
    }

    public static function check(): bool
    {
        return Session::get(self::SESSION_KEY) !== null;
    }

    public static function id(): int|string|null
    {
        return Session::get(self::SESSION_KEY);
    }

    public static function user(): ?array
    {
        $id = self::id();

        return $id === null ? null : Admin::find($id);
    }
}
