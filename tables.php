<?php
$pageTitle = 'Manajemen Meja';
$activePage = 'tables';
require_once __DIR__ . '/includes/header_admin.php';
requireLogin('admin');

$tables = db()->fetchAll("SELECT t.*, (SELECT COUNT(*) FROM orders o WHERE o.table_id=t.id AND o.status NOT IN('cancelled','paid') AND DATE(o.created_at)=CURDATE()) as active_orders FROM coffee_tables t WHERE t.is_active=1 ORDER BY t.table_number");
?>
<meta name="app-url" content="<?= APP_URL ?>">

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem">
    <div></div>
    <button class="btn-sm-custom btn-accent-sm" onclick="openTableModal()">
        <i class="bi bi-plus-lg"></i> Tambah Meja
    </button>
</div>

<div class="row g-3">
    <?php foreach ($tables as $t): ?>
    <div class="col-6 col-md-3">
        <div class="card-custom" style="border-color:<?= $t['status']==='occupied'?'#0d6efd':($t['status']==='reserved'?'#ffc107':'var(--border)') ?>">
            <div style="padding:1.25rem;text-align:center">
                <div style="font-size:2.5rem;margin-bottom:0.5rem">
                    <?= $t['status']==='occupied' ? '🔴' : ($t['status']==='reserved' ? '🟡' : '🟢') ?>
                </div>
                <div class="fw-800" style="font-size:1.1rem">Meja <?= sanitize($t['table_number']) ?></div>
                <div style="font-size:0.78rem;color:var(--text-muted);margin-bottom:0.5rem"><?= $t['capacity'] ?> orang</div>
                <span class="badge-status <?= $t['status']==='available'?'badge-done':($t['status']==='occupied'?'badge-process':'badge-pending') ?>">
                    <?= ['available'=>'Tersedia','occupied'=>'Terisi','reserved'=>'Reserved'][$t['status']] ?>
                </span>
                <?php if ($t['active_orders'] > 0): ?>
                <div style="font-size:0.72rem;color:#0a58ca;margin-top:0.3rem"><?= $t['active_orders'] ?> pesanan aktif</div>
                <?php endif; ?>
                <div style="margin-top:1rem;display:flex;gap:0.4rem;justify-content:center;flex-wrap:wrap">
                    <a href="<?= APP_URL ?>/menu.php?table=<?= $t['id'] ?>" target="_blank" class="btn-sm-custom btn-outline-sm" title="Buka self-order">
                        <i class="bi bi-qr-code-scan"></i> Self Order
                    </a>
                    <button class="btn-sm-custom btn-outline-sm" onclick="editTable(<?= htmlspecialchars(json_encode($t), ENT_QUOTES) ?>)">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <?php if ($t['status'] !== 'available'): ?>
                    <button class="btn-sm-custom btn-outline-sm" onclick="resetTable(<?= $t['id'] ?>)" title="Reset status">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </button>
                    <?php endif; ?>
                    <button class="btn-sm-custom btn-danger-sm" onclick="deleteTable(<?= $t['id'] ?>, '<?= sanitize($t['table_number']) ?>')">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                <!-- QR URL info -->
                <div style="margin-top:0.75rem;padding:0.4rem;background:var(--cream);border-radius:6px;font-size:0.65rem;color:var(--text-muted);word-break:break-all">
                    <?= APP_URL ?>/menu.php?table=<?= $t['id'] ?>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Modal -->
<div class="modal fade" id="tableModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:380px">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-header modal-header-custom">
                <h5 class="modal-title fw-700" id="tableModalTitle">Tambah Meja</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:invert(1)"></button>
            </div>
            <div class="modal-body p-3">
                <input type="hidden" id="tableId">
                <div class="mb-3">
                    <label class="form-label-custom">Nomor Meja *</label>
                    <input type="text" class="form-control-custom" id="tableNumber" placeholder="Contoh: T01, BAR1">
                </div>
                <div class="mb-3">
                    <label class="form-label-custom">Kapasitas (orang)</label>
                    <input type="number" class="form-control-custom" id="tableCapacity" value="2" min="1">
                </div>
                <div>
                    <label class="form-label-custom">Status Aktif</label>
                    <select class="form-select-custom" id="tableActive">
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn-sm-custom btn-outline-sm" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn-sm-custom btn-accent-sm" style="padding:0.5rem 1.5rem" onclick="saveTable()">
                    <i class="bi bi-check-lg"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const APP_URL = '<?= APP_URL ?>';
function openTableModal(title='Tambah Meja'){
    document.getElementById('tableModalTitle').textContent=title;
    document.getElementById('tableId').value='';
    document.getElementById('tableNumber').value='';
    document.getElementById('tableCapacity').value='2';
    document.getElementById('tableActive').value='1';
    new bootstrap.Modal('#tableModal').show();
}
function editTable(t){
    openTableModal('Edit Meja');
    document.getElementById('tableId').value=t.id;
    document.getElementById('tableNumber').value=t.table_number;
    document.getElementById('tableCapacity').value=t.capacity;
    document.getElementById('tableActive').value=t.is_active;
}
function saveTable(){
    const id=document.getElementById('tableId').value;
    const payload={action:id?'update':'create',id,table_number:document.getElementById('tableNumber').value,capacity:document.getElementById('tableCapacity').value,is_active:document.getElementById('tableActive').value};
    fetch(`${APP_URL}/api/tables.php`,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)})
    .then(r=>r.json()).then(data=>{
        if(data.success){Admin.showToast(data.message,'success');bootstrap.Modal.getInstance('#tableModal').hide();setTimeout(()=>location.reload(),600);}
        else Admin.showToast(data.message,'error');
    });
}
function resetTable(id){
    fetch(`${APP_URL}/api/tables.php`,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'reset_status',id})})
    .then(r=>r.json()).then(()=>location.reload());
}
function deleteTable(id,num){
    Admin.confirmDelete(`Hapus meja "${num}"?`,()=>{
        fetch(`${APP_URL}/api/tables.php`,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'delete',id})})
        .then(r=>r.json()).then(data=>{
            if(data.success){Admin.showToast(data.message,'success');setTimeout(()=>location.reload(),600);}
            else Admin.showToast(data.message,'error');
        });
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer_admin.php'; ?>
