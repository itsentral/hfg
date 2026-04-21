CREATE TABLE IF NOT EXISTS tr_spk_production (
    spk_no     VARCHAR(30) PRIMARY KEY,
    plan_no    VARCHAR(30) NOT NULL,
    produk_fg  VARCHAR(50) NOT NULL,
    nm_produk_fg VARCHAR(200),
    target_qty DECIMAL(10,2) NOT NULL DEFAULT 0,
    tgl_spk    DATE NOT NULL,
    due_date   DATE,
    catatan    TEXT,
    status     ENUM('Draft','Released','In Process','Submitted','Closed','Cancelled') DEFAULT 'Draft',
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_plan_no (plan_no),
    INDEX idx_status (status)
);

CREATE TABLE IF NOT EXISTS tr_spk_material_detail (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    spk_no       VARCHAR(30) NOT NULL,
    no_coil      VARCHAR(100) NOT NULL,
    id_material  VARCHAR(50),
    nm_material  VARCHAR(200),
    net_weight   DECIMAL(10,3),
    FOREIGN KEY (spk_no) REFERENCES tr_spk_production(spk_no) ON DELETE CASCADE,
    UNIQUE KEY uk_spk_coil (spk_no, no_coil)
);

CREATE TABLE IF NOT EXISTS tr_spk_scan_log (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    spk_no       VARCHAR(30) NOT NULL,
    no_coil      VARCHAR(100) NOT NULL,
    scan_time    DATETIME NOT NULL,
    scan_user    INT NOT NULL,
    status_scan  ENUM('success','rejected') NOT NULL,
    keterangan   VARCHAR(500),
    INDEX idx_spk_no (spk_no),
    INDEX idx_no_coil (no_coil)
);

CREATE TABLE IF NOT EXISTS tr_stock_move_prod (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    spk_no       VARCHAR(30) NOT NULL,
    no_coil      VARCHAR(100) NOT NULL,
    from_gudang  VARCHAR(50) NOT NULL,
    to_gudang    VARCHAR(50) NOT NULL,
    move_time    DATETIME NOT NULL,
    move_user    INT NOT NULL,
    INDEX idx_spk_no (spk_no),
    INDEX idx_no_coil (no_coil)
);
