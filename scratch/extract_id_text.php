<?php
// Ekstrak kandidat teks tampil berbahasa Indonesia dari file PHP/view (untuk menyusun peta terjemahan).
// Keluaran: "file<TAB>baris<TAB>teks" (teks = isi literal string / teks antar tag HTML, persis seperti di file).
// Baris yang memuat write_log( dilewati (pesan Activity Log tidak diterjemahkan).
// Pemakaian: php scratch/extract_id_text.php modul1 modul2 ...   (nama folder di application/modules)
$ID = 'yang|tidak|belum|sudah|wajib|silakan|silahkan|berhasil|gagal|simpan|hapus|tambah|ubah|pilih|tanggal|keterangan|jumlah|gudang|harus|untuk|dengan|dari|apakah|yakin|batal|kembali|tampilkan|sedang|laporan|karyawan|barang|disimpan|dihapus|diubah|terjadi|kesalahan|periksa|isian|kosong|minimal|maksimal|mohon|catatan|penerimaan|pengeluaran|permintaan|persetujuan|disetujui|ditolak|ditemukan|tersedia|data|nama|harga|bulan|tahun|baru|daftar|pemasok|akan|ada|tutup|cari|lihat|cetak|unduh|unggah|proses|diproses|mata uang|satuan|kode|nomor|alamat|kota|provinsi|kecamatan|telepon|lainnya|aktif|nonaktif|semua|pilihan|isi|masukkan|terlebih|dahulu|anda|kami|ini|itu|atau|dan|ke|di|pada|oleh|sebagai|jenis|kategori|tipe|status|ya|tidak ada|kosongkan|ulang|lagi|sukses|perhatian|peringatan|informasi|konfirmasi|berlaku|mulai|sampai|hingga|sisa|total|nilai|berat|panjang|lebar|tebal|warna|ukuran|bahan|logam|pelanggan|pemasok|rekening|bank|cabang|pajak|diskon|biaya|bayar|pembayaran|tagihan|faktur|tanpa|dengan|bila|jika|maka|karena|sehingga|agar|supaya|telah|sedang|masih|hanya|juga|lebih|kurang|sama|berbeda|baris|kolom|halaman|tombol|klik|menu|pengguna|hak akses|izin|peran|jabatan|departemen|divisi';
$strong = '/\b(yang|tidak|belum|sudah|wajib|silakan|silahkan|berhasil|gagal|simpan|hapus|tambah|ubah|pilih|tanggal|keterangan|jumlah|gudang|harus|untuk|dengan|dari|apakah|yakin|batal|kembali|tampilkan|sedang|laporan|karyawan|barang|disimpan|dihapus|diubah|terjadi|kesalahan|periksa|isian|kosong|maksimal|mohon|catatan|penerimaan|pengeluaran|permintaan|persetujuan|disetujui|ditolak|ditemukan|tersedia|nama|harga|bulan|tahun|daftar|pemasok|akan|tutup|cari|lihat|cetak|unduh|unggah|proses|diproses|satuan|kode|nomor|alamat|kota|provinsi|kecamatan|telepon|lainnya|aktif|semua|pilihan|masukkan|terlebih|dahulu|anda|atau|dan|pada|oleh|jenis|kategori|sisa|nilai|berat|panjang|lebar|tebal|bahan|logam|pelanggan|rekening|cabang|pajak|biaya|bayar|pembayaran|tagihan|tanpa|bila|jika|telah|masih|hanya|juga|lebih|kurang|baris|halaman|klik|pengguna|izin|jabatan|departemen|sukses|perhatian|konfirmasi|berlaku|sampai|hingga|ukuran|warna|ulang|lagi|ini|ke|di|ya|peringatan|informasi|negara|uang|sumber|tujuan|pengiriman|penerima|pengirim|kirim|terima|dibatalkan|setujui|tolak|selesai|menunggu|lanjut|lanjutkan|ganti|perbarui|perubahan|memuat|berkas|dokumen|lampiran|tanda|pengajuan|pengembalian|kas|kasbon|transaksi|saldo|mutasi|penyesuaian|keluar|masuk|stok|persediaan|penyusutan|depresiasi|tutup|buka|baru|lama|semua|cetak|ekspor|impor|unggah|tambahkan|hapuskan|edit data|detail|rincian|ringkasan|jumlah|subtotal|harga|satuan|isi|silahkan|diisi|dipilih|ditambahkan|diperbarui|dikirim|diterima|tersimpan|terhapus|diupdate)\b/i';
$root = dirname(__DIR__) . '/application/modules/';
$mods = array_slice($argv, 1);
$total = 0;
foreach ($mods as $mod) {
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . $mod, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $fi) {
        if ($fi->getExtension() !== 'php') continue;
        $path = $fi->getPathname(); $rel = substr($path, strlen(dirname(__DIR__)) + 1);
        if (preg_match('#/(migrations|tests|backup_old)/#', $rel)) continue;
        $lines = file($path);
        $inBlock = false;
        foreach ($lines as $no => $line) {
            $t = ltrim($line);
            // komentar PHP/JS: blok /* ... */ dan baris //, serta komentar HTML satu baris
            if ($inBlock) { if (strpos($line, '*/') !== false) $inBlock = false; continue; }
            if (preg_match('#^(/\*|\*)#', $t)) { if (strpos($t, '*/') === false && strpos($t, '/*') === 0) $inBlock = true; continue; }
            if (strpos($t, '//') === 0 || strpos($t, '#') === 0) continue;
            if (stripos($line, 'write_log(') !== false || stripos($line, 'log_message(') !== false) continue;
            $segs = [];
            if (preg_match_all('/\'((?:[^\'\\\\]|\\\\.){2,400})\'|"((?:[^"\\\\]|\\\\.){2,400})"|`([^`]{2,400})`/', $line, $m, PREG_SET_ORDER)) {
                foreach ($m as $x) { $s = $x[1] !== '' ? $x[1] : ($x[2] ?? '') ; if ($s === '' && isset($x[3])) $s = $x[3]; $segs[] = $s; }
            }
            if (preg_match_all('/>([^<>]{2,400})</', $line, $m2)) foreach ($m2[1] as $s) $segs[] = $s;
            $seen = [];
            foreach ($segs as $s) {
                $s2 = trim(preg_replace('/<\?(php|=).*?\?>/s', ' ', $s));
                if ($s2 === '' || isset($seen[$s])) continue;
                if (!preg_match('/[A-Za-z]{3,}/', $s2)) continue;
                if (!preg_match($strong, strip_tags($s2))) continue;
                // lewati yang jelas kode: nama kolom/kunci (snake_case tunggal), SQL, URL, selector
                if (preg_match('/^[a-z0-9_.\-\/]+$/', $s2)) continue;
                if (preg_match('/\b(SELECT|INSERT|UPDATE|DELETE|FROM|WHERE|JOIN|GROUP BY|ORDER BY)\b/', $s2)) continue;
                $seen[$s] = 1;
                echo $rel, "\t", $no + 1, "\t", $s, "\n"; $total++;
            }
        }
    }
}
fwrite(STDERR, "total kandidat: $total\n");
