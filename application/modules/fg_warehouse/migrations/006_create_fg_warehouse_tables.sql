-- Modul fg_warehouse: Penerimaan FG, Stok, dan Berat Referensi
-- Migration 006

-- Lengkapi tabel tr_fg_receipt yang sudah ada strukturnya
CREATE TABLE IF NOT EXISTS tr_fg_receipt (
    fg_receipt_no   VARCHAR(30) PRIMARY KEY,
    report_no       VARCHAR(30) NOT NULL,
    spk_no          VARCHAR(30),
    no_coil         VARCHAR(100),
    produk_fg       VARCHAR(50),
    nm_produk_fg    VARCHAR(200),
    fg_kg           DECIMAL(10,3) DEFAULT 0,
    fg_qty          DECIMAL(10,2) DEFAULT 0,
    kw2_internal_kg  DECIMAL(10,3) DEFAULT 0,
    kw2_internal_qty DECIMAL(10,2) DEFAULT 0,
    kw2_supplier_kg  DECIMAL(10,3) DEFAULT 0,
    kw2_supplier_qty DECIMAL(10,2) DEFAULT 0,
    status          ENUM('Draft','Posted','Cancelled') DEFAULT 'Draft',
    created_by      INT NOT NULL,
    posted_by       INT,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    posted_at       DATETIME,
    INDEX idx_report_no (report_no),
    INDEX idx_produk_fg (produk_fg),
    INDEX idx_status (status)
);

CREATE TABLE IF NOT EXISTS fg_stock (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    produk_fg       VARCHAR(50) UNIQUE NOT NULL,
    nm_produk_fg    VARCHAR(200),
    qty_stok        DECIMAL(10,2) DEFAULT 0,
    total_berat     DECIMAL(10,3) DEFAULT 0,
    berat_referensi DECIMAL(10,4) DEFAULT 0 COMMENT 'Rata-rata tertimbang: total_berat / qty_stok',
    last_update     DATETIME,
    INDEX idx_produk_fg (produk_fg)
);

CREATE TABLE IF NOT EXISTS fg_stock_ledger (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    produk_fg     VARCHAR(50) NOT NULL,
    tgl_transaksi DATE NOT NULL,
    no_referensi  VARCHAR(30) NOT NULL,
    jenis_mutasi  ENUM('IN','OUT') NOT NULL,
    qty_in        DECIMAL(10,2) DEFAULT 0,
    qty_out       DECIMAL(10,2) DEFAULT 0,
    berat_in      DECIMAL(10,3) DEFAULT 0,
    berat_out     DECIMAL(10,3) DEFAULT 0,
    qty_saldo     DECIMAL(10,2) DEFAULT 0,
    berat_saldo   DECIMAL(10,3) DEFAULT 0,
    keterangan    VARCHAR(200),
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_produk_fg (produk_fg),
    INDEX idx_tgl (tgl_transaksi),
    INDEX idx_no_ref (no_referensi)
);

CREATE TABLE IF NOT EXISTS ms_fg_weight_history (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    produk_fg        VARCHAR(50) NOT NULL,
    berat_referensi  DECIMAL(10,4) NOT NULL,
    total_qty_stok   DECIMAL(10,2) NOT NULL,
    total_berat_stok DECIMAL(10,3) NOT NULL,
    effective_date   DATETIME NOT NULL,
    created_by       INT NOT NULL,
    INDEX idx_produk_fg (produk_fg),
    INDEX idx_effective_date (effective_date)
);
