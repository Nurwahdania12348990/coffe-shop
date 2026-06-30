-- ============================================================
-- POS Coffee Shop - Database Schema & Seed Data
-- ============================================================
-- Cara import: buka phpMyAdmin → New → pos_coffee_shop → Import file ini
-- Password semua akun: password
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+07:00";

CREATE DATABASE IF NOT EXISTS pos_coffee_shop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pos_coffee_shop;

-- Drop tables jika sudah ada (untuk re-import)
DROP TABLE IF EXISTS order_item_addons;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS menu_addon_groups;
DROP TABLE IF EXISTS addon_options;
DROP TABLE IF EXISTS addon_groups;
DROP TABLE IF EXISTS menus;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS coffee_tables;
DROP TABLE IF EXISTS staff;
DROP TABLE IF EXISTS settings;

-- ============================================================
-- TABEL
-- ============================================================

CREATE TABLE staff (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','cashier','barista') NOT NULL DEFAULT 'cashier',
    phone VARCHAR(20),
    email VARCHAR(100),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    icon VARCHAR(50) DEFAULT 'bi-cup-hot',
    description TEXT,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE menus (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL DEFAULT 0,
    image VARCHAR(255),
    is_available TINYINT(1) DEFAULT 1,
    is_featured TINYINT(1) DEFAULT 0,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE addon_groups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    is_required TINYINT(1) DEFAULT 0,
    max_select INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE addon_options (
    id INT AUTO_INCREMENT PRIMARY KEY,
    addon_group_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    price_add DECIMAL(10,2) DEFAULT 0,
    is_default TINYINT(1) DEFAULT 0,
    sort_order INT DEFAULT 0,
    FOREIGN KEY (addon_group_id) REFERENCES addon_groups(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE menu_addon_groups (
    menu_id INT NOT NULL,
    addon_group_id INT NOT NULL,
    PRIMARY KEY (menu_id, addon_group_id),
    FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE CASCADE,
    FOREIGN KEY (addon_group_id) REFERENCES addon_groups(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE coffee_tables (
    id INT AUTO_INCREMENT PRIMARY KEY,
    table_number VARCHAR(20) NOT NULL UNIQUE,
    capacity INT DEFAULT 2,
    qr_code VARCHAR(255),
    status ENUM('available','occupied','reserved') DEFAULT 'available',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(50) NOT NULL UNIQUE,
    table_id INT NULL,
    order_type ENUM('dine_in','takeaway') DEFAULT 'dine_in',
    order_source ENUM('self_order','cashier') DEFAULT 'cashier',
    customer_name VARCHAR(100) DEFAULT 'Guest',
    status ENUM('pending','process','done','cancelled','paid') DEFAULT 'pending',
    payment_method ENUM('cash','transfer','qris') DEFAULT 'cash',
    payment_status ENUM('unpaid','paid') DEFAULT 'unpaid',
    subtotal DECIMAL(10,2) DEFAULT 0,
    tax DECIMAL(10,2) DEFAULT 0,
    discount DECIMAL(10,2) DEFAULT 0,
    total DECIMAL(10,2) DEFAULT 0,
    amount_paid DECIMAL(10,2) DEFAULT 0,
    change_amount DECIMAL(10,2) DEFAULT 0,
    notes TEXT,
    staff_id INT NULL,
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (table_id) REFERENCES coffee_tables(id) ON DELETE SET NULL,
    FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    menu_id INT NULL,
    menu_name VARCHAR(150) NOT NULL,
    menu_price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0,
    notes TEXT,
    status ENUM('pending','process','done') DEFAULT 'pending',
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE order_item_addons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_item_id INT NOT NULL,
    addon_group_id INT NOT NULL,
    addon_group_name VARCHAR(100) NOT NULL,
    addon_option_id INT NOT NULL,
    addon_option_name VARCHAR(100) NOT NULL,
    price_add DECIMAL(10,2) DEFAULT 0,
    FOREIGN KEY (order_item_id) REFERENCES order_items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    key_name VARCHAR(100) NOT NULL UNIQUE,
    key_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- SEED DATA
-- ============================================================

-- Staff (password: password)
INSERT INTO staff (name, username, password, role) VALUES
('Administrator',  'admin',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Siti Kasir',     'kasir',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cashier'),
('Budi Barista',   'barista', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'barista');

-- Kategori
INSERT INTO categories (name, slug, icon, sort_order) VALUES
('Coffee',      'coffee',      'bi-cup-hot-fill', 1),
('Tea',         'tea',         'bi-cup',          2),
('Non Coffee',  'non-coffee',  'bi-cup-straw',    3),
('Snack',       'snack',       'bi-egg-fried',    4),
('Dessert',     'dessert',     'bi-cake2',        5),
('Main Course', 'main-course', 'bi-bowl-hot',     6);

-- Meja (12 meja)
INSERT INTO coffee_tables (table_number, capacity, status, is_active) VALUES
('T01', 2, 'available', 1),
('T02', 2, 'available', 1),
('T03', 4, 'available', 1),
('T04', 4, 'available', 1),
('T05', 6, 'available', 1),
('T06', 6, 'available', 1),
('T07', 2, 'available', 1),
('T08', 4, 'available', 1),
('T09', 2, 'available', 1),
('T10', 8, 'available', 1),
('BAR1', 1, 'available', 1),
('BAR2', 1, 'available', 1);

-- Add-on Groups
INSERT INTO addon_groups (name, is_required, max_select) VALUES
('Ukuran',       1, 1),
('Jenis Susu',   0, 1),
('Tingkat Gula', 1, 1),
('Topping',      0, 3);

-- Opsi Ukuran
INSERT INTO addon_options (addon_group_id, name, price_add, is_default, sort_order) VALUES
(1, 'Regular (12oz)',    0,     1, 1),
(1, 'Large (16oz)',      5000,  0, 2),
(1, 'Extra Large (20oz)',10000, 0, 3);

-- Opsi Jenis Susu
INSERT INTO addon_options (addon_group_id, name, price_add, is_default, sort_order) VALUES
(2, 'Full Cream',   0,     1, 1),
(2, 'Oat Milk',     8000,  0, 2),
(2, 'Almond Milk',  10000, 0, 3),
(2, 'Soy Milk',     6000,  0, 4);

-- Opsi Tingkat Gula
INSERT INTO addon_options (addon_group_id, name, price_add, is_default, sort_order) VALUES
(3, 'No Sugar',        0, 0, 1),
(3, 'Less Sugar (25%)',0, 0, 2),
(3, 'Half Sugar (50%)',0, 0, 3),
(3, 'Normal (100%)',   0, 1, 4);

-- Opsi Topping
INSERT INTO addon_options (addon_group_id, name, price_add, sort_order) VALUES
(4, 'Whipped Cream',   5000, 1),
(4, 'Caramel Drizzle', 5000, 2),
(4, 'Chocolate Chips', 5000, 3),
(4, 'Boba Pearl',      7000, 4),
(4, 'Coconut Jelly',   7000, 5);

-- Menu Coffee
INSERT INTO menus (category_id, name, description, price, image, is_featured, sort_order) VALUES
(1, 'Espresso',          'Single shot espresso dengan crema tebal dan aroma kopi arabika pilihan', 20000, 'espresso.svg',          0, 1),
(1, 'Americano',         'Espresso dengan air panas, rasa kopi yang kuat dan bersih',               25000, 'americano.svg',         0, 2),
(1, 'Cappuccino',        'Espresso dengan steamed milk dan foam susu yang lembut',                  32000, 'cappuccino.svg',        1, 3),
(1, 'Caffe Latte',       'Espresso dengan banyak steamed milk, rasa creamy dan mild',               35000, 'caffe_latte.svg',       1, 4),
(1, 'Flat White',        'Double shot ristretto dengan microfoam susu yang halus',                  38000, 'flat_white.svg',        0, 5),
(1, 'Caramel Macchiato', 'Vanilla latte dengan lapisan caramel drizzle di atasnya',                 42000, 'caramel_macchiato.svg', 1, 6),
(1, 'Mocha',             'Espresso dengan coklat dan steamed milk, perpaduan sempurna',             40000, 'mocha.svg',             0, 7),
(1, 'Cold Brew',         'Kopi seduh dingin 12 jam, rasa smooth dan less acidic',                   38000, 'cold_brew.svg',         1, 8),
(1, 'Dalgona Coffee',    'Whipped coffee di atas susu dingin, trend coffee hits',                   35000, 'dalgona_coffee.svg',    0, 9),
(1, 'Hazelnut Latte',    'Latte dengan tambahan hazelnut syrup yang kaya rasa',                     40000, 'hazelnut_latte.svg',    0, 10);

-- Menu Tea
INSERT INTO menus (category_id, name, description, price, image, is_featured, sort_order) VALUES
(2, 'Matcha Latte',   'Premium matcha Jepang dengan steamed milk, earthy dan creamy',        38000, 'matcha_latte.svg',   1, 1),
(2, 'Teh Tarik',      'Teh susu khas Asia yang diseduh dengan teknik tarik tradisional',     28000, 'teh_tarik.svg',      0, 2),
(2, 'Thai Tea',       'Teh Thai oranye dengan susu kental manis, manis dan segar',           30000, 'thai_tea.svg',       1, 3),
(2, 'Chamomile Tea',  'Teh chamomile hangat yang menenangkan, cocok untuk relaksasi',        25000, 'chamomile_tea.svg',  0, 4),
(2, 'Earl Grey Latte','Earl grey tea dengan steamed milk dan hint bergamot',                  35000, 'earl_grey_latte.svg',0, 5),
(2, 'Lemon Tea',      'Teh segar dengan perasan lemon dan madu alami',                       22000, 'lemon_tea.svg',      0, 6);

-- Menu Non Coffee
INSERT INTO menus (category_id, name, description, price, image, is_featured, sort_order) VALUES
(3, 'Chocolate Melt',      'Dark chocolate premium dengan susu, rich dan indulgent',            35000, 'chocolate_melt.svg',      1, 1),
(3, 'Strawberry Smoothie', 'Blend strawberry segar dengan yoghurt, fruity dan menyegarkan',    38000, 'strawberry_smoothie.svg', 0, 2),
(3, 'Mango Lassi',         'Mango dengan yoghurt ala India, creamy dan tropical',               35000, 'mango_lassi.svg',         1, 3),
(3, 'Blue Ocean',          'Blue curacao dengan soda dan lime, warna cantik dan segar',         32000, 'blue_ocean.svg',          0, 4),
(3, 'Avocado Shake',       'Alpukat creamy dengan susu dan sedikit vanilla',                    38000, 'avocado_shake.svg',       0, 5),
(3, 'Lychee Soda',         'Lychee syrup dengan sparkling water dan es, ringan dan segar',      28000, 'lychee_soda.svg',         0, 6);

-- Menu Snack (14 item)
INSERT INTO menus (category_id, name, description, price, image, is_featured, sort_order) VALUES
(4, 'Croissant',          'Croissant butter premium dari Prancis, renyah di luar lembut di dalam',    28000, 'croissant.svg',        1,  1),
(4, 'French Fries',       'Kentang goreng crispy dengan berbagai pilihan saus',                        25000, 'french_fries.svg',     0,  2),
(4, 'Cheese Fries',       'Kentang goreng dengan lelehan keju cheddar dan jalapeno',                  32000, 'cheese_fries.svg',     1,  3),
(4, 'Chicken Wings',      'Sayap ayam crispy dengan saus buffalo atau BBQ',                            45000, 'chicken_wings.svg',    0,  4),
(4, 'Chicken Nuggets',    'Nugget ayam homemade crispy, disajikan dengan saus BBQ dan mayo',          35000, 'chicken_nuggets.svg',  0,  5),
(4, 'Nachos',             'Tortilla chips dengan salsa, sour cream, dan keju cheddar leleh',          42000, 'nachos.svg',           0,  6),
(4, 'Bruschetta',         'Roti panggang dengan tomat segar, basil, dan olive oil premium',           32000, 'bruschetta.svg',       0,  7),
(4, 'Onion Rings',        'Bawang bombay goreng crispy dengan saus aioli bawang putih',               28000, 'onion_rings.svg',      0,  8),
(4, 'Spring Rolls',       'Lumpia renyah isi ayam dan sayuran, disajikan dengan saus asam manis',     30000, 'spring_rolls.svg',     1,  9),
(4, 'Garlic Bread',       'Roti baguette panggang dengan butter bawang putih dan parsley segar',      22000, 'garlic_bread.svg',     0, 10),
(4, 'Mozzarella Sticks',  'Keju mozzarella goreng crispy dengan marinara dipping sauce',              38000, 'mozza_sticks.svg',     1, 11),
(4, 'Edamame',            'Edamame rebus dengan taburan garam himalaya, healthy snack pilihan',       20000, 'edamame.svg',          0, 12),
(4, 'Beef Sliders',       'Mini burger daging sapi juicy dengan keju, acar, dan saus spesial',       48000, 'beef_sliders.svg',     1, 13),
(4, 'Calamari Goreng',    'Cumi-cumi goreng tepung crispy dengan saus tartar dan lemon segar',       42000, 'calamari.svg',         0, 14);

-- Menu Dessert (14 item)
INSERT INTO menus (category_id, name, description, price, image, is_featured, sort_order) VALUES
(5, 'Tiramisu',           'Dessert Italia klasik dengan mascarpone dan espresso soaked ladyfingers',   52000, 'tiramisu.svg',       1,  1),
(5, 'Lava Cake',          'Chocolate cake hangat dengan lelehan coklat di dalamnya, disajikan hangat', 48000, 'lava_cake.svg',      1,  2),
(5, 'Cheesecake',         'New York style cheesecake creamy dengan topping mixed berry compote',       50000, 'cheesecake.svg',     0,  3),
(5, 'Creme Brulee',       'Custard vanilla Prancis klasik dengan karamelisasi gula di atasnya',        55000, 'creme_brulee.svg',   1,  4),
(5, 'Pancake',            'Fluffy American pancake bertingkat dengan maple syrup dan fresh butter',    38000, 'pancake.svg',        0,  5),
(5, 'Waffle',             'Belgian waffle crispy golden dengan vanilla ice cream dan whipped cream',   45000, 'waffle.svg',         0,  6),
(5, 'Brownies',           'Fudgy dark chocolate brownies dengan walnuts, disajikan dengan ice cream',  42000, 'brownies.svg',       1,  7),
(5, 'Ice Cream Sundae',   'Tiga scoop es krim pilihan dengan topping coklat, karamel, dan kacang',    40000, 'ice_cream.svg',      0,  8),
(5, 'Chocolate Pudding',  'Pudding coklat lembut dengan saus vanilla custard hangat',                  35000, 'pudding.svg',        0,  9),
(5, 'Fruit Tart',         'Tart mentega renyah dengan custard cream dan seasonal fresh fruits',        48000, 'fruit_tart.svg',     1, 10),
(5, 'Chocolate Fondue',   'Coklat leleh premium untuk mencelup buah segar dan marshmallow',            55000, 'choco_fondue.svg',   0, 11),
(5, 'Banana Split',       'Pisang dengan tiga scoop es krim, whipped cream, cherry, dan coklat',      45000, 'banana_split.svg',   0, 12),
(5, 'Panna Cotta',        'Panna cotta susu Italia lembut dengan coulis berry merah segar',           50000, 'panna_cotta.svg',    1, 13),
(5, 'Donut Glazed',       'Donut fluffy dengan glazed gula, tersedia rasa coklat dan stroberi',       25000, 'donut.svg',          0, 14);

-- Menu Main Course (16 item)
INSERT INTO menus (category_id, name, description, price, image, is_featured, sort_order) VALUES
(6, 'Nasi Goreng Spesial', 'Nasi goreng wok-fried dengan telur mata sapi, ayam suwir, dan sayuran segar',    45000, 'nasi_goreng.svg',       1,  1),
(6, 'Mie Goreng Spesial',  'Mie goreng wok dengan udang, ayam, sayuran, dan telur orak-arik',                42000, 'mie_goreng.svg',        1,  2),
(6, 'Nasi Uduk Komplit',   'Nasi uduk santan dengan ayam goreng, tempe, tahu, dan sambal kacang',            48000, 'nasi_uduk.svg',         0,  3),
(6, 'Ayam Bakar',          'Ayam kampung bakar bumbu rempah dengan nasi, lalapan, dan sambal',               52000, 'ayam_bakar.svg',        1,  4),
(6, 'Soto Ayam',           'Soto ayam bening kunyit dengan suwiran ayam, telur, dan bihun hangat',           38000, 'soto_ayam.svg',         0,  5),
(6, 'Gado-Gado',           'Sayuran rebus segar dengan lontong, tahu, tempe, dan saus kacang kental',        35000, 'gado_gado.svg',         0,  6),
(6, 'Rendang Rice',        'Rendang daging sapi empuk dengan nasi putih dan acar timun segar',               65000, 'rendang_rice.svg',      1,  7),
(6, 'Pasta Carbonara',     'Spaghetti al dente dengan saus krim, pancetta, parmesan, dan kuning telur',      55000, 'pasta_carbonara.svg',   1,  8),
(6, 'Pizza Margherita',    'Pizza tipis dengan saus tomat San Marzano, mozzarella segar, dan basil',         65000, 'pizza_margherita.svg',  0,  9),
(6, 'Chicken Sandwich',    'Grilled chicken fillet dengan lettuce, tomat, saus mayo dalam ciabatta',         52000, 'chicken_sandwich.svg',  0, 10),
(6, 'Beef Burger',         'Beef patty 150gr juicy dengan keju cheddar, lettuce, tomat, dalam brioche bun', 65000, 'beef_burger.svg',       1, 11),
(6, 'Club Sandwich',       'Triple decker toast dengan chicken, bacon, lettuce, tomat, dan mayo',            55000, 'club_sandwich.svg',     0, 12),
(6, 'Salmon Bowl',         'Salmon panggang fillet dengan nasi merah, edamame, dan teriyaki sauce',          75000, 'salmon_bowl.svg',       0, 13),
(6, 'Grilled Salmon',      'Fillet salmon panggang dengan mashed potato, asparagus, dan lemon butter',       85000, 'grilled_salmon.svg',    1, 14),
(6, 'Beef Steak',          'Sirloin steak 200gr medium well dengan french fries dan mushroom sauce',         95000, 'beef_steak.svg',        1, 15),
(6, 'Caesar Salad',        'Romaine lettuce segar dengan crouton, parmesan, dan classic caesar dressing',    42000, 'caesar_salad.svg',      0, 16);

-- Hubungkan menu minuman (Coffee, Tea, Non Coffee) dengan addon groups
INSERT INTO menu_addon_groups (menu_id, addon_group_id)
SELECT m.id, ag.id FROM menus m
CROSS JOIN addon_groups ag
WHERE m.category_id IN (1, 2, 3) AND ag.id IN (1, 2, 3, 4);

-- Settings
INSERT INTO settings (key_name, key_value) VALUES
('shop_name',      'Brewed & Bold Coffee'),
('shop_address',   'Jl. Malioboro No. 123, Yogyakarta'),
('shop_phone',     '0274-123456'),
('shop_email',     'hello@brewedandbold.com'),
('tax_percent',    '10'),
('currency',       'IDR'),
('currency_symbol','Rp'),
('receipt_footer', 'Terima kasih telah mengunjungi Brewed & Bold Coffee. See you next time!'),
('logo',           'logo.svg'),
('qris_image',     'qris-sample.png');

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- SELESAI! Buka: http://localhost/pos_coffee_shop
-- Login: admin / password  |  kasir / password  |  barista / password
-- ============================================================
