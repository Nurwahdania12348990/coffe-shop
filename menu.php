<?php
$pageTitle = 'Menu - Brewed & Bold Coffee';
require_once __DIR__ . '/includes/header_customer.php';

$categories = db()->fetchAll("SELECT * FROM categories WHERE is_active=1 ORDER BY sort_order");
$menus = db()->fetchAll("
    SELECT m.*, c.name as cat_name, c.slug as cat_slug, c.icon as cat_icon
    FROM menus m 
    JOIN categories c ON c.id = m.category_id
    WHERE m.is_available=1 AND c.is_active=1
    ORDER BY c.sort_order, m.sort_order, m.name
");

// Group by category
$grouped = [];
foreach ($menus as $menu) {
    $grouped[$menu['cat_slug']][] = $menu;
}
?>
<meta name="app-url" content="<?= APP_URL ?>">

<!-- Hero -->
<div class="hero-section">
    <div class="container">
        <div class="hero-title">Coffee & More ☕</div>
        <div class="hero-subtitle">Pesan langsung dari meja Anda, siap dalam hitungan menit</div>
        <div class="hero-stats">
            <span class="hero-stat"><i class="bi bi-cup-hot-fill"></i><?= count($menus) ?>+ Menu</span>
            <span class="hero-stat"><i class="bi bi-clock-fill"></i>Est. 10-15 menit</span>
            <span class="hero-stat"><i class="bi bi-star-fill"></i>4.9 Rating</span>
        </div>
    </div>
</div>

<!-- Search -->
<div class="search-bar-wrap">
    <div class="container">
        <div class="search-wrap">
            <i class="bi bi-search"></i>
            <input type="text" class="search-bar" id="menuSearch" placeholder="Cari menu favorit kamu...">
        </div>
    </div>
</div>

<!-- Category Pills -->
<div class="category-section">
    <div class="container">
        <div class="category-pills">
            <span class="cat-pill active" data-slug="all" onclick="filterCategory('all')">
                <i class="bi bi-grid-fill"></i> Semua
            </span>
            <?php foreach ($categories as $cat): ?>
            <?php if (!empty($grouped[$cat['slug']])): ?>
            <span class="cat-pill" data-slug="<?= $cat['slug'] ?>" onclick="filterCategory('<?= $cat['slug'] ?>')">
                <i class="<?= $cat['icon'] ?>"></i> <?= sanitize($cat['name']) ?>
            </span>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Menu Sections -->
<div class="container menu-section">
    <?php foreach ($categories as $cat): ?>
    <?php if (empty($grouped[$cat['slug']])) continue; ?>
    <div class="menu-section-group" data-category="<?= $cat['slug'] ?>">
        <div class="section-header">
            <div class="section-title"><i class="<?= $cat['icon'] ?>"></i> <?= sanitize($cat['name']) ?></div>
            <span class="text-muted small"><?= count($grouped[$cat['slug']]) ?> item</span>
        </div>
        <div class="menu-grid">
            <?php foreach ($grouped[$cat['slug']] as $menu): ?>
            <div class="menu-card" data-name="<?= strtolower(sanitize($menu['name'])) ?>" data-category="<?= $cat['slug'] ?>"
                 onclick="POS.addToCart(<?= $menu['id'] ?>)">
                <?php if ($menu['is_featured']): ?>
                <div class="badge-featured">⭐ Favorit</div>
                <?php endif; ?>
                <?php if ($menu['image'] && file_exists(__DIR__ . '/uploads/menu/' . $menu['image'])): ?>
                <img class="menu-card-img" src="<?= APP_URL ?>/uploads/menu/<?= $menu['image'] ?>" alt="<?= sanitize($menu['name']) ?>" loading="lazy">
                <?php else: ?>
                <div class="menu-img-placeholder"><i class="<?= $cat['icon'] ?>"></i></div>
                <?php endif; ?>
                <div class="menu-card-body">
                    <div class="menu-card-name"><?= sanitize($menu['name']) ?></div>
                    <?php if ($menu['description']): ?>
                    <div class="menu-card-desc"><?= sanitize($menu['description']) ?></div>
                    <?php endif; ?>
                    <div class="menu-card-footer">
                        <div class="menu-price"><?= formatCurrency($menu['price']) ?></div>
                        <button class="btn-add-cart" onclick="event.stopPropagation(); POS.addToCart(<?= $menu['id'] ?>)">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <?php if (empty($menus)): ?>
    <div class="empty-state">
        <i class="bi bi-cup"></i>
        <h5>Menu belum tersedia</h5>
        <p>Hubungi staff kami untuk informasi lebih lanjut</p>
    </div>
    <?php endif; ?>
</div>

<!-- Cart FAB -->
<?php if ($cartCount > 0): ?>
<div style="position:fixed;bottom:1.5rem;right:1.5rem;z-index:200">
    <a href="<?= APP_URL ?>/cart.php" class="btn-primary-custom" style="width:auto;padding:0.875rem 1.5rem;border-radius:50px;box-shadow:0 8px 24px rgba(44,24,16,0.3)">
        <i class="bi bi-bag-fill"></i> Lihat Keranjang
        <span style="background:var(--accent);padding:0.15rem 0.5rem;border-radius:20px;font-size:0.8rem;margin-left:0.25rem"><?= $cartCount ?></span>
    </a>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer_customer.php'; ?>
