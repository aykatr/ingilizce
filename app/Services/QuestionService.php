<?php

namespace App\Services;

use App\Repositories\Contracts\QuestionOptionRepositoryInterface;
use App\Repositories\Contracts\QuestionRepositoryInterface;
use App\Services\Exceptions\ValidationException;

class QuestionService
{
    private const POSITIONS = ['A', 'B', 'C', 'D'];

    public function __construct(
        private QuestionRepositoryInterface $questions,
        private QuestionOptionRepositoryInterface $options,
        private MediaUploadService $media,
    ) {
    }

    public function list(): array
    {
        return $this->questions->allWithCategory();
    }

    public function find(int|string $id): ?array
    {
        $question = $this->questions->find($id);

        if (!$question) {
            return null;
        }

        $question['options'] = $this->options->findByQuestion($id);

        return $question;
    }

    public function create(array $data, array $files): array
    {
        $this->validate($data);

        $id = $this->questions->create([
            'category_id' => $data['category_id'],
            'title' => trim($data['title']),
            'question_text' => trim($data['question_text']),
            'duration_seconds' => $this->nullableInt($data['duration_seconds'] ?? ''),
            'points' => $this->nullableInt($data['points'] ?? ''),
            'is_active' => !empty($data['is_active']) ? 1 : 0,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ]);

        $this->syncCardMedia($id, $data, $files, null);

        $optionFiles = $this->normalizeOptionFiles($files);

        foreach (self::POSITIONS as $position) {
            $optionId = $this->options->create([
                'question_id' => $id,
                'position' => $position,
                'title' => trim($data['options'][$position]['title']),
                'is_correct' => ($data['correct_option'] ?? null) === $position ? 1 : 0,
            ]);

            $this->syncOptionMedia($optionId, $position, $data, $optionFiles, null);
        }

        return $this->find($id);
    }

    public function update(int|string $id, array $data, array $files): array
    {
        $existing = $this->find($id);

        if (!$existing) {
            throw new ValidationException('Soru bulunamadı.');
        }

        $this->validate($data);

        $this->questions->update($id, [
            'category_id' => $data['category_id'],
            'title' => trim($data['title']),
            'question_text' => trim($data['question_text']),
            'duration_seconds' => $this->nullableInt($data['duration_seconds'] ?? ''),
            'points' => $this->nullableInt($data['points'] ?? ''),
            'is_active' => !empty($data['is_active']) ? 1 : 0,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ]);

        $this->syncCardMedia($id, $data, $files, $existing);

        $optionFiles = $this->normalizeOptionFiles($files);
        $byPosition = array_column($existing['options'], null, 'position');

        foreach (self::POSITIONS as $position) {
            $option = $byPosition[$position] ?? null;

            if (!$option) {
                continue;
            }

            $this->options->update($option['id'], [
                'title' => trim($data['options'][$position]['title']),
                'is_correct' => ($data['correct_option'] ?? null) === $position ? 1 : 0,
            ]);

            $this->syncOptionMedia($option['id'], $position, $data, $optionFiles, $option);
        }

        return $this->find($id);
    }

    public function delete(int|string $id): void
    {
        $question = $this->find($id);

        if (!$question) {
            return;
        }

        $this->media->delete($question['card_image']);
        $this->media->delete($question['card_audio']);
        $this->media->delete($question['question_audio']);

        foreach ($question['options'] as $option) {
            $this->media->delete($option['image']);
            $this->media->delete($option['audio']);
        }

        $this->questions->delete($id);
    }

    private function validate(array $data): void
    {
        if (trim((string) ($data['title'] ?? '')) === '') {
            throw new ValidationException('Kart başlığı gerekli.');
        }

        if (empty($data['category_id'])) {
            throw new ValidationException('Kategori seçilmeli.');
        }

        if (trim((string) ($data['question_text'] ?? '')) === '') {
            throw new ValidationException('İngilizce soru gerekli.');
        }

        foreach (self::POSITIONS as $position) {
            if (trim((string) ($data['options'][$position]['title'] ?? '')) === '') {
                throw new ValidationException("Seçenek {$position} başlığı gerekli.");
            }
        }

        if (!in_array($data['correct_option'] ?? null, self::POSITIONS, true)) {
            throw new ValidationException('Doğru cevap seçilmeli.');
        }
    }

    private function nullableInt(string $value): ?int
    {
        return $value === '' ? null : (int) $value;
    }

    private function syncCardMedia(int|string $questionId, array $data, array $files, ?array $existing): void
    {
        $directory = "questions/{$questionId}";

        $cardImage = $this->media->handleImage(
            $files['card_image'] ?? null,
            $directory,
            'card',
            !empty($data['remove_card_image']),
            $existing['card_image'] ?? null
        );

        $cardAudio = $this->media->handleAudio(
            $files['card_audio'] ?? null,
            $directory,
            'card-audio',
            !empty($data['remove_card_audio']),
            $existing['card_audio'] ?? null
        );

        $questionAudio = $this->media->handleAudio(
            $files['question_audio'] ?? null,
            $directory,
            'question-audio',
            !empty($data['remove_question_audio']),
            $existing['question_audio'] ?? null
        );

        $this->questions->update($questionId, [
            'card_image' => $cardImage,
            'card_audio' => $cardAudio,
            'question_audio' => $questionAudio,
        ]);
    }

    private function syncOptionMedia(int|string $optionId, string $position, array $data, array $optionFiles, ?array $existing): void
    {
        $directory = "options/{$optionId}";

        $image = $this->media->handleImage(
            $optionFiles[$position]['image'] ?? null,
            $directory,
            'image',
            !empty($data['options'][$position]['remove_image']),
            $existing['image'] ?? null
        );

        $audio = $this->media->handleAudio(
            $optionFiles[$position]['audio'] ?? null,
            $directory,
            'audio',
            !empty($data['options'][$position]['remove_audio']),
            $existing['audio'] ?? null
        );

        $this->options->update($optionId, ['image' => $image, 'audio' => $audio]);
    }

    private function normalizeOptionFiles(array $files): array
    {
        $normalized = [];

        if (!isset($files['options'])) {
            return $normalized;
        }

        foreach (['name', 'type', 'tmp_name', 'error', 'size'] as $attribute) {
            foreach ($files['options'][$attribute] ?? [] as $position => $fields) {
                foreach ($fields as $field => $value) {
                    $normalized[$position][$field][$attribute] = $value;
                }
            }
        }

        return $normalized;
    }
}
