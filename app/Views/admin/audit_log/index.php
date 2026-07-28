<h1 class="h4 mb-3">Denetim Kaydı</h1>
<p class="text-muted">Giriş/çıkış, içerik ve ayar değişiklikleri, lisans ve medya işlemleri gibi önemli yönetici işlemleri burada kayıt altına alınır.</p>

<form method="GET" action="<?= base_url('admin/audit-log') ?>" class="row g-2 mb-3">
    <div class="col-md-3">
        <select name="action" class="form-select form-select-sm">
            <option value="">Tüm işlem türleri</option>
            <?php foreach ($actions as $action): ?>
                <option value="<?= e($action) ?>" <?= $filters['action'] === $action ? 'selected' : '' ?>><?= e($action) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3">
        <input type="text" name="q" class="form-control form-control-sm" placeholder="Ara (açıklama, kullanıcı)" value="<?= e($filters['q']) ?>">
    </div>
    <div class="col-md-2">
        <input type="date" name="date_from" class="form-control form-control-sm" value="<?= e($filters['date_from']) ?>">
    </div>
    <div class="col-md-2">
        <input type="date" name="date_to" class="form-control form-control-sm" value="<?= e($filters['date_to']) ?>">
    </div>
    <div class="col-md-2">
        <button type="submit" class="btn btn-sm btn-primary w-100">Filtrele</button>
    </div>
</form>

<p class="text-muted small"><?= $total ?> kayıt bulundu.</p>

<div class="table-responsive">
    <table class="table table-sm table-striped align-middle">
        <thead>
            <tr>
                <th>Tarih</th>
                <th>Kullanıcı</th>
                <th>İşlem</th>
                <th>Açıklama</th>
                <th>IP</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($logs)): ?>
                <tr><td colspan="5" class="text-muted text-center py-3">Kayıt bulunamadı.</td></tr>
            <?php endif; ?>
            <?php foreach ($logs as $log): ?>
                <tr>
                    <td class="text-nowrap"><?= e($log['created_at']) ?></td>
                    <td><?= e($log['admin_username'] ?? '—') ?></td>
                    <td><code><?= e($log['action']) ?></code></td>
                    <td><?= e($log['description']) ?></td>
                    <td class="text-nowrap"><?= e($log['ip_address'] ?? '—') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if ($lastPage > 1): ?>
    <nav>
        <ul class="pagination pagination-sm">
            <?php for ($p = 1; $p <= $lastPage; $p++): ?>
                <?php
                    $query = array_merge($filters, ['page' => $p]);
                    $query = array_filter($query, fn ($v) => $v !== '');
                ?>
                <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                    <a class="page-link" href="<?= base_url('admin/audit-log') ?>?<?= http_build_query($query) ?>"><?= $p ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
<?php endif; ?>
