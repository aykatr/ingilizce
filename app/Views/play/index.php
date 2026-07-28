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

                <div class="trust-badges">
                    <span>🛡️ Güvenli</span>
                    <span>🙂 Çocuk Dostu</span>
                    <span>🔒 Gizlilik Koruması</span>
                    <span>🎧 Destek</span>
                </div>

                <div class="game-version">v1.0.0</div>
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
                                    <div class="option-title" id="option-<?= $position ?>-title"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="bottom-row">
                    <button type="button" class="btn-nav" id="btn-prev">← ÖNCEKİ</button>
                    <div class="hearts-pill" id="hearts-pill"></div>
                    <button type="button" class="btn-nav" id="btn-next">SONRAKİ →</button>
                </div>
            </section>

            <section id="screen-result" class="game-screen">
                <div class="result-card">
                    <h1 id="result-heading">Tebrikler!</h1>
                    <div class="result-score" id="result-score">0</div>
                    <div class="result-sub" id="result-sub">Puan Kazandın</div>
                    <button type="button" id="btn-restart" class="btn-pill-primary">🔄 TEKRAR OYNA</button>
                </div>
            </section>

        </div>
    </div>

    <div class="feedback-toast" id="feedback-toast"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
    <script src="<?= asset('js/game-engine.js') ?>"></script>
    <script src="<?= asset('js/game-ui.js') ?>"></script>
</body>
</html>
