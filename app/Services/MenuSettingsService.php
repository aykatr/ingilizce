<?php

namespace App\Services;

use App\Repositories\Contracts\SettingRepositoryInterface;

class MenuSettingsService
{
    private const TEXT_FIELDS = [
        'menu_title' => ['key' => 'menu_title', 'default' => 'Kartını Seç'],
        'menu_description' => ['key' => 'menu_description', 'default' => 'Oynamak istediğin kartı seç ve maceraya başla!'],
    ];

    private const APPEARANCE_FIELDS = [
        'menu_columns' => ['key' => 'menu_columns', 'default' => 2],
        'menu_card_size' => ['key' => 'menu_card_size', 'default' => 140],
        'menu_card_gap' => ['key' => 'menu_card_gap', 'default' => 12],
        'menu_card_radius' => ['key' => 'menu_card_radius', 'default' => 20],
    ];

    private const BACKGROUND_IMAGE_KEY = 'menu_background_image';

    public function __construct(
        private SettingRepositoryInterface $settings,
        private MediaUploadService $media,
    ) {
    }

    public function get(): array
    {
        $result = [];

        foreach (self::TEXT_FIELDS as $field => $meta) {
            $result[$field] = $this->settings->get($meta['key']) ?? $meta['default'];
        }

        foreach (self::APPEARANCE_FIELDS as $field => $meta) {
            $value = $this->settings->get($meta['key']);
            $result[$field] = $value !== null ? (int) $value : $meta['default'];
        }

        $result['menu_background_image'] = $this->settings->get(self::BACKGROUND_IMAGE_KEY);

        return $result;
    }

    public function update(array $data, array $files): void
    {
        foreach (self::TEXT_FIELDS as $field => $meta) {
            $value = trim((string) ($data[$field] ?? ''));

            if ($value === '') {
                $this->settings->delete($meta['key']);
            } else {
                $this->settings->set($meta['key'], $value);
            }
        }

        foreach (self::APPEARANCE_FIELDS as $field => $meta) {
            $value = max(1, (int) ($data[$field] ?? $meta['default']));
            $this->settings->set($meta['key'], (string) $value);
        }

        $current = $this->settings->get(self::BACKGROUND_IMAGE_KEY);
        $remove = !empty($data['remove_menu_background_image']);

        $path = $this->media->handleImage(
            $files['menu_background_image'] ?? null,
            'menu',
            'background',
            $remove,
            $current
        );

        if ($path === null) {
            $this->settings->delete(self::BACKGROUND_IMAGE_KEY);
        } elseif ($path !== $current) {
            $this->settings->set(self::BACKGROUND_IMAGE_KEY, $path);
        }
    }
}
