<?php
$pageTitle = 'Kitchen Display';
$activePage = 'kitchen';
require_once __DIR__ . '/includes/header_admin.php';
requireLogin(['admin','cashier','barista']);

$orders = db()->fetchAll("
    SELECT o.id, o.order_number, o.status, o.created_at, o.order_type, o.notes,
           ct.table_number,
           TIMESTAMPDIFF(MINUTE, o.created_at, NOW()) as minutes_ago
    FROM orders o
    LEFT JOIN coffee_tables ct ON ct.id = o.table_id
    WHERE o.status IN ('pending','process')
      AND DATE(o.created_at) = CURDATE()
    ORDER BY FIELD(o.status,'pending','process'), o.created_at ASC
    LIMIT 50
");

foreach ($orders as &$order) {
    $order['items'] = db()->fetchAll("
        SELECT oi.menu_name, oi.quantity, oi.notes,
               GROUP_CONCAT(oia.addon_option_name SEPARATOR ', ') as addons
        FROM order_items oi
        LEFT JOIN order_item_addons oia ON oia.order_item_id = oi.id
        WHERE oi.order_id = {$order['id']}
        GROUP BY oi.id
    ");
    $m = $order['minutes_ago'];
    $order['time_ago'] = $m < 1 ? 'Baru saja' : ($m < 60 ? $m . ' menit lalu' : floor($m/60) . ' jam lalu');
    $order['status_label'] = match($order['status']) { 'pending' => 'Menunggu', 'process' => 'Diproses', default => $order['status'] };
}
?>
<meta name="app-url" content="<?= APP_URL ?>">

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;flex-wrap:wrap;gap:0.75rem">
    <div>
        <h5 class="fw-700 mb-0">Kitchen Display System</h5>
        <small class="text-muted">Auto-refresh setiap 10 detik · <?= date('H:i') ?></small>
    </div>
    <div style="display:flex;gap:0.5rem;align-items:center">
        <span style="display:flex;align-items:center;gap:0.4rem;font-size:0.8rem">
            <span style="width:10px;height:10px;background:#ffc107;border-radius:50%;display:inline-block"></span> Menunggu
            <span style="width:10px;height:10px;background:#0d6efd;border-radius:50%;display:inline-block;margin-left:0.5rem"></span> Diproses
        </span>
        <button onclick="location.reload()" class="btn-sm-custom btn-outline-sm"><i class="bi bi-arrow-clockwise"></i> Refresh</button>
    </div>
</div>

<div class="kitchen-grid" id="kitchenGrid">
    <?php if (empty($orders)): ?>
    <div style="grid-column:1/-1;text-align:center;padding:4rem 2rem">
        <div style="font-size:4rem;margin-bottom:1rem">✅</div>
        <div class="fw-700" style="font-size:1.1rem;color:var(--text-primary)">Semua Pesanan Selesai</div>
        <div style="color:var(--text-muted);font-size:0.875rem">Tidak ada pesanan yang perlu diproses saat ini</div>
    </div>
    <?php endif; ?>

    <?php foreach ($orders as $order): ?>
    <div class="kitchen-card <?= $order['status'] ?> fade-in" id="order_<?= $order['id'] ?>">
        <div class="kitchen-card-header">
            <div>
                <div style="font-size:1rem"><?= sanitize($order['order_number']) ?></div>
                <?php if ($order['table_number']): ?>
                <div style="font-size:0.72rem;margin-top:0.1rem">🪑 Meja <?= sanitize($order['table_number']) ?></div>
                <?php elseif ($order['order_type'] === 'takeaway'): ?>
                <div style="font-size:0.72rem;margin-top:0.1rem">🛍️ Takeaway</div>
                <?php endif; ?>
            </div>
            <div style="text-align:right">
                <div style="font-size:0.75rem;opacity:0.7"><?= sanitize($order['time_ago']) ?></div>
                <span class="badge-status badge-<?= $order['status'] ?>"><?= sanitize($order['status_label']) ?></span>
            </div>
        </div>

        <div class="kitchen-items">
            <?php foreach ($order['items'] as $item): ?>
            <div class="kitchen-item">
                <span class="kitchen-item-qty"><?= $item['quantity'] ?>x</span>
                <div style="flex:1">
                    <div style="font-weight:600;font-size:0.875rem"><?= sanitize($item['menu_name']) ?></div>
                    <?php if ($item['addons']): ?>
                    <div style="font-size:0.72rem;color:var(--text-muted)"><?= sanitize($item['addons']) ?></div>
                    <?php endif; ?>
                    <?php if ($item['notes']): ?>
                    <div style="font-size:0.72rem;color:var(--accent)"><i class="bi bi-pencil"></i> <?= sanitize($item['notes']) ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>

            <?php if ($order['notes']): ?>
            <div style="margin-top:0.5rem;padding:0.4rem 0.6rem;background:var(--accent-pale);border-radius:6px;font-size:0.78rem;color:var(--text-secondary)">
                <i class="bi bi-chat-quote-fill text-accent"></i> <?= sanitize($order['notes']) ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="kitchen-card-footer">
            <?php if ($order['status'] === 'pending'): ?>
            <button class="btn-sm-custom btn-primary-sm w-100" onclick="updateOrderStatus(<?= $order['id'] ?>, 'process')">
                <i class="bi bi-play-fill"></i> Mulai Proses
            </button>
            <?php elseif ($order['status'] === 'process'): ?>
            <button class="btn-sm-custom btn-accent-sm w-100" onclick="updateOrderStatus(<?= $order['id'] ?>, 'done')">
                <i class="bi bi-check-lg"></i> Tandai Selesai
            </button>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<script>
const APP_URL = '<?= APP_URL ?>';

function updateOrderStatus(id, status) {
    fetch(`${APP_URL}/api/orders.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'update_status', id, status })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            Admin.showToast(status === 'process' ? 'Pesanan mulai diproses' : 'Pesanan selesai!', 'success');
            setTimeout(() => location.reload(), 800);
        }
    });
}

// Auto-refresh
setInterval(() => {
    fetch(`${APP_URL}/api/orders.php?action=kitchen_list`)
        .then(r => r.json())
        .then(data => {
            if (data.success) renderKitchenCards(data.orders);
        });
}, 10000);
</script>

<?php require_once __DIR__ . '/includes/footer_admin.php'; ?>
