<?php

namespace App\Services;

/**
 * Başarı mesajları ve rozetler için ortak animasyon kataloğu. Yeni bir tip
 * eklemek için buraya bir satır eklemek yeterli; frontend (game-ui.js)
 * `#ANIMATIONS` haritasında aynı anahtarla karşılığını tanımlamalı.
 */
class AnimationTypes
{
    public const OPTIONS = [
        'bounce' => 'Zıplama',
        'pulse' => 'Nabız',
        'shake' => 'Sallanma',
        'pop' => 'Patlama',
        'fade' => 'Belirme',
    ];

    public static function isValid(?string $type): bool
    {
        return $type === null || $type === '' || array_key_exists($type, self::OPTIONS);
    }

    public static function default(): string
    {
        return 'pop';
    }
}
