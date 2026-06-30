<?php
$pageTitle = 'Pengaturan Sistem';
$activePage = 'settings';
require_once __DIR__ . '/includes/header_admin.php';
requireLogin('admin');

$settings = getSettings();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    $keys = ['shop_name','shop_address','shop_phone','shop_email','tax_percent','receipt_footer'];
    foreach ($keys as $key) {
        $val = db()->escape(trim($_POST[$key] ?? ''));
        db()->query("INSERT INTO settings (key_name, key_value) VALUES ('$key', '$val') ON DUPLICATE KEY UPDATE key_value='$val'");
    }
    $settings = getSettings();
    $success = 'Pengaturan berhasil disimpan!';
}
?>
<meta name="app-url" content="<?= APP_URL ?>">

<?php if (isset($success)): ?>
<div style="background:#d1e7dd;color:#0f5132;padding:0.75rem 1rem;border-radius:10px;margin-bottom:1rem;font-weight:600">
    <i class="bi bi-check-circle-fill"></i> <?= $success ?>
</div>
<?php endif; ?>

<form method="POST">
    <div class="row g-3">
        <div class="col-md-7">
            <div class="card-custom mb-3">
                <div class="card-header-custom"><div class="card-title"><i class="bi bi-shop"></i> Informasi Toko</div></div>
                <div style="padding:1.25rem">
                    <div class="mb-3">
                        <label class="form-label-custom">Nama Toko</label>
                        <input type="text" name="shop_name" class="form-control-custom" value="<?= sanitize($settings['shop_name'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label-custom">Alamat</label>
                        <input type="text" name="shop_address" class="form-control-custom" value="<?= sanitize($settings['shop_address'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label-custom">Nomor Telepon</label>
                        <input type="text" name="shop_phone" class="form-control-custom" value="<?= sanitize($settings['shop_phone'] ?? '') ?>">
                    </div>
                    <div>
                        <label class="form-label-custom">Email</label>
                        <input type="email" name="shop_email" class="form-control-custom" value="<?= sanitize($settings['shop_email'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <div class="card-custom mb-3">
                <div class="card-header-custom"><div class="card-title"><i class="bi bi-receipt"></i> Pengaturan Transaksi</div></div>
                <div style="padding:1.25rem">
                    <div class="mb-3">
                        <label class="form-label-custom">Pajak (%)</label>
                        <input type="number" name="tax_percent" class="form-control-custom" value="<?= sanitize($settings['tax_percent'] ?? '10') ?>" min="0" max="100">
                    </div>
                    <div>
                        <label class="form-label-custom">Pesan Footer Struk</label>
                        <textarea name="receipt_footer" class="form-control-custom" rows="3"><?= sanitize($settings['receipt_footer'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card-custom mb-3">
                <div class="card-header-custom"><div class="card-title"><i class="bi bi-info-circle"></i> Informasi Sistem</div></div>
                <div style="padding:1.25rem;font-size:0.85rem">
                    <div style="display:flex;justify-content:space-between;padding:0.4rem 0;border-bottom:1px dashed var(--border)">
                        <span style="color:var(--text-muted)">Versi</span>
                        <span class="fw-600"><?= APP_VERSION ?></span>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:0.4rem 0;border-bottom:1px dashed var(--border)">
                        <span style="color:var(--text-muted)">PHP</span>
                        <span class="fw-600"><?= PHP_VERSION ?></span>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:0.4rem 0;border-bottom:1px dashed var(--border)">
                        <span style="color:var(--text-muted)">Database</span>
                        <span class="fw-600"><?= DB_NAME ?></span>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:0.4rem 0">
                        <span style="color:var(--text-muted)">Server</span>
                        <span class="fw-600"><?= $_SERVER['SERVER_SOFTWARE'] ?? 'Apache/Nginx' ?></span>
                    </div>
                </div>
            </div>

            <div class="card-custom mb-3">
                <div class="card-header-custom"><div class="card-title"><i class="bi bi-qr-code"></i> Akses Self Order</div></div>
                <div style="padding:1.25rem">
                    <p style="font-size:0.82rem;color:var(--text-muted);margin-bottom:0.75rem">
                        Bagikan URL berikut atau QR Code meja untuk akses self-order pelanggan:
                    </p>
                    <div style="background:var(--cream);border:1.5px solid var(--border);border-radius:8px;padding:0.75rem;font-size:0.78rem;word-break:break-all;color:var(--accent);font-weight:600">
                        <?= APP_URL ?>/menu.php?table=<span style="color:var(--primary)">{table_id}</span>
                    </div>
                    <a href="<?= APP_URL ?>/tables.php" class="btn-sm-custom btn-outline-sm mt-2">
                        <i class="bi bi-table"></i> Kelola Meja & QR
                    </a>
                </div>
            </div>

            <div class="card-custom">
                <div class="card-header-custom"><div class="card-title"><i class="bi bi-people-fill"></i> Akun Demo</div></div>
                <div style="padding:1.25rem;font-size:0.82rem">
                    <div style="display:flex;justify-content:space-between;padding:0.3rem 0;border-bottom:1px dashed var(--border)">
                        <span>Admin</span><code>admin / password</code>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:0.3rem 0;border-bottom:1px dashed var(--border)">
                        <span>Kasir</span><code>kasir / password</code>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:0.3rem 0">
                        <span>Barista</span><code>barista / password</code>
                    </div>
                    <p style="margin-top:0.75rem;color:var(--text-muted);font-size:0.75rem">Password semua akun demo: <strong>password</strong></p>
                </div>
            </div>
        </div>
    </div>

    <div style="margin-top:1rem">
        <button type="submit" name="save_settings" class="btn-sm-custom btn-accent-sm" style="padding:0.65rem 2rem;font-size:0.9rem">
            <i class="bi bi-save-fill"></i> Simpan Pengaturan
        </button>
    </div>
</form>

<?php require_once __DIR__ . '/includes/footer_admin.php'; ?>
