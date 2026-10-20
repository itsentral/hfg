# Project Instructions

## Integrasi Spesifikasi BA (`specs/`) & Vibe Coding Workflow

Repository ini mengintegrasikan rancangan konsep **Business Analyst (BA)** dan implementasi **Programmer**:
- **Folder `specs/` (Domain BA - Single Source of Truth):**
  Folder ini adalah sumber acuan utama seluruh konsep bisnis, requirement, dan rancangan sebelum dieksekusi ke kode:
  - `specs/docs/fr-nfr.md`: Kebutuhan fungsional (FR) dan non-fungsional (NFR), batasan formula, toleransi, dan logic bisnis.
  - `specs/docs/diagrams/`: Diagram alur proses (BPMN / Activity Diagram) dan skema database (ERD terintegrasi `erd.drawio`).
  - `specs/ui-mockup/`: Desain antarmuka dan prototipe UI yang disepakati dengan klien.
  - `specs/pm/`: Pelacakan status per fitur (`tracker.yaml`, `active.json`, capsule issue di `specs/pm/issues/`).
  - `specs/input/`: Dokumen mentah, notulensi, dan workbook dari klien (append-only / read-only).

- **Cakupan Modul di `specs/` Saat Ini:**
  1. **Modul Laporan Produksi:**
     - FR-01 s.d. FR-36 di `specs/docs/fr-nfr.md`
     - Mockup UI: `specs/ui-mockup/beranda.html`, `material.html`, `hpp.html`, `costbook.html`, `rate.html`
  2. **Modul Product Costing (Update Terkini 2026-10-07):**
     - FR-37 s.d. FR-49 di `specs/docs/fr-nfr.md` (Master Produk 4 Level, Master Material, Price Reference & Kurs USD, BOM Varian, Flow Process, Man Power Rate, Bahan Pendukung & FOH Rate, General Rate, Simulasi Costing HPP)
     - Mockup UI lengkap: `specs/ui-mockup/pc-produk.html`, `pc-material.html`, `pc-price-reference.html`, `pc-kurs.html`, `pc-bom.html`, `pc-flow.html`, `pc-man-power-rate.html`, `pc-bpk-foh.html`, `pc-general-rate.html`, `pc-simulasi.html`

### Prinsip Vibe Coding Bersama AI:
1. **Spec-Driven Development:** Programmer dan AI **TIDAK BOLEH** mengarang alur bisnis, status transaksi, rumus perhitungan, atau nama field sendiri jika fitur bersangkutan sudah/sedang didefinisikan di dalam `specs/`. Selalu rujuk ke dokumen terkait di `specs/docs/` atau `specs/pm/`.
2. **Skema Database:** Sebelum membuat query create/alter tabel database, **WAJIB** mencocokkan dengan ERD di `specs/docs/diagrams/` atau spesifikasi field di `specs/docs/fr-nfr.md`. **Nama tabel dan kolom di kode, migrasi, dan query HARUS persis sama dengan ERD** untuk data yang berasal dari database; bila berbeda, perbaiki ERD (jika ringkas/keliru) atau tabelnya, jangan membuat alias diam-diam (label tampilan di UI boleh berbeda).
3. **Mockup UI:** Gunakan `specs/ui-mockup/` sebagai acuan komponen form, tabel, filter, dan penamaan tombol sebelum membuat view CodeIgniter di `application/modules/`.
4. **Siklus Kerja Kolaborasi:**
   - **Tahap BA:** BA mematangkan konsep di `specs/` (FR/NFR, BPMN, ERD, UI Mockup).
   - **Tahap Handover ke Dev:** Programmer/AI memeriksa `specs/` dan menyusun rencana implementasi di .artifacts/[nama_fitur]/plan_[nama_fitur].md.
   - **Tahap Koding:** Implementasi kode di `application/` & `assets/` sesuai panduan stack CI3 dan standar `write_log()`.
   - **Tahap Review:** Buat .artifacts/[nama_fitur]/walkthrough_[nama_fitur].md sebagai bukti kesesuaian kode dengan spesifikasi BA.

---

## Aturan Artifact & Dokumentasi

### 1. Jangan Replace/Overwrite Artifact
- **JANGAN** menimpa file artifact yang sudah ada.
- **SELALU** buat file baru dengan nama yang sesuai tema atau request yang diberikan.
- Tujuan: agar history tetap tersimpan dan bisa di-review kembali.

### 2. Penamaan File Artifact
Gunakan nama deskriptif sesuai fitur/konteks, contoh:
- `status_user_permission.md`
- `analisis_bug_sync.md`
- `rencana_refactor_permission.md`

### 3. Penamaan Task File
File task **HARUS** diberi nama sesuai fitur yang sedang dikembangkan, contoh:
- `task_user_permission.md` (bukan generic `task.md`)
- `task_fitur_invoice.md`
- `task_bug_fix_shift.md`

### 4. Penamaan Implementation Plan
- `plan_fitur_invoice.md`
- `plan_refactor_permission.md`

### 5. Penamaan Walkthrough
- `walkthrough_fitur_invoice.md`
- `walkthrough_refactor_permission.md`

### 6. Set Dokumen Per Fitur
Setiap fitur harus punya set dokumen yang bisa di-trace:
`
plan_[nama_fitur].md → task_[nama_fitur].md → walkthrough_[nama_fitur].md
`

### 7. Penyimpanan artifact
- File artifact dibuat di folder root project dan di kumpulkan kedalam folder `.artifacts/` folder ini akan diabaikan oleh git (git ignore)
- Folder `.artifacts` akan di kelompokkan lagi berdasarkan nama fitur dan nama file folder ini akan diabaikan oleh git (git ignore)
- Contoh : `.artifacts/invoice_export/plan_invoice_export.md`, `.artifacts/proforma_invoice_export/task_proforma_invoice_export.md`, `.artifacts/walkthrough_invoice_export.md`
- **Nota**: jika fitur yang akan di kembangkan belum ada di dalam folder `.artifacts` maka buat dulu foldernya
- **Nota**: jangan ubah nama file artifact yang sudah ada
- **Nota**: selalu gunakan format tanggal `DD-MM-YYYY` untuk file artifact
- **Nota**: 


## Bahasa
- Gunakan **Bahasa Indonesia** untuk komunikasi dan dokumentasi, kecuali diminta lain.
- Kode dan komentar teknis boleh dalam Bahasa Inggris.
- Semua conversetion harus dalam Bahasa Indonesia, kecuali permintaan lain.

## Stack Teknologi
- Framework: **CodeIgniter 3** (PHP)
- Database: **MySQL**
- Frontend: **Bootstrap 4/5** + jQuery
- Pattern: **HMVC** (modules)

## Standar Audit Log & Activity Log (write_log)

Setiap modul atau fitur yang dibuat/dimodifikasi **WAJIB** menerapkan standar pencatatan log aktivitas menggunakan helper `write_log()`:

### 1. Prinsip Utama: Before & After (Diff) untuk Aksi Edit / Update
- Untuk setiap aksi **Update / Edit**, **WAJIB** mengambil snapshot data lama (`old`) dari database **SEBELUM** query update/transaksi dijalankan.
- Payload yang dikirimkan ke parameter `$data` pada `write_log()` harus menyertakan pasangan `old` dan `new`:
  `php
  $oldData = $this->db->get_where('nama_tabel', ['id' => $id])->row_array();
  // ... jalankan update ...
  $logPayload = [
      'old' => $oldData,
      'new' => array_merge($oldData, $dataUpdate)
  ];
  write_log('Nama Modul', 'Edit', 'Update data ... success: ' . $identifier, $logPayload, null, 1);
  `
- Sistem di `log_helper.php` secara otomatis membandingkan field yang berubah, mengabaikan metadata tanggal (`created_at`, `updated_at`, dll.), dan merendernya ke tabel berwarna merah (Before) vs hijau (After) di UI Activity Log.

### 2. Penanganan Multi-Tabel (Header vs Detail / Child)
- Jika modul memiliki relasi Header - Detail (contoh: PO dengan item barang `dt_trans_po`, PR dengan `pr_detail`, BOM dengan `bom_detail`):
  - Ambil snapshot detail lama sebelum detail dihapus/re-insert.
  - Bandingkan perubahan per baris item (misal: Qty, Harga Satuan, Diskon, Deskripsi).
  - Masukkan ke key `detail_changes` di dalam payload log:
    `php
    $logPayload = [
        'old'            => $oldHeader,
        'new'            => array_merge($oldHeader, $newHeader),
        'detail_changes' => $detailChanges // Array dengan format ['Item [Nama] - Field' => ['old' => ..., 'new' => ...]]
    ];
    write_log('Nama Modul', 'Edit', 'Update ...', $logPayload, null, 1);
    `

### 3. Normalisasi Angka dan NULL vs 0 (Smart Comparison)
- Log helper sudah memiliki toleransi desimal: perbedaan penulisan angka desimal (seperti `0.00` vs `0` atau `3744000000.0000` vs `3744000000`) **tidak akan memicu log perubahan palsu**.
- Khusus kolom bertipe nilai/nominal/kuantiti (misal: `diskon`, `harga`, `total`, `qty`, `ppn`, `kurs`), nilai `NULL` atau string kosong `''` yang berubah menjadi `0` (atau sebaliknya) secara otomatis **diabaikan** agar tampilan log tetap bersih dan fokus pada perubahan substansial.

### 4. Penamaan Dokumen PO (Nomor Surat)
- Pada semua modul yang berinteraksi dengan Purchase Order (`purchase_order`, `approval_po`, `purchase_order_payment`, dll.), pesan log yang ditampilkan kepada user **WAJIB menggunakan `no_surat` (nomor resmi PO)**, bukan ID internal `no_po`. Gunakan helper `get_po_no_surat($no_po)` jika diperlukan.

### 5. Pendaftaran Modul di Dropdown Filter Activity Log
- Dropdown modul pada halaman Activity Log bersifat **otomatis dan dinamis**: modul mengambil query `SELECT DISTINCT module FROM logs`. Setiap kali modul baru mencatat log pertamanya via `write_log('Nama Modul', ...)`, nama modul tersebut akan langsung muncul di filter pencarian Activity Log.