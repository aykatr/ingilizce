<h1 class="h4 mb-3">Yeni Kategori</h1>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<div class="card" style="max-width: 420px;">
    <div class="card-body">
        <form method="POST" action="<?= base_url('admin/categories') ?>">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label for="name" class="form-label">Kategori Adı</label>
                <input type="text" id="name" name="name" class="form-control" required autofocus>
            </div>
            <button type="submit" class="btn btn-primary">Oluştur</button>
        </form>
    </div>
</div>
