# Testing — database uji dan aturan DDL

Berlaku untuk pekerjaan kode (mulai FEAT-11 Claim Material, 2026-10-28).

## Database
- **Hanya `hfg_dev`** (lokal, MySQL Laragon, user `root` tanpa password, host `localhost`). Dipakai CI3 lewat `application/config/development/database.php`.
- **Jangan menyentuh `db_hfg_dev`** (database asli; ditunjuk oleh `application/config/database.php`). Sebelum menjalankan apa pun, pastikan nama database di perintah adalah `hfg_dev`.
- Klien MySQL: `C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysql.exe -uroot hfg_dev`.

## Aturan DDL (keputusan user 2026-10-28)
- **Tabel baru atau kolom baru: cukup tulis query-nya** sebagai file migrasi di `application/modules/<modul>/migrations/NNN_*.sql` (+ `.down.sql`). **Jangan dieksekusi**; user yang menjalankan.
- Pengecualian satu kali yang diizinkan: kolom `kulit`, `clamp_ring`, `status_proses` pada `warehouse_stock_coil` di `hfg_dev` (agar sama dengan database asli). Tidak ada migrasi di repo untuk kolom itu.
- Jangan menjalankan migrasi lama yang berisi `DROP TABLE` (mis. `unpack_coil/001`) terhadap database mana pun tanpa izin eksplisit.

## Pengujian
- Belum ada infrastruktur uji PHP (tidak ada phpunit/CI3 test). Verifikasi dilakukan dengan skrip SQL baca-saja dan walkthrough manual di aplikasi (`hfg_dev`).
- Cek kesesuaian nama kolom migrasi dengan ERD: bandingkan kolom `CREATE TABLE` dengan entitas di `docs/diagrams/erd-revisi.drawio` (grup "Claim Material").
- Data contoh: 268 coil di `hfg_dev` punya rantai lengkap `warehouse_stock_coil.no_ros` → `tr_ros_header.id` → `no_po`/`id_supplier` → `tr_purchase_order` (mis. coil `26011114A` → PO-001/MP/01/2026).
