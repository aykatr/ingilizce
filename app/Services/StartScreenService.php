<?php

namespace App\Services;

use App\Repositories\Contracts\SettingRepositoryInterface;

class StartScreenService
{
    private const IMAGE_FIELDS = [
        'background_image' => 'start_background_image',
        'logo_image' => 'start_logo_image',
        'mascot_left_image' => 'start_mascot_left_image',
        'mascot_right_image' => 'start_mascot_right_image',
        'robot_image' => 'start_robot_image',
        'rocket_image' => 'start_rocket_image',
        'balloon_image' => 'start_balloon_image',
        'decor_image_1' => 'start_decor_image_1',
        'decor_image_2' => 'start_decor_image_2',
        'decor_image_3' => 'start_decor_image_3',
    ];

    private const TEXT_FIELDS = [
        'title' => ['key' => 'start_title', 'default' => 'Hoş Geldin!'],
        'description' => ['key' => 'start_description', 'default' => 'Kartını okut, öğrenmeye eğlenceli bir yolculukla başla!'],
        'button_text' => ['key' => 'start_button_text', 'default' => 'BAŞLA'],
    ];

    public function __construct(
        private SettingRepositoryInterface $settings,
        private MediaUploadService $media,
    ) {
    }

    public function get(): array
    {
        $result = [];

        foreach (self::IMAGE_FIELDS as $field => $key) {
            $result[$field] = $this->settings->get($key);
        }

        foreach (self::TEXT_FIELDS as $field => $meta) {
            $result[$field] = $this->settings->get($meta['key']) ?? $meta['default'];
        }

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

        foreach (self::IMAGE_FIELDS as $field => $key) {
            $current = $this->settings->get($key);
            $remove = !empty($data['remove_' . $field]);

            $path = $this->media->handleImage($files[$field] ?? null, 'start-screen', $field, $remove, $current);

            if ($path === null) {
                $this->settings->delete($key);
            } elseif ($path !== $current) {
                $this->settings->set($key, $path);
            }
        }
    }
}
