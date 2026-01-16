-- 不足しているテーブルを作成するスクリプト

-- 店舗マスタテーブル
CREATE TABLE IF NOT EXISTS stores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    address VARCHAR(255),
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    category ENUM('supermarket', 'pharmacy', 'other') DEFAULT 'supermarket',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- セール対象店舗の中間テーブル
CREATE TABLE IF NOT EXISTS sale_stores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT NOT NULL,
    store_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    FOREIGN KEY (store_id) REFERENCES stores(id) ON DELETE CASCADE,
    UNIQUE KEY unique_sale_store (sale_id, store_id)
);

-- ユーザーお気に入り店舗テーブル
CREATE TABLE IF NOT EXISTS user_favorite_stores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    store_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (store_id) REFERENCES stores(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_store (user_id, store_id)
);

-- ユーザーお気に入り商品テーブル
CREATE TABLE IF NOT EXISTS user_favorite_products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (user_id, product_id)
);

-- ユーザー冷蔵庫管理テーブル
CREATE TABLE IF NOT EXISTS user_refrigerator (
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
CREATE TABLE IF NOT EXISTS recipes (
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
CREATE TABLE IF NOT EXISTS recipe_ingredients (
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

-- 混雑率データテーブル（M5Stack用）
CREATE TABLE IF NOT EXISTS congestion_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL,
    congestion_level INT NOT NULL COMMENT '混雑率（0-100の整数）',
    measured_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    device_id VARCHAR(100) COMMENT 'M5StackデバイスID',
    notes TEXT COMMENT '備考',
    FOREIGN KEY (store_id) REFERENCES stores(id) ON DELETE CASCADE,
    INDEX idx_store_measured (store_id, measured_at),
    INDEX idx_measured_at (measured_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
