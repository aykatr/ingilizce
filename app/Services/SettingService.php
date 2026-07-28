<?php

namespace App\Services;

use App\Repositories\Contracts\SettingRepositoryInterface;
use App\Services\Exceptions\ValidationException;

class SettingService
{
    private const SITE_URL_KEY = 'site_url';
    private const DEFAULT_DURATION_KEY = 'default_duration_seconds';
    private const DEFAULT_POINTS_KEY = 'default_points';
    private const DEFAULT_LIVES_KEY = 'default_lives';

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

    public function getDefaultDuration(): int
    {
        $value = $this->settings->get(self::DEFAULT_DURATION_KEY);

        return $value !== null ? (int) $value : 30;
    }

    public function getDefaultPoints(): int
    {
        $value = $this->settings->get(self::DEFAULT_POINTS_KEY);

        return $value !== null ? (int) $value : 10;
    }

    public function getDefaultLives(): int
    {
        $value = $this->settings->get(self::DEFAULT_LIVES_KEY);

        return $value !== null ? (int) $value : 3;
    }

    public function updateDefaults(int $duration, int $points, int $lives): void
    {
        if ($duration < 1) {
            throw new ValidationException('Varsayılan süre en az 1 saniye olmalı.');
        }

        if ($points < 0) {
            throw new ValidationException('Varsayılan puan negatif olamaz.');
        }

        if ($lives < 1) {
            throw new ValidationException('Can sayısı en az 1 olmalı.');
        }

        $this->settings->set(self::DEFAULT_DURATION_KEY, (string) $duration);
        $this->settings->set(self::DEFAULT_POINTS_KEY, (string) $points);
        $this->settings->set(self::DEFAULT_LIVES_KEY, (string) $lives);
    }
}
