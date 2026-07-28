<h1 class="h4 mb-3">Medya Kütüphanesi</h1>
<p class="text-muted">Sistemde kullanılan tüm görsel ve ses dosyaları burada listelenir. Soru/mesaj/rozet formlarındaki "Kütüphaneden Seç" butonuyla buradaki dosyalar doğrudan kullanılabilir.</p>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= e($success) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<div class="card mb-3">
    <div class="card-body">
        <h2 class="h6">Toplu Yükleme</h2>
        <form method="POST" action="<?= base_url('admin/media-library/upload') ?>" enctype="multipart/form-data" class="row g-2 align-items-end">
            <?= csrf_field() ?>
            <div class="col-md-9">
                <input type="file" name="files[]" class="form-control" multiple accept=".webp,.png,.jpg,.jpeg,.mp3,.ogg">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">Yükle</button>
            </div>
        </form>
    </div>
</div>

<form method="GET" action="<?= base_url('admin/media-library') ?>" class="row g-2 mb-3">
    <div class="col-md-3">
        <select name="type" class="form-select form-select-sm">
            <option value="">Tüm türler</option>
            <option value="image" <?= $filters['type'] === 'image' ? 'selected' : '' ?>>Görsel</option>
            <option value="audio" <?= $filters['type'] === 'audio' ? 'selected' : '' ?>>Ses</option>
        </select>
    </div>
    <div class="col-md-3">
        <select name="usage" class="form-select form-select-sm">
            <option value="">Tümü</option>
            <option value="used" <?= $filters['usage'] === 'used' ? 'selected' : '' ?>>Kullanımda</option>
            <option value="unused" <?= $filters['usage'] === 'unused' ? 'selected' : '' ?>>Kullanılmıyor</option>
        </select>
    </div>
    <div class="col-md-4">
        <input type="text" name="q" class="form-control form-control-sm" placeholder="Dosya adında ara" value="<?= e($filters['q']) ?>">
    </div>
    <div class="col-md-2">
        <button type="submit" class="btn btn-sm btn-primary w-100">Filtrele</button>
    </div>
</form>

<p class="text-muted small"><?= count($files) ?> dosya bulundu.</p>

<div class="row g-3">
    <?php if (empty($files)): ?>
        <p class="text-muted">Dosya bulunamadı.</p>
    <?php endif; ?>
    <?php foreach ($files as $file): ?>
        <div class="col-md-3 col-sm-4 col-6">
            <div class="card h-100">
                <div class="ratio ratio-1x1 bg-light d-flex align-items-center justify-content-center">
                    <?php if ($file['type'] === 'image'): ?>
                        <img src="<?= e(base_url($file['path'])) ?>" alt="" class="w-100 h-100" style="object-fit: contain;">
                    <?php else: ?>
                        <audio controls src="<?= e(base_url($file['path'])) ?>" style="width: 90%;"></audio>
                    <?php endif; ?>
                </div>
                <div class="card-body p-2">
                    <div class="small text-truncate" title="<?= e($file['original_name'] ?? '') ?>"><?= e($file['original_name'] ?? basename($file['path'])) ?></div>
                    <div class="small text-muted"><?= strtoupper($file['extension']) ?> · <?= round($file['size_bytes'] / 1024) ?> KB</div>
                    <?php if ($file['usage_count'] > 0): ?>
                        <div class="small mt-1">
                            <span class="badge bg-success">Kullanımda (<?= $file['usage_count'] ?>)</span>
                            <ul class="small mb-0 mt-1 ps-3">
                                <?php foreach ($file['usages'] as $usage): ?>
                                    <li><a href="<?= e($usage['edit_url']) ?>"><?= e($usage['label']) ?>: <?= e($usage['name']) ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php else: ?>
                        <div class="small mt-1"><span class="badge bg-secondary">Kullanılmıyor</span></div>
                    <?php endif; ?>

                    <div class="mt-2 d-flex flex-column gap-1">
                        <form method="POST" action="<?= base_url('admin/media-library/' . $file['id'] . '/replace') ?>" enctype="multipart/form-data">
                            <?= csrf_field() ?>
                            <input type="file" name="file" class="form-control form-control-sm mb-1" accept=".<?= e($file['extension']) ?>">
                            <button type="submit" class="btn btn-sm btn-outline-secondary w-100">Değiştir</button>
                        </form>
                        <form method="POST" action="<?= base_url('admin/media-library/' . $file['id'] . '/delete') ?>" onsubmit="return confirm('Bu dosyayı silmek istediğinize emin misiniz?');">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-outline-danger w-100" <?= $file['usage_count'] > 0 ? 'disabled' : '' ?>>Sil</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
