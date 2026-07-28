<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Site Ayarları</h1>
    <a href="<?= base_url('admin/settings/start-screen') ?>" class="btn btn-outline-primary btn-sm">Başlangıç Ekranı Ayarları</a>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= e($success) ?></div>
<?php endif; ?>

<div class="card" style="max-width: 480px;">
    <div class="card-body">
        <form method="POST" action="<?= base_url('admin/settings') ?>">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label for="site_url" class="form-label">Site URL</label>
                <input type="url" id="site_url" name="site_url" class="form-control" value="<?= e($siteUrl) ?>" required>
                <div class="form-text">QR kodlarında ve oynama linklerinde bu adres kullanılır. Örn: https://yippee.com.tr/ingilizce</div>
            </div>
            <div class="mb-3">
                <label for="default_duration_seconds" class="form-label">Varsayılan Süre (saniye)</label>
                <input type="number" id="default_duration_seconds" name="default_duration_seconds" class="form-control" value="<?= e((string) $defaultDuration) ?>" min="1" required>
                <div class="form-text">Soru bazında süre belirtilmezse bu değer kullanılır.</div>
            </div>
            <div class="mb-3">
                <label for="default_points" class="form-label">Varsayılan Puan</label>
                <input type="number" id="default_points" name="default_points" class="form-control" value="<?= e((string) $defaultPoints) ?>" min="0" required>
                <div class="form-text">Soru bazında puan belirtilmezse bu değer kullanılır.</div>
            </div>
            <div class="mb-3">
                <label for="default_lives" class="form-label">Can Sayısı</label>
                <input type="number" id="default_lives" name="default_lives" class="form-control" value="<?= e((string) $defaultLives) ?>" min="1" required>
                <div class="form-text">Oyun başına verilen can sayısı.</div>
            </div>
            <button type="submit" class="btn btn-primary">Kaydet</button>
        </form>
    </div>
</div>
