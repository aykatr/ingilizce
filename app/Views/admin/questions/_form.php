<?php
$q = $question ?? [];
$options = $q['options'] ?? [];
$byPosition = [];
foreach ($options as $option) {
    $byPosition[$option['position']] = $option;
}
$formAction = $isEdit ? base_url('admin/questions/' . $q['id']) : base_url('admin/questions');
?>

<h1 class="h4 mb-3"><?= $isEdit ? 'Soru Düzenle' : 'Yeni Soru' ?></h1>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<?php if ($isEdit && !empty($success)): ?>
    <div class="alert alert-success"><?= e($success) ?></div>
<?php endif; ?>

<form method="POST" action="<?= $formAction ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-general" type="button" role="tab">Genel Bilgiler</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-card" type="button" role="tab">Kart</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-question" type="button" role="tab">Soru</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-options" type="button" role="tab">Seçenekler</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-preview" type="button" role="tab">Önizleme</button>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="tab-general" role="tabpanel">
            <div class="card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="title" class="form-label">Kart Başlığı</label>
                            <input type="text" id="title" name="title" class="form-control" value="<?= e($q['title'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="category_id" class="form-label">Kategori</label>
                            <select id="category_id" name="category_id" class="form-select" required>
                                <option value="">Seçiniz</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= e((string) $category['id']) ?>" <?= (isset($q['category_id']) && (int) $q['category_id'] === (int) $category['id']) ? 'selected' : '' ?>>
                                        <?= e($category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="duration_seconds" class="form-label">Süre (saniye)</label>
                            <input type="number" id="duration_seconds" name="duration_seconds" class="form-control" min="1" placeholder="Varsayılan: <?= e((string) $defaultDuration) ?>" value="<?= e(isset($q['duration_seconds']) && $q['duration_seconds'] !== null ? (string) $q['duration_seconds'] : '') ?>">
                            <div class="form-text">Boş bırakılırsa Site Ayarları'ndaki varsayılan kullanılır.</div>
                        </div>
                        <div class="col-md-3">
                            <label for="points" class="form-label">Puan</label>
                            <input type="number" id="points" name="points" class="form-control" min="0" placeholder="Varsayılan: <?= e((string) $defaultPoints) ?>" value="<?= e(isset($q['points']) && $q['points'] !== null ? (string) $q['points'] : '') ?>">
                            <div class="form-text">Boş bırakılırsa Site Ayarları'ndaki varsayılan kullanılır.</div>
                        </div>
                        <div class="col-md-3">
                            <label for="sort_order" class="form-label">Sıra</label>
                            <input type="number" id="sort_order" name="sort_order" class="form-control" min="0" value="<?= e((string) ($q['sort_order'] ?? 0)) ?>">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <div class="form-check">
                                <input type="checkbox" id="is_active" name="is_active" class="form-check-input" value="1" <?= (!array_key_exists('is_active', $q) || $q['is_active']) ? 'checked' : '' ?>>
                                <label for="is_active" class="form-check-label">Aktif</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="tab-card" role="tabpanel">
            <div class="card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="card_image" class="form-label">Kart Görseli (WebP, PNG, JPG)</label>
                            <?php if (!empty($q['card_image'])): ?>
                                <div class="mb-2">
                                    <img src="<?= e(base_url($q['card_image'])) ?>" alt="" style="max-width: 160px;" class="img-thumbnail d-block mb-1">
                                    <div class="form-check">
                                        <input type="checkbox" id="remove_card_image" name="remove_card_image" class="form-check-input" value="1">
                                        <label for="remove_card_image" class="form-check-label">Görseli kaldır</label>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <input type="file" id="card_image" name="card_image" class="form-control" accept=".webp,.png,.jpg,.jpeg">
                        </div>
                        <div class="col-md-6">
                            <label for="card_audio" class="form-label">Kart Sesi (MP3, OGG)</label>
                            <?php if (!empty($q['card_audio'])): ?>
                                <div class="mb-2">
                                    <audio controls src="<?= e(base_url($q['card_audio'])) ?>" class="d-block mb-1"></audio>
                                    <div class="form-check">
                                        <input type="checkbox" id="remove_card_audio" name="remove_card_audio" class="form-check-input" value="1">
                                        <label for="remove_card_audio" class="form-check-label">Sesi kaldır</label>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <input type="file" id="card_audio" name="card_audio" class="form-control" accept=".mp3,.ogg">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="tab-question" role="tabpanel">
            <div class="card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="question_text" class="form-label">İngilizce Soru</label>
                            <input type="text" id="question_text" name="question_text" class="form-control" value="<?= e($q['question_text'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="question_audio" class="form-label">Soru Sesi (MP3, OGG)</label>
                            <?php if (!empty($q['question_audio'])): ?>
                                <div class="mb-2">
                                    <audio controls src="<?= e(base_url($q['question_audio'])) ?>" class="d-block mb-1"></audio>
                                    <div class="form-check">
                                        <input type="checkbox" id="remove_question_audio" name="remove_question_audio" class="form-check-input" value="1">
                                        <label for="remove_question_audio" class="form-check-label">Sesi kaldır</label>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <input type="file" id="question_audio" name="question_audio" class="form-control" accept=".mp3,.ogg">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="tab-options" role="tabpanel">
            <div class="row g-3">
                <?php foreach (['A', 'B', 'C', 'D'] as $position): ?>
                    <?php $option = $byPosition[$position] ?? []; ?>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h2 class="h6">Seçenek <?= $position ?></h2>
                                <div class="mb-2">
                                    <label class="form-label">Başlık</label>
                                    <input type="text" name="options[<?= $position ?>][title]" class="form-control" value="<?= e($option['title'] ?? '') ?>" required>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Görsel</label>
                                    <?php if (!empty($option['image'])): ?>
                                        <div class="mb-1">
                                            <img src="<?= e(base_url($option['image'])) ?>" alt="" style="max-width: 100px;" class="img-thumbnail d-block mb-1">
                                            <div class="form-check">
                                                <input type="checkbox" id="remove_option_<?= $position ?>_image" name="options[<?= $position ?>][remove_image]" class="form-check-input" value="1">
                                                <label for="remove_option_<?= $position ?>_image" class="form-check-label">Görseli kaldır</label>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" name="options[<?= $position ?>][image]" class="form-control" accept=".webp,.png,.jpg,.jpeg">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Ses</label>
                                    <?php if (!empty($option['audio'])): ?>
                                        <div class="mb-1">
                                            <audio controls src="<?= e(base_url($option['audio'])) ?>" class="d-block mb-1"></audio>
                                            <div class="form-check">
                                                <input type="checkbox" id="remove_option_<?= $position ?>_audio" name="options[<?= $position ?>][remove_audio]" class="form-check-input" value="1">
                                                <label for="remove_option_<?= $position ?>_audio" class="form-check-label">Sesi kaldır</label>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" name="options[<?= $position ?>][audio]" class="form-control" accept=".mp3,.ogg">
                                </div>
                                <div class="form-check">
                                    <input type="radio" id="correct_<?= $position ?>" name="correct_option" value="<?= $position ?>" class="form-check-input" <?= !empty($option['is_correct']) ? 'checked' : '' ?> required>
                                    <label for="correct_<?= $position ?>" class="form-check-label">Doğru cevap</label>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="tab-pane fade" id="tab-preview" role="tabpanel">
            <?php if (!$isEdit): ?>
                <p class="text-muted">Kaydettikten sonra önizleme görüntülenebilir.</p>
            <?php else: ?>
                <div class="card" style="max-width: 480px;">
                    <?php if (!empty($q['card_image'])): ?>
                        <img src="<?= e(base_url($q['card_image'])) ?>" class="card-img-top" alt="">
                    <?php endif; ?>
                    <div class="card-body">
                        <h2 class="h5"><?= e($q['title']) ?></h2>
                        <p><?= e($q['question_text']) ?></p>
                        <?php if (!empty($q['question_audio'])): ?>
                            <audio controls src="<?= e(base_url($q['question_audio'])) ?>" class="mb-3 w-100"></audio>
                        <?php endif; ?>
                        <div class="row g-2">
                            <?php foreach (['A', 'B', 'C', 'D'] as $position): ?>
                                <?php $option = $byPosition[$position] ?? []; ?>
                                <div class="col-6">
                                    <div class="border rounded p-2 text-center <?= !empty($option['is_correct']) ? 'border-success' : '' ?>">
                                        <?php if (!empty($option['image'])): ?>
                                            <img src="<?= e(base_url($option['image'])) ?>" alt="" style="max-width: 100%; max-height: 80px;">
                                        <?php endif; ?>
                                        <div><?= e($option['title'] ?? '') ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <button type="submit" class="btn btn-primary mt-3"><?= $isEdit ? 'Güncelle' : 'Oluştur' ?></button>
</form>
