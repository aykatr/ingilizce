<?php

namespace App\Services;

use App\Repositories\Contracts\SettingRepositoryInterface;
use App\Services\Exceptions\ValidationException;

class SettingService
{
    private const SITE_URL_KEY = 'site_url';

    public function __construct(private SettingRepositoryInterface $settings)
    {
    }

    public function getSiteUrl(): string
    {
        return $this->settings->get(self::SITE_URL_KEY) ?? rtrim((string) config('app.url'), '/');
    }

    public function updateSiteUrl(string $url): void
    {
        $url = trim($url);

        if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
            throw new ValidationException('Geçerli bir URL girin.');
        }

        $this->settings->set(self::SITE_URL_KEY, rtrim($url, '/'));
    }
}
