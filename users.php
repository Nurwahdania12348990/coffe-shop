<?php
$pageTitle = 'Manajemen Staff';
$activePage = 'users';
require_once __DIR__ . '/includes/header_admin.php';
requireLogin('admin');

$staff = db()->fetchAll("SELECT * FROM staff ORDER BY role, name");
$roleColors = ['admin'=>'badge-process','cashier'=>'badge-done','barista'=>'badge-pending'];
$roleLabels = ['admin'=>'Admin','cashier'=>'Kasir','barista'=>'Barista'];
?>
<meta name="app-url" content="<?= APP_URL ?>">

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem">
    <div></div>
    <button class="btn-sm-custom btn-accent-sm" onclick="openStaffModal()">
        <i class="bi bi-person-plus-fill"></i> Tambah Staff
    </button>
</div>

<div class="card-custom">
    <div class="card-header-custom">
        <div class="card-title">Daftar Staff (<?= count($staff) ?>)</div>
    </div>
    <div style="overflow-x:auto">
        <table class="table-custom">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>No. HP</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Bergabung</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($staff as $s): ?>
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:0.6rem">
                            <div style="width:34px;height:34px;background:var(--accent);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;color:#fff;font-size:0.85rem;flex-shrink:0">
                                <?= strtoupper(substr($s['name'],0,1)) ?>
                            </div>
                            <div class="fw-600"><?= sanitize($s['name']) ?></div>
                        </div>
                    </td>
                    <td><code style="background:var(--cream);padding:0.15rem 0.4rem;border-radius:4px;font-size:0.82rem"><?= sanitize($s['username']) ?></code></td>
                    <td><span class="badge-status <?= $roleColors[$s['role']] ?? 'badge-cancelled' ?>"><?= $roleLabels[$s['role']] ?? $s['role'] ?></span></td>
                    <td style="font-size:0.85rem"><?= sanitize($s['phone'] ?? '-') ?></td>
                    <td style="font-size:0.82rem"><?= sanitize($s['email'] ?? '-') ?></td>
                    <td>
                        <?= $s['is_active']
                            ? '<span class="badge-status badge-done">Aktif</span>'
                            : '<span class="badge-status badge-cancelled">Nonaktif</span>' ?>
                    </td>
                    <td style="font-size:0.78rem;color:var(--text-muted)"><?= formatDate($s['created_at'],'d M Y') ?></td>
                    <td>
                        <button class="btn-sm-custom btn-outline-sm" onclick="editStaff(<?= htmlspecialchars(json_encode($s), ENT_QUOTES) ?>)">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <?php $me = currentUser(); if ($s['id'] != $me['id']): ?>
                        <button class="btn-sm-custom btn-danger-sm ms-1" onclick="deleteStaff(<?= $s['id'] ?>, '<?= sanitize($s['name']) ?>')">
                            <i class="bi bi-trash"></i>
                        </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="staffModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:440px">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-header modal-header-custom">
                <h5 class="modal-title fw-700" id="staffModalTitle">Tambah Staff</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:invert(1)"></button>
            </div>
            <div class="modal-body p-3">
                <input type="hidden" id="staffId">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label-custom">Nama Lengkap *</label>
                        <input type="text" class="form-control-custom" id="staffName" placeholder="Contoh: Budi Santoso">
                    </div>
                    <div class="col-12">
                        <label class="form-label-custom">Username *</label>
                        <input type="text" class="form-control-custom" id="staffUsername" placeholder="Contoh: budi123">
                    </div>
                    <div class="col-12" id="passwordGroup">
                        <label class="form-label-custom">Password * <span id="passNote" style="color:var(--text-muted);font-weight:400">(kosongkan jika tidak diubah)</span></label>
                        <input type="password" class="form-control-custom" id="staffPassword" placeholder="Min. 6 karakter">
                    </div>
                    <div class="col-6">
                        <label class="form-label-custom">Role *</label>
                        <select class="form-select-custom" id="staffRole">
                            <option value="cashier">Kasir</option>
                            <option value="barista">Barista</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label-custom">Status</label>
                        <select class="form-select-custom" id="staffActive">
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label-custom">No. HP</label>
                        <input type="text" class="form-control-custom" id="staffPhone" placeholder="08xxxxxxxxxx">
                    </div>
                    <div class="col-12">
                        <label class="form-label-custom">Email</label>
                        <input type="email" class="form-control-custom" id="staffEmail" placeholder="email@example.com">
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn-sm-custom btn-outline-sm" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn-sm-custom btn-accent-sm" style="padding:0.5rem 1.5rem" onclick="saveStaff()">
                    <i class="bi bi-check-lg"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const APP_URL = '<?= APP_URL ?>';
function openStaffModal(title='Tambah Staff'){
    document.getElementById('staffModalTitle').textContent=title;
    document.getElementById('staffId').value='';
    document.getElementById('staffName').value='';
    document.getElementById('staffUsername').value='';
    document.getElementById('staffPassword').value='';
    document.getElementById('staffRole').value='cashier';
    document.getElementById('staffActive').value='1';
    document.getElementById('staffPhone').value='';
    document.getElementById('staffEmail').value='';
    document.getElementById('passNote').textContent='';
    new bootstrap.Modal('#staffModal').show();
}
function editStaff(s){
    openStaffModal('Edit Staff');
    document.getElementById('staffId').value=s.id;
    document.getElementById('staffName').value=s.name;
    document.getElementById('staffUsername').value=s.username;
    document.getElementById('staffRole').value=s.role;
    document.getElementById('staffActive').value=s.is_active;
    document.getElementById('staffPhone').value=s.phone||'';
    document.getElementById('staffEmail').value=s.email||'';
    document.getElementById('passNote').textContent='(kosongkan jika tidak diubah)';
}
function saveStaff(){
    const id=document.getElementById('staffId').value;
    const payload={
        action:id?'update':'create',id,
        name:document.getElementById('staffName').value,
        username:document.getElementById('staffUsername').value,
        password:document.getElementById('staffPassword').value,
        role:document.getElementById('staffRole').value,
        is_active:document.getElementById('staffActive').value,
        phone:document.getElementById('staffPhone').value,
        email:document.getElementById('staffEmail').value
    };
    fetch(`${APP_URL}/api/staff.php`,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)})
    .then(r=>r.json()).then(data=>{
        if(data.success){Admin.showToast(data.message,'success');bootstrap.Modal.getInstance('#staffModal').hide();setTimeout(()=>location.reload(),600);}
        else Admin.showToast(data.message,'error');
    });
}
function deleteStaff(id,name){
    Admin.confirmDelete(`Hapus staff "${name}"?`,()=>{
        fetch(`${APP_URL}/api/staff.php`,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'delete',id})})
        .then(r=>r.json()).then(data=>{
            if(data.success){Admin.showToast(data.message,'success');setTimeout(()=>location.reload(),600);}
            else Admin.showToast(data.message,'error');
        });
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer_admin.php'; ?>
