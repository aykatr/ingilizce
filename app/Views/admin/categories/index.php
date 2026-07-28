<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Kategoriler</h1>
    <a href="<?= base_url('admin/categories/create') ?>" class="btn btn-primary btn-sm">+ Yeni Kategori</a>
</div>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= e($success) ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<div class="table-responsive">
    <table class="table table-striped bg-white align-middle">
        <thead>
            <tr>
                <th>Ad</th>
                <th>Oluşturulma</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $category): ?>
                <tr>
                    <td><?= e($category['name']) ?></td>
                    <td><?= e($category['created_at']) ?></td>
                    <td class="text-nowrap">
                        <a href="<?= base_url('admin/categories/' . $category['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary">Düzenle</a>
                        <form method="POST" action="<?= base_url('admin/categories/' . $category['id'] . '/delete') ?>" class="d-inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-outline-danger">Sil</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($categories)): ?>
                <tr><td colspan="3" class="text-center text-muted">Henüz kategori yok.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
