-- Migration: Menambahkan kolom `icon` pada tabel `identitas` dan mengisi aset default (`logo`, `icon`, `gambar`)
-- Tanggal: 2026-11-12

ALTER TABLE `identitas`
    ADD COLUMN `icon` VARCHAR(255) NULL DEFAULT NULL AFTER `logo`;

UPDATE `identitas`
SET
    `logo`   = COALESCE(NULLIF(TRIM(`logo`), ''), 'assets/images/logohfg.png'),
    `icon`   = COALESCE(NULLIF(TRIM(`icon`), ''), 'assets/images/iconhfg.png'),
    `gambar` = COALESCE(NULLIF(TRIM(`gambar`), ''), 'assets/img/wallpaper_harapan.jpg')
WHERE `ididentitas` = 1;

