<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Rozetler</h1>
    <a href="<?= base_url('admin/badges/create') ?>" class="btn btn-primary btn-sm">+ Yeni Rozet</a>
</div>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= e($success) ?></div>
<?php endif; ?>

<div class="table-responsive">
    <table class="table table-striped bg-white align-middle">
        <thead>
            <tr>
                <th>Başlık</th>
                <th>Koşul</th>
                <th>Değer</th>
                <th>Durum</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($badges as $badge): ?>
                <tr>
                    <td><?= e($badge['title']) ?></td>
                    <td><?= e($conditionTypes[$badge['condition_type']] ?? $badge['condition_type']) ?></td>
                    <td><?= $badge['condition_value'] !== null ? e((string) $badge['condition_value']) : '-' ?></td>
                    <td>
                        <span class="badge text-bg-<?= $badge['is_active'] ? 'success' : 'secondary' ?>">
                            <?= $badge['is_active'] ? 'Aktif' : 'Pasif' ?>
                        </span>
                    </td>
                    <td class="text-nowrap">
                        <a href="<?= base_url('admin/badges/' . $badge['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary">Düzenle</a>
                        <form method="POST" action="<?= base_url('admin/badges/' . $badge['id'] . '/delete') ?>" class="d-inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-outline-danger">Sil</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($badges)): ?>
                <tr><td colspan="5" class="text-center text-muted">Henüz rozet yok.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
