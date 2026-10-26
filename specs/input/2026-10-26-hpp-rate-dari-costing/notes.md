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
