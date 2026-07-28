<?php

namespace App\Services;

use App\Core\Session;

/**
 * Kart Seçim Menüsü'nde gösterilen ziyaret-boyu (kartlar arası kalıcı) ilerlemeyi tutar.
 * Kart bazlı oyun oturumundan (GameSessionService) bilinçli olarak ayrı bir session anahtarı kullanır.
 */
class MenuProgressService
{
    private const SESSION_KEY = 'menu_progress';

    public function snapshot(): array
    {
        $state = $this->state();

        return [
            'totalScore' => $state['total_score'],
            'completedCount' => count($state['completed_card_ids']),
            'completedCardIds' => $state['completed_card_ids'],
            'totalBadges' => count($state['awarded_badge_ids']),
        ];
    }

    public function recordCompletion(int $questionId, int $scoreDelta): void
    {
        $state = $this->state();
        $state['total_score'] += $scoreDelta;

        if (!in_array($questionId, $state['completed_card_ids'], true)) {
            $state['completed_card_ids'][] = $questionId;
        }

        Session::put(self::SESSION_KEY, $state);
    }

    public function alreadyAwardedBadgeIds(): array
    {
        return $this->state()['awarded_badge_ids'];
    }

    public function recordBadgeAwarded(int $badgeId): void
    {
        $state = $this->state();

        if (!in_array($badgeId, $state['awarded_badge_ids'], true)) {
            $state['awarded_badge_ids'][] = $badgeId;
        }

        Session::put(self::SESSION_KEY, $state);
    }

    private function state(): array
    {
        return Session::get(self::SESSION_KEY) ?? [
            'total_score' => 0,
            'completed_card_ids' => [],
            'awarded_badge_ids' => [],
        ];
    }
}
