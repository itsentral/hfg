-- =====================================================================================================
-- 001_create_subkont_tables.sql  --  FEAT-12 Proses Subkont (SPK Subkont -> Tampungan Siap Kirim -> Surat Jalan -> Portal Vendor -> Incoming)
-- Sumber: specs/docs/diagrams/erd-revisi.drawio halaman "Subkont ERD" + specs/docs/fr-nfr.md FR-99..FR-125 (status 2026-11-20).
-- Nama tabel dan kolom SAMA dengan ERD (aturan penamaan seragam). 14 tabel baru; tabel yang sudah ada hanya dirujuk lewat FK:
--   new_supplier(id), warehouse_stock_coil(id), gl_interface(id)   (ketiganya INT, InnoDB).
-- TIDAK DIEKSEKUSI oleh agen (specs/context/coder-only/testing.md): jalankan manual di hfg_dev (BUKAN db_hfg_dev) setelah ditinjau.
-- Urutan pembuatan mengikuti ketergantungan FK. Pasangan tr_subkont_tampungan <-> tr_subkont_delivery_detail saling menunjuk,
-- sehingga FK tr_subkont_tampungan.id_subkont_delivery_detail ditambahkan lewat ALTER setelah kedua tabel ada.
-- Tidak ada seed data dan tidak ada perubahan pada tabel yang sudah ada.
-- Poin untuk programmer (bukan keputusan migrasi, lihat catatan di bawah tiap tabel):
--   * Kolom kode produk FG tidak diberi FK ke product_lvl_4 karena product_lvl_4.code_lv4 tidak unik/terindeks; dipakai pola yang sama
--     dengan fg_stock.produk_fg (VARCHAR).
--   * fg_stock.produk_fg bertipe VARCHAR(50), sedangkan kode FG baru `Nama Produk/Kode Internal Material/No. Urut Subkont` bisa lebih
--     panjang (contoh: "Spandek 0.35 x 1000/DEBDA0319-T2/1" = 34 karakter). Perlu pelebaran kolom itu saat implementasi (di luar migrasi ini).
--   * Kode akun jurnal (Material In Supplier 9901-01-01, Unbill 9901-02-01 dst.) masih sementara; diatur di Master Jurnal Mapping, bukan di sini.
-- =====================================================================================================

-- ---------------------------------------------------------------------------------------------
-- Production Planning -- SPK Subkont
-- ---------------------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS tr_spk_subkont_header (
    spk_no            VARCHAR(30)  NOT NULL COMMENT 'mis. SPK-Sub-001',
    jenis_subkont     VARCHAR(30)  NOT NULL COMMENT 'SPK Subkont Material | SPK Subkont FG',
    tgl_spk           DATE         NOT NULL,
    proses_disubkont  VARCHAR(100)     NULL COMMENT 'mis. Proses Slitting Material | Proses Tambah Pasir (merah)',
    tujuan_incoming   VARCHAR(50)  NOT NULL COMMENT 'otomatis: Warehouse Material (Material) | Warehouse Finish Good (FG)',
    catatan           TEXT             NULL,
    status            VARCHAR(20)  NOT NULL DEFAULT 'Draft' COMMENT 'Draft | Requested | Dikirim Sebagian | Dikirim Penuh | Incoming | Closed | Cancelled',
    created_by        INT              NULL COMMENT 'id user HFG',
    created_at        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by        INT              NULL,
    updated_at        DATETIME         NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (spk_no),
    INDEX idx_spk_status (status),
    INDEX idx_spk_tgl (tgl_spk)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
  COMMENT='SPK Subkont (header). Tanpa FK ke vendor dan tanpa FK ke PO (keputusan user, FR-99)';

CREATE TABLE IF NOT EXISTS tr_spk_subkont_detail (
    id             INT           NOT NULL AUTO_INCREMENT,
    spk_no         VARCHAR(30)   NOT NULL,
    urut           INT           NOT NULL DEFAULT 0,
    kode_barang    VARCHAR(50)   NOT NULL COMMENT 'Material: kode material; FG: produk_fg',
    nama_barang    VARCHAR(255)      NULL,
    satuan         VARCHAR(10)   NOT NULL COMMENT 'Pack (Material) | pcs (FG)',
    stok_saat_ini  DECIMAL(15,3)     NULL COMMENT 'snapshot stok saat item ditambahkan',
    qty_subkont    DECIMAL(15,3) NOT NULL DEFAULT 0 COMMENT 'jumlah diminta',
    qty_siap_kirim DECIMAL(15,3) NOT NULL DEFAULT 0 COMMENT 'di Tampungan (Siap Kirim + Terkunci di Surat Jalan)',
    qty_terkirim   DECIMAL(15,3) NOT NULL DEFAULT 0 COMMENT 'Surat Jalan Confirmed',
    keterangan     VARCHAR(300)      NULL,
    PRIMARY KEY (id),
    INDEX idx_spkdet_spk (spk_no),
    INDEX idx_spkdet_kode (kode_barang),
    CONSTRAINT fk_spkdet_spk FOREIGN KEY (spk_no) REFERENCES tr_spk_subkont_header (spk_no) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------------------------------------------
-- Gudang -- Tampungan Siap Kirim (gudang logis; akun tetap Inventory, tanpa jurnal)
-- ---------------------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS tr_subkont_tampungan (
    id                          INT           NOT NULL AUTO_INCREMENT,
    spk_no                      VARCHAR(30)   NOT NULL,
    id_spk_subkont_detail       INT           NOT NULL,
    id_warehouse_stock_coil     INT               NULL COMMENT 'Material: coil/pack di warehouse_stock_coil; FG: NULL',
    pack_code                   VARCHAR(100)      NULL COMMENT 'Material: no_coil pack; FG: NULL',
    produk_fg                   VARCHAR(50)       NULL COMMENT 'FG: kode produk (fg_stock.produk_fg); Material: NULL',
    qty                         DECIMAL(15,3) NOT NULL DEFAULT 1 COMMENT 'Pack (Material) | pcs (FG)',
    net_weight                  DECIMAL(20,3)     NULL COMMENT 'Material: berat pack (kg)',
    status                      VARCHAR(30)   NOT NULL DEFAULT 'Siap Kirim' COMMENT 'Siap Kirim | Terkunci di Surat Jalan | Terkirim',
    id_subkont_delivery_detail  INT               NULL COMMENT 'terisi saat masuk Surat Jalan (FK ditambah lewat ALTER di bawah)',
    tgl_masuk                   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    scan_by                     INT               NULL COMMENT 'id user Staf Gudang',
    PRIMARY KEY (id),
    INDEX idx_tamp_spk (spk_no),
    INDEX idx_tamp_status (status),
    INDEX idx_tamp_pack (pack_code),
    CONSTRAINT fk_tamp_spk    FOREIGN KEY (spk_no)                  REFERENCES tr_spk_subkont_header (spk_no),
    CONSTRAINT fk_tamp_spkdet FOREIGN KEY (id_spk_subkont_detail)   REFERENCES tr_spk_subkont_detail (id),
    CONSTRAINT fk_tamp_coil   FOREIGN KEY (id_warehouse_stock_coil) REFERENCES warehouse_stock_coil (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
  COMMENT='Tampungan Siap Kirim: satu baris per pack (Material) atau per lot qty (FG). Satu pack hanya boleh berada di satu baris aktif (dijaga aplikasi)';

-- ---------------------------------------------------------------------------------------------
-- Delivery -- Surat Jalan Subkont + Saldo Material In Supplier
-- ---------------------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS tr_subkont_delivery (
    kode_delivery   VARCHAR(20)   NOT NULL COMMENT 'mis. DV-SUB-001',
    no_surat_jalan  VARCHAR(50)   NOT NULL COMMENT 'teks bebas, mis. SJ/30/09/2026/Sub',
    tgl_kirim       DATE          NOT NULL,
    id_vendor       INT           NOT NULL COMMENT 'new_supplier.id; dipilih saat Surat Jalan dari seluruh Master Supplier; satu vendor per Surat Jalan',
    alamat_vendor   TEXT              NULL COMMENT 'otomatis dari new_supplier.address',
    nopol_sopir     VARCHAR(100)      NULL,
    catatan         TEXT              NULL,
    total_net_weight DECIMAL(20,3) NOT NULL DEFAULT 0 COMMENT 'jumlah net_weight (kg) barang SPK Material',
    total_qty_pcs   DECIMAL(15,3) NOT NULL DEFAULT 0 COMMENT 'jumlah pcs barang SPK FG',
    status          VARCHAR(20)   NOT NULL DEFAULT 'Draft' COMMENT 'Draft | Confirmed',
    created_by      INT               NULL,
    created_at      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    confirmed_by    INT               NULL,
    confirmed_at    DATETIME          NULL,
    PRIMARY KEY (kode_delivery),
    INDEX idx_dlv_vendor (id_vendor),
    INDEX idx_dlv_status (status),
    UNIQUE KEY uk_dlv_sj_vendor (id_vendor, no_surat_jalan),
    CONSTRAINT fk_dlv_vendor FOREIGN KEY (id_vendor) REFERENCES new_supplier (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
  COMMENT='Surat Jalan Subkont. Satu Surat Jalan boleh berisi beberapa SPK (campur Material dan FG) untuk satu vendor; spk_no ada di tr_subkont_delivery_detail. Satu SPK boleh banyak Surat Jalan';

CREATE TABLE IF NOT EXISTS tr_subkont_delivery_detail (
    id                    INT           NOT NULL AUTO_INCREMENT,
    kode_delivery         VARCHAR(20)   NOT NULL,
    spk_no                VARCHAR(30)   NOT NULL COMMENT 'SPK pemilik barang ini (satu Surat Jalan boleh beberapa SPK)',
    id_subkont_tampungan  INT           NOT NULL,
    pack_code             VARCHAR(100)      NULL,
    kode_barang           VARCHAR(50)   NOT NULL,
    nama_barang           VARCHAR(255)      NULL,
    qty                   DECIMAL(15,3) NOT NULL DEFAULT 1,
    net_weight            DECIMAL(20,3)     NULL,
    costbook              DECIMAL(18,2)     NULL COMMENT 'diambil saat Confirm (Material Rp/kg; FG Rp/pcs dari fg_stock); NULL selama Draft. DATA INTERNAL: tidak ke Portal Vendor (NFR-18)',
    nilai                 DECIMAL(20,2)     NULL COMMENT 'Material: net_weight x costbook; FG: qty x costbook; NULL selama Draft. DATA INTERNAL',
    sumber                VARCHAR(20)       NULL COMMENT 'Scan QR | Tampungan',
    keterangan            VARCHAR(300)      NULL,
    PRIMARY KEY (id),
    INDEX idx_dlvdet_dlv (kode_delivery),
    INDEX idx_dlvdet_spk (spk_no),
    UNIQUE KEY uk_dlvdet_tamp (id_subkont_tampungan),
    CONSTRAINT fk_dlvdet_dlv  FOREIGN KEY (kode_delivery)        REFERENCES tr_subkont_delivery (kode_delivery) ON DELETE CASCADE,
    CONSTRAINT fk_dlvdet_spk  FOREIGN KEY (spk_no)               REFERENCES tr_spk_subkont_header (spk_no),
    CONSTRAINT fk_dlvdet_tamp FOREIGN KEY (id_subkont_tampungan) REFERENCES tr_subkont_tampungan (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- FK kedua arah (menghindari ketergantungan melingkar saat CREATE)
ALTER TABLE tr_subkont_tampungan
    ADD CONSTRAINT fk_tamp_dlvdet FOREIGN KEY (id_subkont_delivery_detail) REFERENCES tr_subkont_delivery_detail (id) ON DELETE SET NULL;

CREATE TABLE IF NOT EXISTS subkont_in_supplier_ledger (
    id             INT           NOT NULL AUTO_INCREMENT,
    id_vendor      INT           NOT NULL,
    spk_no         VARCHAR(30)   NOT NULL,
    kode_delivery  VARCHAR(20)   NOT NULL,
    tgl_transaksi  DATE          NOT NULL,
    jenis_mutasi   VARCHAR(10)   NOT NULL COMMENT 'Kirim (Confirm Surat Jalan, menambah) | Terima (Konfirmasi Incoming, mengurangi)',
    qty            DECIMAL(15,3) NOT NULL DEFAULT 0,
    net_weight     DECIMAL(20,3)     NULL,
    nilai          DECIMAL(20,2) NOT NULL DEFAULT 0,
    saldo_nilai    DECIMAL(20,2) NOT NULL DEFAULT 0 COMMENT 'saldo berjalan per vendor + SPK',
    no_referensi   VARCHAR(50)       NULL COMMENT 'no_surat_jalan (Kirim) | no_incoming (Terima)',
    PRIMARY KEY (id),
    INDEX idx_led_vendor_spk (id_vendor, spk_no),
    INDEX idx_led_dlv (kode_delivery),
    CONSTRAINT fk_led_vendor FOREIGN KEY (id_vendor)     REFERENCES new_supplier (id),
    CONSTRAINT fk_led_spk    FOREIGN KEY (spk_no)        REFERENCES tr_spk_subkont_header (spk_no),
    CONSTRAINT fk_led_dlv    FOREIGN KEY (kode_delivery) REFERENCES tr_subkont_delivery (kode_delivery)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
  COMMENT='Ledger Saldo Material In Supplier (model kartu stok, bukan snapshot); dibaca Accounting. Hanya INSERT. Satu baris Kirim per SPK dalam Surat Jalan';

-- ---------------------------------------------------------------------------------------------
-- Portal Vendor -- akun dan Laporan Subkont (LHP)
-- ---------------------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS ms_vendor_portal_link (
    id              INT          NOT NULL AUTO_INCREMENT,
    id_vendor       INT          NOT NULL COMMENT 'new_supplier.id; satu supplier hanya boleh punya satu link Aktif (dijaga aplikasi)',
    token_link      VARCHAR(64)  NOT NULL COMMENT 'kode acak unik (minimal 32 karakter heksadesimal dari generator acak aman, mis. random_bytes); bagian dari link yang diberikan ke vendor; jangan ditulis ke log',
    status_link     VARCHAR(20)  NOT NULL DEFAULT 'Aktif' COMMENT 'Aktif | Nonaktif (Buat Ulang Link menonaktifkan link lama dan membuat baris baru)',
    created_by      INT              NULL COMMENT 'id user HFG (Purchasing) yang membuat link',
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    nonaktif_by     INT              NULL,
    nonaktif_at     DATETIME         NULL,
    last_access_at  DATETIME         NULL COMMENT 'terakhir dibuka vendor',
    PRIMARY KEY (id),
    UNIQUE KEY uk_vpl_token (token_link),
    INDEX idx_vpl_vendor (id_vendor, status_link),
    CONSTRAINT fk_vpl_vendor FOREIGN KEY (id_vendor) REFERENCES new_supplier (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
  COMMENT='Link Portal Vendor (keputusan user 2026-11-20): tanpa pendaftaran/login/password; HFG membuat link unik per supplier dari Master Supplier. Portal membatasi seluruh data pada id_vendor link yang dibuka';

CREATE TABLE IF NOT EXISTS tr_laporan_subkont (
    no_laporan             VARCHAR(20)   NOT NULL COMMENT 'mis. LHP-Sub-001',
    spk_no                 VARCHAR(30)   NOT NULL,
    kode_delivery          VARCHAR(20)   NOT NULL,
    id_vendor              INT           NOT NULL,
    no_surat_jalan_vendor  VARCHAR(50)       NULL COMMENT 'dokumen kirim vendor; teks',
    no_po                  VARCHAR(50)       NULL COMMENT 'referensi PO jasa dari vendor; HANYA teks, bukan FK (FR-99)',
    tgl_laporan            DATE              NULL,
    nett_weight_before     DECIMAL(20,3)     NULL COMMENT 'dari Surat Jalan (Material)',
    nett_weight_actual     DECIMAL(20,3)     NULL COMMENT 'hasil slitting, input vendor (Material)',
    width_after            DECIMAL(10,2)     NULL COMMENT 'mm (Material)',
    total_roll             INT               NULL,
    total_scrap            DECIMAL(20,3)     NULL,
    total_nett_production  DECIMAL(20,3)     NULL COMMENT 'nett_weight_actual + total_scrap',
    selisih_kg             DECIMAL(20,3)     NULL COMMENT 'nett_weight_before - total_nett_production',
    selisih_persen         DECIMAL(9,4)      NULL,
    status                 VARCHAR(20)   NOT NULL DEFAULT 'Draft' COMMENT 'Draft | Closed | Dibuka Kembali',
    PRIMARY KEY (no_laporan),
    INDEX idx_lap_spk (spk_no),
    INDEX idx_lap_dlv (kode_delivery),
    INDEX idx_lap_vendor (id_vendor),
    INDEX idx_lap_status (status),
    CONSTRAINT fk_lap_spk    FOREIGN KEY (spk_no)        REFERENCES tr_spk_subkont_header (spk_no),
    CONSTRAINT fk_lap_dlv    FOREIGN KEY (kode_delivery) REFERENCES tr_subkont_delivery (kode_delivery),
    CONSTRAINT fk_lap_vendor FOREIGN KEY (id_vendor)     REFERENCES new_supplier (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
  COMMENT='Laporan Subkont (LHP) dari vendor lewat Portal. Kolom nilai/costbook/biaya jasa TIDAK ada di sini (NFR-18). Kolom Material bernilai NULL untuk SPK FG';

CREATE TABLE IF NOT EXISTS tr_laporan_subkont_detail (
    id                          INT           NOT NULL AUTO_INCREMENT,
    no_laporan                  VARCHAR(20)   NOT NULL,
    id_subkont_delivery_detail  INT           NOT NULL,
    pack_code                   VARCHAR(100)      NULL,
    id_material                 VARCHAR(50)       NULL COMMENT 'kode material (kode_barang pada Surat Jalan)',
    kode_internal               VARCHAR(50)       NULL COMMENT 'kode internal material, input vendor; dasar nama baby coil {kode_internal}-BC.{n}',
    nett_weight_before          DECIMAL(20,3)     NULL,
    jumlah_coil_roll            INT               NULL COMMENT 'jumlah roll baby coil, input vendor',
    PRIMARY KEY (id),
    INDEX idx_lapdet_lap (no_laporan),
    CONSTRAINT fk_lapdet_lap    FOREIGN KEY (no_laporan)                REFERENCES tr_laporan_subkont (no_laporan) ON DELETE CASCADE,
    CONSTRAINT fk_lapdet_dlvdet FOREIGN KEY (id_subkont_delivery_detail) REFERENCES tr_subkont_delivery_detail (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS tr_laporan_subkont_baby (
    id                       INT           NOT NULL AUTO_INCREMENT,
    id_laporan_detail        INT           NOT NULL,
    babycoil_code            VARCHAR(100)  NOT NULL COMMENT '{kode_internal}-BC.{n}, n berurut per kode_internal',
    net_weight               DECIMAL(20,3) NOT NULL COMMENT 'nett_weight_actual / total jumlah roll (dibagi rata)',
    id_warehouse_stock_coil  INT               NULL COMMENT 'terisi saat Konfirmasi Incoming (masuk Gudang Produksi 2 / PRT)',
    PRIMARY KEY (id),
    UNIQUE KEY uk_baby_code (babycoil_code),
    INDEX idx_baby_det (id_laporan_detail),
    CONSTRAINT fk_baby_det  FOREIGN KEY (id_laporan_detail)       REFERENCES tr_laporan_subkont_detail (id) ON DELETE CASCADE,
    CONSTRAINT fk_baby_coil FOREIGN KEY (id_warehouse_stock_coil) REFERENCES warehouse_stock_coil (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS tr_laporan_subkont_fg (
    id                      INT           NOT NULL AUTO_INCREMENT,
    no_laporan              VARCHAR(20)   NOT NULL,
    produk_fg_asal          VARCHAR(50)   NOT NULL COMMENT 'kode FG yang dikirim (fg_stock.produk_fg); tanpa FK karena product_lvl_4.code_lv4 tidak unik',
    qty_pcs_asal            DECIMAL(15,3)     NULL,
    qty_pcs_hasil           DECIMAL(15,3)     NULL COMMENT 'input vendor; penyebut costbook FG baru per pcs',
    nama_material_tambahan  VARCHAR(150)      NULL COMMENT 'teks (mis. Pasir); bukan dari stok HFG',
    berat_tambahan_kg       DECIMAL(12,4)     NULL COMMENT 'berat pasir (material tambahan) PER PCS, kg/pcs, input vendor; Berat/Pcs Baru = fg_stock.berat_referensi + berat_tambahan_kg',
    total_berat_kg          DECIMAL(20,3)     NULL COMMENT 'total kg hasil per produk, OTOMATIS = qty_pcs_hasil x (fg_stock.berat_referensi + berat_tambahan_kg); dasar biaya jasa per Kg dan costbook FG baru',
    no_urut_subkont         INT               NULL COMMENT 'diberikan saat Konfirmasi Incoming (di portal hanya pratinjau)',
    produk_fg_baru          VARCHAR(100)      NULL COMMENT 'Nama Produk/Kode Internal Material/No. Urut Subkont; NULL sampai Konfirmasi Incoming. Lihat catatan lebar fg_stock.produk_fg',
    PRIMARY KEY (id),
    INDEX idx_lapfg_lap (no_laporan),
    INDEX idx_lapfg_asal (produk_fg_asal),
    CONSTRAINT fk_lapfg_lap FOREIGN KEY (no_laporan) REFERENCES tr_laporan_subkont (no_laporan) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS tr_laporan_subkont_revisi (
    id              INT          NOT NULL AUTO_INCREMENT,
    no_laporan      VARCHAR(20)  NOT NULL,
    asal            VARCHAR(30)  NOT NULL COMMENT 'Pengajuan Vendor | Temuan Incoming',
    alasan          TEXT         NOT NULL,
    status          VARCHAR(20)  NOT NULL COMMENT 'Pengajuan Vendor: Diajukan -> Dibuka -> Ditutup; Temuan Incoming: Open -> Ditutup',
    diajukan_oleh   VARCHAR(150)     NULL COMMENT 'nama pengaju: akun vendor (Pengajuan Vendor) atau user PPIC (Temuan Incoming)',
    diajukan_at     DATETIME         NULL,
    diputuskan_by   INT              NULL COMMENT 'id user HFG yang membuka akses edit',
    diputuskan_at   DATETIME         NULL,
    ditutup_by      VARCHAR(150)     NULL COMMENT 'vendor (Close ulang) atau PPIC (Tutup Temuan)',
    ditutup_at      DATETIME         NULL,
    PRIMARY KEY (id),
    INDEX idx_rev_lap (no_laporan),
    INDEX idx_rev_asal_status (asal, status),
    CONSTRAINT fk_rev_lap FOREIGN KEY (no_laporan) REFERENCES tr_laporan_subkont (no_laporan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
  COMMENT='Riwayat pengajuan perbaikan vendor dan Temuan Incoming; versi lama laporan tidak hilang (audit, FR-117/FR-119)';

-- ---------------------------------------------------------------------------------------------
-- Incoming Subkont (PPIC): satu Incoming = header + beberapa Laporan Subkont (satu vendor)
-- ---------------------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS tr_subkont_incoming (
    no_incoming            VARCHAR(20)  NOT NULL COMMENT 'mis. INC-Sub-001',
    id_vendor              INT          NOT NULL,
    no_surat_jalan_vendor  VARCHAR(50)  NOT NULL,
    tgl_incoming           DATE         NOT NULL,
    status                 VARCHAR(20)  NOT NULL DEFAULT 'Draft' COMMENT 'Draft | Confirmed',
    id_gl_interface        INT              NULL COMMENT 'jurnal incoming gabungan (satu per Incoming) setelah Konfirmasi',
    created_by             INT              NULL,
    created_at             DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    confirmed_by           INT              NULL,
    confirmed_at           DATETIME         NULL,
    PRIMARY KEY (no_incoming),
    INDEX idx_inc_vendor (id_vendor),
    INDEX idx_inc_status (status),
    CONSTRAINT fk_inc_vendor FOREIGN KEY (id_vendor)       REFERENCES new_supplier (id),
    CONSTRAINT fk_inc_gl     FOREIGN KEY (id_gl_interface) REFERENCES gl_interface (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
  COMMENT='Header Incoming Subkont. Incoming boleh sebagian (FR-118)';

CREATE TABLE IF NOT EXISTS tr_subkont_incoming_laporan (
    id                           INT           NOT NULL AUTO_INCREMENT,
    no_incoming                  VARCHAR(20)   NOT NULL,
    no_laporan                   VARCHAR(20)   NOT NULL,
    spk_no                       VARCHAR(30)   NOT NULL,
    no_po_jasa                   VARCHAR(50)       NULL COMMENT 'referensi PO jasa (teks), input PPIC; tidak terhubung ke SPK',
    status_cek                   VARCHAR(20)   NOT NULL DEFAULT 'Belum Dicek' COMMENT 'Material: Sesuai | Tidak Sesuai (selisih_persen <= 0,7%); FG: Sesuai (tanpa cek berat). Dihitung dari LHP, tanpa input berat di Incoming',
    nett_weight_packing_list     DECIMAL(20,3)     NULL COMMENT 'Material: nett_weight_before (Packing List) saat dicek',
    nett_weight_supplier         DECIMAL(20,3)     NULL COMMENT 'Material: dari LHP vendor = nett_weight_actual + total_scrap',
    selisih_persen               DECIMAL(9,4)      NULL COMMENT 'Material: (nett_weight_packing_list - nett_weight_supplier) / nett_weight_packing_list x 100',
    jenis_tarif_jasa             VARCHAR(10)   NOT NULL DEFAULT 'Per Kg' COMMENT 'Per Kg | Lot',
    harga_jasa_per_kg            DECIMAL(18,2)     NULL COMMENT 'jenis_tarif_jasa = Per Kg',
    nilai_jasa_lot               DECIMAL(20,2)     NULL COMMENT 'jenis_tarif_jasa = Lot',
    biaya_subkont                DECIMAL(20,2)     NULL COMMENT 'Per Kg: total weight x harga_jasa_per_kg; Lot: nilai_jasa_lot. Terisi saat Konfirmasi',
    costbook_gudang_hfg          DECIMAL(18,2)     NULL COMMENT 'Material: costbook asal dari Surat Jalan',
    nilai_material_gudang        DECIMAL(20,2)     NULL COMMENT 'Material: total weight slitting x costbook_gudang_hfg',
    nilai_material_biaya_proses  DECIMAL(20,2)     NULL COMMENT 'nilai_material_gudang + biaya_subkont',
    total_scrap_kg               DECIMAL(20,3)     NULL COMMENT 'Material; 100% ke Inventory Scrap',
    nilai_scrap                  DECIMAL(20,2)     NULL,
    selisih_kg                   DECIMAL(20,3)     NULL,
    nilai_selisih                DECIMAL(20,2)     NULL,
    costbook_baru_per_kg         DECIMAL(18,2)     NULL COMMENT 'HANYA Material (NULL untuk FG): nilai_material_biaya_proses / total weight slitting',
    costbook_baru_per_pcs        DECIMAL(18,2)     NULL COMMENT 'HANYA FG (NULL untuk Material): (nilai FG kirim + biaya jasa) / qty hasil; diteruskan ke fg_stock',
    PRIMARY KEY (id),
    UNIQUE KEY uk_incl_laporan (no_laporan),
    INDEX idx_incl_inc (no_incoming),
    INDEX idx_incl_spk (spk_no),
    CONSTRAINT fk_incl_inc FOREIGN KEY (no_incoming) REFERENCES tr_subkont_incoming (no_incoming) ON DELETE CASCADE,
    CONSTRAINT fk_incl_lap FOREIGN KEY (no_laporan)  REFERENCES tr_laporan_subkont (no_laporan),
    CONSTRAINT fk_incl_spk FOREIGN KEY (spk_no)      REFERENCES tr_spk_subkont_header (spk_no)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
  COMMENT='Satu baris per Laporan Subkont dalam sebuah Incoming (cek Packing List vs LHP + Laporan Nilai Slitting, HFG saja, tidak terlihat vendor). Satu laporan hanya boleh masuk satu Incoming (UNIQUE). Laporan yang dicatat Temuan dikeluarkan (baris dihapus)';
