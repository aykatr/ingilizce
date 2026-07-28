<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Menü Yönetimi</h1>
    <a href="<?= base_url('admin/settings') ?>" class="btn btn-outline-primary btn-sm">Site Ayarları'na Dön</a>
</div>
<p class="text-muted">Kart Seçim Menüsü'nün görünümünü ve kartların menüdeki sırasını/görünürlüğünü buradan yönetin. Menüdeki kart görselleri her kartın kendi "Kart Görseli" alanından otomatik kullanılır — burada ayrıca bir görsel yüklenmez.</p>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= e($success) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<form method="POST" action="<?= base_url('admin/settings/menu') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="card mb-3">
        <div class="card-body">
            <h2 class="h6">Genel</h2>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="menu_title" class="form-label">Menü Başlığı</label>
                    <input type="text" id="menu_title" name="menu_title" class="form-control" value="<?= e($menuSettings['menu_title']) ?>">
                </div>
                <div class="col-md-6">
                    <label for="menu_description" class="form-label">Menü Açıklaması</label>
                    <input type="text" id="menu_description" name="menu_description" class="form-control" value="<?= e($menuSettings['menu_description']) ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">Arka Plan Görseli</label>
                    <?php if (!empty($menuSettings['menu_background_image'])): ?>
                        <div class="mb-2">
                            <img src="<?= e(base_url($menuSettings['menu_background_image'])) ?>" alt="" style="max-width: 160px;" class="img-thumbnail d-block mb-1">
                            <div class="form-check">
                                <input type="checkbox" id="remove_menu_background_image" name="remove_menu_background_image" class="form-check-input" value="1">
                                <label for="remove_menu_background_image" class="form-check-label">Görseli kaldır</label>
                            </div>
                        </div>
                    <?php endif; ?>
                    <input type="file" id="menu_background_image" name="menu_background_image" class="form-control" accept=".webp,.png,.jpg,.jpeg" data-media-picker="image">
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h2 class="h6">Görünüm</h2>
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="menu_columns" class="form-label">Kolon Sayısı</label>
                    <input type="number" id="menu_columns" name="menu_columns" class="form-control" min="1" max="4" value="<?= e((string) $menuSettings['menu_columns']) ?>">
                </div>
                <div class="col-md-3">
                    <label for="menu_card_size" class="form-label">Kart Boyutu (px)</label>
                    <input type="number" id="menu_card_size" name="menu_card_size" class="form-control" min="60" value="<?= e((string) $menuSettings['menu_card_size']) ?>">
                </div>
                <div class="col-md-3">
                    <label for="menu_card_gap" class="form-label">Kartlar Arası Boşluk (px)</label>
                    <input type="number" id="menu_card_gap" name="menu_card_gap" class="form-control" min="0" value="<?= e((string) $menuSettings['menu_card_gap']) ?>">
                </div>
                <div class="col-md-3">
                    <label for="menu_card_radius" class="form-label">Kart Köşe Yuvarlaklığı (px)</label>
                    <input type="number" id="menu_card_radius" name="menu_card_radius" class="form-control" min="0" value="<?= e((string) $menuSettings['menu_card_radius']) ?>">
                </div>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Kaydet</button>
</form>

<div class="card mt-4">
    <div class="card-body">
        <h2 class="h6">Kartlar — Sıra ve Görünürlük</h2>
        <p class="text-muted small">Sıra, menüdeki kart dizilimini belirler (mevcut soru sırası alanıyla aynıdır). "Menüde Göster" işaretini kaldırmak kartı pasif yapar (mevcut "Aktif" alanıyla aynıdır) — oyun akışında da görünmez.</p>

        <form method="POST" action="<?= base_url('admin/settings/menu/cards') ?>">
            <?= csrf_field() ?>
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Kart</th>
                            <th>Kategori</th>
                            <th style="width: 100px;">Sıra</th>
                            <th>Menüde Göster</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($questions)): ?>
                            <tr><td colspan="5" class="text-muted text-center py-3">Henüz soru eklenmemiş.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($questions as $q): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($q['card_image'])): ?>
                                        <img src="<?= e(base_url($q['card_image'])) ?>" alt="" style="width: 40px; height: 40px; object-fit: cover; border-radius: 8px;">
                                    <?php endif; ?>
                                </td>
                                <td><?= e($q['title']) ?></td>
                                <td><?= e($q['category_name']) ?></td>
                                <td>
                                    <input type="number" name="cards[<?= (int) $q['id'] ?>][sort_order]" class="form-control form-control-sm" min="0" value="<?= e((string) $q['sort_order']) ?>">
                                </td>
                                <td>
                                    <input type="checkbox" name="cards[<?= (int) $q['id'] ?>][is_active]" class="form-check-input" value="1" <?= (int) $q['is_active'] === 1 ? 'checked' : '' ?>>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <button type="submit" class="btn btn-primary">Kartları Kaydet</button>
        </form>
    </div>
</div>
