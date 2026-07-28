<h1 class="h4 mb-3">Yeni Lisans</h1>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<div class="card" style="max-width: 420px;">
    <div class="card-body">
        <form method="POST" action="<?= base_url('admin/licenses') ?>">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label for="name" class="form-label">Lisans Adı</label>
                <input type="text" id="name" name="name" class="form-control" required autofocus>
            </div>
            <div class="mb-3">
                <label for="expires_at" class="form-label">Son Kullanma Tarihi (opsiyonel)</label>
                <input type="date" id="expires_at" name="expires_at" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Oluştur</button>
        </form>
    </div>
</div>
