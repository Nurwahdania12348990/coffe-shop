# ☕ Brewed & Bold Coffee — POS System

Sistem Point of Sale Coffee Shop berbasis PHP Native + MySQL + Bootstrap 5.

---

## 🚀 Cara Instalasi

### Persyaratan
- PHP 7.4+ (disarankan PHP 8.0+)
- MySQL 5.7+ atau MariaDB 10.4+
- Apache/Nginx dengan mod_rewrite aktif
- XAMPP / Laragon / WAMP (untuk local development)

### Langkah Instalasi

1. **Copy folder project** ke root server:
   - XAMPP: `C:/xampp/htdocs/pos_coffee_shop/`
   - Laragon: `C:/laragon/www/pos_coffee_shop/`

2. **Import database:**
   - Buka phpMyAdmin → Create Database `pos_coffee_shop`
   - Import file `database.sql`

3. **Konfigurasi koneksi** di `config/config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');      // sesuaikan username MySQL
   define('DB_PASS', '');          // sesuaikan password MySQL
   define('DB_NAME', 'pos_coffee_shop');
   define('APP_URL', 'http://localhost/pos_coffee_shop');
   ```

4. **Pastikan folder writable:**
   ```
   uploads/menu/   → chmod 755 (atau 777 jika perlu)
   ```

5. **Buka browser:** `http://localhost/pos_coffee_shop`

---

## 👤 Akun Default

| Role    | Username | Password |
|---------|----------|----------|
| Admin   | admin    | password |
| Kasir   | kasir    | password |
| Barista | barista  | password |

> **Catatan:** Password hash di database menggunakan `password_hash()` PHP.  
> Password default di atas adalah `password` (bukan `admin123`).  
> Ganti password setelah login pertama!

---

## 📁 Struktur Project

```
pos_coffee_shop/
├── api/                    ← Endpoint API (CRUD)
│   ├── auth.php           ← Login / Logout
│   ├── menu.php           ← CRUD Menu
│   ├── cart.php           ← Keranjang belanja (session)
│   ├── orders.php         ← Buat & update pesanan
│   ├── categories.php     ← CRUD Kategori
│   ├── addons.php         ← CRUD Add-on
│   ├── tables.php         ← CRUD Meja
│   ├── staff.php          ← CRUD Staff
│   ├── reports.php        ← Laporan penjualan
│   └── settings.php       ← Update pengaturan
│
├── assets/
│   ├── css/
│   │   ├── customer.css   ← Style halaman customer
│   │   └── admin.css      ← Style dashboard admin/staff
│   ├── js/
│   │   ├── customer.js    ← JS customer (cart, addon modal)
│   │   └── admin.js       ← JS admin (kitchen, cashier POS)
│   └── images/
│       ├── menu/          ← Gambar menu statis
│       ├── logo/          ← Logo toko
│       ├── banner/        ← Banner promosi
│       ├── qris/          ← Gambar QRIS statis
│       └── qr-table/      ← QR code per meja
│
├── config/
│   ├── config.php         ← Konfigurasi app & database
│   └── database.php       ← Database connection class
│
├── includes/
│   ├── functions.php      ← Helper functions
│   ├── header_customer.php
│   ├── footer_customer.php
│   ├── header_admin.php
│   └── footer_admin.php
│
├── uploads/menu/          ← Upload gambar menu (dari admin)
│
├── index.php              ← Landing page
├── login.php              ← Login staff
├── menu.php               ← Halaman menu (customer)
├── cart.php               ← Keranjang (customer)
├── checkout.php           ← Checkout (customer)
├── receipt.php            ← Struk pesanan
├── dashboard_admin.php    ← Dashboard admin
├── dashboard_cashier.php  ← POS kasir
├── kitchen.php            ← Kitchen Display (barista)
├── menus.php              ← Kelola menu (admin)
├── category.php           ← Kelola kategori (admin)
├── addons.php             ← Kelola add-on (admin)
├── tables.php             ← Kelola meja (admin)
├── users.php              ← Kelola staff (admin)
├── orders.php             ← Daftar pesanan
├── reports.php            ← Laporan penjualan
├── settings.php           ← Pengaturan sistem
├── unauthorized.php       ← Halaman akses ditolak
├── database.sql           ← Schema + seed data
└── .htaccess              ← Security & config Apache
```

---

## 🔄 Alur Penggunaan

### Self Order (Customer)
1. Customer scan QR Code di meja → buka `menu.php?table={id}`
2. Pilih menu → tambah add-on → masuk keranjang
3. Checkout → pilih metode bayar (Cash/Transfer/QRIS)
4. Pesanan masuk ke kitchen display barista
5. Struk otomatis ditampilkan

### Kasir
1. Login → `dashboard_cashier.php`
2. Pilih meja, tambah menu dari POS panel
3. Klik "Proses Pembayaran" → isi nominal → cetak struk

### Barista
1. Login → `kitchen.php`
2. Lihat pesanan masuk → klik "Mulai Proses" → "Selesai"
3. Auto-refresh setiap 10 detik

### Admin
1. Login → `dashboard_admin.php`
2. Kelola menu, kategori, add-on, meja, staff
3. Lihat laporan penjualan di `reports.php`

---

## 🛠️ Teknologi

- **Backend:** PHP Native (no framework)
- **Database:** MySQL
- **Frontend:** Bootstrap 5.3, Bootstrap Icons, Vanilla JS
- **Charts:** Chart.js 4.4
- **Alerts:** SweetAlert2
- **Fonts:** Plus Jakarta Sans, Playfair Display (Google Fonts)

---

## 💡 Tips

- Untuk production, set `error_reporting(0)` di `config/config.php`
- Ganti password semua akun demo sebelum go-live
- Backup database secara rutin
- Pastikan folder `uploads/menu/` memiliki permission write

---

*Brewed & Bold POS v1.0.0 — Developed with ☕ & 💻*
