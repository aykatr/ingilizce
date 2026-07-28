<?php
$s = $startScreen;
$imageFields = [
    'background_image' => 'Başlangıç Arka Plan Görseli',
    'logo_image' => 'Başlangıç Logo Görseli',
    'mascot_left_image' => 'Sol Maskot Görseli',
    'mascot_right_image' => 'Sağ Maskot Görseli',
    'robot_image' => 'Robot Görseli',
    'rocket_image' => 'Roket Görseli',
    'balloon_image' => 'Balon Görseli',
    'decor_image_1' => 'Alt Dekor Görseli 1 (opsiyonel)',
    'decor_image_2' => 'Alt Dekor Görseli 2 (opsiyonel)',
    'decor_image_3' => 'Alt Dekor Görseli 3 (opsiyonel)',
    'qr_image' => 'QR Kod Görseli (Giriş Ekranı — yalnızca görsel amaçlı, tarama/doğrulama yapmaz)',
];
?>

<h1 class="h4 mb-3">Başlangıç Ekranı Ayarları</h1>
<p class="text-muted">Bu ayarlar oyuncunun lisans doğrulandıktan sonra gördüğü karşılama ekranını yönetir. Görsel yüklenmemiş alanlar tasarımı bozmadan gizlenir.</p>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= e($success) ?></div>
<?php endif; ?>

<form method="POST" action="<?= base_url('admin/settings/start-screen') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="card mb-3">
        <div class="card-body">
            <h2 class="h6">Metinler</h2>
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="title" class="form-label">Hoş Geldiniz Başlığı</label>
                    <input type="text" id="title" name="title" class="form-control" value="<?= e($s['title']) ?>">
                </div>
                <div class="col-md-4">
                    <label for="description" class="form-label">Açıklama Metni</label>
                    <textarea id="description" name="description" class="form-control" rows="2"><?= e($s['description']) ?></textarea>
                </div>
                <div class="col-md-4">
                    <label for="button_text" class="form-label">Başla Butonu Yazısı</label>
                    <input type="text" id="button_text" name="button_text" class="form-control" value="<?= e($s['button_text']) ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h2 class="h6">Görseller</h2>
            <div class="row g-3">
                <?php foreach ($imageFields as $field => $label): ?>
                    <div class="col-md-4">
                        <label class="form-label"><?= e($label) ?></label>
                        <?php if (!empty($s[$field])): ?>
                            <div class="mb-1">
                                <img src="<?= e(base_url($s[$field])) ?>" alt="" style="max-width: 120px; max-height: 90px;" class="img-thumbnail d-block mb-1">
                                <div class="form-check">
                                    <input type="checkbox" id="remove_<?= $field ?>" name="remove_<?= $field ?>" class="form-check-input" value="1">
                                    <label for="remove_<?= $field ?>" class="form-check-label">Kaldır</label>
                                </div>
                            </div>
                        <?php endif; ?>
                        <input type="file" name="<?= $field ?>" class="form-control" accept=".webp,.png,.jpg,.jpeg" data-media-picker="image">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Kaydet</button>
    <a href="<?= base_url('admin/settings') ?>" class="btn btn-outline-secondary">Site Ayarları'na Dön</a>
</form>
