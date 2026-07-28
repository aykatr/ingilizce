<div class="d-flex align-items-center justify-content-center min-vh-100 bg-light">
    <div class="card shadow-sm" style="width: 100%; max-width: 380px;">
        <div class="card-body p-4">
            <h1 class="h4 mb-3 text-center">Admin Girişi</h1>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger py-2"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="<?= base_url('admin/login') ?>">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label for="username" class="form-label">Kullanıcı Adı</label>
                    <input type="text" id="username" name="username" class="form-control" autocomplete="username" required autofocus>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Şifre</label>
                    <input type="password" id="password" name="password" class="form-control" autocomplete="current-password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Giriş Yap</button>
            </form>
        </div>
    </div>
</div>
