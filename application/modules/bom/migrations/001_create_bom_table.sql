CREATE TABLE IF NOT EXISTS ms_bom_header (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    id_produk   VARCHAR(50) NOT NULL COMMENT 'code_lv4 dari new_inventory_4 category=product',
    nm_produk   VARCHAR(200),
    keterangan  TEXT,
    status      TINYINT(1) DEFAULT 1,
    created_by  INT,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_produk (id_produk),
    INDEX idx_status (status)
);

CREATE TABLE IF NOT EXISTS ms_bom_detail (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    id_bom          INT NOT NULL,
    id_material     VARCHAR(50) NOT NULL COMMENT 'code_lv4 dari new_inventory_4 category=material',
    nm_material     VARCHAR(200),
    trade_name      VARCHAR(200),
    qty             DECIMAL(10,4) DEFAULT 1,
    id_unit         VARCHAR(20),
    nm_unit         VARCHAR(50),
    keterangan      VARCHAR(300),
    urut            INT DEFAULT 0,
    FOREIGN KEY (id_bom) REFERENCES ms_bom_header(id) ON DELETE CASCADE,
    INDEX idx_id_bom (id_bom),
    INDEX idx_id_material (id_material)
);
