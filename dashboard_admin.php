<?php
$pageTitle = 'Dashboard Admin';
$activePage = 'dashboard';
require_once __DIR__ . '/includes/header_admin.php';
requireLogin('admin');

// Today stats
$today = date('Y-m-d');
$revenue = db()->fetchValue("SELECT COALESCE(SUM(total),0) FROM orders WHERE DATE(created_at)='$today' AND payment_status='paid'");
$orders  = db()->fetchValue("SELECT COUNT(*) FROM orders WHERE DATE(created_at)='$today'");
$pending = db()->fetchValue("SELECT COUNT(*) FROM orders WHERE status='pending' AND DATE(created_at)='$today'");
$menus   = db()->fetchValue("SELECT COUNT(*) FROM menus WHERE is_available=1");

// Weekly revenue
$weekly = db()->fetchAll("SELECT DATE(created_at) as day, SUM(total) as rev FROM orders WHERE payment_status='paid' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) GROUP BY DATE(created_at) ORDER BY day");

// Top menus today
$topMenus = db()->fetchAll("SELECT oi.menu_name, SUM(oi.quantity) as qty, SUM(oi.subtotal) as rev FROM orders o JOIN order_items oi ON oi.order_id=o.id WHERE DATE(o.created_at)='$today' AND o.payment_status='paid' GROUP BY oi.menu_name ORDER BY qty DESC LIMIT 5");

// Recent orders
$recentOrders = db()->fetchAll("SELECT o.*, ct.table_number FROM orders o LEFT JOIN coffee_tables ct ON ct.id=o.table_id ORDER BY o.created_at DESC LIMIT 8");
?>
<meta name="app-url" content="<?= APP_URL ?>">

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon coffee"><i class="bi bi-currency-dollar"></i></div>
            <div>
                <div class="stat-value" style="font-size:1.1rem"><?= formatCurrency($revenue) ?></div>
                <div class="stat-label">Pendapatan Hari Ini</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon success"><i class="bi bi-receipt"></i></div>
            <div>
                <div class="stat-value"><?= $orders ?></div>
                <div class="stat-label">Total Pesanan</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon danger"><i class="bi bi-clock"></i></div>
            <div>
                <div class="stat-value"><?= $pending ?></div>
                <div class="stat-label">Menunggu Proses</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon info"><i class="bi bi-journal-richtext"></i></div>
            <div>
                <div class="stat-value"><?= $menus ?></div>
                <div class="stat-label">Menu Aktif</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <!-- Revenue Chart -->
    <div class="col-md-8">
        <div class="card-custom">
            <div class="card-header-custom">
                <div class="card-title">Pendapatan 7 Hari Terakhir</div>
            </div>
            <div style="padding:1rem">
                <div class="chart-container">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Menus -->
    <div class="col-md-4">
        <div class="card-custom h-100">
            <div class="card-header-custom">
                <div class="card-title">Menu Terlaris Hari Ini</div>
            </div>
            <div style="padding:0.75rem">
                <?php if ($topMenus): ?>
                <?php foreach ($topMenus as $i => $m): ?>
                <div style="display:flex;align-items:center;gap:0.75rem;padding:0.5rem 0;border-bottom:1px dashed var(--border)">
                    <div style="width:24px;height:24px;background:<?= ['var(--accent)','#0d6efd','#198754','#6f42c1','#fd7e14'][$i] ?>;color:#fff;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:0.72rem;font-weight:700;flex-shrink:0"><?= $i+1 ?></div>
                    <div style="flex:1;min-width:0">
                        <div class="fw-600" style="font-size:0.82rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= sanitize($m['menu_name']) ?></div>
                        <div style="font-size:0.72rem;color:var(--text-muted)"><?= $m['qty'] ?>x terjual</div>
                    </div>
                    <div class="fw-700 text-accent" style="font-size:0.8rem;white-space:nowrap"><?= formatCurrency($m['rev']) ?></div>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <div style="text-align:center;padding:2rem;color:var(--text-muted);font-size:0.85rem">Belum ada penjualan hari ini</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Recent Orders -->
<div class="card-custom">
    <div class="card-header-custom">
        <div class="card-title">Pesanan Terbaru</div>
        <a href="<?= APP_URL ?>/orders.php" class="btn-sm-custom btn-outline-sm">Lihat Semua</a>
    </div>
    <div style="overflow-x:auto">
        <table class="table-custom">
            <thead>
                <tr>
                    <th>No. Pesanan</th>
                    <th>Meja</th>
                    <th>Tipe</th>
                    <th>Total</th>
                    <th>Pembayaran</th>
                    <th>Status</th>
                    <th>Waktu</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentOrders as $o): ?>
                <tr>
                    <td><span class="fw-600"><?= sanitize($o['order_number']) ?></span></td>
                    <td><?= $o['table_number'] ? 'Meja ' . sanitize($o['table_number']) : '-' ?></td>
                    <td><?= $o['order_type'] === 'dine_in' ? '🪑 Dine In' : '🛍️ Takeaway' ?></td>
                    <td class="fw-700 text-accent"><?= formatCurrency($o['total']) ?></td>
                    <td>
                        <?php $pm = ['cash'=>'Tunai','transfer'=>'Transfer','qris'=>'QRIS']; ?>
                        <?= $pm[$o['payment_method']] ?? $o['payment_method'] ?>
                    </td>
                    <td><?= statusBadge($o['status']) ?></td>
                    <td style="font-size:0.78rem;color:var(--text-muted)"><?= formatDate($o['created_at'], 'H:i') ?></td>
                    <td>
                        <a href="<?= APP_URL ?>/receipt.php?order=<?= $o['id'] ?>&cashier=1" class="btn-sm-custom btn-outline-sm" target="_blank">
                            <i class="bi bi-receipt"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (!$recentOrders): ?>
                <tr><td colspan="8" style="text-align:center;padding:2rem;color:var(--text-muted)">Belum ada pesanan</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Revenue Chart
const weeklyData = <?= json_encode($weekly) ?>;
const labels = weeklyData.map(d => {
    const dt = new Date(d.day);
    return dt.toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short' });
});
const revenues = weeklyData.map(d => parseFloat(d.rev) || 0);

new Chart(document.getElementById('revenueChart'), {
    type: 'bar',
    data: {
        labels: labels.length ? labels : ['Tidak ada data'],
        datasets: [{
            label: 'Pendapatan (Rp)',
            data: revenues.length ? revenues : [0],
            backgroundColor: 'rgba(200,129,58,0.15)',
            borderColor: '#C8813A',
            borderWidth: 2,
            borderRadius: 8,
            borderSkipped: false,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false }, tooltip: {
            callbacks: { label: ctx => 'Rp ' + new Intl.NumberFormat('id-ID').format(ctx.raw) }
        }},
        scales: {
            y: { beginAtZero: true, grid: { color: '#f0e8e0' }, ticks: { callback: v => 'Rp ' + (v/1000) + 'K' } },
            x: { grid: { display: false } }
        }
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer_admin.php'; ?>
