# Laporan Security Audit

- **Tanggal:** 10-11-2026
- **Cakupan:** `application/` (CodeIgniter 3, HMVC)
- **Metode:** Pemindaian statis berbasis pola (grep) dan pembacaan kode pada titik yang mencurigakan.
- **Batasan:** Aplikasi tidak dijalankan dan tidak ada uji exploit. `assets/`, `uploads/`, vendor, dan `.htaccess` tidak diaudit mendalam. Jumlah titik rawan pada temuan 1 dan 2 kemungkinan lebih banyak dari yang tercantum. View belum diperiksa satu per satu untuk XSS.

## Ringkasan

| No | Temuan | Tingkat | Status |
|----|--------|---------|--------|
| 1 | SQL Injection (input langsung ke SQL mentah) | Tinggi | Terbuka |
| 2 | Upload file tanpa batasan tipe (potensi RCE) | Tinggi | Terbuka |
| 3 | Kredensial database hardcoded | Tinggi | Terbuka |
| 4 | CSRF protection nonaktif | Sedang | Terbuka |
| 5 | Environment default `development` | Sedang | Terbuka |
| 6 | Cookie/session tanpa flag `secure` dan `httponly` | Sedang | Terbuka |
| 7 | `encryption_key` kosong | Sedang | Terbuka |
| 8 | XSS filter global mati, akses `$_GET`/`$_POST` langsung | Sedang | Perlu verifikasi |

## Temuan Tinggi

### 1. SQL Injection

Parameter `$_GET` dimasukkan ke SQL mentah tanpa escaping atau binding. Endpoint ini memerlukan login (`Admin_Controller` mengarahkan ke `login`), tetapi user berhak rendah tetap bisa membaca atau mengubah seluruh database.

| Lokasi | Pola |
|--------|------|
| `modules/purchase_order/controllers/Purchase_order.php:602-604` | `WHERE suplier = '$id_suplier'` dari `$_GET['id_suplier']` |
| `modules/purchase_request/controllers/Purchase_request.php:433-435`, `443-445` | `WHERE id_category3 = '$id_category3'` dari `$_GET['idmaterial']` |
| `modules/purchase_order/controllers/Purchase_request.php:293-295`, `303-305` | Pola yang sama |
| `modules/{asset_planning,incoming_asset,incoming_check,incoming_departemen}/models/Master_model.php` (`get_select_detail*`) | `WHERE id_category = '$type'` (asal `$type` belum diverifikasi) |

**Rekomendasi:** Gunakan `$this->db->get_where()` atau query binding (`->query($sql, [$id])`). Cast ke `(int)` untuk nilai numerik. Ambil input lewat `$this->input->get()`.

### 2. Upload file tanpa batasan tipe

- `modules/expense/controllers/Expense.php:176-220`: `move_uploaded_file` memakai ekstensi asli dari user tanpa whitelist, batas ukuran, atau cek MIME. File juga disalin ke `./assets/expense/` yang kemungkinan dapat diakses web. Folder dibuat dengan mode `0777`.
- `Expense.php:1597`, `modules/non_rutin/controllers/Non_rutin.php:210` dan `:691`: `allowed_types = '*'`.

Dampak: user yang login dapat mengunggah file `.php` lalu menjalankannya lewat URL.

**Rekomendasi:**
- Terapkan whitelist ekstensi (mis. `pdf|jpg|jpeg|png|xlsx|xls|doc|docx`) dan `max_size`.
- Simpan di luar webroot, atau blokir eksekusi PHP di folder upload lewat `.htaccess`.
- Ubah mode folder ke `0755`.
- Contoh yang sudah benar: `modules/new_ros/controllers/New_ros.php` (whitelist, `max_size`, `encrypt_name`).

### 3. Kredensial database hardcoded

- `application/config/database.php` berisi username dan password DB dalam teks biasa (baris 80-81), serta kredensial `root` untuk grup lain (baris 103-104).
- Direktori `application/config` ada di `.gitignore` dan `database.php` tidak ter-commit.

**Rekomendasi:** Rotasi password DB karena sudah terlihat di luar konfigurasi. Pindahkan kredensial ke environment variable. Jangan gunakan akun `root` untuk aplikasi.

## Temuan Sedang

### 4. CSRF protection nonaktif
`config.php:471`: `csrf_protection = FALSE`. Seluruh form POST (approval, pembayaran, hapus user) rentan CSRF.
**Rekomendasi:** Aktifkan, lalu sertakan token CSRF di form dan permintaan AJAX.

### 5. Environment default `development`
`index.php:58`: bila `CI_ENV` tidak diset, `display_errors` menyala dan detail error, path, serta query tampil ke user.
**Rekomendasi:** Ubah default ke `production`, atau pastikan server produksi men-set `CI_ENV=production`.

### 6. Cookie/session kurang aman
`config.php:426-427`: `cookie_secure = FALSE` dan `cookie_httponly = FALSE`. Cookie sesi dapat dibaca JavaScript dan dapat terkirim lewat HTTP.
**Rekomendasi:** Set keduanya `TRUE` bila situs memakai HTTPS. Sesi sudah memakai driver database (`ci_sessions`).

### 7. `encryption_key` kosong
`config.php:338`. Fitur enkripsi CI (bila dipakai) tidak aman.
**Rekomendasi:** Isi dengan key acak yang kuat dan simpan di luar repositori.

### 8. XSS filter global mati dan input langsung
`global_xss_filtering = FALSE`, dan sebagian kode memakai `$_GET`/`$_POST` langsung (mis. `Master_customers.php:970-999`, `Expense.php:286` dst.) sehingga melewati `$this->input`.
**Rekomendasi:** Pastikan output di view memakai `html_escape()`/`htmlspecialchars()`. Lakukan audit view terpisah.

## Temuan Rendah / Informasi

- **Autentikasi baik:** memakai `password_hash`/`password_verify` (bcrypt), pesan error login generik (anti user enumeration), dan pencatatan log. Belum terlihat rate limiting atau lockout brute-force. Disarankan ditambahkan.
- `third_party/MX/Loader.php:333` memakai `eval` pada view. Ini kode bawaan HMVC dan hanya aktif bila `short_open_tag` mati. Risiko rendah.
- Tidak ditemukan `exec`, `shell_exec`, `system`, `passthru`, `popen`, `proc_open`, atau `unserialize` di kode aplikasi (di luar `third_party`).

## Prioritas Perbaikan

1. Perbaiki SQL injection pada `AddMaterial`, `CariBentuk`, `CariIdBentuk`, dan `Master_model::get_select_detail*`.
2. Terapkan whitelist dan batas ukuran upload di Expense dan Non_rutin.
3. Aktifkan CSRF, set `CI_ENV=production`, dan atur flag cookie.
4. Rotasi password DB dan pindahkan ke environment variable.
5. Audit view untuk XSS dan tambahkan rate limiting login.
