<?php

namespace App\Services;

use App\Repositories\Contracts\BadgeRepositoryInterface;
use App\Services\Exceptions\ValidationException;

/**
 * Rozet koşulları kod içine sabit yazılmaz: her rozet DB'de bir
 * `condition_type` (+ opsiyonel `condition_value`) taşır, bu sınıf o tipe
 * karşılık gelen bir değerlendirici (evaluator) çalıştırır. Yeni bir koşul
 * türü eklemek için CONDITION_LABELS'e bir satır ve constructor'daki
 * $evaluators haritasına bir closure eklemek yeterlidir — mevcut rozetler
 * ve GameSessionService entegrasyonu değişmeden kalır.
 */
class BadgeService
{
    public const CONDITION_FIRST_CORRECT = 'first_correct';
    public const CONDITION_CORRECT_COUNT = 'correct_count';
    public const CONDITION_SCORE_REACHED = 'score_reached';
    public const CONDITION_FLAWLESS_COMPLETION = 'flawless_completion';
    public const CONDITION_NO_TIMEOUT_COMPLETION = 'no_timeout_completion';

    public const CONDITION_LABELS = [
        self::CONDITION_FIRST_CORRECT => 'İlk doğru cevap',
        self::CONDITION_CORRECT_COUNT => 'Belirli sayıda doğru cevap (Koşul Değeri: adet)',
        self::CONDITION_SCORE_REACHED => 'Belirli puana ulaşma (Koşul Değeri: puan)',
        self::CONDITION_FLAWLESS_COMPLETION => 'Hatasız tamamlama',
        self::CONDITION_NO_TIMEOUT_COMPLETION => 'Süre dolmadan tamamlama',
    ];

    private const VALUE_REQUIRED = [self::CONDITION_CORRECT_COUNT, self::CONDITION_SCORE_REACHED];

    /** @var array<string, callable(array, ?int): bool> */
    private array $evaluators;

    public function __construct(
        private BadgeRepositoryInterface $badges,
        private MediaUploadService $media,
    ) {
        $this->evaluators = [
            self::CONDITION_FIRST_CORRECT => static fn (array $c, ?int $v) => $c['correctCount'] === 1,
            self::CONDITION_CORRECT_COUNT => static fn (array $c, ?int $v) => $v !== null && $c['correctCount'] >= $v,
            self::CONDITION_SCORE_REACHED => static fn (array $c, ?int $v) => $v !== null && $c['score'] >= $v,
            self::CONDITION_FLAWLESS_COMPLETION => static fn (array $c, ?int $v) => $c['isFinished'] && !$c['gameOver'] && $c['lives'] === $c['maxLives'],
            self::CONDITION_NO_TIMEOUT_COMPLETION => static fn (array $c, ?int $v) => $c['isFinished'] && !$c['gameOver'] && !$c['timeoutOccurred'],
        ];
    }

    public function list(): array
    {
        return $this->badges->all();
    }

    public function find(int|string $id): ?array
    {
        return $this->badges->find($id);
    }

    public function conditionTypes(): array
    {
        return self::CONDITION_LABELS;
    }

    /**
     * @param array{correctCount:int,score:int,lives:int,maxLives:int,timeoutOccurred:bool,isFinished:bool,gameOver:bool} $context
     * @param int[] $alreadyAwardedIds bu oyun oturumunda daha önce verilmiş rozet id'leri
     * @return array yeni kazanılan rozet satırları
     */
    public function evaluateNewlyEarned(array $context, array $alreadyAwardedIds): array
    {
        $earned = [];

        foreach ($this->badges->activeBadges() as $badge) {
            if (in_array((int) $badge['id'], $alreadyAwardedIds, true)) {
                continue;
            }

            $evaluator = $this->evaluators[$badge['condition_type']] ?? null;

            if (!$evaluator) {
                continue;
            }

            $value = $badge['condition_value'] !== null ? (int) $badge['condition_value'] : null;

            if ($evaluator($context, $value)) {
                $earned[] = $badge;
            }
        }

        return $earned;
    }

    public function create(array $data, array $files): array
    {
        $this->validate($data);

        $id = $this->badges->create([
            'title' => trim($data['title']),
            'description' => $this->normalizedText($data['description'] ?? ''),
            'animation_type' => $data['animation_type'] ?: null,
            'condition_type' => $data['condition_type'],
            'condition_value' => $this->normalizedValue($data),
            'is_active' => !empty($data['is_active']) ? 1 : 0,
        ]);

        $this->syncMedia($id, $data, $files, null);

        return $this->find($id);
    }

    public function update(int|string $id, array $data, array $files): array
    {
        $existing = $this->find($id);

        if (!$existing) {
            throw new ValidationException('Rozet bulunamadı.');
        }

        $this->validate($data);

        $this->badges->update($id, [
            'title' => trim($data['title']),
            'description' => $this->normalizedText($data['description'] ?? ''),
            'animation_type' => $data['animation_type'] ?: null,
            'condition_type' => $data['condition_type'],
            'condition_value' => $this->normalizedValue($data),
            'is_active' => !empty($data['is_active']) ? 1 : 0,
        ]);

        $this->syncMedia($id, $data, $files, $existing);

        return $this->find($id);
    }

    public function delete(int|string $id): void
    {
        $badge = $this->find($id);

        if (!$badge) {
            return;
        }

        $this->media->delete($badge['image']);
        $this->media->delete($badge['audio']);
        $this->badges->delete($id);
    }

    private function validate(array $data): void
    {
        if (trim((string) ($data['title'] ?? '')) === '') {
            throw new ValidationException('Rozet başlığı gerekli.');
        }

        $conditionType = $data['condition_type'] ?? null;

        if (!array_key_exists($conditionType, self::CONDITION_LABELS)) {
            throw new ValidationException('Geçerli bir koşul tipi seçilmeli.');
        }

        $value = trim((string) ($data['condition_value'] ?? ''));

        if (in_array($conditionType, self::VALUE_REQUIRED, true) && ($value === '' || (int) $value < 1)) {
            throw new ValidationException('Bu koşul için geçerli bir sayısal değer girilmeli.');
        }

        if (!AnimationTypes::isValid($data['animation_type'] ?? null)) {
            throw new ValidationException('Geçersiz animasyon tipi.');
        }
    }

    private function normalizedValue(array $data): ?int
    {
        $value = trim((string) ($data['condition_value'] ?? ''));

        return $value === '' ? null : (int) $value;
    }

    private function normalizedText(string $value): ?string
    {
        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private function syncMedia(int|string $id, array $data, array $files, ?array $existing): void
    {
        $directory = 'badges/' . $id;

        $image = $this->media->handleImage(
            $files['image'] ?? null,
            $directory,
            'image',
            !empty($data['remove_image']),
            $existing['image'] ?? null
        );

        $audio = $this->media->handleAudio(
            $files['audio'] ?? null,
            $directory,
            'audio',
            !empty($data['remove_audio']),
            $existing['audio'] ?? null
        );

        $this->badges->update($id, ['image' => $image, 'audio' => $audio]);
    }
}
