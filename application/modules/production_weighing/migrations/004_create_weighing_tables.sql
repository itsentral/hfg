CREATE TABLE IF NOT EXISTS tr_coil_preweigh (
    preweigh_no  VARCHAR(30) PRIMARY KEY,
    spk_no       VARCHAR(30) NOT NULL,
    no_coil      VARCHAR(100) NOT NULL,
    gross_actual DECIMAL(10,3) DEFAULT 0 COMMENT 'Gross weight timbang aktual',
    gross_pl     DECIMAL(10,3) DEFAULT 0 COMMENT 'Gross weight dari packing list',
    net_pl       DECIMAL(10,3) DEFAULT 0 COMMENT 'Net weight dari packing list',
    selisih_gross DECIMAL(10,3) GENERATED ALWAYS AS (gross_actual - gross_pl) STORED,
    status       ENUM('Draft','Confirmed','Exception') DEFAULT 'Draft',
    override_alasan TEXT,
    created_by   INT NOT NULL,
    created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_spk_no (spk_no),
    INDEX idx_no_coil (no_coil),
    INDEX idx_status (status)
);

CREATE TABLE IF NOT EXISTS tr_coil_preweigh_component (
    id                   INT AUTO_INCREMENT PRIMARY KEY,
    preweigh_no          VARCHAR(30) NOT NULL,
    berat_kulit          DECIMAL(10,3) DEFAULT 0,
    berat_clamp_ring     DECIMAL(10,3) DEFAULT 0,
    berat_coil_tong      DECIMAL(10,3) DEFAULT 0,
    berat_cover_wrapping DECIMAL(10,3) DEFAULT 0,
    net_timbang_awal     DECIMAL(10,3) GENERATED ALWAYS AS (berat_coil_tong + berat_cover_wrapping) STORED,
    selisih_net          DECIMAL(10,3) DEFAULT 0 COMMENT 'Dihitung saat save: net_timbang_awal - net_pl',
    selisih_net_pct      DECIMAL(8,4) DEFAULT 0 COMMENT 'Persentase selisih net',
    FOREIGN KEY (preweigh_no) REFERENCES tr_coil_preweigh(preweigh_no) ON DELETE CASCADE
);
