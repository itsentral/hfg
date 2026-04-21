CREATE TABLE IF NOT EXISTS tr_delivery_order (
    do_no        VARCHAR(30) PRIMARY KEY,
    customer     VARCHAR(100) NOT NULL,
    tgl_delivery DATE NOT NULL,
    keterangan   TEXT,
    status       ENUM('Draft','Waiting Approval','Approved Exception','Shipped','Cancelled') DEFAULT 'Draft',
    created_by   INT NOT NULL,
    approved_by  INT,
    created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
    approved_at  DATETIME,
    updated_at   DATETIME ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_tgl (tgl_delivery)
);

CREATE TABLE IF NOT EXISTS tr_delivery_detail (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    do_no            VARCHAR(30) NOT NULL,
    produk_fg        VARCHAR(50) NOT NULL,
    nm_produk_fg     VARCHAR(200),
    qty_kirim        DECIMAL(10,2) NOT NULL DEFAULT 0,
    berat_referensi  DECIMAL(10,4) NOT NULL DEFAULT 0,
    estimasi_berat   DECIMAL(10,3) NOT NULL DEFAULT 0,
    FOREIGN KEY (do_no) REFERENCES tr_delivery_order(do_no) ON DELETE CASCADE,
    INDEX idx_do_no (do_no)
);

CREATE TABLE IF NOT EXISTS tr_delivery_weight_log (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    do_no        VARCHAR(30) NOT NULL,
    berat_aktual DECIMAL(10,3) NOT NULL,
    tgl_timbang  DATETIME NOT NULL,
    user_timbang INT NOT NULL,
    selisih_kg   DECIMAL(10,3),
    selisih_pct  DECIMAL(8,4),
    keterangan   VARCHAR(200),
    INDEX idx_do_no (do_no)
);

CREATE TABLE IF NOT EXISTS tr_delivery_approval (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    do_no        VARCHAR(30) NOT NULL,
    approver_id  INT NOT NULL,
    action       ENUM('Approved','Rejected') NOT NULL,
    alasan       VARCHAR(500),
    tgl_approval DATETIME NOT NULL,
    INDEX idx_do_no (do_no)
);
