# Pencatatan stok material — 20-11-2026

Penyesuaian atas permintaan user untuk menyelesaikan audit flow stok, tanpa menambah flow/status/tombol. Rujukan bisnis: FR-02, FR-08, NFR-07 dalam fr-nfr.md. ERD: erd-revisi.drawio, page Stock Ledger dan Stock Posting Links (017).

## Identitas dan presisi

- Gudang di-resolve dari master warehouse.kd_gudang yang aktif dan tunggal. Seed development menyiapkan PRT=3, WIP=4, HLD=5 setelah memeriksa konflik; PRO=1 dan SLI=2 tetap.
- warehouse_stock di-key oleh code_lv4 + id_gudang + kd_gudang. Identitas silang atau duplikat harus direkonsiliasi, bukan diambil baris pertama.
- Kuantitas saldo/snapshot memakai tiga desimal seperti coil; nominal/harga tetap dua desimal. Presisi target ada di migrasi 017, bukan perubahan otomatis saat aplikasi berjalan.
- Kolom baru nullable: warehouse_stock_transaction_summary.production_report_id (int); warehouse_stock_transaction_detail.id_summary (int unsigned), id_coil (int); warehouse_history.id_summary (int unsigned); fg_stock_ledger.production_report_id (int). Semua bertaut logis ke PK bernama sama pada tabel induknya (production_reports.id, summary.id, warehouse_stock_coil.id). Tidak mengarang tautan untuk data legacy.

## Posting

- Scan PRO → PRT mencatat OUT/IN dengan qty dan nilai transfer yang sama dalam satu transaksi. Nilai keluar berdasarkan costbook gudang PRO. PRT menerima nilai tersebut dan menghitung moving average saldo baru. Penarikan seluruh qty membawa seluruh sisa nilai buku (termasuk residu sen pembulatan).
- Unpack hanya memecah fisik. Total packing list baby harus sama packing list induk. net_weight menyimpan berat aktual, qty baby menyimpan berat packing, total_nilai baby mengikuti berat packing × costbook. Penimbangan aktual tetap terpisah dan mengikuti toleransi resmi; tidak dibuat adjustment otomatis.
- Pemakaian menghabiskan coil dengan berat packing sumber yang tervalidasi, dari gudang asal sebenarnya. Costbook/HPP diambil per material + gudang saat posting, bukan lot/coil/gudang lain. Sisa WIP dan Hold menambah saldo gudang tujuan dengan moving average masing-masing.
- Setiap mutasi buku mempunyai summary, detail ber-ID coil, history, snapshot buku/coil, dan audit diff. Peristiwa pemecahan dengan saldo tetap ditandai Saldo Tetap di tampilan, bukan IN.
- Snapshot menunjukkan waktu posting aktual. Tanggal dokumen disimpan pada tanggal transaksi; backdate tidak menempelkan saldo hari ini ke snapshot masa lalu.

## Edit final tetap pada flow yang sama

- Posting milik production_report_id dipulihkan dalam transaksi save, lalu di-post ulang dari input terbaru, termasuk FG/Scrap/WIP/Hold/kartu dan jurnal GL pending. Sebelum penghapusan baris posting milik laporan, data lama disimpan dalam payload audit.
- Posting yang sudah mempunyai mutasi lanjutan, hasil coil yang telah dipakai, keputusan claim lanjutan, atau jurnal akuntansi posted tidak ditimpa. Save gagal dan seluruh perubahan dibatalkan. Ini validasi dependensi, bukan status/workflow baru.
- Laporan final legacy tanpa ID posting harus direkonsiliasi lebih dahulu; sistem tidak menebak baris berdasarkan SPK saja. Final yang dikembalikan ke Draft melepaskan posting sebelumnya pada transaksi yang sama.

## Data lama

Migrasi 017 tidak membetulkan saldo historis atau menebak foreign key. Query rekonsiliasi baca-saja disediakan terpisah. Preview selisih dan referensi sumber harus ditinjau sebelum mengeksekusi koreksi data.
