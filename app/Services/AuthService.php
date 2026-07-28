<?php

namespace App\Services;

use App\Repositories\Contracts\AdminRepositoryInterface;
use App\Services\Exceptions\ValidationException;

class AuthService
{
    public function __construct(private AdminRepositoryInterface $admins)
    {
    }

    public function attempt(string $username, string $password): ?array
    {
        $admin = $this->admins->findByUsername($username);

        if (!$admin || !$admin['is_active'] || !password_verify($password, $admin['password'])) {
            return null;
        }

        return $admin;
    }

    public function changePassword(int|string $adminId, array $admin, string $current, string $new, string $confirm): void
    {
        if (!password_verify($current, $admin['password'])) {
            throw new ValidationException('Mevcut şifre hatalı.');
        }

        if (mb_strlen($new) < 8) {
            throw new ValidationException('Yeni şifre en az 8 karakter olmalı.');
        }

        if ($new !== $confirm) {
            throw new ValidationException('Yeni şifreler eşleşmiyor.');
        }

        $this->admins->updatePassword($adminId, password_hash($new, PASSWORD_DEFAULT));
    }
}
