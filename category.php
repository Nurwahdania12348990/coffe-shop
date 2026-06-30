<?php
$pageTitle = 'Kategori Menu';
$activePage = 'category';
require_once __DIR__ . '/includes/header_admin.php';
requireLogin('admin');

$categories = db()->fetchAll("SELECT c.*, COUNT(m.id) as menu_count FROM categories c LEFT JOIN menus m ON m.category_id=c.id GROUP BY c.id ORDER BY c.sort_order");
$icons = ['bi-cup-hot-fill','bi-cup','bi-cup-straw','bi-egg-fried','bi-cake2','bi-bowl-hot','bi-heart-fill','bi-star-fill','bi-lightning-fill','bi-droplet-fill'];
?>
<meta name="app-url" content="<?= APP_URL ?>">

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem">
    <div></div>
    <button class="btn-sm-custom btn-accent-sm" onclick="openCatModal()">
        <i class="bi bi-plus-lg"></i> Tambah Kategori
    </button>
</div>

<div class="row g-3">
    <?php foreach ($categories as $c): ?>
    <div class="col-md-4 col-6">
        <div class="card-custom">
            <div style="padding:1.25rem">
                <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:0.75rem">
                    <div style="width:44px;height:44px;background:var(--accent-pale);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;color:var(--accent)">
                        <i class="<?= $c['icon'] ?>"></i>
                    </div>
                    <div>
                        <div class="fw-700"><?= sanitize($c['name']) ?></div>
                        <div style="font-size:0.75rem;color:var(--text-muted)"><?= $c['menu_count'] ?> menu</div>
                    </div>
                    <div style="margin-left:auto">
                        <?= $c['is_active'] 
                            ? '<span class="badge-status badge-done">Aktif</span>'
                            : '<span class="badge-status badge-cancelled">Nonaktif</span>' ?>
                    </div>
                </div>
                <?php if ($c['description']): ?>
                <p style="font-size:0.8rem;color:var(--text-muted);margin-bottom:0.75rem"><?= sanitize($c['description']) ?></p>
                <?php endif; ?>
                <div style="display:flex;gap:0.4rem">
                    <button class="btn-sm-custom btn-outline-sm" onclick="editCat(<?= htmlspecialchars(json_encode($c), ENT_QUOTES) ?>)">
                        <i class="bi bi-pencil"></i> Edit
                    </button>
                    <button class="btn-sm-custom btn-danger-sm" onclick="deleteCat(<?= $c['id'] ?>, '<?= sanitize($c['name']) ?>')">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Modal -->
<div class="modal fade" id="catModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-header modal-header-custom">
                <h5 class="modal-title fw-700" id="catModalTitle">Tambah Kategori</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:invert(1)"></button>
            </div>
            <div class="modal-body p-3">
                <input type="hidden" id="catId">
                <div class="mb-3">
                    <label class="form-label-custom">Nama Kategori *</label>
                    <input type="text" class="form-control-custom" id="catName" placeholder="Contoh: Coffee">
                </div>
                <div class="mb-3">
                    <label class="form-label-custom">Icon Bootstrap</label>
                    <select class="form-select-custom" id="catIcon">
                        <?php foreach ($icons as $ic): ?>
                        <option value="<?= $ic ?>"><?= $ic ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label-custom">Deskripsi</label>
                    <input type="text" class="form-control-custom" id="catDesc" placeholder="Deskripsi singkat (opsional)">
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label-custom">Urutan</label>
                        <input type="number" class="form-control-custom" id="catSort" value="0">
                    </div>
                    <div class="col-6">
                        <label class="form-label-custom">Status</label>
                        <select class="form-select-custom" id="catActive">
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn-sm-custom btn-outline-sm" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn-sm-custom btn-accent-sm" style="padding:0.5rem 1.5rem" onclick="saveCat()">
                    <i class="bi bi-check-lg"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const APP_URL = '<?= APP_URL ?>';
function openCatModal(title='Tambah Kategori') {
    document.getElementById('catModalTitle').textContent=title;
    document.getElementById('catId').value='';
    document.getElementById('catName').value='';
    document.getElementById('catDesc').value='';
    document.getElementById('catSort').value='0';
    document.getElementById('catActive').value='1';
    new bootstrap.Modal('#catModal').show();
}
function editCat(c) {
    openCatModal('Edit Kategori');
    document.getElementById('catId').value=c.id;
    document.getElementById('catName').value=c.name;
    document.getElementById('catIcon').value=c.icon;
    document.getElementById('catDesc').value=c.description||'';
    document.getElementById('catSort').value=c.sort_order;
    document.getElementById('catActive').value=c.is_active;
}
function saveCat() {
    const id=document.getElementById('catId').value;
    const payload={action:id?'update':'create',id,name:document.getElementById('catName').value,icon:document.getElementById('catIcon').value,description:document.getElementById('catDesc').value,sort_order:document.getElementById('catSort').value,is_active:document.getElementById('catActive').value};
    fetch(`${APP_URL}/api/categories.php`,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)})
    .then(r=>r.json()).then(data=>{
        if(data.success){Admin.showToast(data.message,'success');bootstrap.Modal.getInstance('#catModal').hide();setTimeout(()=>location.reload(),600);}
        else Admin.showToast(data.message,'error');
    });
}
function deleteCat(id,name) {
    Admin.confirmDelete(`Hapus kategori "${name}"?`,()=>{
        fetch(`${APP_URL}/api/categories.php`,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'delete',id})})
        .then(r=>r.json()).then(data=>{
            if(data.success){Admin.showToast(data.message,'success');setTimeout(()=>location.reload(),600);}
            else Admin.showToast(data.message,'error');
        });
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer_admin.php'; ?>
