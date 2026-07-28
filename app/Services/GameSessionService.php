<?php

namespace App\Services;

use App\Core\Session;
use App\Repositories\Contracts\QuestionOptionRepositoryInterface;
use App\Repositories\Contracts\QuestionRepositoryInterface;
use App\Services\Exceptions\ValidationException;

class GameSessionService
{
    private const SESSION_KEY = 'game';

    public function __construct(
        private QuestionRepositoryInterface $questions,
        private QuestionOptionRepositoryInterface $options,
        private SettingService $settings,
    ) {
    }

    public function start(): array
    {
        $active = array_values(array_filter(
            $this->questions->allWithCategory(),
            fn (array $q) => (int) $q['is_active'] === 1
        ));

        usort($active, function (array $a, array $b) {
            return $a['sort_order'] <=> $b['sort_order'] ?: $a['id'] <=> $b['id'];
        });

        $ids = array_map(fn (array $q) => (int) $q['id'], $active);
        $lives = $this->settings->getDefaultLives();

        Session::put(self::SESSION_KEY, [
            'question_ids' => $ids,
            'index' => 0,
            'score' => 0,
            'lives' => $lives,
            'max_lives' => $lives,
            'attempted' => [],
            'game_over' => false,
        ]);

        return $this->currentPayload();
    }

    public function answer(?string $position, bool $isTimeout = false): array
    {
        $state = $this->requireState();

        if ($state['game_over']) {
            throw new ValidationException('Oyun sona erdi.');
        }

        $index = $state['index'];
        $total = count($state['question_ids']);

        if ($index >= $total) {
            throw new ValidationException('Oynanacak soru kalmadı.');
        }

        $questionId = $state['question_ids'][$index];
        $options = $this->options->findByQuestion($questionId);
        $correctOption = null;

        foreach ($options as $option) {
            if ((int) $option['is_correct'] === 1) {
                $correctOption = $option;
                break;
            }
        }

        $isCorrect = !$isTimeout && $position !== null && $correctOption && $correctOption['position'] === $position;

        if ($isCorrect) {
            $question = $this->questions->find($questionId);
            $points = $question['points'] !== null ? (int) $question['points'] : $this->settings->getDefaultPoints();

            $state['score'] += $points;
            $state['index']++;
            $state['attempted'] = [];

            Session::put(self::SESSION_KEY, $state);

            return [
                'correct' => true,
                'scoreDelta' => $points,
                'score' => $state['score'],
                'lives' => max(0, $state['lives']),
                'maxLives' => $state['max_lives'],
                'correctPosition' => $correctOption['position'],
                'gameOver' => false,
                'next' => $this->currentPayload(),
            ];
        }

        if (!$isTimeout && $position !== null) {
            $state['attempted'][] = $position;
        }

        $state['lives']--;

        if ($state['lives'] <= 0) {
            $state['game_over'] = true;
        }

        Session::put(self::SESSION_KEY, $state);

        return [
            'correct' => false,
            'scoreDelta' => 0,
            'score' => $state['score'],
            'lives' => max(0, $state['lives']),
            'maxLives' => $state['max_lives'],
            'attempted' => $state['attempted'],
            'gameOver' => $state['game_over'],
            'next' => null,
        ];
    }

    private function currentPayload(): array
    {
        $state = $this->requireState();
        $index = $state['index'];
        $total = count($state['question_ids']);

        if ($state['game_over'] || $index >= $total) {
            return [
                'finished' => true,
                'gameOver' => $state['game_over'],
                'score' => $state['score'],
                'lives' => max(0, $state['lives']),
                'maxLives' => $state['max_lives'],
                'total' => $total,
                'completed' => $index,
            ];
        }

        $questionId = $state['question_ids'][$index];
        $question = $this->questions->find($questionId);
        $options = $this->options->findByQuestion($questionId);

        return [
            'finished' => false,
            'index' => $index,
            'total' => $total,
            'score' => $state['score'],
            'lives' => max(0, $state['lives']),
            'maxLives' => $state['max_lives'],
            'attempted' => $state['attempted'],
            'question' => [
                'title' => $question['title'],
                'cardImage' => $this->mediaUrl($question['card_image']),
                'cardAudio' => $this->mediaUrl($question['card_audio']),
                'questionText' => $question['question_text'],
                'questionAudio' => $this->mediaUrl($question['question_audio']),
                'duration' => $question['duration_seconds'] !== null
                    ? (int) $question['duration_seconds']
                    : $this->settings->getDefaultDuration(),
                'options' => array_map(fn (array $o) => [
                    'position' => $o['position'],
                    'title' => $o['title'],
                    'image' => $this->mediaUrl($o['image']),
                    'audio' => $this->mediaUrl($o['audio']),
                ], $options),
            ],
        ];
    }

    private function mediaUrl(?string $path): ?string
    {
        return $path ? base_url($path) : null;
    }

    private function requireState(): array
    {
        $state = Session::get(self::SESSION_KEY);

        if (!$state) {
            throw new ValidationException('Oyun oturumu bulunamadı. Lütfen tekrar başlatın.');
        }

        return $state;
    }
}
