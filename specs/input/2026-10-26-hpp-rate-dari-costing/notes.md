# 2026-10-26 — HPP memakai Rp/Kg dari Hasil Costing; Add SPK memilih varian BOM

Sumber: pesan user (chat), 2026-10-26. Kutipan inti, apa adanya:

> Untuk saat ini jika kita lihat di Konsep Mockup hpp.html, untuk Rate terhadap komposisi biaya masih mengambil dari rate.html.
> Sekarang, kita perlu sesuaikan lagi. Ketika HPP, untuk biaya Man Power, FOH, Bahan Pendukung Khusus, Consumables, Scrap ratenya
> mengambil dari Rp/Kg dari Hasil Costing di pc-simulasi.html berdasarkan BOM Produk yang dipakai.
> 1. Man Power mengambil dari Rp/Kg Man Power Cost 2. FOH mengambil dari Rp/Kg FOH Costing 3. Bahan Pendukung Khusus mengambil dari Rp/Kg
> Bahan Pendukung Khusus dari Costing 4. Consumables mengambil dari Rp/Kg Consumables dari Costing 5. Biaya Koordinasi mengambil dari
> Rp/Kg Biaya Koordinasi dari Costing 6. Scrap mengambil dari Rp/Kg Scrap dari Costing.
> Sehingga, pada saat "Add SPK" di beranda.html, setelah memilih produk, pilih juga varian BOM-nya.

Dampak: FR-48 direvisi, FR-74 baru, OQ 50–55 (asumsi), ERD `uniq_id` pada `production_report_items` / `tr_spk_material_detail`.

## Jawaban atas pertanyaan terbuka (OQ 50–55), 2026-10-26

Sumber: pesan user (chat). Kutipan apa adanya:

> 1. Sebaiknya ditolak. Karena jika sudah tercatat di Laporan Keuangan, akan repot banyak adjustment data atau input ulang.
> 2. Betul sesuai asumsi kamu.
> 3. Diizinkan apabila SPK belum diinput laporan Produksinya.
> 4. Betul, saat ini berbeda modul. Kamu bisa menyesuaikan kembali tampilan mockup nya agar sesuai ERD.
> 5. Dipensiunkan saja.

Pemetaan: 1 = OQ 50, 2 = OQ 51, 3 = OQ 53, 4 = OQ 55, 5 = OQ 54. OQ 52 belum ditanyakan saat itu; dijawab menyusul (di bawah).
Extracted: FR-74 dan OQ 50, 51, 53, 54, 55 -> `docs/fr-nfr.md`.

## Jawaban menyusul (2026-10-26)

> QQ 52 : betul, sesuai update costing terakhir.

> Ya, KW2 juga ikuti varian berdasarkan produk yang sudah dipilih di SPK

> Ya, pakai varian_type dan pastikan nama di field ERD dengan nama di kolom tampilan program itu sama yaa. Oh iya, untuk ERD yang diupdate adalah file "erd-revisi.drawio" di folder docs, subfolder diagram.

Pemetaan: OQ 52 RESOLVED; Stok Bebas dan KW 2 mengikuti varian SPK (FR-48, FR-74); kolom varian = `varian_type` (bukan `uniq_id`); label tampilan = "Varian BOM (varian_type)"; ERD yang diperbarui = `docs/diagrams/erd-revisi.drawio`.
Extracted: FR-48, FR-74, NFR-14, NFR-15 -> `docs/fr-nfr.md`; ERD -> `docs/diagrams/erd-revisi.drawio`.
