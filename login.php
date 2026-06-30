<?php
require_once __DIR__ . '/includes/functions.php';
startSession();

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
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Staff - Brewed & Bold POS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Playfair+Display:wght@700&display=swap">
    <style>
        :root{--primary:#2C1810;--accent:#C8813A;--cream:#FAF6F1}
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Plus Jakarta Sans',sans-serif;min-height:100vh;display:flex;background:var(--cream)}
        .login-left{flex:1;background:linear-gradient(160deg,var(--primary) 0%,#4A2C1A 60%,#3D1F0E 100%);display:flex;flex-direction:column;align-items:center;justify-content:center;padding:3rem;position:relative;overflow:hidden}
        .login-left::before{content:'';position:absolute;top:-30%;right:-20%;width:500px;height:500px;background:radial-gradient(circle,rgba(200,129,58,0.15) 0%,transparent 70%);pointer-events:none}
        .login-left::after{content:'☕';position:absolute;bottom:2rem;right:3rem;font-size:8rem;opacity:0.05}
        .brand-mark{width:72px;height:72px;background:var(--accent);border-radius:20px;display:flex;align-items:center;justify-content:center;font-size:2rem;color:#fff;margin-bottom:1.5rem}
        .brand-name{font-family:'Playfair Display',serif;font-size:2.2rem;color:#fff;margin-bottom:0.5rem;line-height:1.2}
        .brand-sub{color:rgba(255,255,255,0.6);font-size:0.9rem}
        .features{margin-top:3rem;display:flex;flex-direction:column;gap:1rem}
        .feature-item{display:flex;align-items:center;gap:1rem;color:rgba(255,255,255,0.75);font-size:0.875rem}
        .feature-icon{width:36px;height:36px;background:rgba(200,129,58,0.2);border-radius:10px;display:flex;align-items:center;justify-content:center;color:var(--accent);flex-shrink:0;font-size:1rem}
        .login-right{width:480px;display:flex;align-items:center;justify-content:center;padding:2rem}
        .login-card{width:100%;max-width:400px}
        .login-title{font-weight:800;font-size:1.6rem;color:var(--primary);margin-bottom:0.4rem}
        .login-subtitle{color:#999;font-size:0.875rem;margin-bottom:2rem}
        .form-group{margin-bottom:1.25rem}
        .form-label{font-weight:600;font-size:0.82rem;color:#555;margin-bottom:0.4rem;display:block}
        .input-wrap{position:relative}
        .input-wrap i{position:absolute;left:1rem;top:50%;transform:translateY(-50%);color:#bbb}
        .form-input{width:100%;padding:0.75rem 1rem 0.75rem 2.75rem;border:1.5px solid #E5E5E5;border-radius:12px;font-size:0.9rem;font-family:inherit;transition:border-color 0.2s;background:#fafafa}
        .form-input:focus{outline:none;border-color:var(--accent);background:#fff;box-shadow:0 0 0 3px rgba(200,129,58,0.12)}
        .btn-login{width:100%;padding:0.85rem;background:var(--primary);color:#fff;border:none;border-radius:12px;font-size:0.95rem;font-weight:700;cursor:pointer;transition:all 0.2s;font-family:inherit;display:flex;align-items:center;justify-content:center;gap:0.5rem}
        .btn-login:hover{background:var(--accent)}
        .alert-error{background:#fee;border:1px solid #fcc;color:#c00;padding:0.75rem 1rem;border-radius:10px;font-size:0.85rem;margin-bottom:1rem;display:none}
        .demo-info{background:#f0f7ff;border:1px solid #c8dff7;border-radius:10px;padding:0.875rem 1rem;margin-top:1.5rem;font-size:0.78rem;color:#4a6fa5}
        .demo-info strong{display:block;margin-bottom:0.4rem;color:#2C5F8A}
        .demo-row{display:flex;justify-content:space-between;padding:0.15rem 0}
        @media(max-width:768px){.login-left{display:none}.login-right{width:100%}}
    </style>
</head>
<body>
<div class="login-left">
    <div class="brand-mark"><i class="bi bi-cup-hot-fill"></i></div>
    <div class="brand-name">Brewed & Bold</div>
    <div class="brand-sub">Point of Sale System</div>
    <div class="features">
        <div class="feature-item"><div class="feature-icon"><i class="bi bi-qr-code-scan"></i></div><span>Self-Order via QR Code Meja</span></div>
        <div class="feature-item"><div class="feature-icon"><i class="bi bi-display"></i></div><span>Kitchen Display Real-time</span></div>
        <div class="feature-item"><div class="feature-icon"><i class="bi bi-credit-card"></i></div><span>Pembayaran Cash, Transfer & QRIS</span></div>
        <div class="feature-item"><div class="feature-icon"><i class="bi bi-bar-chart-fill"></i></div><span>Laporan Penjualan Lengkap</span></div>
    </div>
</div>

<div class="login-right">
    <div class="login-card">
        <div class="login-title">Selamat Datang</div>
        <div class="login-subtitle">Masuk ke sistem POS Coffee Shop</div>

        <div class="alert-error" id="alertError"></div>

        <div class="form-group">
            <label class="form-label">Username</label>
            <div class="input-wrap">
                <i class="bi bi-person"></i>
                <input type="text" class="form-input" id="username" placeholder="Masukkan username" autocomplete="username">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Password</label>
            <div class="input-wrap">
                <i class="bi bi-lock"></i>
                <input type="password" class="form-input" id="password" placeholder="Masukkan password" autocomplete="current-password">
            </div>
        </div>

        <button class="btn-login" id="btnLogin" onclick="doLogin()">
            <i class="bi bi-box-arrow-in-right"></i> Masuk
        </button>

        <div class="demo-info">
            <strong>🔑 Akun Demo (password: password)</strong>
            <div class="demo-row"><span>Admin</span><span><b>admin</b></span></div>
            <div class="demo-row"><span>Kasir</span><span><b>kasir</b></span></div>
            <div class="demo-row"><span>Barista</span><span><b>barista</b></span></div>
        </div>
    </div>
</div>

<script>
const APP_URL = '<?= APP_URL ?>';
function doLogin() {
    const btn = document.getElementById('btnLogin');
    const err = document.getElementById('alertError');
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value;

    if (!username || !password) { showError('Username dan password wajib diisi'); return; }

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Memproses...';
    err.style.display = 'none';

    fetch(`${APP_URL}/api/auth.php?action=login`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username, password })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.location.href = data.redirect;
        } else {
            showError(data.message || 'Login gagal');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-box-arrow-in-right"></i> Masuk';
        }
    })
    .catch(() => {
        showError('Terjadi kesalahan, coba lagi');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-box-arrow-in-right"></i> Masuk';
    });
}

function showError(msg) {
    const el = document.getElementById('alertError');
    el.textContent = msg; el.style.display = 'block';
}

document.getElementById('password').addEventListener('keydown', e => { if (e.key === 'Enter') doLogin(); });
document.getElementById('username').addEventListener('keydown', e => { if (e.key === 'Enter') document.getElementById('password').focus(); });
</script>
</body>
</html>
