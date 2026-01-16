-- 混雑率データテーブル（M5Stack用）
-- 店舗の混雑率を時系列で保存

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
