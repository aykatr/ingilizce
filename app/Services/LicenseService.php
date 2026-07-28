<?php

namespace App\Services;

use App\Helpers\Str;
use App\Repositories\Contracts\LicenseRepositoryInterface;
use App\Services\Exceptions\ValidationException;

class LicenseService
{
    public function __construct(private LicenseRepositoryInterface $licenses)
    {
    }

    public function list(): array
    {
        return $this->licenses->all();
    }

    public function create(string $name, ?string $expiresAt = null): array
    {
        $name = trim($name);

        if ($name === '') {
            throw new ValidationException('Lisans adı gerekli.');
        }

        do {
            $token = Str::random(32);
        } while ($this->licenses->findByToken($token));

        do {
            $code = Str::code();
        } while ($this->licenses->findByCode($code));

        $id = $this->licenses->create([
            'name' => $name,
            'token' => $token,
            'code' => $code,
            'is_active' => 1,
            'expires_at' => $expiresAt,
        ]);

        return $this->licenses->find($id);
    }

    public function toggleStatus(int|string $id): void
    {
        $license = $this->licenses->find($id);

        if ($license) {
            $this->licenses->update($id, ['is_active' => $license['is_active'] ? 0 : 1]);
        }
    }

    public function statusLabel(array $license): string
    {
        if (!$license['is_active']) {
            return 'Pasif';
        }

        if (!empty($license['expires_at']) && strtotime($license['expires_at']) < time()) {
            return 'Süresi Doldu';
        }

        return 'Aktif';
    }

    public function validateAndTrack(string $token, string $ip, ?string $userAgent): array
    {
        $license = $this->licenses->findByToken($token);

        if (!$license) {
            throw new ValidationException('Lisans bulunamadı.');
        }

        if (!$license['is_active']) {
            throw new ValidationException('Bu lisans pasif durumda.');
        }

        if (!empty($license['expires_at']) && strtotime($license['expires_at']) < time()) {
            throw new ValidationException('Bu lisansın süresi dolmuş.');
        }

        $this->licenses->recordUsage($license['id'], [
            'first_activated_at' => $license['first_activated_at'] ?? date('Y-m-d H:i:s'),
            'last_used_at' => date('Y-m-d H:i:s'),
            'last_ip' => $ip,
            'last_device' => $userAgent !== null ? mb_substr($userAgent, 0, 255) : null,
        ]);

        return $license;
    }
}
