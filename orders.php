<?php
$pageTitle = 'Daftar Pesanan';
$activePage = 'orders';
require_once __DIR__ . '/includes/header_admin.php';
requireLogin(['admin','cashier']);

$date = $_GET['date'] ?? date('Y-m-d');
$status = $_GET['status'] ?? '';

$where = "DATE(o.created_at) = '" . db()->escape($date) . "'";
if ($status) $where .= " AND o.status = '" . db()->escape($status) . "'";

$orders = db()->fetchAll("
    SELECT o.*, ct.table_number, s.name as staff_name,
           COUNT(oi.id) as item_count
    FROM orders o
    LEFT JOIN coffee_tables ct ON ct.id = o.table_id
    LEFT JOIN staff s ON s.id = o.staff_id
    LEFT JOIN order_items oi ON oi.order_id = o.id
    WHERE $where
    GROUP BY o.id
    ORDER BY o.created_at DESC
");

$totalRev = array_sum(array_filter(array_column($orders, 'total'), fn($o) => true));
$paid = array_filter($orders, fn($o) => $o['payment_status'] === 'paid');
?>
<meta name="app-url" content="<?= APP_URL ?>">

<!-- Filters -->
<div style="display:flex;gap:0.75rem;align-items:center;margin-bottom:1.25rem;flex-wrap:wrap">
    <input type="date" value="<?= $date ?>" onchange="applyFilter()" id="filterDate"
           style="padding:0.5rem 0.75rem;border:1.5px solid var(--border);border-radius:10px;font-family:inherit;font-size:0.85rem">
    <select onchange="applyFilter()" id="filterStatus"
            style="padding:0.5rem 0.75rem;border:1.5px solid var(--border);border-radius:10px;font-family:inherit;font-size:0.85rem">
        <option value="" <?= !$status?'selected':'' ?>>Semua Status</option>
        <option value="pending" <?= $status==='pending'?'selected':'' ?>>Pending</option>
        <option value="process" <?= $status==='process'?'selected':'' ?>>Diproses</option>
        <option value="done" <?= $status==='done'?'selected':'' ?>>Selesai</option>
        <option value="paid" <?= $status==='paid'?'selected':'' ?>>Dibayar</option>
        <option value="cancelled" <?= $status==='cancelled'?'selected':'' ?>>Dibatalkan</option>
    </select>
    <span style="font-size:0.85rem;color:var(--text-muted)"><?= count($orders) ?> pesanan · <span class="fw-700 text-accent"><?= formatCurrency(array_sum(array_column(array_filter($orders, fn($o) => $o['payment_status']==='paid'), 'total'))) ?></span> terkumpul</span>
</div>

<div class="card-custom">
    <div style="overflow-x:auto">
        <table class="table-custom">
            <thead>
                <tr>
                    <th>No. Pesanan</th>
                    <th>Meja/Tipe</th>
                    <th>Item</th>
                    <th>Total</th>
                    <th>Pembayaran</th>
                    <th>Status Bayar</th>
                    <th>Status Pesanan</th>
                    <th>Kasir</th>
                    <th>Waktu</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $o): ?>
                <tr>
                    <td class="fw-700" style="font-size:0.82rem"><?= sanitize($o['order_number']) ?></td>
                    <td>
                        <?php if ($o['table_number']): ?>
                        <span>🪑 Meja <?= sanitize($o['table_number']) ?></span>
                        <?php else: ?>
                        <span>🛍️ <?= $o['order_type']==='takeaway'?'Takeaway':'Dine In' ?></span>
                        <?php endif; ?>
                        <?php if ($o['order_source'] === 'self_order'): ?>
                        <div style="font-size:0.68rem;color:var(--accent)">📱 Self Order</div>
                        <?php endif; ?>
                    </td>
                    <td><?= $o['item_count'] ?> item</td>
                    <td class="fw-700 text-accent"><?= formatCurrency($o['total']) ?></td>
                    <td style="font-size:0.82rem">
                        <?php $pm=['cash'=>'💵 Tunai','transfer'=>'🏦 Transfer','qris'=>'📱 QRIS']; ?>
                        <?= $pm[$o['payment_method']] ?? $o['payment_method'] ?>
                    </td>
                    <td>
                        <?= $o['payment_status']==='paid'
                            ? '<span class="badge-status badge-done">Lunas</span>'
                            : '<span class="badge-status badge-pending">Belum Bayar</span>' ?>
                    </td>
                    <td><?= statusBadge($o['status']) ?></td>
                    <td style="font-size:0.78rem"><?= sanitize($o['staff_name'] ?? 'Self Order') ?></td>
                    <td style="font-size:0.78rem;color:var(--text-muted)"><?= formatDate($o['created_at'],'H:i') ?></td>
                    <td>
                        <div style="display:flex;gap:0.3rem">
                            <a href="<?= APP_URL ?>/receipt.php?order=<?= $o['id'] ?>&cashier=1" target="_blank" class="btn-sm-custom btn-outline-sm" title="Struk">
                                <i class="bi bi-receipt"></i>
                            </a>
                            <?php if ($o['status'] !== 'paid' && $o['status'] !== 'cancelled'): ?>
                            <?php if ($o['payment_status'] !== 'paid'): ?>
                            <button class="btn-sm-custom btn-accent-sm" onclick="markPaid(<?= $o['id'] ?>)" title="Tandai Lunas">
                                <i class="bi bi-check-circle"></i>
                            </button>
                            <?php endif; ?>
                            <button class="btn-sm-custom btn-danger-sm" onclick="cancelOrder(<?= $o['id'] ?>)" title="Batalkan">
                                <i class="bi bi-x-circle"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (!$orders): ?>
                <tr><td colspan="10" style="text-align:center;padding:3rem;color:var(--text-muted)">Tidak ada pesanan untuk tanggal ini</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
const APP_URL = '<?= APP_URL ?>';
function applyFilter(){
    const d=document.getElementById('filterDate').value;
    const s=document.getElementById('filterStatus').value;
    window.location.href=`${APP_URL}/orders.php?date=${d}&status=${s}`;
}
function markPaid(id){
    fetch(`${APP_URL}/api/orders.php`,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'update_status',id,status:'paid'})})
    .then(r=>r.json()).then(d=>{if(d.success){Admin.showToast('Pesanan ditandai lunas','success');setTimeout(()=>location.reload(),600);}});
}
function cancelOrder(id){
    Admin.confirmDelete('Batalkan pesanan ini?',()=>{
        fetch(`${APP_URL}/api/orders.php`,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'cancel',id})})
        .then(r=>r.json()).then(d=>{if(d.success){Admin.showToast('Pesanan dibatalkan','success');setTimeout(()=>location.reload(),600);}});
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer_admin.php'; ?>
