<?php
$b = $badge ?? [];
$formAction = $isEdit ? base_url('admin/badges/' . $b['id']) : base_url('admin/badges');
?>

<h1 class="h4 mb-3"><?= $isEdit ? 'Rozeti Düzenle' : 'Yeni Rozet' ?></h1>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<div class="card" style="max-width: 560px;">
    <div class="card-body">
        <form method="POST" action="<?= $formAction ?>" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label for="title" class="form-label">Başlık</label>
                <input type="text" id="title" name="title" class="form-control" value="<?= e($b['title'] ?? '') ?>" required>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Açıklama</label>
                <textarea id="description" name="description" class="form-control" rows="2"><?= e($b['description'] ?? '') ?></textarea>
            </div>

            <div class="row g-3">
                <div class="col-md-7">
                    <label for="condition_type" class="form-label">Koşul</label>
                    <select id="condition_type" name="condition_type" class="form-select" required>
                        <?php foreach ($conditionTypes as $value => $label): ?>
                            <option value="<?= e($value) ?>" <?= ($b['condition_type'] ?? '') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-5">
                    <label for="condition_value" class="form-label">Koşul Değeri</label>
                    <input type="number" id="condition_value" name="condition_value" class="form-control" min="1" value="<?= e($b['condition_value'] !== null && ($b['condition_value'] ?? null) !== '' ? (string) $b['condition_value'] : '') ?>">
                </div>
            </div>

            <div class="mb-3 mt-3">
                <label for="animation_type" class="form-label">Animasyon Tipi</label>
                <select id="animation_type" name="animation_type" class="form-select">
                    <option value="">Yok</option>
                    <?php foreach ($animationTypes as $value => $label): ?>
                        <option value="<?= e($value) ?>" <?= ($b['animation_type'] ?? '') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Görsel (WebP, PNG, JPG)</label>
                    <?php if (!empty($b['image'])): ?>
                        <div class="mb-1">
                            <img src="<?= e(base_url($b['image'])) ?>" alt="" style="max-width: 120px;" class="img-thumbnail d-block mb-1">
                            <div class="form-check">
                                <input type="checkbox" id="remove_image" name="remove_image" class="form-check-input" value="1">
                                <label for="remove_image" class="form-check-label">Görseli kaldır</label>
                            </div>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="image" class="form-control" accept=".webp,.png,.jpg,.jpeg">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Ses (MP3, OGG)</label>
                    <?php if (!empty($b['audio'])): ?>
                        <div class="mb-1">
                            <audio controls src="<?= e(base_url($b['audio'])) ?>" class="d-block mb-1"></audio>
                            <div class="form-check">
                                <input type="checkbox" id="remove_audio" name="remove_audio" class="form-check-input" value="1">
                                <label for="remove_audio" class="form-check-label">Sesi kaldır</label>
                            </div>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="audio" class="form-control" accept=".mp3,.ogg">
                </div>
            </div>

            <div class="mb-3 mt-3 form-check">
                <input type="checkbox" id="is_active" name="is_active" class="form-check-input" value="1" <?= (!array_key_exists('is_active', $b) || $b['is_active']) ? 'checked' : '' ?>>
                <label for="is_active" class="form-check-label">Aktif</label>
            </div>

            <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Güncelle' : 'Oluştur' ?></button>
        </form>
    </div>
</div>
