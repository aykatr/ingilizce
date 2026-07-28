<div class="page-header">
    <h1>Lisanslar</h1>
    <a href="<?= base_url('admin/licenses/create') ?>" class="btn-link">+ Yeni Lisans</a>
</div>

<?php if (!empty($success)): ?>
    <p class="alert alert-success"><?= e($success) ?></p>
<?php endif; ?>

<?php if (!empty($newLink)): ?>
    <p class="alert alert-success">Oynama linki: <code><?= e($newLink) ?></code></p>
<?php endif; ?>

<table class="data-table">
    <thead>
        <tr>
            <th>Ad</th>
            <th>Token</th>
            <th>Durum</th>
            <th>Oluşturulma</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($licenses as $license): ?>
            <tr>
                <td><?= e($license['name']) ?></td>
                <td><code><?= e($license['token']) ?></code></td>
                <td><?= $license['is_active'] ? 'Aktif' : 'Pasif' ?></td>
                <td><?= e($license['created_at']) ?></td>
                <td>
                    <form method="POST" action="<?= base_url('admin/licenses/' . $license['id'] . '/toggle') ?>">
                        <?= csrf_field() ?>
                        <button type="submit"><?= $license['is_active'] ? 'Pasif Yap' : 'Aktif Yap' ?></button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($licenses)): ?>
            <tr><td colspan="5">Henüz lisans yok.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
