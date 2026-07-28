<?php

namespace App\Services;

use App\Repositories\Contracts\TransitionMessageRepositoryInterface;
use App\Services\Exceptions\ValidationException;

class TransitionMessageService
{
    public function __construct(
        private TransitionMessageRepositoryInterface $messages,
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

    /** Aktif geçiş mesajlarından birini rastgele seçer. Hiçbiri yoksa null döner. */
    public function pickRandom(): ?array
    {
        $candidates = $this->messages->activeMessages();

        if (empty($candidates)) {
            return null;
        }

        return $candidates[array_rand($candidates)];
    }

    public function create(array $data, array $files): array
    {
        $this->validate($data);

        $id = $this->messages->create([
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

        if (!AnimationTypes::isValid($data['animation_type'] ?? null)) {
            throw new ValidationException('Geçersiz animasyon tipi.');
        }
    }

    private function syncAudio(int|string $id, array $data, array $files, ?array $existing): void
    {
        $audio = $this->media->handleAudio(
            $files['audio'] ?? null,
            'transition-messages/' . $id,
            'audio',
            !empty($data['remove_audio']),
            $existing['audio'] ?? null
        );

        $this->messages->update($id, ['audio' => $audio]);
    }
}
