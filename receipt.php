<?php
require_once __DIR__ . '/includes/functions.php';
startSession();

$orderId = (int)($_GET['order'] ?? 0);
$isCashier = !empty($_GET['cashier']);

if (!$orderId) { header('Location: ' . APP_URL . '/menu.php'); exit; }

$order = db()->fetchOne("SELECT o.*, ct.table_number, s.name as staff_name FROM orders o LEFT JOIN coffee_tables ct ON ct.id=o.table_id LEFT JOIN staff s ON s.id=o.staff_id WHERE o.id=$orderId");
if (!$order) { header('Location: ' . APP_URL . '/menu.php'); exit; }

$items = db()->fetchAll("SELECT oi.*, GROUP_CONCAT(CONCAT(oia.addon_group_name,': ',oia.addon_option_name) SEPARATOR '\n') as addon_lines FROM order_items oi LEFT JOIN order_item_addons oia ON oia.order_item_id=oi.id WHERE oi.order_id=$orderId GROUP BY oi.id");
$settings = getSettings();
$shopName = $settings['shop_name'] ?? 'Brewed & Bold Coffee';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pesanan - <?= sanitize($order['order_number']) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/customer.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; }
            .receipt-card { box-shadow: none !important; border: none !important; }
        }
    </style>
</head>
<body style="background:var(--cream)">

<div class="no-print" style="background:var(--primary);padding:0.75rem 0">
    <div class="container d-flex align-items-center gap-2">
        <a href="<?= APP_URL ?>/menu.php" style="color:rgba(255,255,255,0.7);text-decoration:none;font-size:0.875rem"><i class="bi bi-arrow-left"></i> Menu</a>
        <span style="color:rgba(255,255,255,0.3)">|</span>
        <span style="color:#fff;font-weight:700;font-size:0.875rem"><i class="bi bi-cup-hot-fill"></i> <?= sanitize($shopName) ?></span>
    </div>
</div>

<div class="receipt-page">
    <div class="receipt-card">
        <div class="receipt-header">
            <div class="receipt-logo"><i class="bi bi-cup-hot-fill"></i></div>
            <h5 class="fw-800 mb-1"><?= sanitize($shopName) ?></h5>
            <div style="font-size:0.8rem;opacity:0.7"><?= sanitize($settings['shop_address'] ?? '') ?></div>
            <div style="font-size:0.8rem;opacity:0.7"><?= sanitize($settings['shop_phone'] ?? '') ?></div>
            <div style="margin-top:1rem;background:rgba(255,255,255,0.1);padding:0.5rem 1rem;border-radius:8px;display:inline-block">
                <div style="font-size:0.7rem;opacity:0.7">No. Pesanan</div>
                <div style="font-weight:800;letter-spacing:1px"><?= sanitize($order['order_number']) ?></div>
            </div>
        </div>

        <div class="receipt-body">
            <!-- Order Info -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.5rem;margin-bottom:1rem;font-size:0.8rem">
                <div>
                    <div style="color:var(--text-muted)">Tanggal</div>
                    <div class="fw-600"><?= formatDate($order['created_at']) ?></div>
                </div>
                <div>
                    <div style="color:var(--text-muted)">Tipe</div>
                    <div class="fw-600"><?= $order['order_type'] === 'dine_in' ? 'Dine In' : 'Takeaway' ?></div>
                </div>
                <?php if ($order['table_number']): ?>
                <div>
                    <div style="color:var(--text-muted)">Meja</div>
                    <div class="fw-600">Meja <?= sanitize($order['table_number']) ?></div>
                </div>
                <?php endif; ?>
                <div>
                    <div style="color:var(--text-muted)">Pelanggan</div>
                    <div class="fw-600"><?= sanitize($order['customer_name']) ?></div>
                </div>
            </div>

            <div style="border-top:2px dashed var(--border);margin:1rem 0"></div>

            <!-- Items -->
            <?php foreach ($items as $item): ?>
            <div style="margin-bottom:0.6rem">
                <div class="receipt-row" style="border:none;padding:0;margin:0;align-items:start">
                    <div>
                        <div class="fw-600" style="font-size:0.875rem"><?= sanitize($item['menu_name']) ?></div>
                        <?php if ($item['addon_lines']): ?>
                        <div style="font-size:0.72rem;color:var(--text-muted);margin-top:0.1rem">
                            <?php foreach (explode("\n", $item['addon_lines']) as $line): ?>
                            <div><?= sanitize($line) ?></div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div style="text-align:right;white-space:nowrap">
                        <div style="font-size:0.78rem;color:var(--text-muted)"><?= $item['quantity'] ?>x <?= formatCurrency($item['menu_price']) ?></div>
                        <div class="fw-700 small"><?= formatCurrency($item['subtotal']) ?></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <div style="border-top:2px dashed var(--border);margin:1rem 0"></div>

            <!-- Totals -->
            <div class="receipt-row"><span>Subtotal</span><span><?= formatCurrency($order['subtotal']) ?></span></div>
            <div class="receipt-row"><span>Pajak (<?= TAX_PERCENT ?>%)</span><span><?= formatCurrency($order['tax']) ?></span></div>
            <?php if ($order['discount'] > 0): ?>
            <div class="receipt-row" style="color:var(--success)"><span>Diskon</span><span>-<?= formatCurrency($order['discount']) ?></span></div>
            <?php endif; ?>
            <div class="receipt-row receipt-total"><span>TOTAL</span><span><?= formatCurrency($order['total']) ?></span></div>

            <?php if ($order['payment_method'] === 'cash'): ?>
            <div class="receipt-row" style="margin-top:0.5rem"><span>Bayar</span><span><?= formatCurrency($order['amount_paid']) ?></span></div>
            <div class="receipt-row"><span>Kembalian</span><span><?= formatCurrency($order['change_amount']) ?></span></div>
            <?php endif; ?>

            <div style="border-top:2px dashed var(--border);margin:1rem 0"></div>

            <!-- Payment -->
            <div style="text-align:center;margin-bottom:0.75rem">
                <?php
                $payIcons = ['cash' => '💵 Tunai', 'transfer' => '🏦 Transfer Bank', 'qris' => '📱 QRIS'];
                ?>
                <span style="background:var(--accent-pale);border:1px solid var(--accent);color:var(--accent);padding:0.3rem 1rem;border-radius:20px;font-size:0.8rem;font-weight:700">
                    <?= $payIcons[$order['payment_method']] ?? $order['payment_method'] ?>
                </span>
                <br><br>
                <?php if ($order['payment_status'] === 'paid'): ?>
                <span style="background:#d1e7dd;color:#0f5132;padding:0.3rem 1rem;border-radius:20px;font-size:0.8rem;font-weight:700">
                    ✅ LUNAS
                </span>
                <?php else: ?>
                <span style="background:#fff3cd;color:#664d03;padding:0.3rem 1rem;border-radius:20px;font-size:0.8rem;font-weight:700">
                    ⏳ BELUM DIBAYAR
                </span>
                <?php endif; ?>
            </div>

            <!-- Status -->
            <div style="text-align:center;margin-bottom:0.75rem">
                <?php
                $statusInfo = [
                    'pending'  => ['⏳', 'Pesanan diterima, menunggu diproses', '#856404'],
                    'process'  => ['👨‍🍳', 'Pesanan sedang diproses barista', '#0a58ca'],
                    'done'     => ['✅', 'Pesanan siap! Silakan ambil di konter', '#0f5132'],
                    'paid'     => ['✅', 'Transaksi selesai. Terima kasih!', '#0f5132'],
                ];
                $si = $statusInfo[$order['status']] ?? ['ℹ️', ucfirst($order['status']), '#666'];
                ?>
                <div style="font-size:1.5rem"><?= $si[0] ?></div>
                <div style="font-size:0.8rem;color:<?= $si[2] ?>;font-weight:600;margin-top:0.2rem"><?= $si[1] ?></div>
            </div>

            <!-- Footer Message -->
            <div style="text-align:center;border-top:2px dashed var(--border);padding-top:0.75rem;font-size:0.75rem;color:var(--text-muted)">
                <?= sanitize($settings['receipt_footer'] ?? 'Terima kasih!') ?>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="no-print text-center mt-3 d-flex gap-2 justify-content-center" style="max-width:480px;margin:0 auto">
        <button onclick="window.print()" class="btn-primary-custom" style="width:auto;flex:1">
            <i class="bi bi-printer"></i> Cetak Struk
        </button>
        <a href="<?= APP_URL ?>/menu.php" class="btn-primary-custom" style="background:var(--text-muted);width:auto;flex:1;text-decoration:none">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <?php if (!$isCashier && in_array($order['status'], ['pending','process'])): ?>
    <!-- Order tracking refresh -->
    <div class="no-print text-center mt-2" style="max-width:480px;margin:0 auto">
        <small class="text-muted">Status akan diperbarui otomatis</small>
        <div id="trackingStatus" style="margin-top:0.5rem"></div>
    </div>
    <script>
    function checkStatus() {
        fetch('<?= APP_URL ?>/api/orders.php?action=detail&id=<?= $orderId ?>')
            .then(r => r.json())
            .then(data => {
                if (data.success && data.order.status !== '<?= $order['status'] ?>') {
                    location.reload();
                }
            });
    }
    setInterval(checkStatus, 15000);
    </script>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
