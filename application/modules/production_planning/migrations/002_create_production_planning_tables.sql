CREATE TABLE IF NOT EXISTS tr_production_plan (
    plan_no      VARCHAR(30) PRIMARY KEY,
    tgl_plan     DATE NOT NULL,
    id_produk_fg VARCHAR(50) NOT NULL,
    nm_produk_fg VARCHAR(200),
    target_qty   DECIMAL(10,2) NOT NULL DEFAULT 0,
    target_berat DECIMAL(10,3),
    due_date     DATE,
    catatan      TEXT,
    status       ENUM('Draft','Released','Closed','Cancelled') DEFAULT 'Draft',
    created_by   INT NOT NULL,
    created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_tgl_plan (tgl_plan)
);

CREATE TABLE IF NOT EXISTS tr_production_plan_detail (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    plan_no          VARCHAR(30) NOT NULL,
    id_material      VARCHAR(50),
    nm_material      VARCHAR(200),
    no_coil          VARCHAR(100),
    no_ros           VARCHAR(30),
    net_weight_coil  DECIMAL(10,3),
    estimasi_fg      DECIMAL(10,3),
    FOREIGN KEY (plan_no) REFERENCES tr_production_plan(plan_no) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS tr_production_plan_coil_alloc (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    plan_no      VARCHAR(30) NOT NULL,
    no_coil      VARCHAR(100) NOT NULL,
    status_alloc ENUM('allocated','issued','done','cancelled') DEFAULT 'allocated',
    created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (plan_no) REFERENCES tr_production_plan(plan_no) ON DELETE CASCADE,
    UNIQUE KEY uk_plan_coil (plan_no, no_coil)
);
