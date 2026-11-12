-- =====================================================
-- Migrasi 001: Tabel Flow Process & Cycletime
-- Modul   : flow_process (FR-42, UC-N10, PG-10)
-- ERD     : halaman "Product Costing ERD", entitas flow_process_header dan flow_process_detail
-- =====================================================
-- Satu header per Product Category (code_lv2 UNIQUE) berisi banyak proses (detail).
--   man hour per proses (jam/meter) = cycletime (menit/meter) x man_power / 60
--   header.cycletime       = jumlah cycletime semua proses
--   header.total_man_hour  = jumlah man hour semua proses
-- Nilai turunan dihitung di server (controller), bukan oleh database.
--
-- code_lv2 tidak diberi FOREIGN KEY ke product_lvl_2: skema/collation tabel itu belum dikonfirmasi
-- (SHOW CREATE TABLE, D0). Keberadaan kategori divalidasi di aplikasi.
-- Jalankan di database UJI lebih dulu. Aman dijalankan ulang (IF NOT EXISTS).
-- Rollback: 001_create_flow_process.down.sql
-- =====================================================

CREATE TABLE IF NOT EXISTS flow_process_header (
    id             INT NOT NULL AUTO_INCREMENT,
    code_lv2       VARCHAR(50) NOT NULL COMMENT 'kode Product Category (product_lvl_2.code_lv2)',
    cycletime      DECIMAL(14,6) NOT NULL DEFAULT 0 COMMENT 'jumlah cycletime semua proses, menit/meter',
    total_man_hour DECIMAL(18,8) NOT NULL DEFAULT 0 COMMENT 'jumlah man hour semua proses, jam/meter',
    latest_update  DATETIME DEFAULT NULL,
    updated_by     INT DEFAULT NULL COMMENT 'id_user yang terakhir mengubah',
    PRIMARY KEY (id),
    UNIQUE KEY uk_flow_process_code_lv2 (code_lv2)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS flow_process_detail (
    id                     INT NOT NULL AUTO_INCREMENT,
    id_flow_process_header INT NOT NULL,
    costcentre_area        VARCHAR(150) DEFAULT NULL COMMENT 'nama cost centre (ms_costcenter.nama_costcenter)',
    process_name           VARCHAR(150) NOT NULL,
    machine                VARCHAR(150) DEFAULT NULL COMMENT 'teks bebas (master mesin belum ada)',
    cycletime              DECIMAL(14,6) NOT NULL DEFAULT 0 COMMENT 'menit/meter',
    man_power              INT NOT NULL DEFAULT 0 COMMENT 'jumlah orang',
    total_man_hour         DECIMAL(18,8) NOT NULL DEFAULT 0 COMMENT 'cycletime x man_power / 60, jam/meter',
    PRIMARY KEY (id),
    KEY idx_flow_process_detail_header (id_flow_process_header),
    CONSTRAINT fk_flow_process_detail_header FOREIGN KEY (id_flow_process_header)
        REFERENCES flow_process_header (id) ON DELETE CASCADE
) ENGINE=InnoDB;
