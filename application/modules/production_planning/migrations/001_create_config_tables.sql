-- Tabel konfigurasi parameter
CREATE TABLE IF NOT EXISTS ms_config_param (
    param_key   VARCHAR(100) PRIMARY KEY,
    param_value VARCHAR(255) NOT NULL,
    keterangan  VARCHAR(500),
    updated_by  INT,
    updated_at  DATETIME
);

-- Insert data awal
INSERT IGNORE INTO ms_config_param (param_key, param_value, keterangan) VALUES
('toleransi_timbang_pct', '0.05', 'Toleransi selisih berat timbang awal vs packing list (5%)'),
('toleransi_deviasi_fg_pct', '0.05', 'Toleransi deviasi berat satuan FG vs standar (5%)'),
('toleransi_selisih_kirim_pct', '0.03', 'Toleransi selisih berat pengiriman vs estimasi (3%)');

-- Tabel notifikasi in-app
CREATE TABLE IF NOT EXISTS ms_notification (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    user_id      INT NOT NULL,
    judul        VARCHAR(200) NOT NULL,
    pesan        TEXT NOT NULL,
    no_referensi VARCHAR(30),
    modul        VARCHAR(50),
    is_read      TINYINT(1) DEFAULT 0,
    created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_is_read (is_read)
);
