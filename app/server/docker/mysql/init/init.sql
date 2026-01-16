-- supermarket_appデータベースの初期化スクリプト

-- 文字セットを明示的に設定
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

-- データベース作成
CREATE DATABASE IF NOT EXISTS supermarket_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ユーザー'dev'にsupermarket_appデータベースへの権限を付与
GRANT ALL PRIVILEGES ON supermarket_app.* TO 'dev'@'%';
FLUSH PRIVILEGES;

USE supermarket_app;

-- データベース接続後の文字セット設定
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ユーザー（店舗スタッフ）テーブル
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    store_name VARCHAR(100),
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- カテゴリテーブル
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- 商品テーブル
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    category_id INT,
    regular_price DECIMAL(10,2) NOT NULL,
    stock_quantity INT DEFAULT 0,
    stock_status ENUM('in_stock', 'low_stock', 'out_of_stock') DEFAULT 'in_stock',
    description TEXT,
    user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- 店舗マスタテーブル（外部キー参照されるため先に定義）
CREATE TABLE stores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    address VARCHAR(255),
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    category ENUM('supermarket', 'pharmacy', 'other') DEFAULT 'supermarket',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- セール情報テーブル
CREATE TABLE sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    sale_price DECIMAL(10,2),
    discount_amount DECIMAL(10,2),
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    inventory_status ENUM('sufficient', 'low', 'out_of_stock') DEFAULT 'sufficient',
    notes TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- セール対象店舗の中間テーブル
CREATE TABLE sale_stores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT NOT NULL,
    store_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    FOREIGN KEY (store_id) REFERENCES stores(id) ON DELETE CASCADE,
    UNIQUE KEY unique_sale_store (sale_id, store_id)
);

-- 売上データテーブル
CREATE TABLE sales_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    quantity_sold INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    sale_date DATE NOT NULL,
    user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- 目標設定テーブル
CREATE TABLE sales_targets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    target_amount DECIMAL(12,2) NOT NULL,
    target_period ENUM('daily', 'weekly', 'monthly') NOT NULL,
    target_date DATE NOT NULL,
    user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- 通知設定テーブル（ユーザーと一緒に削除）
CREATE TABLE notification_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    stock_alerts BOOLEAN DEFAULT TRUE,
    sale_expiry_alerts BOOLEAN DEFAULT TRUE,
    target_alerts BOOLEAN DEFAULT TRUE,
    email_notifications BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ユーザーお気に入り店舗テーブル
CREATE TABLE user_favorite_stores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    store_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (store_id) REFERENCES stores(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_store (user_id, store_id)
);

-- ユーザーお気に入り商品テーブル
CREATE TABLE user_favorite_products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (user_id, product_id)
);

-- ユーザー冷蔵庫管理テーブル
CREATE TABLE user_refrigerator (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT,
    product_name VARCHAR(200) NOT NULL,
    quantity INT DEFAULT 1,
    expiry_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

-- レシピマスタテーブル
CREATE TABLE recipes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    external_id VARCHAR(20) UNIQUE NOT NULL COMMENT 'TheMealDB API ID',
    name VARCHAR(255) NOT NULL,
    alternate_name VARCHAR(255),
    category VARCHAR(100),
    area VARCHAR(100),
    instructions TEXT,
    thumbnail_url VARCHAR(500),
    tags VARCHAR(255),
    youtube_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_area (area),
    INDEX idx_name (name)
);

-- レシピ材料テーブル
CREATE TABLE recipe_ingredients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recipe_id INT NOT NULL,
    ingredient_name VARCHAR(200) NOT NULL,
    measure VARCHAR(100),
    ingredient_order TINYINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE,
    INDEX idx_recipe (recipe_id),
    INDEX idx_ingredient (ingredient_name)
);

-- サンプルユーザーデータの挿入
INSERT INTO users (username, email, password_hash, store_name) VALUES
('admin', 'admin@supermarket.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'メインストア');

-- 初期カテゴリデータの挿入
INSERT INTO categories (name, user_id) VALUES 
('食品', 1),
('飲料', 1),
('日用品', 1),
('冷凍食品', 1),
('お菓子', 1);

-- サンプル商品データの挿入
INSERT INTO products (name, category_id, regular_price, stock_quantity, user_id) VALUES
('りんご', 1, 150.00, 100, 1),
('コーヒー', 2, 298.00, 50, 1),
('洗剤', 3, 380.00, 25, 1),
('冷凍うどん', 4, 120.00, 80, 1),
('チョコレート', 5, 200.00, 60, 1);

-- サンプル売上目標データの挿入
INSERT INTO sales_targets (target_amount, target_period, target_date, user_id) VALUES
(580000.00, 'daily', CURDATE(), 1);

-- 通知設定の初期化
INSERT INTO notification_settings (user_id) VALUES (1);

-- サンプル店舗データの挿入（マップ機能用）
INSERT INTO stores (name, address, latitude, longitude, category) VALUES
('ライフ八戸ノ里店', '大阪府東大阪市下小阪5-1-2', 34.662625, 135.589097, 'supermarket'),
('イオン登美ヶ丘店', '奈良県生駒市鹿畑町3027', 34.702891, 135.727234, 'supermarket'),
('マツモトキヨシ八戸ノ里駅前店', '大阪府東大阪市小阪2-19-15', 34.663245, 135.590123, 'pharmacy'),
('スギ薬局小阪店', '大阪府東大阪市小阪本町1-3-5', 34.665890, 135.591456, 'pharmacy'),
('関西スーパー若江店', '大阪府東大阪市若江南町2-1-40', 34.659123, 135.582345, 'supermarket');

-- 追加の店舗ユーザー（サンプルデータ）
INSERT INTO users (username, email, password_hash, store_name) VALUES
('aeon_tomigaoka', 'aeon@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'イオン登美ヶ丘店'),
('life_yahenosato', 'life@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ライフ八戸ノ里店');

-- 追加の商品データ（イオン登美ヶ丘店用）
INSERT INTO products (name, category_id, regular_price, stock_quantity, user_id) VALUES
('卵 10個パック', 1, 100.00, 50, 2),
('牛乳 1L', 2, 210.00, 30, 2);

-- 追加の商品データ（ライフ八戸ノ里店用）
INSERT INTO products (name, category_id, regular_price, stock_quantity, user_id) VALUES
('トマト 3個入り', 1, 300.00, 20, 3);

-- セール情報のサンプルデータ（イオン登美ヶ丘店）
INSERT INTO sales (product_id, sale_price, discount_amount, start_date, end_date, notes, is_active, user_id) VALUES
(6, 80.00, 20.00, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY), '19:00まで', 1, 2),
(7, 180.00, 30.00, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY), '19:00まで', 1, 2);

-- セール情報のサンプルデータ（ライフ八戸ノ里店）
INSERT INTO sales (product_id, sale_price, discount_amount, start_date, end_date, notes, is_active, user_id) VALUES
(8, 250.00, 50.00, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY), '18:00まで', 1, 3);

-- 追加店舗の通知設定の初期化
INSERT INTO notification_settings (user_id) VALUES (2), (3);
