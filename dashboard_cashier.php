<?php
$pageTitle = 'Dashboard Kasir';
$activePage = 'cashier';
require_once __DIR__ . '/includes/header_admin.php';
requireLogin(['admin','cashier']);

$categories = db()->fetchAll("SELECT * FROM categories WHERE is_active=1 ORDER BY sort_order");
$tables = db()->fetchAll("SELECT * FROM coffee_tables WHERE is_active=1 ORDER BY table_number");
$menus = db()->fetchAll("SELECT m.*, c.name as cat_name, c.slug as cat_slug FROM menus m JOIN categories c ON c.id=m.category_id WHERE m.is_available=1 AND c.is_active=1 ORDER BY c.sort_order, m.sort_order");
$grouped = [];
foreach ($menus as $m) { $grouped[$m['cat_slug']][] = $m; }

// Pending orders count
$pendingCount = db()->fetchValue("SELECT COUNT(*) FROM orders WHERE status IN ('pending','process') AND DATE(created_at)=CURDATE()");
?>
<meta name="app-url" content="<?= APP_URL ?>">

<style>
.pos-cat-pills { display:flex; gap:0.5rem; overflow-x:auto; padding-bottom:0.5rem; scrollbar-width:none; margin-bottom:1rem; }
.pos-cat-pills::-webkit-scrollbar { display:none; }
.pos-cat-pill { padding:0.35rem 0.9rem; background:var(--cream); border:1.5px solid var(--border); border-radius:20px; font-size:0.8rem; font-weight:600; cursor:pointer; white-space:nowrap; color:var(--text-secondary); transition:all 0.2s; flex-shrink:0; }
.pos-cat-pill:hover,.pos-cat-pill.active { background:var(--primary); border-color:var(--primary); color:#fff; }
.pos-menu-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(130px,1fr)); gap:0.6rem; }
.pos-menu-item { background:var(--surface); border:1.5px solid var(--border); border-radius:12px; padding:0.75rem 0.5rem; text-align:center; cursor:pointer; transition:all 0.2s; }
.pos-menu-item:hover { border-color:var(--accent); background:var(--accent-pale); transform:translateY(-2px); }
.pos-menu-item-icon { font-size:1.8rem; margin-bottom:0.3rem; }
.pos-menu-item-name { font-size:0.75rem; font-weight:700; color:var(--text-primary); line-height:1.3; margin-bottom:0.2rem; }
.pos-menu-item-price { font-size:0.78rem; color:var(--accent); font-weight:700; }
.pos-menu-img { width:100%; height:70px; object-fit:cover; border-radius:8px; margin-bottom:0.4rem; }
.order-type-btn { padding:0.4rem 0.75rem; border:1.5px solid var(--border); border-radius:8px; background:var(--cream); cursor:pointer; font-size:0.8rem; font-weight:600; transition:all 0.2s; }
.order-type-btn.active { background:var(--primary); color:#fff; border-color:var(--primary); }
</style>

<div class="pos-layout">
    <!-- LEFT: Menu Panel -->
    <div class="pos-menu-panel">
        <!-- Top controls -->
        <div style="display:flex;gap:0.75rem;align-items:center;margin-bottom:0.75rem;flex-wrap:wrap">
            <div style="position:relative;flex:1;min-width:200px">
                <i class="bi bi-search" style="position:absolute;left:0.75rem;top:50%;transform:translateY(-50%);color:var(--text-muted)"></i>
                <input type="text" id="posSearch" placeholder="Cari menu..." oninput="posFilter(this.value)"
                       style="width:100%;padding:0.55rem 0.75rem 0.55rem 2.25rem;border:1.5px solid var(--border);border-radius:10px;font-size:0.85rem;font-family:inherit">
            </div>
            <div style="display:flex;gap:0.4rem">
                <button class="order-type-btn active" id="btnDineIn" onclick="setCashierOrderType('dine_in')">🪑 Dine In</button>
                <button class="order-type-btn" id="btnTakeaway" onclick="setCashierOrderType('takeaway')">🛍️ Takeaway</button>
            </div>
            <select id="tableSelect" style="padding:0.55rem 0.75rem;border:1.5px solid var(--border);border-radius:10px;font-size:0.85rem;font-family:inherit">
                <option value="">Pilih Meja</option>
                <?php foreach ($tables as $t): ?>
                <option value="<?= $t['id'] ?>">Meja <?= sanitize($t['table_number']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Category Pills -->
        <div class="pos-cat-pills">
            <span class="pos-cat-pill active" onclick="posCatFilter('all', this)">Semua</span>
            <?php foreach ($categories as $cat): ?>
            <?php if (!empty($grouped[$cat['slug']])): ?>
            <span class="pos-cat-pill" onclick="posCatFilter('<?= $cat['slug'] ?>', this)">
                <i class="<?= $cat['icon'] ?>"></i> <?= sanitize($cat['name']) ?>
            </span>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <!-- Menu Grid -->
        <div class="pos-menu-grid" id="posMenuGrid">
            <?php foreach ($menus as $m): ?>
            <div class="pos-menu-item" data-cat="<?= $m['cat_slug'] ?>" data-name="<?= strtolower($m['name']) ?>"
                 onclick="posAddMenu(<?= htmlspecialchars(json_encode(['id'=>$m['id'],'name'=>$m['name'],'price'=>$m['price'],'image'=>$m['image']]), ENT_QUOTES) ?>)">
                <?php if ($m['image'] && file_exists(__DIR__ . '/uploads/menu/' . $m['image'])): ?>
                <img class="pos-menu-img" src="<?= APP_URL ?>/uploads/menu/<?= $m['image'] ?>" alt="" loading="lazy">
                <?php else: ?>
                <div class="pos-menu-item-icon">☕</div>
                <?php endif; ?>
                <div class="pos-menu-item-name"><?= sanitize($m['name']) ?></div>
                <div class="pos-menu-item-price"><?= formatCurrency($m['price']) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- RIGHT: Order Panel -->
    <div class="pos-order-panel">
        <div class="pos-order-header">
            <div>
                <div class="fw-700">Pesanan Kasir</div>
                <div style="font-size:0.75rem;opacity:0.7"><span id="posItemCount">0</span> item</div>
            </div>
            <div style="display:flex;gap:0.5rem;align-items:center">
                <?php if ($pendingCount > 0): ?>
                <a href="<?= APP_URL ?>/orders.php" style="background:rgba(255,193,7,0.2);color:#ffc107;padding:0.25rem 0.6rem;border-radius:8px;text-decoration:none;font-size:0.75rem;font-weight:700">
                    <i class="bi bi-bell-fill"></i> <?= $pendingCount ?>
                </a>
                <?php endif; ?>
                <button onclick="CashierPOS.cart=[];CashierPOS.renderCart()" style="background:rgba(255,255,255,0.1);border:none;color:#fff;border-radius:8px;padding:0.25rem 0.6rem;cursor:pointer;font-size:0.75rem">
                    <i class="bi bi-trash"></i> Reset
                </button>
            </div>
        </div>

        <div class="pos-order-items" id="posCartItems">
            <div style="text-align:center;padding:2rem;color:#ccc">
                <i class="bi bi-bag" style="font-size:2rem;display:block;margin-bottom:0.5rem"></i>
                <div>Belum ada item</div>
            </div>
        </div>

        <div class="pos-footer">
            <div class="pos-total-row"><span>Subtotal</span><span id="posSubtotal">Rp 0</span></div>
            <div class="pos-total-row"><span>Pajak (10%)</span><span id="posTax">Rp 0</span></div>
            <div class="pos-total-row grand"><span>TOTAL</span><span id="posTotal">Rp 0</span></div>
            <button onclick="openCheckout()" class="btn-sm-custom btn-accent-sm w-100 mt-2" style="padding:0.65rem;font-size:0.875rem">
                <i class="bi bi-cash-coin"></i> Proses Pembayaran
            </button>
        </div>
    </div>
</div>

<!-- Checkout Modal -->
<div class="modal fade" id="checkoutModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-header modal-header-custom">
                <h5 class="modal-title fw-700"><i class="bi bi-credit-card me-2"></i>Proses Pembayaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:invert(1)"></button>
            </div>
            <div class="modal-body p-3">
                <div class="mb-3">
                    <label class="form-label-custom">Nama Pelanggan</label>
                    <input type="text" class="form-control-custom" id="customerName" placeholder="Contoh: Budi (opsional)">
                </div>
                <div class="mb-3">
                    <label class="form-label-custom">Metode Pembayaran</label>
                    <div class="d-flex gap-2">
                        <?php foreach (['cash'=>['💵','Tunai'],'transfer'=>['🏦','Transfer'],'qris'=>['📱','QRIS']] as $k=>$v): ?>
                        <label style="flex:1;text-align:center;border:2px solid var(--border);border-radius:10px;padding:0.6rem;cursor:pointer;transition:all 0.2s" 
                               id="payOpt_<?= $k ?>" onclick="selectModalPay('<?= $k ?>', this)">
                            <input type="radio" name="paymentMethod" value="<?= $k ?>" style="display:none" <?= $k==='cash'?'checked':'' ?>>
                            <div style="font-size:1.4rem"><?= $v[0] ?></div>
                            <div style="font-size:0.75rem;font-weight:700"><?= $v[1] ?></div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div id="cashInput" class="mb-3">
                    <label class="form-label-custom">Jumlah Bayar</label>
                    <input type="number" class="form-control-custom" id="amountPaid" placeholder="0" oninput="calcChange()">
                    <div id="changeInfo" style="margin-top:0.5rem;padding:0.5rem 0.75rem;background:var(--accent-pale);border-radius:8px;font-size:0.875rem;display:none">
                        Kembalian: <strong id="changeAmt" class="text-accent"></strong>
                    </div>
                </div>
                <div id="qrisModal" style="display:none;margin-bottom:1rem">
                    <div style="background:#1a1a2e;border-radius:12px;padding:1.25rem;text-align:center;color:#fff">
                        <div style="font-weight:800;letter-spacing:2px;color:#E63946;font-size:1.1rem;margin-bottom:0.75rem">QRIS</div>
                        <div style="background:#fff;display:inline-block;padding:0.75rem;border-radius:8px;margin-bottom:0.5rem">
                            <svg width="100" height="100" viewBox="0 0 150 150"><rect width="150" height="150" fill="white"/>
                            <g fill="#000"><rect x="10" y="10" width="50" height="50" rx="4" fill="none" stroke="#000" stroke-width="5"/><rect x="20" y="20" width="30" height="30" rx="2"/><rect x="90" y="10" width="50" height="50" rx="4" fill="none" stroke="#000" stroke-width="5"/><rect x="100" y="20" width="30" height="30" rx="2"/><rect x="10" y="90" width="50" height="50" rx="4" fill="none" stroke="#000" stroke-width="5"/><rect x="20" y="100" width="30" height="30" rx="2"/><rect x="70" y="10" width="10" height="10"/><rect x="70" y="30" width="10" height="10"/><rect x="70" y="50" width="10" height="10"/><rect x="70" y="70" width="10" height="10"/><rect x="90" y="70" width="10" height="10"/><rect x="110" y="90" width="10" height="10"/><rect x="130" y="90" width="10" height="10"/></g></svg>
                        </div>
                        <div id="qrisTotalDisp" style="font-weight:800;font-size:1rem"></div>
                        <button onclick="confirmQRIS()" style="margin-top:0.75rem;background:#27ae60;color:#fff;border:none;border-radius:8px;padding:0.5rem 1rem;cursor:pointer;font-weight:700;font-family:inherit;font-size:0.85rem">
                            ✅ Konfirmasi Terima Bayar
                        </button>
                    </div>
                </div>
                <div class="mb-0">
                    <label class="form-label-custom">Catatan</label>
                    <textarea class="form-control-custom" id="orderNotes" rows="2" placeholder="Catatan pesanan..."></textarea>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn-sm-custom btn-outline-sm" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn-sm-custom btn-accent-sm" style="flex:1;padding:0.65rem" onclick="CashierPOS.processPayment()">
                    <i class="bi bi-check-circle-fill"></i> Konfirmasi & Cetak Struk
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const APP_URL = '<?= APP_URL ?>';
let qrisConfirmed = false;

function posFilter(q) {
    const items = document.querySelectorAll('.pos-menu-item');
    items.forEach(el => {
        const name = el.dataset.name || '';
        el.style.display = q && !name.includes(q.toLowerCase()) ? 'none' : '';
    });
}

function posCatFilter(slug, el) {
    document.querySelectorAll('.pos-cat-pill').forEach(p => p.classList.remove('active'));
    el.classList.add('active');
    document.querySelectorAll('.pos-menu-item').forEach(item => {
        item.style.display = (slug === 'all' || item.dataset.cat === slug) ? '' : 'none';
    });
}

function setCashierOrderType(type) {
    CashierPOS.orderType = type;
    document.getElementById('btnDineIn').classList.toggle('active', type === 'dine_in');
    document.getElementById('btnTakeaway').classList.toggle('active', type === 'takeaway');
    document.getElementById('tableSelect').style.display = type === 'dine_in' ? '' : 'none';
}

function posAddMenu(menu) {
    CashierPOS.tableId = document.getElementById('tableSelect').value || null;
    // Check if menu has addons
    fetch(`${APP_URL}/api/menu.php?id=${menu.id}`)
        .then(r => r.json())
        .then(data => {
            if (data.addons && data.addons.length > 0) {
                // Use POS addon modal
                POS.showAddonModal(data.menu, data.addons).catch(() => {});
                // Override Swal confirm
                const origShow = window._swalShown;
            } else {
                CashierPOS.addItem(menu, [], '');
            }
        });
}

function openCheckout() {
    if (CashierPOS.cart.length === 0) { Admin.showToast('Keranjang kosong', 'warning'); return; }
    document.getElementById('amountPaid').value = '';
    document.getElementById('changeInfo').style.display = 'none';
    document.getElementById('qrisTotalDisp').textContent = Admin.formatCurrency(CashierPOS.getTotal());
    qrisConfirmed = false;
    new bootstrap.Modal('#checkoutModal').show();
}

function selectModalPay(method, el) {
    document.querySelectorAll('[id^="payOpt_"]').forEach(e => { e.style.borderColor='var(--border)'; e.style.background=''; });
    el.style.borderColor = 'var(--primary)';
    el.style.background = 'rgba(44,24,16,0.04)';
    el.querySelector('input').checked = true;
    document.getElementById('cashInput').style.display = method === 'cash' ? 'block' : 'none';
    document.getElementById('qrisModal').style.display = method === 'qris' ? 'block' : 'none';
    qrisConfirmed = false;
}

function calcChange() {
    const paid = parseFloat(document.getElementById('amountPaid').value) || 0;
    const total = CashierPOS.getTotal();
    const change = paid - total;
    const info = document.getElementById('changeInfo');
    if (paid > 0) {
        info.style.display = 'block';
        document.getElementById('changeAmt').textContent = Admin.formatCurrency(Math.max(0, change));
        info.style.background = change < 0 ? '#fee' : 'var(--accent-pale)';
    } else { info.style.display = 'none'; }
}

function confirmQRIS() { qrisConfirmed = true; Admin.showToast('Pembayaran QRIS dikonfirmasi', 'success'); }

// Override CashierPOS processPayment for validation
const origProcess = CashierPOS.processPayment.bind(CashierPOS);
CashierPOS.processPayment = function() {
    const method = document.querySelector('[name="paymentMethod"]:checked')?.value || 'cash';
    if (method === 'qris' && !qrisConfirmed) {
        Admin.showToast('Konfirmasi pembayaran QRIS terlebih dahulu', 'warning');
        return;
    }
    if (method === 'cash') {
        const paid = parseFloat(document.getElementById('amountPaid').value) || 0;
        const total = CashierPOS.getTotal();
        if (paid < total) {
            Admin.showToast('Jumlah bayar kurang dari total', 'warning');
            return;
        }
    }
    CashierPOS.tableId = document.getElementById('tableSelect').value || null;
    origProcess();
};

// Init
document.getElementById('payOpt_cash').style.borderColor = 'var(--primary)';
document.getElementById('payOpt_cash').style.background = 'rgba(44,24,16,0.04)';
</script>

<?php require_once __DIR__ . '/includes/footer_admin.php'; ?>
