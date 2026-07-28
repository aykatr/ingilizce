<h1>Yeni Lisans</h1>

<?php if (!empty($error)): ?>
    <p class="alert alert-error"><?= e($error) ?></p>
<?php endif; ?>

<form method="POST" action="<?= base_url('admin/licenses') ?>" class="password-form">
    <?= csrf_field() ?>
    <div class="form-group">
        <label for="name">Lisans Adı</label>
        <input type="text" id="name" name="name" required autofocus>
    </div>
    <button type="submit">Oluştur</button>
</form>
