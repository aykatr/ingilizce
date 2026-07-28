<h1>Şifre Değiştir</h1>

<?php if (!empty($error)): ?>
    <p class="alert alert-error"><?= e($error) ?></p>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <p class="alert alert-success"><?= e($success) ?></p>
<?php endif; ?>

<form method="POST" action="<?= base_url('admin/password') ?>" class="password-form">
    <?= csrf_field() ?>
    <div class="form-group">
        <label for="current_password">Mevcut Şifre</label>
        <input type="password" id="current_password" name="current_password" autocomplete="current-password" required>
    </div>
    <div class="form-group">
        <label for="new_password">Yeni Şifre</label>
        <input type="password" id="new_password" name="new_password" autocomplete="new-password" minlength="8" required>
    </div>
    <div class="form-group">
        <label for="new_password_confirmation">Yeni Şifre (Tekrar)</label>
        <input type="password" id="new_password_confirmation" name="new_password_confirmation" autocomplete="new-password" minlength="8" required>
    </div>
    <button type="submit">Güncelle</button>
</form>
