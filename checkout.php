<?php
$pageTitle = 'Checkout - Brewed & Bold';
require_once __DIR__ . '/includes/header_customer.php';

$cart = getCartFromSession();
if (empty($cart)) { header('Location: ' . APP_URL . '/menu.php'); exit; }

$subtotal = cartTotal();
$tax = $subtotal * TAX_PERCENT / 100;
$total = $subtotal + $tax;
$tables = db()->fetchAll("SELECT * FROM coffee_tables WHERE is_active=1 ORDER BY table_number");
?>
<meta name="app-url" content="<?= APP_URL ?>">

<div class="checkout-page container">
    <div style="margin-bottom:1rem">
        <a href="<?= APP_URL ?>/cart.php" class="text-decoration-none text-muted d-flex align-items-center gap-1 small fw-600">
            <i class="bi bi-arrow-left"></i> Kembali ke Keranjang
        </a>
    </div>

    <div class="row g-3">
        <div class="col-md-7">
            <!-- Customer Info -->
            <div style="background:var(--surface);border-radius:var(--radius-md);padding:1.25rem;border:1px solid var(--border);margin-bottom:1rem">
                <h6 class="fw-700 mb-3"><i class="bi bi-person-circle text-accent"></i> Informasi Pemesan</h6>
                <div class="mb-3">
                    <label class="form-label">Nama (opsional)</label>
                    <input type="text" class="form-control" id="customerName" placeholder="Contoh: Andi" value="<?= sanitize($_SESSION['customer_name'] ?? '') ?>">
                </div>
                <div class="mb-0">
                    <label class="form-label">Tipe Pesanan</label>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="payment-option active" id="opt_dine">
                                <input type="radio" name="orderType" value="dine_in" checked style="display:none">
                                <i class="bi bi-house-door-fill" style="color:var(--primary)"></i>
                                <div class="fw-700 small">Dine In</div>
                            </label>
                        </div>
                        <div class="col-6">
                            <label class="payment-option" id="opt_take">
                                <input type="radio" name="orderType" value="takeaway" style="display:none">
                                <i class="bi bi-bag-fill" style="color:var(--text-muted)"></i>
                                <div class="fw-700 small">Takeaway</div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table Selection -->
            <div id="tableSection" style="background:var(--surface);border-radius:var(--radius-md);padding:1.25rem;border:1px solid var(--border);margin-bottom:1rem">
                <h6 class="fw-700 mb-3"><i class="bi bi-table text-accent"></i> Pilih Meja</h6>
                <select class="form-control" id="tableSelect">
                    <option value="">-- Pilih Meja --</option>
                    <?php foreach ($tables as $t): ?>
                    <option value="<?= $t['id'] ?>" <?= ($tableId == $t['id']) ? 'selected' : '' ?>>
                        Meja <?= sanitize($t['table_number']) ?> (<?= $t['capacity'] ?> orang)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Payment Method -->
            <div style="background:var(--surface);border-radius:var(--radius-md);padding:1.25rem;border:1px solid var(--border);margin-bottom:1rem">
                <h6 class="fw-700 mb-3"><i class="bi bi-credit-card text-accent"></i> Metode Pembayaran</h6>
                <div class="row g-2">
                    <div class="col-4">
                        <label class="payment-option selected" onclick="selectPayment('cash', this)">
                            <input type="radio" name="payMethod" value="cash" checked style="display:none">
                            <i class="bi bi-cash-coin" style="color:var(--success,#2D7A4F)"></i>
                            <div class="fw-700 small">Tunai</div>
                        </label>
                    </div>
                    <div class="col-4">
                        <label class="payment-option" onclick="selectPayment('transfer', this)">
                            <input type="radio" name="payMethod" value="transfer" style="display:none">
                            <i class="bi bi-bank" style="color:#2980B9"></i>
                            <div class="fw-700 small">Transfer</div>
                        </label>
                    </div>
                    <div class="col-4">
                        <label class="payment-option" onclick="selectPayment('qris', this)">
                            <input type="radio" name="payMethod" value="qris" style="display:none">
                            <i class="bi bi-qr-code" style="color:#8e44ad"></i>
                            <div class="fw-700 small">QRIS</div>
                        </label>
                    </div>
                </div>

                <!-- QRIS Simulator -->
                <div id="qrisSection" style="display:none;margin-top:1rem">
                    <div class="qris-container">
                        <div class="qris-logo">QRIS</div>
                        <div style="background:#fff;padding:1rem;border-radius:12px;display:inline-block;margin-bottom:0.75rem">
                            <svg width="150" height="150" viewBox="0 0 150 150" style="display:block">
                                <rect width="150" height="150" fill="white"/>
                                <!-- Simulated QR pattern -->
                                <g fill="#000">
                                    <rect x="10" y="10" width="50" height="50" rx="4" fill="none" stroke="#000" stroke-width="5"/>
                                    <rect x="20" y="20" width="30" height="30" rx="2"/>
                                    <rect x="90" y="10" width="50" height="50" rx="4" fill="none" stroke="#000" stroke-width="5"/>
                                    <rect x="100" y="20" width="30" height="30" rx="2"/>
                                    <rect x="10" y="90" width="50" height="50" rx="4" fill="none" stroke="#000" stroke-width="5"/>
                                    <rect x="20" y="100" width="30" height="30" rx="2"/>
                                    <rect x="70" y="10" width="10" height="10"/><rect x="70" y="30" width="10" height="10"/>
                                    <rect x="70" y="50" width="10" height="10"/><rect x="70" y="70" width="10" height="10"/>
                                    <rect x="10" y="70" width="10" height="10"/><rect x="30" y="70" width="10" height="10"/>
                                    <rect x="50" y="70" width="10" height="10"/><rect x="90" y="70" width="10" height="10"/>
                                    <rect x="110" y="70" width="10" height="10"/><rect x="130" y="70" width="10" height="10"/>
                                    <rect x="90" y="90" width="10" height="10"/><rect x="110" y="90" width="10" height="10"/>
                                    <rect x="130" y="90" width="10" height="10"/><rect x="90" y="110" width="10" height="10"/>
                                    <rect x="110" y="110" width="10" height="10"/><rect x="130" y="110" width="10" height="10"/>
                                    <rect x="90" y="130" width="10" height="10"/><rect x="110" y="130" width="10" height="10"/>
                                </g>
                            </svg>
                        </div>
                        <div style="color:#aaa;font-size:0.8rem;margin-bottom:0.5rem">Scan QR ini untuk membayar</div>
                        <div style="font-size:1.2rem;font-weight:800;color:#fff"><?= formatCurrency($total) ?></div>
                        <div style="margin-top:1rem">
                            <button onclick="simulateQRISPayment()" class="btn-accent" style="border-radius:10px;padding:0.6rem 1.5rem;border:none;cursor:pointer;font-weight:700;font-family:inherit">
                                <i class="bi bi-check-circle"></i> Simulasi Pembayaran Berhasil
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div style="background:var(--surface);border-radius:var(--radius-md);padding:1.25rem;border:1px solid var(--border)">
                <h6 class="fw-700 mb-2"><i class="bi bi-pencil-square text-accent"></i> Catatan Pesanan</h6>
                <textarea class="form-control" id="orderNotes" rows="2" placeholder="Contoh: tidak ada es, pisahkan saus..."></textarea>
            </div>
        </div>

        <div class="col-md-5">
            <div style="background:var(--surface);border-radius:var(--radius-md);padding:1.25rem;border:1px solid var(--border);position:sticky;top:80px">
                <h6 class="fw-700 mb-3">Ringkasan Pesanan</h6>
                <?php foreach ($cart as $item): ?>
                <div style="display:flex;justify-content:space-between;padding:0.4rem 0;border-bottom:1px dashed var(--border);font-size:0.85rem">
                    <div>
                        <div class="fw-600"><?= sanitize($item['menu_name']) ?></div>
                        <?php if (!empty($item['addons'])): ?>
                        <div style="font-size:0.72rem;color:var(--text-muted)"><?= implode(', ', array_map(fn($a) => sanitize($a['addon_option_name']), $item['addons'])) ?></div>
                        <?php endif; ?>
                    </div>
                    <div style="white-space:nowrap">
                        <span style="color:var(--text-muted)"><?= $item['quantity'] ?>x</span>
                        <span class="fw-700 ms-2"><?= formatCurrency($item['subtotal']) ?></span>
                    </div>
                </div>
                <?php endforeach; ?>

                <div style="margin-top:0.75rem">
                    <div style="display:flex;justify-content:space-between;font-size:0.875rem;color:var(--text-muted);margin-bottom:0.3rem">
                        <span>Subtotal</span><span><?= formatCurrency($subtotal) ?></span>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-size:0.875rem;color:var(--text-muted);margin-bottom:0.3rem">
                        <span>Pajak (<?= TAX_PERCENT ?>%)</span><span><?= formatCurrency($tax) ?></span>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-weight:800;font-size:1.05rem;border-top:2px solid var(--border);padding-top:0.5rem;margin-top:0.5rem">
                        <span>Total</span><span style="color:var(--accent)"><?= formatCurrency($total) ?></span>
                    </div>
                </div>

                <button onclick="placeOrder()" class="btn-primary-custom mt-3" id="btnOrder">
                    <i class="bi bi-check-circle-fill"></i> Pesan Sekarang
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const APP_URL = '<?= APP_URL ?>';
let qrisPaid = false;

function selectPayment(method, el) {
    document.querySelectorAll('.payment-option').forEach(o => o.classList.remove('selected'));
    el.classList.add('selected');
    document.querySelector(`[name="payMethod"][value="${method}"]`).checked = true;
    document.getElementById('qrisSection').style.display = method === 'qris' ? 'block' : 'none';
    qrisPaid = false;
}

document.querySelectorAll('[name="orderType"]').forEach(r => {
    r.closest('label').addEventListener('click', function() {
        document.querySelectorAll('[name="orderType"]').closest?.('label')?.classList.remove('selected');
        document.getElementById('opt_dine').classList.remove('selected');
        document.getElementById('opt_take').classList.remove('selected');
        this.classList.add('selected');
        const val = this.querySelector('input').value;
        document.getElementById('tableSection').style.display = val === 'dine_in' ? 'block' : 'none';
    });
});

function simulateQRISPayment() {
    qrisPaid = true;
    Swal.fire({ icon: 'success', title: 'Pembayaran Berhasil!', text: 'QRIS telah terkonfirmasi', timer: 2000, showConfirmButton: false });
}

function placeOrder() {
    const payMethod = document.querySelector('[name="payMethod"]:checked').value;
    if (payMethod === 'qris' && !qrisPaid) {
        Swal.fire('Belum Bayar', 'Selesaikan pembayaran QRIS terlebih dahulu', 'warning');
        return;
    }

    const btn = document.getElementById('btnOrder');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Memproses...';

    const orderType = document.querySelector('[name="orderType"]:checked')?.value || 'dine_in';
    const tableId = document.getElementById('tableSelect')?.value || '<?= $tableId ?>';
    const customerName = document.getElementById('customerName').value || 'Guest';
    const notes = document.getElementById('orderNotes').value;

    // Get cart from session via API
    fetch(`${APP_URL}/api/cart.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'get' })
    })
    .then(r => r.json())
    .then(cartData => {
        return fetch(`${APP_URL}/api/orders.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'create',
                cart: cartData.cart,
                table_id: tableId,
                order_type: orderType,
                order_source: 'self_order',
                customer_name: customerName,
                payment_method: payMethod,
                amount_paid: cartData.total,
                notes: notes,
                subtotal: cartData.subtotal,
                tax: cartData.tax,
                total: cartData.total
            })
        });
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.location.href = `${APP_URL}/receipt.php?order=${data.order_id}`;
        } else {
            Swal.fire('Error', data.message || 'Gagal memproses pesanan', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Pesan Sekarang';
        }
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer_customer.php'; ?>
