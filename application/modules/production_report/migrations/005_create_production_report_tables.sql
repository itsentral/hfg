-- ============================================================================
-- Migration: Create Production Report Tables
-- Module: production_report
-- Description: Tabel untuk laporan produksi, validasi berat FG, dan supplier performance feed
-- ============================================================================

CREATE TABLE IF NOT EXISTS tr_production_report (
    report_no       VARCHAR(30) PRIMARY KEY,
    spk_no          VARCHAR(30) NOT NULL,
    no_coil         VARCHAR(100) NOT NULL,
    berat_cover_wrapping DECIMAL(10,3) DEFAULT 0 COMMENT 'Diambil dari timbang awal',
    status          ENUM('Draft','Submitted','Approved','Rejected','Posted to FG') DEFAULT 'Draft',
    override_fg     TINYINT(1) DEFAULT 0,
    override_alasan TEXT,
    created_by      INT NOT NULL,
    approved_by     INT,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    approved_at     DATETIME,
    updated_at      DATETIME ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_spk_no (spk_no),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tr_production_report_result (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    report_no           VARCHAR(30) NOT NULL,
    reject_supplier     DECIMAL(10,3) DEFAULT 0,
    waste_potong        DECIMAL(10,3) DEFAULT 0,
    ng_internal         DECIMAL(10,3) DEFAULT 0,
    ng_supplier         DECIMAL(10,3) DEFAULT 0,
    plat_bs             DECIMAL(10,3) DEFAULT 0,
    fg_kg               DECIMAL(10,3) DEFAULT 0,
    fg_qty              DECIMAL(10,2) DEFAULT 0,
    kw2_internal_kg     DECIMAL(10,3) DEFAULT 0,
    kw2_internal_qty    DECIMAL(10,2) DEFAULT 0,
    kw2_supplier_kg     DECIMAL(10,3) DEFAULT 0,
    kw2_supplier_qty    DECIMAL(10,2) DEFAULT 0,
    tong_coil           DECIMAL(10,3) DEFAULT 0,
    total_berat_coil    DECIMAL(10,3) DEFAULT 0 COMMENT 'Dihitung otomatis',
    net_hasil_produksi  DECIMAL(10,3) DEFAULT 0 COMMENT 'Dihitung otomatis',
    berat_satuan_fg     DECIMAL(10,4) DEFAULT 0 COMMENT 'fg_kg / fg_qty',
    FOREIGN KEY (report_no) REFERENCES tr_production_report(report_no) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tr_supplier_perf_feed (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    report_no           VARCHAR(30) NOT NULL,
    no_coil             VARCHAR(100) NOT NULL,
    id_supplier         INT,
    selisih_gross       DECIMAL(10,3) DEFAULT 0,
    selisih_net         DECIMAL(10,3) DEFAULT 0,
    reject_supplier_kg  DECIMAL(10,3) DEFAULT 0,
    ng_supplier_kg      DECIMAL(10,3) DEFAULT 0,
    kw2_supplier_kg     DECIMAL(10,3) DEFAULT 0,
    tgl_feed            DATE NOT NULL,
    INDEX idx_report_no (report_no),
    INDEX idx_no_coil (no_coil),
    INDEX idx_id_supplier (id_supplier)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
