<h1 class="h4 mb-3">Kategori Düzenle</h1>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<div class="card" style="max-width: 420px;">
    <div class="card-body">
        <form method="POST" action="<?= base_url('admin/categories/' . $category['id']) ?>">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label for="name" class="form-label">Kategori Adı</label>
                <input type="text" id="name" name="name" class="form-control" value="<?= e($category['name']) ?>" required autofocus>
            </div>
            <button type="submit" class="btn btn-primary">Güncelle</button>
        </form>
    </div>
</div>
