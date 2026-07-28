<?php
function renderMessageTable(array $messages): void
{
    if (empty($messages)) {
        echo '<p class="text-muted">Henüz mesaj yok.</p>';
        return;
    }
    ?>
    <div class="table-responsive">
        <table class="table table-striped bg-white align-middle">
            <thead>
                <tr>
                    <th>Başlık</th>
                    <th>Animasyon</th>
                    <th>Ses</th>
                    <th>Durum</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($messages as $message): ?>
                    <tr>
                        <td><?= e($message['title']) ?></td>
                        <td><?= e($message['animation_type'] ?? '-') ?></td>
                        <td><?= $message['audio'] ? '🔊' : '-' ?></td>
                        <td>
                            <span class="badge text-bg-<?= $message['is_active'] ? 'success' : 'secondary' ?>">
                                <?= $message['is_active'] ? 'Aktif' : 'Pasif' ?>
                            </span>
                        </td>
                        <td class="text-nowrap">
                            <a href="<?= base_url('admin/messages/' . $message['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary">Düzenle</a>
                            <form method="POST" action="<?= base_url('admin/messages/' . $message['id'] . '/delete') ?>" class="d-inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger">Sil</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Başarı Mesajları</h1>
    <a href="<?= base_url('admin/messages/create') ?>" class="btn btn-primary btn-sm">+ Yeni Mesaj</a>
</div>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= e($success) ?></div>
<?php endif; ?>

<h2 class="h6 mt-4">Doğru Cevap Mesajları</h2>
<?php renderMessageTable($correctMessages); ?>

<h2 class="h6 mt-4">Yanlış Cevap Mesajları</h2>
<?php renderMessageTable($wrongMessages); ?>
