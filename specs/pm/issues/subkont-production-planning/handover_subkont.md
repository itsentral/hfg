# Handover ke Programmer — Proses Subkont (FEAT-12)

Status BA: **siap dikerjakan** (dokumen, ERD, SQL migrasi, mockup, plan dan task selesai; 2026-11-20). Semua jalur relatif terhadap root repo `hfg/`.

## 1. Mulai dari sini
| Urutan | File | Isi |
|---|---|---|
| 1 | `specs/pm/issues/subkont-production-planning/plan_subkont_20-11-2026.md` | Rencana implementasi 9 tahap, fondasi modul yang dipakai ulang, syarat NFR-18, contoh angka uji, titik keputusan |
| 2 | `specs/pm/issues/subkont-production-planning/task_subkont_20-11-2026.md` | Daftar centang pekerjaan per tahap |
| 3 | `specs/pm/issues/subkont-production-planning/walkthrough_mockup_subkont_20-11-2026.md` | Rekap mockup, keputusan user, asumsi, dan hasil uji |

Salinan di folder ini berasal dari `.artifacts/subkont/` dan `.artifacts/mockup_subkont/` (folder `.artifacts/` tidak ikut git).

## 2. Sumber kebenaran (jangan mengarang di luar ini)
| Apa | Lokasi |
|---|---|
| Kebutuhan fungsional dan non-fungsional | `specs/docs/fr-nfr.md`, bagian "Proses Subkont": **FR-99–FR-125, NFR-18–NFR-21, OQ-S1–OQ-S12** |
| ERD (14 tabel baru) | `specs/docs/diagrams/erd-revisi.drawio`, halaman **"Subkont ERD"** (halaman ke-4) |
| SQL migrasi (ditulis, belum dijalankan) | `application/modules/subkont/migrations/001_create_subkont_tables.sql` dan `.down.sql` |
| Use case, halaman, entitas | `specs/docs/workbook.xlsx`: sheet UC BPMN (UC-17–UC-27), UC Non-BPMN (UC-N37–UC-N39), Pages (PG-37–PG-51), Entities |
| Aturan proyek | `AGENTS.md`, `specs/context/coder-only/testing.md` (database uji **hanya `hfg_dev`**; DDL hanya ditulis, dijalankan user) |

## 3. Mockup UI (buka langsung di browser)
Folder `specs/ui-mockup/`. Data contoh disimpan di browser; tombol **Reset ke Data Contoh** ada di tiap halaman.

| Halaman | File |
|---|---|
| SPK Subkont | `sk-spk.html` |
| Tampungan Siap Kirim | `sk-tampungan.html` |
| Surat Jalan Subkont (beberapa SPK, campur Material dan FG, satu vendor) | `sk-surat-jalan.html` |
| Master Supplier + Link Portal Vendor | `sk-master-supplier.html` |
| Portal Vendor (dibuka lewat link: `sk-portal.html?t=<token>`; buat link dari Master Supplier) | `sk-portal.html` |
| Laporan Subkont Vendor (buka akses edit) | `sk-laporan-vendor.html` |
| Incoming Subkont + Temuan | `sk-incoming.html` |
| Laporan Nilai Slitting | `sk-nilai-slitting.html` |
| Saldo Material In Supplier | `sk-saldo-supplier.html` |
| Stok yang terpengaruh | `wh-stock-value.html` (costbook/harga rata-rata), `wh-stock-from-warehouse.html` (baby coil PRT), `wh-stock-coil.html`, `fg-stock.html` dan `fg-kartu-stok.html` |

Logika dan rumus yang sudah teruji ada di `sk-data.js`, `sk-pages.js`, `sk-sj.js`, `sk-portal.js`, `sk-hfg.js`, `sk-inc.js`; sambungan stok di `wh-data.js`, `wh-pages2.js`, `fg-data.js`. Pembantu umum: `berry-shell.js`, `wh-core.js`, `numfmt.js`.

## 4. Bahan klien dan riwayat keputusan
| Apa | Lokasi |
|---|---|
| Alur BPMN to-be, workbook klien, dan seluruh jawaban user | `specs/input/Production Planning/` (`*.png`, `*.xlsx`, `notes.md`) — hanya baca |
| Status, log keputusan, ringkasan fitur | `specs/pm/tracker.yaml` (FEAT-12), `specs/pm/log.md`, `specs/pm/issues/subkont-production-planning/plan.md` |
| Istilah dan peta entitas | `specs/context/index/vocabulary.md`, `specs/context/index/map.md`, `specs/context/gotchas.md` |

## 5. Titik yang perlu diputuskan programmer/BA saat koding
1. `fg_stock.produk_fg` VARCHAR(50) terlalu pendek untuk kode FG baru: lebarkan atau ubah format kode.
2. Sumber nyata costbook: Material = Purchase Price (Avg) di Stock Value (`warehouse_stock_history`); FG = costbook di `fg_stock`.
3. Master Jurnal (Accounting) untuk `confirm_surat_jalan` dan `konfirmasi_incoming` belum ada (kode akun sementara, OQ-S9).
4. Penyimpanan `token_link`: apa adanya (link bisa ditampilkan ulang) atau hash (link hanya tampil saat dibuat).
5. Email/notifikasi tidak ada di dokumen; jangan dibuat tanpa keputusan BA.
6. Kode produk FG di laporan tidak ber-FK ke `product_lvl_4` (`code_lv4` tidak unik).

## 6. Catatan penting
- Banyak file di atas **belum di-commit** (folder `specs/`, `application/modules/subkont/`). Commit dan push dulu sebelum programmer mengambilnya.
- Mockup bukan kode produksi: data di browser, harga dasar dibaca dari penyimpanan browser, dan tidak ada autentikasi nyata.
