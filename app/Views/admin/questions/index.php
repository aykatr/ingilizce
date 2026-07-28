<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Soru Modülleri</h1>
    <a href="<?= base_url('admin/questions/create') ?>" class="btn btn-primary btn-sm">+ Yeni Soru</a>
</div>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= e($success) ?></div>
<?php endif; ?>

<div class="table-responsive">
    <table class="table table-striped bg-white align-middle">
        <thead>
            <tr>
                <th>Kart Başlığı</th>
                <th>Kategori</th>
                <th>Süre</th>
                <th>Puan</th>
                <th>Durum</th>
                <th>Sıra</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($questions as $question): ?>
                <tr>
                    <td><?= e($question['title']) ?></td>
                    <td><?= e($question['category_name']) ?></td>
                    <td><?= $question['duration_seconds'] !== null ? e((string) $question['duration_seconds']) . ' sn' : '<span class="text-muted">varsayılan</span>' ?></td>
                    <td><?= $question['points'] !== null ? e((string) $question['points']) : '<span class="text-muted">varsayılan</span>' ?></td>
                    <td>
                        <span class="badge text-bg-<?= $question['is_active'] ? 'success' : 'secondary' ?>">
                            <?= $question['is_active'] ? 'Aktif' : 'Pasif' ?>
                        </span>
                    </td>
                    <td><?= e((string) $question['sort_order']) ?></td>
                    <td class="text-nowrap">
                        <a href="<?= base_url('admin/questions/' . $question['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary">Düzenle</a>
                        <form method="POST" action="<?= base_url('admin/questions/' . $question['id'] . '/delete') ?>" class="d-inline" onsubmit="return confirm('Bu soru ve tüm medyası silinecek. Emin misiniz?');">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-outline-danger">Sil</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($questions)): ?>
                <tr><td colspan="7" class="text-center text-muted">Henüz soru yok.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
