# Dokumen Requirements

## Pendahuluan

Modul **Produksi, FG (Finished Goods), dan Delivery** adalah lanjutan dari sistem inventory gudang coil/roll yang sudah ada di aplikasi PT HFG. Modul ini mencakup seluruh alur dari perencanaan produksi, penerbitan SPK, issue material, timbang awal coil, pencatatan hasil produksi, penerimaan FG ke gudang, hingga pengiriman ke pelanggan beserta kontrol selisih berat.

Fokus utama sistem adalah: traceability coil end-to-end, akurasi timbang, pengendalian loss produksi, pengukuran kinerja supplier, dan kontrol selisih berat saat delivery.

---

## Glosarium

- **Sistem**: Aplikasi PT HFG berbasis CodeIgniter HMVC
- **PPIC**: Production Planning and Inventory Control — peran yang merencanakan produksi
- **SPK**: Surat Perintah Kerja — dokumen resmi perintah produksi
- **Coil**: Gulungan material baja/logam sebagai bahan baku, diidentifikasi dengan barcode unik
- **Tong_Coil**: Wadah/drum tempat coil diletakkan, memiliki berat tara sendiri
- **Gross_Weight**: Berat kotor coil termasuk semua komponen pembungkus
- **Net_Weight**: Berat bersih coil setelah dikurangi komponen tara
- **Packing_List**: Dokumen berat dari supplier yang menjadi referensi berat coil
- **FG**: Finished Goods — barang jadi hasil produksi
- **KW2**: Barang jadi kualitas kedua (second quality)
- **NG**: Not Good — produk yang tidak memenuhi standar kualitas
- **Plat_BS**: Besi/baja sisa (scrap) dari proses produksi
- **Berat_Referensi_FG**: Berat rata-rata tertimbang per unit FG yang digunakan sebagai acuan pengiriman
- **Delivery_Order**: Dokumen pengiriman barang ke pelanggan
- **Surat_Jalan**: Dokumen fisik yang menyertai pengiriman, hanya bisa dicetak setelah disetujui
- **Yield**: Persentase output FG terhadap total input material
- **Toleransi_Timbang**: Batas selisih berat yang diizinkan tanpa memerlukan approval
- **Approval_Exception**: Proses persetujuan untuk kondisi di luar toleransi normal
- **Self_Approval**: Kondisi di mana pembuat dokumen menyetujui dokumennya sendiri — tidak diizinkan
- **Supplier_Performance**: Rekap data kualitas material per supplier berdasarkan data produksi
- **Issue_Material**: Proses pengeluaran coil dari gudang material ke area produksi
- **Production_Plan**: Dokumen rencana produksi yang dibuat PPIC
- **Berat_Acuan**: Berat standar FG yang ditetapkan sebagai referensi deviasi
- **Operator_Produksi**: Peran yang melakukan timbang dan input laporan produksi
- **Warehouse_FG**: Peran yang mengelola gudang barang jadi
- **Delivery_Admin**: Peran yang membuat dan mengelola dokumen pengiriman

---

## Requirements

### Requirement 1: Perencanaan Produksi

**User Story:** Sebagai PPIC, saya ingin membuat rencana produksi berdasarkan produk FG yang dibutuhkan, sehingga saya dapat mengalokasikan coil yang tepat dan memperkirakan output produksi sebelum menerbitkan SPK.

#### Acceptance Criteria

1. WHEN PPIC memilih produk FG pada form rencana produksi, THE Sistem SHALL menampilkan daftar coil yang tersedia di gudang material dengan status `available` beserta estimasi output FG berdasarkan berat coil.
2. THE Sistem SHALL mengizinkan PPIC untuk mengalokasikan satu atau lebih coil ke satu Production_Plan.
3. WHEN PPIC menyimpan rencana produksi, THE Sistem SHALL menetapkan status Production_Plan menjadi `Draft`.
4. WHEN PPIC melakukan Release pada Production_Plan berstatus `Draft`, THE Sistem SHALL mengubah status menjadi `Released` dan secara otomatis membuat dokumen Request Material serta Draft SPK.
5. WHILE Production_Plan berstatus `Released` dan belum ada SPK yang dibuat, THE Sistem SHALL mengizinkan PPIC untuk membatalkan rencana dengan mengubah status menjadi `Cancelled`.
6. WHEN semua SPK yang terkait dengan Production_Plan telah berstatus `Closed`, THE Sistem SHALL mengubah status Production_Plan menjadi `Closed`.
7. THE Sistem SHALL menampilkan halaman Monitoring Plan yang menampilkan seluruh Production_Plan beserta status terkini dan progres coil yang dialokasikan.

---

### Requirement 2: SPK dan Issue Material

**User Story:** Sebagai PPIC dan Warehouse Material, saya ingin menerbitkan SPK dan melakukan issue material melalui scan barcode coil, sehingga coil yang tepat dipindahkan ke area produksi sesuai perintah kerja.

#### Acceptance Criteria

1. WHEN Production_Plan berstatus `Released`, THE Sistem SHALL mengizinkan PPIC untuk men-generate SPK dengan status awal `Draft`.
2. WHEN PPIC merilis SPK berstatus `Draft`, THE Sistem SHALL mengubah status SPK menjadi `Released`.
3. WHEN Warehouse Material melakukan scan barcode coil pada proses Issue Material, THE Sistem SHALL memvalidasi bahwa barcode coil tersebut terdaftar dalam SPK yang berstatus `Released` dan status coil adalah `available` di gudang material.
4. IF barcode coil yang discan tidak terdaftar dalam SPK aktif atau status coil bukan `available`, THEN THE Sistem SHALL menolak scan dan menampilkan pesan kesalahan yang menjelaskan alasan penolakan.
5. WHEN scan barcode coil berhasil divalidasi, THE Sistem SHALL mencatat mutasi lokasi coil dari gudang material ke gudang produksi tanpa membuat jurnal akuntansi.
6. WHEN semua coil dalam SPK telah di-scan dan dimutasi, THE Sistem SHALL mengubah status SPK menjadi `In Process`.
7. THE Sistem SHALL memastikan setiap barcode coil bersifat unik dan hanya dapat dikaitkan dengan satu identitas coil aktif pada satu waktu.
8. THE Sistem SHALL menyediakan halaman Monitoring Coil in Production yang menampilkan posisi dan status terkini setiap coil yang sedang dalam proses produksi.
9. THE Sistem SHALL menyediakan halaman Histori Coil yang menampilkan seluruh riwayat mutasi lokasi setiap coil dari penerimaan hingga selesai produksi.

---

### Requirement 3: Timbang Awal (Check Before Production)

**User Story:** Sebagai Operator Produksi, saya ingin melakukan timbang awal coil sebelum produksi dimulai dan membandingkannya dengan packing list supplier, sehingga selisih berat dapat dideteksi sejak awal dan menjadi dasar perhitungan loss produksi yang akurat.

#### Acceptance Criteria

1. WHEN Operator_Produksi melakukan scan barcode coil pada form Timbang Awal, THE Sistem SHALL memvalidasi bahwa coil tersebut terdaftar dalam SPK berstatus `In Process` dan berlokasi di gudang produksi.
2. THE Sistem SHALL mengizinkan Operator_Produksi untuk menginput komponen berat berikut: berat kulit (packaging luar), berat clamp/ring, berat coil beserta tong, dan berat cover wrapping.
3. WHEN semua komponen berat diinput, THE Sistem SHALL menghitung Net_Weight timbang awal secara otomatis menggunakan rumus: `Net_Weight = (berat_coil + berat_tong_coil) + berat_cover_wrapping`.
4. WHEN Net_Weight timbang awal dihitung, THE Sistem SHALL membandingkan hasilnya dengan berat pada Packing_List supplier dan menampilkan nilai selisih beserta persentase deviasi.
5. IF selisih antara Net_Weight timbang awal dan berat Packing_List melebihi Toleransi_Timbang yang dikonfigurasi, THEN THE Sistem SHALL menandai record sebagai exception dan mengirimkan notifikasi kepada QC/Supervisor Produksi.
6. THE Sistem SHALL menyimpan semua data komponen berat beserta hasil perhitungan Net_Weight ke tabel `tr_coil_preweigh` dan `tr_coil_preweigh_component`.
7. THE Sistem SHALL menampilkan halaman perbandingan timbang awal yang merangkum selisih berat semua coil dalam satu SPK.

---

### Requirement 4: Laporan Produksi

**User Story:** Sebagai Operator Produksi, saya ingin menginput seluruh hasil produksi dalam satuan kilogram setelah proses selesai, sehingga sistem dapat menghitung yield, loss, dan data kinerja supplier secara otomatis.

#### Acceptance Criteria

1. WHEN Operator_Produksi membuka form Laporan Produksi untuk SPK berstatus `In Process`, THE Sistem SHALL menampilkan form input hasil produksi dengan kategori: reject material supplier (kg), waste potong pisau (kg), NG internal (kg), NG supplier (kg), plat BS (kg), FG beserta qty (kg), KW2 internal beserta qty (kg), KW2 supplier beserta qty (kg), dan berat tong coil (kg).
2. WHEN semua data hasil produksi diinput, THE Sistem SHALL menghitung total berat coil laporan produksi secara otomatis menggunakan rumus: `Total_Berat_Coil = reject_supplier + waste_potong_pisau + NG_internal + NG_supplier + plat_BS + FG + KW2_internal + KW2_supplier`.
3. WHEN total berat coil dihitung, THE Sistem SHALL menghitung Net_Hasil_Produksi menggunakan rumus: `Net_Hasil_Produksi = Total_Berat_Coil + berat_tong_coil + berat_cover_wrapping`.
4. THE Sistem SHALL menghitung persentase yield untuk setiap kategori output terhadap total berat coil dan menampilkannya pada form review.
5. WHEN Operator_Produksi melakukan Submit laporan produksi, THE Sistem SHALL mengubah status laporan menjadi `Submitted` dan mengirimkan notifikasi kepada QC/Supervisor Produksi untuk review.
6. WHEN QC/Supervisor Produksi menyetujui laporan produksi berstatus `Submitted`, THE Sistem SHALL mengubah status menjadi `Approved`.
7. WHEN QC/Supervisor Produksi menolak laporan produksi berstatus `Submitted`, THE Sistem SHALL mengubah status menjadi `Rejected` dan mengizinkan Operator_Produksi untuk melakukan revisi.
8. WHEN laporan produksi berstatus `Approved` diposting, THE Sistem SHALL mengubah status menjadi `Posted to FG`, mengubah status SPK menjadi `Submitted`, dan memindahkan data FG ke gudang FG.
9. WHEN laporan produksi diposting, THE Sistem SHALL secara otomatis mencatat data reject supplier dan selisih berat ke tabel `tr_supplier_perf_feed` sebagai feed untuk Supplier Performance.

---

### Requirement 5: Validasi Berat Satuan FG

**User Story:** Sebagai QC/Supervisor Produksi, saya ingin sistem mendeteksi otomatis apabila berat satuan FG menyimpang dari standar, sehingga produk yang tidak sesuai spesifikasi dapat ditahan sebelum masuk ke gudang FG.

#### Acceptance Criteria

1. WHEN berat satuan FG dihitung dari laporan produksi, THE Sistem SHALL membandingkan nilai tersebut dengan Berat_Acuan standar FG yang tersimpan di master data.
2. THE Sistem SHALL menghitung berat satuan FG menggunakan dua metode: Metode 1 = `total_berat_FG / qty_FG`; Metode 2 = `berat_satuan_standar × qty_FG`.
3. IF deviasi berat satuan FG melebihi ±5% dari Berat_Acuan standar, menggunakan rumus `|berat_satuan_aktual - berat_standar| / berat_standar > 0.05`, THEN THE Sistem SHALL menampilkan notifikasi deviasi dan menahan proses posting laporan produksi hingga ada konfirmasi dari QC/Supervisor Produksi.
4. WHEN QC/Supervisor Produksi memberikan konfirmasi override pada laporan yang tertahan karena deviasi berat, THE Sistem SHALL mencatat alasan konfirmasi dan mengizinkan proses posting dilanjutkan.

---

### Requirement 6: Gudang FG — Penerimaan dan Stok

**User Story:** Sebagai Warehouse FG, saya ingin mencatat penerimaan FG dari produksi dan mengelola stok beserta berat referensi rata-rata, sehingga data stok FG selalu akurat dan dapat digunakan sebagai dasar estimasi berat pengiriman.

#### Acceptance Criteria

1. WHEN laporan produksi berstatus `Posted to FG`, THE Sistem SHALL secara otomatis membuat dokumen FG Receipt berstatus `Draft` yang berisi data FG dan KW2 dari laporan produksi.
2. WHEN Warehouse_FG memposting FG Receipt berstatus `Draft`, THE Sistem SHALL mengubah status menjadi `Posted` dan menambahkan qty serta berat FG ke tabel stok FG (`fg_stock`).
3. WHEN FG Receipt diposting, THE Sistem SHALL mencatat setiap transaksi ke kartu stok FG (`fg_stock_ledger`) dengan informasi: tanggal, nomor referensi SPK, qty masuk, berat masuk, dan saldo akhir.
4. WHEN stok FG diperbarui, THE Sistem SHALL menghitung ulang Berat_Referensi_FG menggunakan rumus rata-rata tertimbang: `Berat_Referensi_FG = total_berat_stok_FG / total_qty_stok_FG` dan menyimpan riwayat perubahan ke tabel `ms_fg_weight_history`.
5. WHEN Warehouse_FG membatalkan FG Receipt berstatus `Posted`, THE Sistem SHALL mengubah status menjadi `Cancelled` dan membalik (reverse) mutasi stok FG yang terkait.
6. THE Sistem SHALL menampilkan halaman Stok FG yang menampilkan qty dan berat stok terkini per produk FG beserta Berat_Referensi_FG yang berlaku.
7. THE Sistem SHALL menampilkan Kartu Stok FG yang menampilkan seluruh riwayat mutasi masuk dan keluar per produk FG dalam rentang tanggal yang dipilih.

---

### Requirement 7: Delivery Order dan Timbang Aktual

**User Story:** Sebagai Delivery Admin, saya ingin membuat delivery order berdasarkan stok FG yang tersedia dan mendapatkan estimasi berat otomatis, sehingga proses pengiriman dapat direncanakan dengan akurat dan selisih berat dapat dikontrol.

#### Acceptance Criteria

1. WHEN Delivery_Admin membuat Delivery Order baru, THE Sistem SHALL menampilkan daftar produk FG dengan stok available beserta Berat_Referensi_FG terkini.
2. WHEN Delivery_Admin menginput qty kirim untuk suatu produk FG, THE Sistem SHALL menghitung estimasi berat pengiriman secara otomatis menggunakan rumus: `Estimasi_Berat = qty_kirim × Berat_Referensi_FG`.
3. IF qty kirim yang diinput melebihi stok FG yang available, THEN THE Sistem SHALL menolak input dan menampilkan pesan kesalahan beserta informasi stok yang tersedia.
4. WHEN Delivery_Admin menyimpan Delivery Order, THE Sistem SHALL menetapkan status menjadi `Draft`.
5. WHEN Delivery_Admin menginput berat timbang aktual pengiriman pada Delivery Order berstatus `Draft`, THE Sistem SHALL menghitung selisih antara berat aktual dan estimasi berat serta menampilkan persentase deviasi.
6. IF selisih antara berat timbang aktual dan estimasi berat melebihi Toleransi_Timbang yang dikonfigurasi, THEN THE Sistem SHALL mengubah status Delivery Order menjadi `Waiting Approval` dan mencegah pencetakan Surat_Jalan.
7. IF selisih berat berada dalam Toleransi_Timbang, THEN THE Sistem SHALL mengizinkan Delivery Order langsung berstatus `Approved Exception` dan Surat_Jalan dapat dicetak.
8. WHILE status Delivery Order adalah `Waiting Approval`, THE Sistem SHALL mencegah pencetakan Surat_Jalan.
9. THE Sistem SHALL mencatat semua data timbang aktual ke tabel `tr_delivery_weight_log`.

---

### Requirement 8: Approval Exception Delivery

**User Story:** Sebagai Manager Gudang atau Manager Produksi, saya ingin menyetujui atau menolak delivery yang memiliki selisih berat di luar toleransi, sehingga setiap pengiriman yang tidak normal tetap terkontrol dan terdokumentasi.

#### Acceptance Criteria

1. WHEN Delivery Order berstatus `Waiting Approval`, THE Sistem SHALL mengirimkan notifikasi kepada Manager Gudang dan Manager Produksi untuk melakukan review.
2. WHEN Manager menyetujui Delivery Order berstatus `Waiting Approval`, THE Sistem SHALL mengubah status menjadi `Approved Exception` dan mengizinkan pencetakan Surat_Jalan.
3. WHEN Manager menolak Delivery Order berstatus `Waiting Approval`, THE Sistem SHALL mengubah status menjadi `Draft` dan mengizinkan Delivery_Admin untuk merevisi data timbang.
4. THE Sistem SHALL mencegah Self_Approval, yaitu kondisi di mana pengguna yang membuat Delivery Order adalah pengguna yang sama yang melakukan approval.
5. WHEN Delivery Order berstatus `Approved Exception` atau telah disetujui, THE Sistem SHALL mengizinkan pencetakan Surat_Jalan.
6. WHEN Surat_Jalan dicetak dan pengiriman dikonfirmasi, THE Sistem SHALL mengubah status Delivery Order menjadi `Shipped` dan mengurangi stok FG sesuai qty yang dikirim.
7. WHEN Delivery_Admin membatalkan Delivery Order sebelum berstatus `Shipped`, THE Sistem SHALL mengubah status menjadi `Cancelled` dan membebaskan stok FG yang sebelumnya direservasi.
8. THE Sistem SHALL mencatat seluruh riwayat approval beserta alasan dan timestamp ke tabel `tr_delivery_approval`.

---

### Requirement 9: Supplier Performance

**User Story:** Sebagai Manager Produksi, saya ingin melihat data kinerja supplier berdasarkan data reject dan selisih berat per coil dari setiap proses produksi, sehingga evaluasi supplier dapat dilakukan secara objektif dan berbasis data.

#### Acceptance Criteria

1. WHEN laporan produksi diposting, THE Sistem SHALL secara otomatis mencatat data berikut ke `tr_supplier_perf_feed` per coil: berat reject supplier (kg), berat NG supplier (kg), berat KW2 supplier (kg), dan selisih berat timbang awal vs packing list.
2. THE Sistem SHALL menampilkan halaman Feed per Coil yang menampilkan detail data kinerja per coil per supplier.
3. THE Sistem SHALL menampilkan halaman Summary Periodik yang merangkum total reject, NG, KW2, dan selisih berat per supplier dalam rentang periode yang dipilih.
4. THE Sistem SHALL menampilkan Dashboard Supplier yang menampilkan perbandingan kinerja antar supplier secara visual.

---

### Requirement 10: Dashboard dan Laporan Analitis

**User Story:** Sebagai Manager Produksi dan Manager Gudang, saya ingin melihat dashboard dan laporan analitis yang merangkum data produksi, stok FG, dan pengiriman, sehingga pengambilan keputusan dapat dilakukan berdasarkan data yang akurat dan terkini.

#### Acceptance Criteria

1. THE Sistem SHALL menampilkan laporan Perbandingan Timbang Awal yang membandingkan Net_Weight timbang awal dengan berat Packing_List per coil per SPK.
2. THE Sistem SHALL menampilkan Laporan Hasil Produksi per SPK yang mencakup: total input material, breakdown output per kategori (FG, KW2, reject, waste, NG, plat BS), dan persentase yield.
3. THE Sistem SHALL menampilkan Delivery Discrepancy Report yang merangkum selisih antara estimasi berat dan berat aktual per Delivery Order dalam rentang periode yang dipilih.
4. THE Sistem SHALL menampilkan laporan Berat Standar vs Aktual FG yang membandingkan Berat_Acuan standar dengan berat aktual FG yang diterima dari produksi per periode.

---

### Requirement 11: Parser dan Serialisasi Data Barcode Coil

**User Story:** Sebagai sistem, saya ingin memastikan data barcode coil dapat dibaca, divalidasi, dan direpresentasikan kembali secara konsisten, sehingga tidak ada kesalahan identifikasi coil dalam seluruh alur transaksi.

#### Acceptance Criteria

1. WHEN barcode coil discan, THE Sistem SHALL mem-parse string barcode menjadi objek identitas coil yang berisi: kode coil, nomor heat, dan kode supplier.
2. IF format barcode tidak sesuai dengan pola yang terdefinisi, THEN THE Sistem SHALL mengembalikan pesan kesalahan deskriptif yang menyebutkan format yang diharapkan.
3. THE Sistem SHALL memformat (pretty-print) objek identitas coil kembali menjadi string barcode yang valid sesuai format standar.
4. FOR ALL objek identitas coil yang valid, proses parse kemudian format kemudian parse ulang SHALL menghasilkan objek yang ekuivalen dengan objek awal (round-trip property).

---

## Prioritas Implementasi

| Phase | Cakupan |
|-------|---------|
| Phase 1 | Production Planning, SPK, Issue Material, Timbang Awal |
| Phase 2 | Laporan Produksi, Validasi Berat FG, Supplier Performance Feed |
| Phase 3 | FG Receipt, Berat Referensi FG, Stok FG |
| Phase 4 | Delivery Order, Timbang Aktual, Approval Exception, Surat Jalan |
| Phase 5 | Dashboard Analitis, Supplier Performance Summary, Integrasi Final |
