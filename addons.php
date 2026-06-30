<?php
$pageTitle = 'Manajemen Add-On';
$activePage = 'addons';
require_once __DIR__ . '/includes/header_admin.php';
requireLogin('admin');

$groups = db()->fetchAll("SELECT ag.*, COUNT(ao.id) as option_count FROM addon_groups ag LEFT JOIN addon_options ao ON ao.addon_group_id=ag.id GROUP BY ag.id ORDER BY ag.name");
foreach ($groups as &$g) {
    $g['options'] = db()->fetchAll("SELECT * FROM addon_options WHERE addon_group_id={$g['id']} ORDER BY sort_order, name");
}
?>
<meta name="app-url" content="<?= APP_URL ?>">

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem">
    <div></div>
    <button class="btn-sm-custom btn-accent-sm" onclick="openGroupModal()">
        <i class="bi bi-plus-lg"></i> Tambah Grup Add-On
    </button>
</div>

<div class="row g-3">
    <?php foreach ($groups as $g): ?>
    <div class="col-md-6">
        <div class="card-custom">
            <div class="card-header-custom">
                <div>
                    <div class="card-title"><?= sanitize($g['name']) ?></div>
                    <div style="font-size:0.75rem;color:var(--text-muted);margin-top:0.1rem">
                        <?= $g['is_required'] ? '<span style="color:#c0392b">Wajib dipilih</span>' : 'Opsional' ?>
                        · Maks <?= $g['max_select'] ?> pilihan
                    </div>
                </div>
                <div style="display:flex;gap:0.4rem">
                    <button class="btn-sm-custom btn-outline-sm" onclick="addOption(<?= $g['id'] ?>, '<?= sanitize($g['name']) ?>')">
                        <i class="bi bi-plus"></i> Opsi
                    </button>
                    <button class="btn-sm-custom btn-outline-sm" onclick="editGroup(<?= htmlspecialchars(json_encode($g), ENT_QUOTES) ?>)">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn-sm-custom btn-danger-sm" onclick="deleteGroup(<?= $g['id'] ?>, '<?= sanitize($g['name']) ?>')">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
            <div style="padding:0.75rem">
                <?php if ($g['options']): ?>
                <?php foreach ($g['options'] as $opt): ?>
                <div style="display:flex;align-items:center;gap:0.75rem;padding:0.4rem 0.5rem;border-bottom:1px dashed var(--border)">
                    <div style="flex:1">
                        <span class="fw-600" style="font-size:0.85rem"><?= sanitize($opt['name']) ?></span>
                        <?= $opt['is_default'] ? ' <span style="background:#d1e7dd;color:#0f5132;padding:0.1rem 0.4rem;border-radius:4px;font-size:0.65rem;font-weight:700">DEFAULT</span>' : '' ?>
                    </div>
                    <div class="text-accent fw-700" style="font-size:0.82rem">
                        <?= $opt['price_add'] > 0 ? '+'.formatCurrency($opt['price_add']) : 'Gratis' ?>
                    </div>
                    <button class="btn-sm-custom btn-outline-sm" onclick="editOption(<?= htmlspecialchars(json_encode($opt), ENT_QUOTES) ?>, '<?= sanitize($g['name']) ?>')">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn-sm-custom btn-danger-sm" onclick="deleteOption(<?= $opt['id'] ?>, '<?= sanitize($opt['name']) ?>')">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <div style="text-align:center;padding:1rem;color:var(--text-muted);font-size:0.82rem">Belum ada opsi. Klik "+ Opsi" untuk menambahkan.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Group Modal -->
<div class="modal fade" id="groupModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:380px">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-header modal-header-custom">
                <h5 class="modal-title fw-700" id="groupModalTitle">Tambah Grup</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:invert(1)"></button>
            </div>
            <div class="modal-body p-3">
                <input type="hidden" id="groupId">
                <div class="mb-3">
                    <label class="form-label-custom">Nama Grup *</label>
                    <input type="text" class="form-control-custom" id="groupName" placeholder="Contoh: Ukuran, Jenis Susu">
                </div>
                <div class="mb-3">
                    <label class="form-label-custom">Wajib Dipilih?</label>
                    <select class="form-select-custom" id="groupRequired">
                        <option value="0">Tidak (Opsional)</option>
                        <option value="1">Ya (Wajib)</option>
                    </select>
                </div>
                <div>
                    <label class="form-label-custom">Maks. Pilihan</label>
                    <input type="number" class="form-control-custom" id="groupMax" value="1" min="1">
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn-sm-custom btn-outline-sm" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn-sm-custom btn-accent-sm" style="padding:0.5rem 1.5rem" onclick="saveGroup()">
                    <i class="bi bi-check-lg"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Option Modal -->
<div class="modal fade" id="optionModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:380px">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-header modal-header-custom">
                <h5 class="modal-title fw-700" id="optionModalTitle">Tambah Opsi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:invert(1)"></button>
            </div>
            <div class="modal-body p-3">
                <input type="hidden" id="optionId">
                <input type="hidden" id="optionGroupId">
                <div class="mb-3">
                    <label class="form-label-custom">Nama Opsi *</label>
                    <input type="text" class="form-control-custom" id="optionName" placeholder="Contoh: Large (16oz)">
                </div>
                <div class="mb-3">
                    <label class="form-label-custom">Harga Tambah (Rp)</label>
                    <input type="number" class="form-control-custom" id="optionPrice" value="0" min="0">
                </div>
                <div class="mb-3">
                    <label class="form-label-custom">Urutan</label>
                    <input type="number" class="form-control-custom" id="optionSort" value="0">
                </div>
                <div>
                    <label class="form-label-custom">Default?</label>
                    <select class="form-select-custom" id="optionDefault">
                        <option value="0">Tidak</option>
                        <option value="1">Ya (default terpilih)</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn-sm-custom btn-outline-sm" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn-sm-custom btn-accent-sm" style="padding:0.5rem 1.5rem" onclick="saveOption()">
                    <i class="bi bi-check-lg"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const APP_URL = '<?= APP_URL ?>';

// ---- Group CRUD ----
function openGroupModal(t='Tambah Grup Add-On'){
    document.getElementById('groupModalTitle').textContent=t;
    document.getElementById('groupId').value='';
    document.getElementById('groupName').value='';
    document.getElementById('groupRequired').value='0';
    document.getElementById('groupMax').value='1';
    new bootstrap.Modal('#groupModal').show();
}
function editGroup(g){
    openGroupModal('Edit Grup Add-On');
    document.getElementById('groupId').value=g.id;
    document.getElementById('groupName').value=g.name;
    document.getElementById('groupRequired').value=g.is_required;
    document.getElementById('groupMax').value=g.max_select;
}
function saveGroup(){
    const id=document.getElementById('groupId').value;
    const p={action:id?'update':'create',id,name:document.getElementById('groupName').value,is_required:document.getElementById('groupRequired').value,max_select:document.getElementById('groupMax').value};
    fetch(`${APP_URL}/api/addons.php`,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(p)})
    .then(r=>r.json()).then(d=>{
        if(d.success){Admin.showToast(d.message,'success');bootstrap.Modal.getInstance('#groupModal').hide();setTimeout(()=>location.reload(),600);}
        else Admin.showToast(d.message,'error');
    });
}
function deleteGroup(id,name){
    Admin.confirmDelete(`Hapus grup "${name}"? Semua opsinya juga akan terhapus.`,()=>{
        fetch(`${APP_URL}/api/addons.php`,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'delete_group',id})})
        .then(r=>r.json()).then(d=>{if(d.success){Admin.showToast(d.message,'success');setTimeout(()=>location.reload(),600);}else Admin.showToast(d.message,'error');});
    });
}

// ---- Option CRUD ----
function addOption(groupId, groupName){
    document.getElementById('optionModalTitle').textContent=`Tambah Opsi: ${groupName}`;
    document.getElementById('optionId').value='';
    document.getElementById('optionGroupId').value=groupId;
    document.getElementById('optionName').value='';
    document.getElementById('optionPrice').value='0';
    document.getElementById('optionSort').value='0';
    document.getElementById('optionDefault').value='0';
    new bootstrap.Modal('#optionModal').show();
}
function editOption(opt, groupName){
    document.getElementById('optionModalTitle').textContent=`Edit Opsi: ${groupName}`;
    document.getElementById('optionId').value=opt.id;
    document.getElementById('optionGroupId').value=opt.addon_group_id;
    document.getElementById('optionName').value=opt.name;
    document.getElementById('optionPrice').value=opt.price_add;
    document.getElementById('optionSort').value=opt.sort_order;
    document.getElementById('optionDefault').value=opt.is_default;
    new bootstrap.Modal('#optionModal').show();
}
function saveOption(){
    const id=document.getElementById('optionId').value;
    const p={action:id?'update_option':'create_option',id,addon_group_id:document.getElementById('optionGroupId').value,name:document.getElementById('optionName').value,price_add:document.getElementById('optionPrice').value,sort_order:document.getElementById('optionSort').value,is_default:document.getElementById('optionDefault').value};
    fetch(`${APP_URL}/api/addons.php`,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(p)})
    .then(r=>r.json()).then(d=>{
        if(d.success){Admin.showToast(d.message,'success');bootstrap.Modal.getInstance('#optionModal').hide();setTimeout(()=>location.reload(),600);}
        else Admin.showToast(d.message,'error');
    });
}
function deleteOption(id,name){
    Admin.confirmDelete(`Hapus opsi "${name}"?`,()=>{
        fetch(`${APP_URL}/api/addons.php`,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'delete_option',id})})
        .then(r=>r.json()).then(d=>{if(d.success){Admin.showToast(d.message,'success');setTimeout(()=>location.reload(),600);}else Admin.showToast(d.message,'error');});
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer_admin.php'; ?>
