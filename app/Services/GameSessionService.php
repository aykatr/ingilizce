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
        private AchievementMessageService $messages,
        private BadgeService $badges,
        private TransitionMessageService $transitions,
        private MenuProgressService $menuProgress,
    ) {
    }

    /**
     * Kart Seçim Menüsü'nden seçilen tek bir kartın (sorunun) oturumunu başlatır.
     * Can her kart için bağımsızdır (sıfırdan başlar); skor/rozet dedup ise
     * MenuProgressService üzerinden ziyaret boyunca kartlar arası kalıcıdır.
     */
    public function start(int $questionId): array
    {
        $question = $this->questions->find($questionId);

        if (!$question || (int) $question['is_active'] !== 1) {
            throw new ValidationException('Kart bulunamadı veya artık aktif değil.');
        }

        $lives = $this->settings->getDefaultLives();

        Session::put(self::SESSION_KEY, [
            'question_ids' => [(int) $questionId],
            'index' => 0,
            'score' => 0,
            'lives' => $lives,
            'max_lives' => $lives,
            'attempted' => [],
            'game_over' => false,
            'timeout_occurred' => false,
        ]);

        return array_merge($this->currentPayload(), [
            'menuProgress' => $this->menuProgress->snapshot(),
        ]);
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

        if ($isTimeout) {
            $state['timeout_occurred'] = true;
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

            $isFinished = $state['index'] >= $total;

            if ($isFinished) {
                $this->menuProgress->recordCompletion($questionId, $points);
            }

            // recordCompletion() az önce çağrıldı — rozet bağlamındaki correctCount/score artık
            // bu kartın tamamlanmasını da içeren ziyaret-boyu (kartlar arası kalıcı) toplamı yansıtır.
            $earnedBadges = $this->evaluateBadges($state, $isFinished);

            Session::put(self::SESSION_KEY, $state);

            return [
                'correct' => true,
                'scoreDelta' => $points,
                'score' => $state['score'],
                'lives' => max(0, $state['lives']),
                'maxLives' => $state['max_lives'],
                'correctPosition' => $correctOption['position'],
                'gameOver' => false,
                'message' => $this->formatMessage($this->messages->pickRandom(AchievementMessageService::TYPE_CORRECT)),
                'transitionMessage' => $isFinished ? null : $this->formatMessage($this->transitions->pickRandom()),
                'earnedBadges' => array_map($this->formatBadge(...), $earnedBadges),
                'menuProgress' => $this->menuProgress->snapshot(),
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

        $earnedBadges = $this->evaluateBadges($state, $state['game_over']);

        Session::put(self::SESSION_KEY, $state);

        return [
            'correct' => false,
            'scoreDelta' => 0,
            'score' => $state['score'],
            'lives' => max(0, $state['lives']),
            'maxLives' => $state['max_lives'],
            'attempted' => $state['attempted'],
            'gameOver' => $state['game_over'],
            'message' => $this->formatMessage($this->messages->pickRandom(AchievementMessageService::TYPE_WRONG)),
            'earnedBadges' => array_map($this->formatBadge(...), $earnedBadges),
            'menuProgress' => $this->menuProgress->snapshot(),
            'next' => null,
        ];
    }

    /**
     * @return array newly earned badge rows (raw, unformatted)
     *
     * correctCount/score kasıtlı olarak MenuProgressService'ten (ziyaret boyunca kartlar arası
     * kalıcı) okunur, $state'in kart-bazlı sayaçlarından değil — aksi halde "N doğru cevap"/"X
     * puana ulaşma" gibi koşullar tek soruluk kart oturumunda asla sağlanamazdı (bkz. Kart Seçim
     * Menüsü revizyonu). lives/maxLives/timeoutOccurred bilinçli olarak kart-bazlı kalır: "hatasız
     * tamamlama" artık "bu kartı ilk denemede bitirme" anlamına gelir.
     */
    private function evaluateBadges(array &$state, bool $isFinished): array
    {
        $progress = $this->menuProgress->snapshot();

        $context = [
            'correctCount' => $progress['completedCount'],
            'score' => $progress['totalScore'],
            'lives' => max(0, $state['lives']),
            'maxLives' => $state['max_lives'],
            'timeoutOccurred' => $state['timeout_occurred'],
            'isFinished' => $isFinished,
            'gameOver' => $state['game_over'],
        ];

        $earned = $this->badges->evaluateNewlyEarned($context, $this->menuProgress->alreadyAwardedBadgeIds());

        foreach ($earned as $badge) {
            $this->menuProgress->recordBadgeAwarded((int) $badge['id']);
        }

        return $earned;
    }

    private function formatMessage(?array $message): ?array
    {
        if (!$message) {
            return null;
        }

        return [
            'title' => $message['title'],
            'audio' => $this->mediaUrl($message['audio']),
            'animationType' => $message['animation_type'],
        ];
    }

    private function formatBadge(array $badge): array
    {
        return [
            'id' => (int) $badge['id'],
            'title' => $badge['title'],
            'description' => $badge['description'],
            'image' => $this->mediaUrl($badge['image']),
            'audio' => $this->mediaUrl($badge['audio']),
            'animationType' => $badge['animation_type'],
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
