<h1 class="h4 mb-3">Şifre Değiştir</h1>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= e($success) ?></div>
<?php endif; ?>

<div class="card" style="max-width: 420px;">
    <div class="card-body">
        <form method="POST" action="<?= base_url('admin/password') ?>">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label for="current_password" class="form-label">Mevcut Şifre</label>
                <input type="password" id="current_password" name="current_password" class="form-control" autocomplete="current-password" required>
            </div>
            <div class="mb-3">
                <label for="new_password" class="form-label">Yeni Şifre</label>
                <input type="password" id="new_password" name="new_password" class="form-control" autocomplete="new-password" minlength="8" required>
            </div>
            <div class="mb-3">
                <label for="new_password_confirmation" class="form-label">Yeni Şifre (Tekrar)</label>
                <input type="password" id="new_password_confirmation" name="new_password_confirmation" class="form-control" autocomplete="new-password" minlength="8" required>
            </div>
            <button type="submit" class="btn btn-primary">Güncelle</button>
        </form>
    </div>
</div>
