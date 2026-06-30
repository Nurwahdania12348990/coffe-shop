<?php
$pageTitle = 'Manajemen Menu';
$activePage = 'menus';
require_once __DIR__ . '/includes/header_admin.php';
requireLogin('admin');

$categories = db()->fetchAll("SELECT * FROM categories WHERE is_active=1 ORDER BY sort_order");
$addonGroups = db()->fetchAll("SELECT * FROM addon_groups ORDER BY name");
$menus = db()->fetchAll("SELECT m.*, c.name as cat_name FROM menus m LEFT JOIN categories c ON c.id=m.category_id ORDER BY c.sort_order, m.sort_order, m.name");
?>
<meta name="app-url" content="<?= APP_URL ?>">

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem">
    <div></div>
    <button class="btn-sm-custom btn-accent-sm" onclick="openMenuModal()">
        <i class="bi bi-plus-lg"></i> Tambah Menu
    </button>
</div>

<div class="card-custom">
    <div class="card-header-custom">
        <div class="card-title">Daftar Menu (<?= count($menus) ?>)</div>
        <input type="text" placeholder="Cari menu..." oninput="filterMenuTable(this.value)"
               style="padding:0.4rem 0.75rem;border:1.5px solid var(--border);border-radius:8px;font-size:0.82rem;font-family:inherit;width:200px">
    </div>
    <div style="overflow-x:auto">
        <table class="table-custom" id="menuTable">
            <thead>
                <tr>
                    <th style="width:90px">Foto</th>
                    <th>Nama Menu</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Status</th>
                    <th>Featured</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($menus as $m): ?>
                <tr style="vertical-align:middle">
                    <td style="padding:0.6rem 0.75rem">
                        <?php if ($m['image'] && file_exists(__DIR__ . '/uploads/menu/' . $m['image'])): ?>
                        <img src="<?= APP_URL ?>/uploads/menu/<?= $m['image'] ?>" style="width:72px;height:72px;object-fit:cover;border-radius:12px;box-shadow:0 2px 8px rgba(44,24,16,0.15);display:block">
                        <?php else: ?>
                        <div style="width:72px;height:72px;background:linear-gradient(135deg,var(--accent-pale),var(--border));border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.6rem;box-shadow:0 2px 8px rgba(44,24,16,0.08)">☕</div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="fw-600"><?= sanitize($m['name']) ?></div>
                        <?php if ($m['description']): ?>
                        <div style="font-size:0.72rem;color:var(--text-muted);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:200px"><?= sanitize($m['description']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td><?= sanitize($m['cat_name']) ?></td>
                    <td class="fw-700 text-accent"><?= formatCurrency($m['price']) ?></td>
                    <td>
                        <button onclick="toggleAvail(<?= $m['id'] ?>, this)" style="background:none;border:none;cursor:pointer">
                            <?= $m['is_available'] 
                                ? '<span class="badge-status badge-done">Tersedia</span>' 
                                : '<span class="badge-status badge-cancelled">Tidak Tersedia</span>' ?>
                        </button>
                    </td>
                    <td><?= $m['is_featured'] ? '⭐' : '-' ?></td>
                    <td>
                        <button class="btn-sm-custom btn-outline-sm" onclick="editMenu(<?= htmlspecialchars(json_encode($m), ENT_QUOTES) ?>)">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn-sm-custom btn-danger-sm ms-1" onclick="deleteMenu(<?= $m['id'] ?>, '<?= sanitize($m['name']) ?>')">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="menuModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-header modal-header-custom">
                <h5 class="modal-title fw-700" id="menuModalTitle">Tambah Menu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:invert(1)"></button>
            </div>
            <div class="modal-body p-3">
                <input type="hidden" id="menuId">
                <div class="row g-3">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label class="form-label-custom">Nama Menu *</label>
                            <input type="text" class="form-control-custom" id="menuName" placeholder="Contoh: Caramel Latte">
                        </div>
                        <div class="mb-3">
                            <label class="form-label-custom">Kategori *</label>
                            <select class="form-select-custom" id="menuCategory">
                                <option value="">-- Pilih Kategori --</option>
                                <?php foreach ($categories as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= sanitize($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-custom">Harga (Rp) *</label>
                            <input type="number" class="form-control-custom" id="menuPrice" placeholder="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label-custom">Deskripsi</label>
                            <textarea class="form-control-custom" id="menuDesc" rows="3" placeholder="Deskripsi singkat menu..."></textarea>
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label-custom">Urutan</label>
                                <input type="number" class="form-control-custom" id="menuSort" value="0" min="0">
                            </div>
                            <div class="col-3">
                                <label class="form-label-custom">Tersedia</label>
                                <select class="form-select-custom" id="menuAvail">
                                    <option value="1">Ya</option>
                                    <option value="0">Tidak</option>
                                </select>
                            </div>
                            <div class="col-3">
                                <label class="form-label-custom">Featured</label>
                                <select class="form-select-custom" id="menuFeatured">
                                    <option value="0">Tidak</option>
                                    <option value="1">Ya ⭐</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-custom">Foto Menu</label>
                        <div style="border:2px dashed var(--border);border-radius:12px;padding:1rem;text-align:center;cursor:pointer" onclick="document.getElementById('menuImage').click()">
                            <img id="imgPreview" style="width:100%;max-height:150px;object-fit:cover;border-radius:8px;display:none">
                            <div id="imgPlaceholder"><i class="bi bi-image" style="font-size:2rem;color:var(--border)"></i><div style="font-size:0.78rem;color:var(--text-muted);margin-top:0.25rem">Klik untuk upload</div></div>
                        </div>
                        <input type="file" id="menuImage" accept="image/*" style="display:none" onchange="previewImage(this,'imgPreview');document.getElementById('imgPlaceholder').style.display='none'">

                        <div style="margin-top:1rem">
                            <label class="form-label-custom">Add-On Groups</label>
                            <?php foreach ($addonGroups as $ag): ?>
                            <label style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.3rem;font-size:0.85rem">
                                <input type="checkbox" class="addon-group-check" value="<?= $ag['id'] ?>" style="accent-color:var(--primary)">
                                <?= sanitize($ag['name']) ?>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn-sm-custom btn-outline-sm" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn-sm-custom btn-accent-sm" style="padding:0.5rem 1.5rem" onclick="saveMenu()">
                    <i class="bi bi-check-lg"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const APP_URL = '<?= APP_URL ?>';
let currentMenuAddonGroups = [];

function filterMenuTable(q) {
    document.querySelectorAll('#menuTable tbody tr').forEach(row => {
        row.style.display = q && !row.textContent.toLowerCase().includes(q.toLowerCase()) ? 'none' : '';
    });
}

function openMenuModal(title = 'Tambah Menu') {
    document.getElementById('menuModalTitle').textContent = title;
    document.getElementById('menuId').value = '';
    document.getElementById('menuName').value = '';
    document.getElementById('menuCategory').value = '';
    document.getElementById('menuPrice').value = '';
    document.getElementById('menuDesc').value = '';
    document.getElementById('menuSort').value = '0';
    document.getElementById('menuAvail').value = '1';
    document.getElementById('menuFeatured').value = '0';
    document.getElementById('imgPreview').style.display = 'none';
    document.getElementById('imgPlaceholder').style.display = 'block';
    document.getElementById('menuImage').value = '';
    document.querySelectorAll('.addon-group-check').forEach(c => c.checked = false);
    new bootstrap.Modal('#menuModal').show();
}

async function editMenu(menu) {
    openMenuModal('Edit Menu');
    document.getElementById('menuId').value = menu.id;
    document.getElementById('menuName').value = menu.name;
    document.getElementById('menuCategory').value = menu.category_id;
    document.getElementById('menuPrice').value = menu.price;
    document.getElementById('menuDesc').value = menu.description || '';
    document.getElementById('menuSort').value = menu.sort_order;
    document.getElementById('menuAvail').value = menu.is_available;
    document.getElementById('menuFeatured').value = menu.is_featured;

    if (menu.image) {
        const img = document.getElementById('imgPreview');
        img.src = `${APP_URL}/uploads/menu/${menu.image}`;
        img.style.display = 'block';
        document.getElementById('imgPlaceholder').style.display = 'none';
    }

    // Load addon groups for this menu
    const data = await fetch(`${APP_URL}/api/menu.php?id=${menu.id}`).then(r => r.json());
    const selectedIds = (data.addons || []).map(a => String(a.id));
    document.querySelectorAll('.addon-group-check').forEach(c => {
        c.checked = selectedIds.includes(c.value);
    });
}

function saveMenu() {
    const id = document.getElementById('menuId').value;
    const fd = new FormData();
    fd.append('action', id ? 'update' : 'create');
    if (id) fd.append('id', id);
    fd.append('name', document.getElementById('menuName').value);
    fd.append('category_id', document.getElementById('menuCategory').value);
    fd.append('price', document.getElementById('menuPrice').value);
    fd.append('description', document.getElementById('menuDesc').value);
    fd.append('sort_order', document.getElementById('menuSort').value);
    fd.append('is_available', document.getElementById('menuAvail').value);
    fd.append('is_featured', document.getElementById('menuFeatured').value);

    const imageFile = document.getElementById('menuImage').files[0];
    if (imageFile) fd.append('image', imageFile);

    document.querySelectorAll('.addon-group-check:checked').forEach(c => {
        fd.append('addon_groups[]', c.value);
    });

    fetch(`${APP_URL}/api/menu.php`, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                Admin.showToast(data.message, 'success');
                bootstrap.Modal.getInstance('#menuModal').hide();
                setTimeout(() => location.reload(), 800);
            } else {
                Admin.showToast(data.message, 'error');
            }
        });
}

function toggleAvail(id, el) {
    fetch(`${APP_URL}/api/menu.php`, {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'toggle_available', id })
    }).then(() => location.reload());
}

function deleteMenu(id, name) {
    Admin.confirmDelete(`Hapus menu "${name}"? Tindakan ini tidak dapat dibatalkan.`, () => {
        fetch(`${APP_URL}/api/menu.php`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'delete', id })
        }).then(r => r.json()).then(data => {
            if (data.success) { Admin.showToast(data.message, 'success'); setTimeout(() => location.reload(), 600); }
            else Admin.showToast(data.message, 'error');
        });
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer_admin.php'; ?>
