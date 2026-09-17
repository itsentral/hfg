-- ============================================================
-- Unpack Coil Module - Database Migration
-- Report Unpack Coil (lanjutan request_list)
--
-- Konsep:
--   Coil induk yang sudah "Material Confirmed" (via request_list) berada di
--   gudang PRT (id_gudang=3, kd_gudang='PRT', status_proses='in_transit'),
--   dikelompokkan dalam pack (warehouse_pack / pack_code).
--
--   1 report unpack = 1 PACK.
--   Input 2 tahap:
--     - Level PACK  : berat actual (nett/gross) diinput manual, catatan kulit &
--                     clamp/ring (sekedar catatan). Packing List = agregat coil asli.
--     - Level MATERIAL (dalam pack): jumlah baby coil (roll) + berat actual/PL.
--     - Level BABY COIL (per material): roll fisik hasil unpack.
--
--   Report bisa di-EDIT ulang: baby coil di warehouse_stock_coil ditulis ulang
--   beserta costbook proporsional, dan history dicatat ulang.
-- ============================================================

DROP TABLE IF EXISTS tr_unpack_coil_baby;
DROP TABLE IF EXISTS tr_unpack_coil_material;
DROP TABLE IF EXISTS tr_unpack_coil_pack;
DROP TABLE IF EXISTS tr_unpack_coil_header;

-- ------------------------------------------------------------
-- Header: 1 report unpack = 1 pack (level PACK)
-- ------------------------------------------------------------
CREATE TABLE tr_unpack_coil_header (
    unpack_no           VARCHAR(30) PRIMARY KEY COMMENT 'Format: UNPK-YYYYMM-0001',
    tgl_unpack          DATETIME NOT NULL COMMENT 'Auto: tanggal saat save',
    id_pack             INT COMMENT 'warehouse_pack.id',
    pack_code           VARCHAR(50),
    request_id          INT COMMENT 'tr_warehouse_request_header.id sumber (Material Confirmed)',
    -- Catatan level pack (sekedar catatan)
    catatan_kulit       VARCHAR(200),
    catatan_clamp_ring  VARCHAR(200),
    -- Actual weight level pack (input manual)
    net_weight_actual   DECIMAL(12,2) NOT NULL DEFAULT 0,
    gross_weight_actual DECIMAL(12,2) NOT NULL DEFAULT 0,
    -- Packing list level pack (agregat coil asli)
    net_weight_pl       DECIMAL(12,2) NOT NULL DEFAULT 0,
    gross_weight_pl     DECIMAL(12,2) NOT NULL DEFAULT 0,
    catatan             TEXT,
    status              ENUM('Draft','Confirmed','Cancelled') NOT NULL DEFAULT 'Confirmed',
    is_delete           TINYINT(1) NOT NULL DEFAULT 0,
    created_by          INT NOT NULL,
    created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_by          INT,
    updated_at          DATETIME ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_pack (id_pack),
    INDEX idx_is_delete (is_delete)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- Detail per material dalam pack (level MATERIAL)
-- ------------------------------------------------------------
CREATE TABLE tr_unpack_coil_material (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    unpack_no           VARCHAR(30) NOT NULL,
    id_coil_induk       INT COMMENT 'warehouse_stock_coil.id (coil material sumber)',
    id_material         VARCHAR(50),
    nm_material         VARCHAR(200),
    kode_internal       VARCHAR(100),
    no_coil             VARCHAR(100),
    id_gudang           INT(11),
    kd_gudang           VARCHAR(25),
    jumlah_coil_roll    INT NOT NULL DEFAULT 0 COMMENT 'Jumlah baby coil (input user, default dari DB bila sudah ada)',
    -- Actual weight total per material
    net_weight_actual   DECIMAL(12,2) NOT NULL DEFAULT 0,
    gross_weight_actual DECIMAL(12,2) NOT NULL DEFAULT 0,
    -- Packing list total per material
    net_weight_pl       DECIMAL(12,2) NOT NULL DEFAULT 0,
    gross_weight_pl     DECIMAL(12,2) NOT NULL DEFAULT 0,
    is_delete           TINYINT(1) NOT NULL DEFAULT 0,
    FOREIGN KEY (unpack_no) REFERENCES tr_unpack_coil_header(unpack_no) ON DELETE CASCADE,
    INDEX idx_unpack_no (unpack_no),
    INDEX idx_coil_induk (id_coil_induk),
    INDEX idx_is_delete (is_delete)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- Detail per baby coil (roll) — di bawah material
-- ------------------------------------------------------------
CREATE TABLE tr_unpack_coil_baby (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    id_unpack_material  INT NOT NULL COMMENT 'FK tr_unpack_coil_material.id',
    babycoil_code       VARCHAR(120) COMMENT 'Contoh: DEBDA0319-T1-BC.1',
    no_coil             VARCHAR(100),
    -- Actual per roll (input user)
    net_weight_actual   DECIMAL(12,2) NOT NULL DEFAULT 0,
    gross_weight_actual DECIMAL(12,2) NOT NULL DEFAULT 0,
    -- Packing list per roll
    net_weight_pl       DECIMAL(12,2) NOT NULL DEFAULT 0,
    gross_weight_pl     DECIMAL(12,2) NOT NULL DEFAULT 0,
    costbook            DECIMAL(18,4) NOT NULL DEFAULT 0 COMMENT 'Harga per baby coil (proporsional)',
    total_nilai         DECIMAL(18,4) NOT NULL DEFAULT 0,
    id_coil_baru        INT COMMENT 'warehouse_stock_coil.id hasil insert baby coil',
    is_delete           TINYINT(1) NOT NULL DEFAULT 0,
    FOREIGN KEY (id_unpack_material) REFERENCES tr_unpack_coil_material(id) ON DELETE CASCADE,
    INDEX idx_unpack_material (id_unpack_material),
    INDEX idx_coil_baru (id_coil_baru),
    INDEX idx_is_delete (is_delete)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
