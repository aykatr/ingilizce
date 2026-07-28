<main class="page page-login">
    <div class="login-box">
        <h1>Admin Girişi</h1>

        <?php if (!empty($error)): ?>
            <p class="alert alert-error"><?= e($error) ?></p>
        <?php endif; ?>

        <form method="POST" action="<?= base_url('admin/login') ?>">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="username">Kullanıcı Adı</label>
                <input type="text" id="username" name="username" autocomplete="username" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Şifre</label>
                <input type="password" id="password" name="password" autocomplete="current-password" required>
            </div>
            <button type="submit">Giriş Yap</button>
        </form>
    </div>
</main>
