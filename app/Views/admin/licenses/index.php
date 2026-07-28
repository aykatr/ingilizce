<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Lisanslar</h1>
    <a href="<?= base_url('admin/licenses/create') ?>" class="btn btn-primary btn-sm">+ Yeni Lisans</a>
</div>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= e($success) ?></div>
<?php endif; ?>

<?php if (!empty($newLink)): ?>
    <div class="alert alert-success">Oynama linki: <code><?= e($newLink) ?></code></div>
<?php endif; ?>

<div class="table-responsive">
    <table class="table table-striped bg-white align-middle">
        <thead>
            <tr>
                <th>Ad</th>
                <th>Lisans Kodu</th>
                <th>Durum</th>
                <th>İlk Aktivasyon</th>
                <th>Son Kullanım</th>
                <th>Son Cihaz</th>
                <th>Son IP</th>
                <th>Oluşturulma</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($licenses as $license): ?>
                <?php
                    $badge = match ($license['status_label']) {
                        'Aktif' => 'success',
                        'Süresi Doldu' => 'warning',
                        default => 'secondary',
                    };
                ?>
                <tr>
                    <td><?= e($license['name']) ?></td>
                    <td><code><?= e($license['code']) ?></code></td>
                    <td><span class="badge text-bg-<?= $badge ?>"><?= e($license['status_label']) ?></span></td>
                    <td><?= e($license['first_activated_at'] ?? '-') ?></td>
                    <td><?= e($license['last_used_at'] ?? '-') ?></td>
                    <td class="text-truncate" style="max-width: 160px;" title="<?= e($license['last_device'] ?? '') ?>"><?= e($license['last_device'] ?? '-') ?></td>
                    <td><?= e($license['last_ip'] ?? '-') ?></td>
                    <td><?= e($license['created_at']) ?></td>
                    <td class="text-nowrap">
                        <button
                            type="button"
                            class="btn btn-sm btn-outline-primary"
                            data-qr-url="<?= e($license['play_url']) ?>"
                            data-bs-toggle="modal"
                            data-bs-target="#qrModal"
                        >QR</button>
                        <form method="POST" action="<?= base_url('admin/licenses/' . $license['id'] . '/toggle') ?>" class="d-inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-outline-secondary"><?= $license['is_active'] ? 'Pasif Yap' : 'Aktif Yap' ?></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($licenses)): ?>
                <tr><td colspan="9" class="text-center text-muted">Henüz lisans yok.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="qrModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">QR Kod</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div id="qrCode" class="d-flex justify-content-center mb-3"></div>
                <code id="qrLink" class="d-block"></code>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script src="<?= asset('js/license-qr.js') ?>"></script>
