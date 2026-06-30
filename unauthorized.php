<?php
require_once __DIR__ . '/includes/functions.php';
startSession();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Ditolak</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;700;800&display=swap">
    <style>
        body{font-family:'Plus Jakarta Sans',sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;background:#FAF6F1;text-align:center;padding:2rem}
        .icon{font-size:4rem;color:#e74c3c;margin-bottom:1rem}
        h2{font-weight:800;color:#2C1810;margin-bottom:0.5rem}
        p{color:#999;margin-bottom:1.5rem}
        a{background:#2C1810;color:#fff;text-decoration:none;padding:0.75rem 1.5rem;border-radius:12px;font-weight:700;display:inline-block}
    </style>
</head>
<body>
    <div>
        <div class="icon"><i class="bi bi-shield-x-fill"></i></div>
        <h2>Akses Ditolak</h2>
        <p>Anda tidak memiliki izin untuk mengakses halaman ini.</p>
        <a href="<?= APP_URL ?>/login.php"><i class="bi bi-arrow-left"></i> Kembali ke Login</a>
    </div>
</body>
</html>
