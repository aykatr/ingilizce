<?php
$m = $message ?? [];
$formAction = $isEdit ? base_url('admin/messages/' . $m['id']) : base_url('admin/messages');
?>

<h1 class="h4 mb-3"><?= $isEdit ? 'Mesajı Düzenle' : 'Yeni Başarı Mesajı' ?></h1>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<div class="card" style="max-width: 480px;">
    <div class="card-body">
        <form method="POST" action="<?= $formAction ?>" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label for="type" class="form-label">Tip</label>
                <select id="type" name="type" class="form-select" required>
                    <option value="correct" <?= ($m['type'] ?? '') === 'correct' ? 'selected' : '' ?>>Doğru Cevap Mesajı</option>
                    <option value="wrong" <?= ($m['type'] ?? '') === 'wrong' ? 'selected' : '' ?>>Yanlış Cevap Mesajı</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="title" class="form-label">Başlık</label>
                <input type="text" id="title" name="title" class="form-control" value="<?= e($m['title'] ?? '') ?>" required>
            </div>

            <div class="mb-3">
                <label for="animation_type" class="form-label">Animasyon Tipi</label>
                <select id="animation_type" name="animation_type" class="form-select">
                    <option value="">Yok</option>
                    <?php foreach ($animationTypes as $value => $label): ?>
                        <option value="<?= e($value) ?>" <?= ($m['animation_type'] ?? '') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Ses Dosyası (MP3, OGG)</label>
                <?php if (!empty($m['audio'])): ?>
                    <div class="mb-1">
                        <audio controls src="<?= e(base_url($m['audio'])) ?>" class="d-block mb-1"></audio>
                        <div class="form-check">
                            <input type="checkbox" id="remove_audio" name="remove_audio" class="form-check-input" value="1">
                            <label for="remove_audio" class="form-check-label">Sesi kaldır</label>
                        </div>
                    </div>
                <?php endif; ?>
                <input type="file" name="audio" class="form-control" accept=".mp3,.ogg" data-media-picker="audio">
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" id="is_active" name="is_active" class="form-check-input" value="1" <?= (!array_key_exists('is_active', $m) || $m['is_active']) ? 'checked' : '' ?>>
                <label for="is_active" class="form-check-label">Aktif</label>
            </div>

            <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Güncelle' : 'Oluştur' ?></button>
        </form>
    </div>
</div>
