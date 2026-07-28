<!doctype html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title><?= e($startScreen['title']) ?></title>
    <meta name="csrf-token" content="<?= e($csrfToken) ?>">
    <meta name="start-total-questions" content="<?= e((string) $totalQuestions) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@500;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/game.css') ?>">
</head>
<body class="game-body">
    <div class="game-scene">
        <div class="game-scene-content">

            <section id="screen-start" class="game-screen is-active">
                <div class="top-bar">
                    <button type="button" class="icon-btn" id="btn-mute-start" aria-label="Ses">🔊</button>
                    <button type="button" class="icon-btn" id="btn-info" aria-label="Bilgi">ℹ️</button>
                </div>

                <?php if (!empty($startScreen['mascot_left_image'])): ?>
                    <img src="<?= e(base_url($startScreen['mascot_left_image'])) ?>" class="mascot mascot-left" alt="">
                <?php endif; ?>
                <?php if (!empty($startScreen['mascot_right_image'])): ?>
                    <img src="<?= e(base_url($startScreen['mascot_right_image'])) ?>" class="mascot mascot-right" alt="">
                <?php endif; ?>
                <?php if (!empty($startScreen['rocket_image'])): ?>
                    <img src="<?= e(base_url($startScreen['rocket_image'])) ?>" class="decor-float decor-rocket" alt="">
                <?php endif; ?>
                <?php if (!empty($startScreen['balloon_image'])): ?>
                    <img src="<?= e(base_url($startScreen['balloon_image'])) ?>" class="decor-float decor-balloon" alt="">
                <?php endif; ?>
                <?php if (!empty($startScreen['robot_image'])): ?>
                    <img src="<?= e(base_url($startScreen['robot_image'])) ?>" class="decor-float decor-robot" alt="">
                <?php endif; ?>
                <?php foreach (['decor_image_1', 'decor_image_2', 'decor_image_3'] as $decorField): ?>
                    <?php if (!empty($startScreen[$decorField])): ?>
                        <img src="<?= e(base_url($startScreen[$decorField])) ?>" class="decor-float" alt="">
                    <?php endif; ?>
                <?php endforeach; ?>

                <div class="game-logo-wrap">
                    <?php if (!empty($startScreen['logo_image'])): ?>
                        <img src="<?= e(base_url($startScreen['logo_image'])) ?>" class="game-logo-img" alt="<?= e(config('app.name')) ?>">
                    <?php else: ?>
                        <div class="game-logo-fallback">YIPPEE!</div>
                        <div class="game-logo-subtitle">AKILLI KART OYUNU</div>
                    <?php endif; ?>
                </div>

                <p class="game-tagline"><?= e($startScreen['description']) ?></p>

                <div class="welcome-card">
                    <h1><?= e($startScreen['title']) ?></h1>
                    <div class="welcome-divider"></div>
                    <div class="welcome-stat">⭐ <span id="start-total-label"><?= e((string) $totalQuestions) ?></span> Soru Seni Bekliyor!</div>
                    <div>
                        <button type="button" id="btn-start-game" class="btn-pill-primary">▶ <?= e($startScreen['button_text']) ?></button>
                    </div>
                </div>

                <!-- QR paneli yalnızca görsel amaçlıdır; tarama/kamera/doğrulama işlemi yapmaz. Gerçek giriş play.php?t=TOKEN ile zaten tamamlanmıştır. -->
                <div class="qr-panel">
                    <div class="qr-tabs">
                        <button type="button" class="qr-tab is-active" data-qr-tab="qr">📷 QR KODU İLE GİRİŞ</button>
                        <button type="button" class="qr-tab" data-qr-tab="license">🔒 LİSANS KODU İLE GİRİŞ</button>
                    </div>
                    <div class="qr-tab-panel" data-qr-panel="qr">
                        <div class="qr-frame">
                            <?php if (!empty($startScreen['qr_image'])): ?>
                                <img src="<?= e(base_url($startScreen['qr_image'])) ?>" alt="" class="qr-image">
                            <?php else: ?>
                                <div class="qr-placeholder">▦</div>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="btn-pill-primary qr-camera-btn">📷 KAMERAYI AÇ</button>
                        <p class="qr-caption">Kartını okutmak için kamerayı kullan.</p>
                    </div>
                    <div class="qr-tab-panel is-hidden" data-qr-panel="license">
                        <input type="text" class="form-control qr-license-input" placeholder="Lisans kodunu gir (ör. K3F9-8H2M-QW7X)" disabled>
                        <button type="button" class="btn-pill-primary qr-license-btn" disabled>DOĞRULA</button>
                    </div>
                </div>

                <div class="how-it-works">
                    <h2 class="how-it-works-title">NASIL ÇALIŞIR?</h2>
                    <div class="how-it-works-steps">
                        <div class="how-step">
                            <span class="how-step-icon">🃏</span>
                            <div class="how-step-title">Kartını hazırla</div>
                            <div class="how-step-desc">Akıllı kartındaki QR kodunu bul.</div>
                        </div>
                        <div class="how-step">
                            <span class="how-step-icon">📱</span>
                            <div class="how-step-title">QR kodu okut</div>
                            <div class="how-step-desc">Kamerayı aç ve QR kodu okut.</div>
                        </div>
                        <div class="how-step">
                            <span class="how-step-icon">🎮</span>
                            <div class="how-step-title">Oyuna başla</div>
                            <div class="how-step-desc">Kartındaki içeriklerle hemen oynamaya başla!</div>
                        </div>
                    </div>
                </div>

                <div class="trust-badges">
                    <span>🛡️ Güvenli</span>
                    <span>🙂 Çocuk Dostu</span>
                    <span>🔒 Gizlilik Koruması</span>
                    <span>🎧 Destek</span>
                </div>

                <div class="game-version">v1.0.0</div>
            </section>

            <?php
                $menuCardPalette = [
                    ['bg' => '#ede6fa', 'accent' => '#7c4dbd', 'title' => '#6b3fa0'],
                    ['bg' => '#fce1ec', 'accent' => '#e14f86', 'title' => '#d6336c'],
                    ['bg' => '#dceefb', 'accent' => '#2f8fd1', 'title' => '#1971c2'],
                    ['bg' => '#fcf3d9', 'accent' => '#d9a404', 'title' => '#b8860b'],
                    ['bg' => '#e4f3de', 'accent' => '#58a854', 'title' => '#3f8f3b'],
                    ['bg' => '#e3e7fb', 'accent' => '#5c6fd1', 'title' => '#4453b8'],
                    ['bg' => '#fce7d6', 'accent' => '#e8823a', 'title' => '#d9660a'],
                    ['bg' => '#dff3f0', 'accent' => '#319795', 'title' => '#1f7a73'],
                ];
                $menuTotalCards = count($menuCards);
                $menuProgressPct = $menuTotalCards > 0 ? (int) round($menuProgress['completedCount'] / $menuTotalCards * 100) : 0;
            ?>
            <section id="screen-menu" class="game-screen<?= !empty($menuSettings['menu_background_image']) ? ' has-menu-bg' : '' ?>"<?= !empty($menuSettings['menu_background_image']) ? ' style="background-image: url(' . e(base_url($menuSettings['menu_background_image'])) . ');"' : '' ?>>
                <div class="menu-header">
                    <button type="button" class="menu-icon-btn" id="btn-home-menu">
                        <span class="menu-icon-circle menu-icon-home">🏠</span>
                        <span class="menu-icon-label">ANA SAYFA</span>
                    </button>

                    <div class="menu-header-title">
                        <h1 class="menu-title"><?= e($menuSettings['menu_title']) ?></h1>
                        <?php if ($menuSettings['menu_description'] !== ''): ?>
                            <p class="menu-description"><?= e($menuSettings['menu_description']) ?></p>
                        <?php endif; ?>
                    </div>

                    <button type="button" class="menu-icon-btn" id="btn-mute-menu">
                        <span class="menu-icon-circle menu-icon-mute">🔊</span>
                        <span class="menu-icon-label" id="menu-mute-label">SES AÇIK</span>
                    </button>
                </div>

                <?php if (empty($menuCards)): ?>
                    <p class="text-center">Henüz aktif kart yok.</p>
                <?php else: ?>
                    <div class="menu-panel">
                        <div class="menu-grid" style="--menu-columns: <?= (int) $menuSettings['menu_columns'] ?>; --menu-gap: <?= (int) $menuSettings['menu_card_gap'] ?>px; --menu-card-size: <?= (int) $menuSettings['menu_card_size'] ?>px; --menu-radius: <?= (int) $menuSettings['menu_card_radius'] ?>px;">
                            <?php foreach ($menuCards as $cardIndex => $card): ?>
                                <?php
                                    $cardTheme = $menuCardPalette[$cardIndex % count($menuCardPalette)];
                                    $cardCompleted = in_array((int) $card['id'], $menuProgress['completedCardIds'], true);
                                ?>
                                <button type="button" class="menu-card<?= $cardCompleted ? ' is-completed' : '' ?>" data-question-id="<?= (int) $card['id'] ?>" style="--card-bg: <?= e($cardTheme['bg']) ?>; --card-accent: <?= e($cardTheme['accent']) ?>; --card-title: <?= e($cardTheme['title']) ?>;">
                                    <span class="menu-card-check">✓</span>
                                    <div class="menu-card-title"><?= e($card['title']) ?></div>
                                    <div class="menu-card-image-frame">
                                        <?php if (!empty($card['card_image'])): ?>
                                            <img src="<?= e(base_url($card['card_image'])) ?>" alt="" class="menu-card-image">
                                        <?php else: ?>
                                            <div class="image-placeholder">🖼️</div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="menu-card-stars">
                                        <span class="star">★</span><span class="star">★</span><span class="star">★</span>
                                    </div>
                                    <div class="menu-card-status">
                                        <span class="status-not-started">BAŞLAMADIN</span>
                                        <span class="status-completed">✓ TAMAMLANDI</span>
                                    </div>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="menu-stats-bar">
                    <div class="menu-stats-item">
                        <span class="menu-stats-icon">🏆</span>
                        <div>
                            <div class="menu-stats-label">TOPLAM PUAN</div>
                            <div class="menu-stats-value" id="menu-total-score"><?= e((string) $menuProgress['totalScore']) ?></div>
                        </div>
                    </div>
                    <div class="menu-stats-item">
                        <span class="menu-stats-icon">⭐</span>
                        <div>
                            <div class="menu-stats-label">TAMAMLANAN KART</div>
                            <div class="menu-stats-value"><span id="menu-completed-count"><?= e((string) $menuProgress['completedCount']) ?></span> / <span id="menu-total-cards"><?= e((string) $menuTotalCards) ?></span></div>
                        </div>
                    </div>
                    <div class="menu-stats-item">
                        <span class="menu-stats-icon">🛡️</span>
                        <div>
                            <div class="menu-stats-label">ROZETLER</div>
                            <div class="menu-stats-value" id="menu-total-badges"><?= e((string) $menuProgress['totalBadges']) ?></div>
                        </div>
                    </div>
                    <div class="menu-progress-block">
                        <div class="menu-progress-header">
                            <span>İLERLEMEM</span>
                            <span class="menu-progress-pct" id="menu-progress-pct"><?= $menuProgressPct ?>%</span>
                        </div>
                        <div class="menu-progress-track">
                            <div class="menu-progress-fill" id="menu-progress-fill" style="width: <?= $menuProgressPct ?>%;"></div>
                        </div>
                        <div class="menu-progress-caption"><span id="menu-progress-caption-count"><?= e((string) $menuProgress['completedCount']) ?> / <?= e((string) $menuTotalCards) ?></span> kart tamamlandı</div>
                    </div>
                </div>

                <p class="menu-footer-tagline">★ Her kart yeni bir macera, her doğru cevap bir yıldız! ★</p>
            </section>

            <section id="screen-game" class="game-screen">
                <div class="top-bar">
                    <button type="button" class="icon-btn" id="btn-home" aria-label="Ana Sayfa">🏠</button>
                    <div class="game-logo-wrap mb-0">
                        <?php if (!empty($startScreen['logo_image'])): ?>
                            <img src="<?= e(base_url($startScreen['logo_image'])) ?>" class="game-logo-img" style="max-width:140px;" alt="">
                        <?php else: ?>
                            <div class="game-logo-fallback" style="font-size:1.4rem;">YIPPEE!</div>
                        <?php endif; ?>
                    </div>
                    <button type="button" class="icon-btn" id="btn-mute-game" aria-label="Ses">🔊</button>
                </div>

                <div class="status-row">
                    <div class="progress-pill">
                        <div class="progress-pill-fill" id="progress-fill"></div>
                        <div class="progress-pill-label"><span id="progress-label">Soru 1 / 1</span></div>
                    </div>
                    <div class="score-badge">⭐ <span id="score-label">0</span></div>
                </div>

                <div class="question-card">
                    <div class="timer-badge timer-badge-corner" id="timer-badge">30</div>
                    <div class="question-text" id="question-text">-</div>
                    <button type="button" class="question-audio-btn" id="btn-question-audio">
                        <span class="icon-btn">🔊</span>
                        <span class="question-audio-caption">Dinlemek için tıkla</span>
                    </button>
                    <div class="card-image-frame">
                        <img id="card-image" src="" alt="" style="display:none;">
                        <div class="image-placeholder" id="card-image-placeholder">🖼️</div>
                    </div>
                </div>

                <div class="options-grid">
                    <div class="row g-3">
                        <?php foreach (['A', 'B', 'C', 'D'] as $position): ?>
                            <div class="col-6">
                                <div class="option-card" data-position="<?= $position ?>" id="option-<?= $position ?>">
                                    <span class="option-badge option-badge-<?= $position ?>"><?= $position ?></span>
                                    <button type="button" class="icon-btn option-audio-btn" data-audio-position="<?= $position ?>" aria-label="Sesi dinle">🔊</button>
                                    <img class="option-image" id="option-<?= $position ?>-image" src="" alt="" style="display:none;">
                                    <div class="option-image-placeholder image-placeholder" id="option-<?= $position ?>-placeholder">🖼️</div>
                                    <div class="option-title" id="option-<?= $position ?>-title"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="bottom-row">
                    <button type="button" class="btn-nav" id="btn-bottom-menu">🏠 ANA MENÜ</button>
                    <div class="hearts-pill" id="hearts-pill"></div>
                    <button type="button" class="btn-nav" id="btn-bottom-replay">🔄 TEKRAR OYNA</button>
                </div>
            </section>

            <section id="screen-result" class="game-screen">
                <div class="result-card">
                    <h1 id="result-heading">Tebrikler!</h1>
                    <div class="result-score" id="result-score">0</div>
                    <div class="result-sub" id="result-sub">Puan Kazandın</div>
                    <div class="result-badges" id="result-badges"></div>
                    <button type="button" id="btn-restart" class="btn-pill-primary">🔄 TEKRAR OYNA</button>
                </div>
            </section>

        </div>
    </div>

    <div class="feedback-toast" id="feedback-toast"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/howler@2.2.4/dist/howler.min.js"></script>
    <script src="<?= asset('js/audio-manager.js') ?>"></script>
    <script src="<?= asset('js/game-engine.js') ?>"></script>
    <script src="<?= asset('js/game-ui.js') ?>"></script>
</body>
</html>
