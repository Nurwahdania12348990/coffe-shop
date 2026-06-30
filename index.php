<?php
require_once __DIR__ . '/includes/functions.php';
startSession();

// If logged in, redirect to respective dashboard
if (isLoggedIn()) {
    $user = currentUser();
    $redirect = match($user['role']) {
        'admin'   => APP_URL . '/dashboard_admin.php',
        'cashier' => APP_URL . '/dashboard_cashier.php',
        'barista' => APP_URL . '/kitchen.php',
        default   => APP_URL . '/login.php'
    };
    header("Location: $redirect"); exit;
}

// Otherwise show landing page
$settings = getSettings();
$shopName = $settings['shop_name'] ?? 'Brewed & Bold Coffee';
$menuCount = db()->fetchValue("SELECT COUNT(*) FROM menus WHERE is_available=1");
$featured  = db()->fetchAll("SELECT m.*, c.name as cat_name FROM menus m JOIN categories c ON c.id=m.category_id WHERE m.is_featured=1 AND m.is_available=1 LIMIT 6");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= sanitize($shopName) ?> - Coffee Shop POS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Playfair+Display:wght@700&display=swap">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/customer.css">
    <style>
        .landing-hero{background:linear-gradient(160deg,#2C1810 0%,#4A2C1A 50%,#3D1F0E 100%);min-height:100vh;display:flex;align-items:center;position:relative;overflow:hidden;padding:4rem 0}
        .landing-hero::before{content:'';position:absolute;top:-20%;right:-10%;width:600px;height:600px;background:radial-gradient(circle,rgba(200,129,58,0.15) 0%,transparent 70%);pointer-events:none}
        .landing-hero::after{content:'☕';position:absolute;bottom:-2rem;left:-2rem;font-size:14rem;opacity:0.04;transform:rotate(-15deg)}
        .hero-cta{display:flex;gap:1rem;flex-wrap:wrap;margin-top:2rem}
        .btn-hero-primary{background:var(--accent);color:#fff;border:none;padding:0.875rem 2rem;border-radius:14px;font-weight:700;font-size:1rem;cursor:pointer;transition:all 0.2s;text-decoration:none;display:inline-flex;align-items:center;gap:0.5rem}
        .btn-hero-primary:hover{background:var(--accent-light);color:#fff;transform:translateY(-2px)}
        .btn-hero-secondary{background:rgba(255,255,255,0.1);color:#fff;border:2px solid rgba(255,255,255,0.2);padding:0.875rem 2rem;border-radius:14px;font-weight:700;font-size:1rem;cursor:pointer;transition:all 0.2s;text-decoration:none;display:inline-flex;align-items:center;gap:0.5rem}
        .btn-hero-secondary:hover{background:rgba(255,255,255,0.15);color:#fff;transform:translateY(-2px)}
        .features-section{background:var(--cream);padding:5rem 0}
        .feature-card{background:#fff;border-radius:20px;padding:2rem;border:1px solid var(--border);text-align:center;box-shadow:var(--shadow-sm);transition:all 0.3s}
        .feature-card:hover{transform:translateY(-6px);box-shadow:var(--shadow-md)}
        .feature-icon-wrap{width:60px;height:60px;background:var(--accent-pale);border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:var(--accent);margin:0 auto 1rem}
        .featured-section{background:var(--surface);padding:5rem 0}
    </style>
</head>
<body>

<!-- Navbar -->
<nav style="position:fixed;top:0;left:0;right:0;z-index:1000;background:rgba(44,24,16,0.95);backdrop-filter:blur(10px);padding:0.875rem 0;border-bottom:1px solid rgba(255,255,255,0.08)">
    <div class="container d-flex align-items-center justify-content-between">
        <div style="display:flex;align-items:center;gap:0.5rem;color:#fff;font-weight:800;font-size:1.1rem">
            <div style="width:34px;height:34px;background:var(--accent);border-radius:10px;display:flex;align-items:center;justify-content:center">
                <i class="bi bi-cup-hot-fill"></i>
            </div>
            <?= sanitize($shopName) ?>
        </div>
        <div style="display:flex;gap:0.75rem">
            <a href="<?= APP_URL ?>/menu.php" style="color:rgba(255,255,255,0.7);text-decoration:none;font-size:0.875rem;font-weight:600">Menu</a>
            <a href="<?= APP_URL ?>/login.php" style="background:var(--accent);color:#fff;text-decoration:none;padding:0.35rem 0.875rem;border-radius:8px;font-size:0.85rem;font-weight:700">Staff Login</a>
        </div>
    </div>
</nav>

<!-- Hero -->
<section class="landing-hero">
    <div class="container" style="position:relative;z-index:1">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div style="display:inline-flex;align-items:center;gap:0.5rem;background:rgba(200,129,58,0.15);border:1px solid rgba(200,129,58,0.3);color:var(--accent-light);padding:0.4rem 1rem;border-radius:20px;font-size:0.82rem;font-weight:600;margin-bottom:1.5rem">
                    <i class="bi bi-stars"></i> Point of Sale System
                </div>
                <h1 style="font-family:'Playfair Display',serif;font-size:3rem;color:#fff;line-height:1.15;margin-bottom:1rem">
                    Selamat Datang di<br>
                    <span style="color:var(--accent-light)"><?= sanitize($shopName) ?></span>
                </h1>
                <p style="color:rgba(255,255,255,0.65);font-size:1rem;line-height:1.7;margin-bottom:0">
                    Nikmati pengalaman memesan kopi favorit Anda langsung dari meja, tanpa antri. Sistem self-order kami memudahkan Anda.
                </p>
                <div class="hero-cta">
                    <a href="<?= APP_URL ?>/menu.php" class="btn-hero-primary">
                        <i class="bi bi-cup-hot-fill"></i> Pesan Sekarang
                    </a>
                    <a href="<?= APP_URL ?>/login.php" class="btn-hero-secondary">
                        <i class="bi bi-box-arrow-in-right"></i> Login Staff
                    </a>
                </div>
                <div style="display:flex;gap:2rem;margin-top:2.5rem;flex-wrap:wrap">
                    <div style="color:rgba(255,255,255,0.7)">
                        <div style="font-size:1.5rem;font-weight:800;color:#fff"><?= $menuCount ?>+</div>
                        <div style="font-size:0.78rem">Menu Pilihan</div>
                    </div>
                    <div style="color:rgba(255,255,255,0.7)">
                        <div style="font-size:1.5rem;font-weight:800;color:#fff">3</div>
                        <div style="font-size:0.78rem">Metode Bayar</div>
                    </div>
                    <div style="color:rgba(255,255,255,0.7)">
                        <div style="font-size:1.5rem;font-weight:800;color:#fff">10-15</div>
                        <div style="font-size:0.78rem">Menit Siap</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 d-none d-lg-block text-center">
                <div style="font-size:12rem;opacity:0.15">☕</div>
            </div>
        </div>
    </div>
</section>

<!-- Features -->
<section class="features-section">
    <div class="container">
        <div style="text-align:center;margin-bottom:3rem">
            <h2 style="font-family:'Playfair Display',serif;font-size:2rem;color:var(--primary);margin-bottom:0.5rem">Fitur Unggulan</h2>
            <p style="color:var(--text-muted)">Sistem POS modern untuk pengalaman coffee shop terbaik</p>
        </div>
        <div class="row g-3">
            <?php
            $features = [
                ['bi-qr-code-scan','Self Order via QR','Pelanggan scan QR meja dan langsung pesan tanpa perlu download app'],
                ['bi-display','Kitchen Display','Barista melihat pesanan real-time dengan update status otomatis'],
                ['bi-credit-card','Multi Pembayaran','Dukung Tunai, Transfer Bank, dan QRIS untuk kemudahan transaksi'],
                ['bi-bar-chart-fill','Laporan Lengkap','Analitik penjualan harian, menu terlaris, dan laporan keuangan'],
                ['bi-puzzle-fill','Add-On Fleksibel','Ukuran, jenis susu, gula, topping — sesuai selera pelanggan'],
                ['bi-shield-check','3 Role Pengguna','Admin, Kasir, dan Barista dengan akses yang tepat'],
            ];
            foreach ($features as [$icon,$title,$desc]):
            ?>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon-wrap"><i class="bi <?= $icon ?>"></i></div>
                    <h6 style="font-weight:800;color:var(--primary);margin-bottom:0.4rem"><?= $title ?></h6>
                    <p style="font-size:0.82rem;color:var(--text-muted);margin:0"><?= $desc ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Menu -->
<?php if ($featured): ?>
<section class="featured-section">
    <div class="container">
        <div style="text-align:center;margin-bottom:2.5rem">
            <h2 style="font-family:'Playfair Display',serif;font-size:2rem;color:var(--primary);margin-bottom:0.5rem">Menu Favorit</h2>
            <p style="color:var(--text-muted)">Yang paling banyak dipesan pelanggan kami</p>
        </div>
        <div class="menu-grid">
            <?php foreach ($featured as $m): ?>
            <a href="<?= APP_URL ?>/menu.php" class="menu-card" style="text-decoration:none">
                <div class="badge-featured">⭐ Favorit</div>
                <?php if ($m['image'] && file_exists(__DIR__ . '/uploads/menu/' . $m['image'])): ?>
                <img class="menu-card-img" src="<?= APP_URL ?>/uploads/menu/<?= $m['image'] ?>" alt="<?= sanitize($m['name']) ?>">
                <?php else: ?>
                <div class="menu-img-placeholder"><i class="bi bi-cup-hot-fill"></i></div>
                <?php endif; ?>
                <div class="menu-card-body">
                    <div class="menu-card-name"><?= sanitize($m['name']) ?></div>
                    <div class="menu-card-footer">
                        <div class="menu-price"><?= formatCurrency($m['price']) ?></div>
                        <span class="btn-add-cart"><i class="bi bi-arrow-right"></i></span>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <div style="text-align:center;margin-top:2rem">
            <a href="<?= APP_URL ?>/menu.php" class="btn-hero-primary" style="display:inline-flex">
                <i class="bi bi-grid-fill"></i> Lihat Semua Menu
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Footer -->
<footer style="background:var(--primary);color:rgba(255,255,255,0.6);text-align:center;padding:2rem;font-size:0.82rem">
    <div style="color:#fff;font-weight:700;margin-bottom:0.25rem"><?= sanitize($shopName) ?></div>
    <?= sanitize($settings['shop_address'] ?? '') ?> · <?= sanitize($settings['shop_phone'] ?? '') ?><br>
    <div style="margin-top:0.5rem;color:rgba(255,255,255,0.35)">Powered by Brewed & Bold POS v<?= APP_VERSION ?></div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
