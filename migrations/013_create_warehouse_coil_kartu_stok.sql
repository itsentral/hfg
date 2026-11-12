-- Migration 013: Kartu Pemakaian Material per Coil (WRH. Production 2 / warehouse_stock_coil)
-- Tanggal: 2026-11-12

CREATE TABLE IF NOT EXISTS `warehouse_coil_kartu_stok` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_coil` int(11) NOT NULL COMMENT 'Referensi ke warehouse_stock_coil.id',
  `no_trs` varchar(100) NOT NULL COMMENT 'Nomor SPK Induk / Unpack / Referensi transaksi',
  `id_material` varchar(50) NOT NULL COMMENT 'Kode material (code_lv4)',
  `nm_material` varchar(255) DEFAULT NULL COMMENT 'Nama material',
  `no_coil` varchar(100) DEFAULT NULL COMMENT 'Nomor coil fisik',
  `kode_internal` varchar(255) DEFAULT NULL COMMENT 'Kode internal coil',
  `status` enum('in','out') NOT NULL COMMENT 'in = masuk prod. 2, out = pemakaian/keluar dari prod. 2',
  `dari` varchar(100) NOT NULL COMMENT 'Lokasi/proses asal (mis. Produksi 1, WIP, Slitting, Hold, Unpack, prod. 2)',
  `ke` varchar(100) NOT NULL COMMENT 'Lokasi/tujuan (mis. prod. 2, consume, WIP, hold, Scrap, Unpack)',
  `qty_trans` decimal(20,3) NOT NULL DEFAULT '0.000' COMMENT 'Kuantitas transaksi (Kg)',
  `qty_awal` decimal(20,3) NOT NULL DEFAULT '0.000' COMMENT 'Saldo awal coil sebelum transaksi (Kg)',
  `qty_akhir` decimal(20,3) NOT NULL DEFAULT '0.000' COMMENT 'Saldo akhir coil setelah transaksi (Kg, dapat bernilai negatif bila selisih timbang)',
  `production_report_id` int(11) DEFAULT NULL COMMENT 'Referensi production_reports.id untuk idempotensi laporan produksi',
  `keterangan` varchar(255) DEFAULT NULL COMMENT 'Catatan transaksi opsional',
  `created_by` varchar(50) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_kartu_id_coil` (`id_coil`),
  KEY `idx_kartu_no_coil` (`no_coil`),
  KEY `idx_kartu_no_trs` (`no_trs`),
  KEY `idx_kartu_report` (`production_report_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Kartu Pemakaian Material per Coil (WRH. Production 2)';

