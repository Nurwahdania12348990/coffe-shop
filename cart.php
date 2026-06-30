<?php
$pageTitle = 'Keranjang - Brewed & Bold';
require_once __DIR__ . '/includes/header_customer.php';

$cart = getCartFromSession();
$subtotal = cartTotal();
$tax = $subtotal * TAX_PERCENT / 100;
$total = $subtotal + $tax;
?>
<meta name="app-url" content="<?= APP_URL ?>">

<div class="cart-page container">
    <div style="margin-bottom:1rem">
        <a href="<?= APP_URL ?>/menu.php<?= $tableId ? '?table='.$tableId : '' ?>" class="text-decoration-none text-muted d-flex align-items-center gap-1 small fw-600">
            <i class="bi bi-arrow-left"></i> Kembali ke Menu
        </a>
    </div>

    <?php if (empty($cart)): ?>
    <div class="empty-state">
        <i class="bi bi-bag"></i>
        <h5>Keranjang Kosong</h5>
        <p class="mb-3">Tambahkan menu favoritmu dulu yuk!</p>
        <a href="<?= APP_URL ?>/menu.php<?= $tableId ? '?table='.$tableId : '' ?>" class="btn-primary-custom" style="width:auto;display:inline-flex">
            <i class="bi bi-cup-hot-fill"></i> Lihat Menu
        </a>
    </div>
    <?php else: ?>

    <h6 class="fw-700 mb-3"><?= array_sum(array_column($cart, 'quantity')) ?> Item di Keranjang</h6>

    <?php foreach ($cart as $key => $item): ?>
    <div class="cart-item">
        <?php if ($item['image'] && file_exists(__DIR__ . '/uploads/menu/' . $item['image'])): ?>
        <img class="cart-item-img" src="<?= APP_URL ?>/uploads/menu/<?= $item['image'] ?>" alt="<?= sanitize($item['menu_name']) ?>">
        <?php else: ?>
        <div class="cart-item-img d-flex align-items-center justify-content-center" style="background:var(--border);border-radius:10px">
            <i class="bi bi-cup-hot" style="font-size:1.5rem;color:var(--text-muted)"></i>
        </div>
        <?php endif; ?>
        <div class="cart-item-info">
            <div class="cart-item-name"><?= sanitize($item['menu_name']) ?></div>
            <?php if (!empty($item['addons'])): ?>
            <div class="cart-item-addons">
                <?= implode(', ', array_map(fn($a) => sanitize($a['addon_option_name']), $item['addons'])) ?>
            </div>
            <?php endif; ?>
            <?php if ($item['notes']): ?>
            <div class="cart-item-addons" style="color:var(--accent)"><i class="bi bi-pencil"></i> <?= sanitize($item['notes']) ?></div>
            <?php endif; ?>
            <div class="qty-control">
                <button class="qty-btn" onclick="POS.updateQty('<?= $key ?>', -1)"><i class="bi bi-dash"></i></button>
                <span class="qty-val"><?= $item['quantity'] ?></span>
                <button class="qty-btn" onclick="POS.updateQty('<?= $key ?>', 1)"><i class="bi bi-plus"></i></button>
            </div>
        </div>
        <div class="d-flex flex-column align-items-end gap-1">
            <div class="cart-item-price"><?= formatCurrency($item['subtotal']) ?></div>
            <button class="btn-remove-item" onclick="POS.removeItem('<?= $key ?>')"><i class="bi bi-trash"></i></button>
        </div>
    </div>
    <?php endforeach; ?>

    <div style="height:160px"></div>

    <div class="cart-summary">
        <div class="cart-summary-row"><span>Subtotal</span><span><?= formatCurrency($subtotal) ?></span></div>
        <div class="cart-summary-row"><span>Pajak (<?= TAX_PERCENT ?>%)</span><span><?= formatCurrency($tax) ?></span></div>
        <div class="cart-summary-row total"><span>Total</span><span><?= formatCurrency($total) ?></span></div>
        <a href="<?= APP_URL ?>/checkout.php" class="btn-primary-custom mt-2">
            <i class="bi bi-arrow-right-circle-fill"></i> Lanjut ke Checkout
        </a>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer_customer.php'; ?>
