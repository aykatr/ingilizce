<!doctype html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? e($title) . ' - ' : '' ?><?= e(config('app.name')) ?></title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body class="admin">
    <header class="admin-header">
        <span class="admin-brand"><?= e(config('app.name')) ?></span>
        <nav class="admin-nav">
            <a href="<?= base_url('admin/dashboard') ?>">Panel</a>
            <a href="<?= base_url('admin/password') ?>">Şifre Değiştir</a>
            <form method="POST" action="<?= base_url('admin/logout') ?>" class="logout-form">
                <?= csrf_field() ?>
                <button type="submit">Çıkış Yap</button>
            </form>
        </nav>
    </header>
    <main class="admin-content">
        <?= $content ?>
    </main>
</body>
</html>
