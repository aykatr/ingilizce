<!doctype html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? e($title) . ' - ' : '' ?><?= e(config('app.name')) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
        <div class="container-fluid">
            <span class="navbar-brand fw-semibold mb-0"><?= e(config('app.name')) ?></span>
            <div class="d-flex flex-wrap align-items-center gap-3">
                <a href="<?= base_url('admin/dashboard') ?>" class="nav-link d-inline">Panel</a>
                <a href="<?= base_url('admin/categories') ?>" class="nav-link d-inline">Kategoriler</a>
                <a href="<?= base_url('admin/questions') ?>" class="nav-link d-inline">Soru Modülleri</a>
                <a href="<?= base_url('admin/messages') ?>" class="nav-link d-inline">Başarı Mesajları</a>
                <a href="<?= base_url('admin/badges') ?>" class="nav-link d-inline">Rozetler</a>
                <a href="<?= base_url('admin/licenses') ?>" class="nav-link d-inline">Lisanslar</a>
                <a href="<?= base_url('admin/settings') ?>" class="nav-link d-inline">Site Ayarları</a>
                <a href="<?= base_url('admin/password') ?>" class="nav-link d-inline">Şifre Değiştir</a>
                <form method="POST" action="<?= base_url('admin/logout') ?>" class="mb-0">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-outline-secondary">Çıkış Yap</button>
                </form>
            </div>
        </div>
    </nav>
    <main class="container-xl py-4">
        <?= $content ?>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
