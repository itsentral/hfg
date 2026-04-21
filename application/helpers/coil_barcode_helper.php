<?php
defined('BASEPATH') || exit('No direct script access allowed');

/**
 * Coil Barcode Helper
 *
 * Fungsi parse dan format barcode coil untuk modul produksi PT HFG.
 *
 * Format barcode standar: {KODE_COIL}-{NOMOR_HEAT}-{KODE_SUPPLIER}
 * Contoh: C-240001-SUP01
 *
 * Requirements: 11.1, 11.2, 11.3, 11.4
 */

if (!function_exists('coil_barcode_parse')) {
    /**
     * Parse string barcode menjadi array identitas coil.
     *
     * @param  string $barcode_string String barcode dari scan
     * @return array  ['success' => bool, 'data' => array|null, 'message' => string]
     *                data berisi: kode_coil, nomor_heat, kode_supplier
     */
    function coil_barcode_parse($barcode_string)
    {
        if (empty($barcode_string) || !is_string($barcode_string)) {
            return [
                'success' => false,
                'data'    => null,
                'message' => 'Barcode tidak boleh kosong. Format yang diharapkan: {KODE_COIL}-{NOMOR_HEAT}-{KODE_SUPPLIER}',
            ];
        }

        $barcode_string = trim($barcode_string);

        // Format: bagian dipisahkan oleh '-', minimal 3 bagian
        // Kode coil bisa mengandung '-' (misal C-240001), jadi split dari kanan
        // Asumsi: 2 bagian terakhir adalah nomor_heat dan kode_supplier
        $parts = explode('-', $barcode_string);

        if (count($parts) < 3) {
            return [
                'success' => false,
                'data'    => null,
                'message' => 'Format barcode tidak valid. Format yang diharapkan: {KODE_COIL}-{NOMOR_HEAT}-{KODE_SUPPLIER}. '
                           . 'Barcode yang diterima: "' . $barcode_string . '"',
            ];
        }

        // Ambil 2 bagian terakhir sebagai nomor_heat dan kode_supplier
        $kode_supplier = array_pop($parts);
        $nomor_heat    = array_pop($parts);
        $kode_coil     = implode('-', $parts);

        if (empty($kode_coil) || empty($nomor_heat) || empty($kode_supplier)) {
            return [
                'success' => false,
                'data'    => null,
                'message' => 'Salah satu komponen barcode kosong. Format yang diharapkan: {KODE_COIL}-{NOMOR_HEAT}-{KODE_SUPPLIER}. '
                           . 'Barcode yang diterima: "' . $barcode_string . '"',
            ];
        }

        return [
            'success' => true,
            'data'    => [
                'kode_coil'     => $kode_coil,
                'nomor_heat'    => $nomor_heat,
                'kode_supplier' => $kode_supplier,
            ],
            'message' => 'OK',
        ];
    }
}

if (!function_exists('coil_barcode_format')) {
    /**
     * Format array identitas coil kembali menjadi string barcode standar.
     *
     * @param  array  $coil_identity Array dengan key: kode_coil, nomor_heat, kode_supplier
     * @return array  ['success' => bool, 'barcode' => string|null, 'message' => string]
     */
    function coil_barcode_format($coil_identity)
    {
        if (!is_array($coil_identity)) {
            return [
                'success' => false,
                'barcode' => null,
                'message' => 'Input harus berupa array dengan key kode_coil, nomor_heat, kode_supplier',
            ];
        }

        $kode_coil     = isset($coil_identity['kode_coil']) ? trim($coil_identity['kode_coil']) : '';
        $nomor_heat    = isset($coil_identity['nomor_heat']) ? trim($coil_identity['nomor_heat']) : '';
        $kode_supplier = isset($coil_identity['kode_supplier']) ? trim($coil_identity['kode_supplier']) : '';

        if (empty($kode_coil) || empty($nomor_heat) || empty($kode_supplier)) {
            return [
                'success' => false,
                'barcode' => null,
                'message' => 'kode_coil, nomor_heat, dan kode_supplier tidak boleh kosong',
            ];
        }

        $barcode = $kode_coil . '-' . $nomor_heat . '-' . $kode_supplier;

        return [
            'success' => true,
            'barcode' => $barcode,
            'message' => 'OK',
        ];
    }
}

if (!function_exists('coil_barcode_roundtrip')) {
    /**
     * Verifikasi round-trip: parse → format → parse ulang menghasilkan objek yang ekuivalen.
     * Digunakan untuk validasi konsistensi barcode.
     *
     * @param  string $barcode_string
     * @return bool
     */
    function coil_barcode_roundtrip($barcode_string)
    {
        $parsed1 = coil_barcode_parse($barcode_string);
        if (!$parsed1['success']) {
            return false;
        }

        $formatted = coil_barcode_format($parsed1['data']);
        if (!$formatted['success']) {
            return false;
        }

        $parsed2 = coil_barcode_parse($formatted['barcode']);
        if (!$parsed2['success']) {
            return false;
        }

        return $parsed1['data'] === $parsed2['data'];
    }
}
