<?php

namespace App\Services;

use App\Repositories\Contracts\AchievementMessageRepositoryInterface;
use App\Services\Exceptions\ValidationException;

class AchievementMessageService
{
    public const TYPE_CORRECT = 'correct';
    public const TYPE_WRONG = 'wrong';

    public function __construct(
        private AchievementMessageRepositoryInterface $messages,
        private MediaUploadService $media,
    ) {
    }

    public function list(): array
    {
        return $this->messages->all();
    }

    public function find(int|string $id): ?array
    {
        return $this->messages->find($id);
    }

    /** Belirtilen gruptan (correct|wrong) aktif bir mesajı rastgele seçer. Yoksa null döner. */
    public function pickRandom(string $type): ?array
    {
        $candidates = $this->messages->activeByType($type);

        if (empty($candidates)) {
            return null;
        }

        return $candidates[array_rand($candidates)];
    }

    public function create(array $data, array $files): array
    {
        $this->validate($data);

        $id = $this->messages->create([
            'type' => $data['type'],
            'title' => trim($data['title']),
            'animation_type' => $data['animation_type'] ?: null,
            'is_active' => !empty($data['is_active']) ? 1 : 0,
        ]);

        $this->syncAudio($id, $data, $files, null);

        return $this->find($id);
    }

    public function update(int|string $id, array $data, array $files): array
    {
        $existing = $this->find($id);

        if (!$existing) {
            throw new ValidationException('Mesaj bulunamadı.');
        }

        $this->validate($data);

        $this->messages->update($id, [
            'type' => $data['type'],
            'title' => trim($data['title']),
            'animation_type' => $data['animation_type'] ?: null,
            'is_active' => !empty($data['is_active']) ? 1 : 0,
        ]);

        $this->syncAudio($id, $data, $files, $existing);

        return $this->find($id);
    }

    public function delete(int|string $id): void
    {
        $message = $this->find($id);

        if (!$message) {
            return;
        }

        $this->media->delete($message['audio']);
        $this->messages->delete($id);
    }

    private function validate(array $data): void
    {
        if (trim((string) ($data['title'] ?? '')) === '') {
            throw new ValidationException('Başlık gerekli.');
        }

        if (!in_array($data['type'] ?? null, [self::TYPE_CORRECT, self::TYPE_WRONG], true)) {
            throw new ValidationException('Geçerli bir mesaj tipi seçilmeli.');
        }

        if (!AnimationTypes::isValid($data['animation_type'] ?? null)) {
            throw new ValidationException('Geçersiz animasyon tipi.');
        }
    }

    private function syncAudio(int|string $id, array $data, array $files, ?array $existing): void
    {
        $audio = $this->media->handleAudio(
            $files['audio'] ?? null,
            'messages/' . $id,
            'audio',
            !empty($data['remove_audio']),
            $existing['audio'] ?? null
        );

        $this->messages->update($id, ['audio' => $audio]);
    }
}
