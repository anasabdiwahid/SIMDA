-- Murabaha goods catalog: items SIMDA buys and resells at cost plus a fixed markup.
-- The app also creates this table on first use (MurabahaGoodsService::ensureTable).

CREATE TABLE IF NOT EXISTS murabaha_goods (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(160) NOT NULL,
    category VARCHAR(100) NULL,
    description TEXT NULL,
    price DECIMAL(15,2) NOT NULL COMMENT 'SIMDA purchase (cost) price per unit',
    markup_pct DECIMAL(6,2) NOT NULL DEFAULT 6.00,
    image_path VARCHAR(255) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_goods_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
