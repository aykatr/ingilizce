<h1 class="h4 mb-3">Yedekleme</h1>
<p class="text-muted">Veritabanının tam bir SQL yedeğini indirin. Bu işlem yalnızca indirme sağlar; yedekten geri yükleme (restore) bu sürümde desteklenmez.</p>

<div class="card" style="max-width: 480px;">
    <div class="card-body">
        <form method="POST" action="<?= base_url('admin/backup/download') ?>">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-primary">Yedek İndir (SQL)</button>
        </form>
    </div>
</div>
