<?php
defined('BASEPATH') || exit('No direct script access allowed');

if (!function_exists('get_identitas')) {
    /**
     * Ambil data identitas perusahaan (baris ididentitas = 1) dengan static cache per request.
     *
     * @param bool $refresh Paksa muat ulang dari database
     * @return object
     */
    function get_identitas($refresh = false)
    {
        static $cached = null;

        if ($cached !== null && !$refresh) {
            return $cached;
        }

        $default = (object) [
            'ididentitas'   => 1,
            'nm_perusahaan' => 'Harapan Fajar Gemilang',
            'kode'          => 'HFG',
            'alamat'        => null,
            'kota'          => null,
            'no_telp'       => null,
            'fax'           => null,
            'email'         => null,
            'website'       => null,
            'logo'          => null,
            'icon'          => null,
            'gambar'        => null,
        ];

        if (!function_exists('get_instance')) {
            return $default;
        }

        $CI = &get_instance();
        if (!isset($CI->db)) {
            return $default;
        }

        $row = $CI->db->get_where('identitas', ['ididentitas' => 1])->row();
        if (!$row) {
            $row = $CI->db->order_by('ididentitas', 'ASC')->limit(1)->get('identitas')->row();
        }

        if ($row) {
            $cached = $row;
            return $cached;
        }

        $cached = $default;
        return $cached;
    }
}

if (!function_exists('get_company_name')) {
    /**
     * Ambil nama perusahaan dari tabel identitas.
     *
     * @param bool $with_pt   Tambahkan awalan "PT " bila belum diawali "PT"
     * @param bool $uppercase Ubah ke huruf kapital seluruhnya
     * @return string
     */
    function get_company_name($with_pt = false, $uppercase = false)
    {
        $idt  = get_identitas();
        $name = isset($idt->nm_perusahaan) ? trim((string) $idt->nm_perusahaan) : '';
        if ($name === '') {
            $name = 'Harapan Fajar Gemilang';
        }

        if ($with_pt && !preg_match('/^pt\.?\s+/i', $name)) {
            $name = 'PT ' . $name;
        }

        return $uppercase ? strtoupper($name) : $name;
    }
}

if (!function_exists('get_company_code')) {
    /**
     * Ambil kode singkat perusahaan dari tabel identitas (field kode).
     *
     * @return string
     */
    function get_company_code()
    {
        $idt  = get_identitas();
        $code = isset($idt->kode) ? trim((string) $idt->kode) : '';
        return $code !== '' ? $code : 'HFG';
    }
}

if (!function_exists('_resolve_identitas_asset_url')) {
    /**
     * Resolve URL aset gambar dari field logo/gambar tabel identitas.
     *
     * @param string|null $value         Nilai kolom DB
     * @param array       $candidateDirs Direktori kandidat relatif ke FCPATH jika hanya nama file
     * @param string      $fallbackPath  Path fallback relatif ke FCPATH
     * @return string
     */
    function _resolve_identitas_asset_url($value, array $candidateDirs, $fallbackPath)
    {
        $val = trim((string) $value);
        if ($val !== '') {
            if (preg_match('#^https?://#i', $val)) {
                return $val;
            }

            $clean = ltrim($val, '/');
            if (strpos($clean, '/') !== false) {
                return base_url($clean);
            }

            foreach ($candidateDirs as $dir) {
                $rel = rtrim($dir, '/') . '/' . $clean;
                if (defined('FCPATH') && file_exists(FCPATH . $rel)) {
                    return base_url($rel);
                }
            }

            $firstDir = rtrim($candidateDirs[0], '/');
            return base_url($firstDir . '/' . $clean);
        }

        return base_url($fallbackPath);
    }
}

if (!function_exists('get_company_logo')) {
    /**
     * Ambil URL logo/icon perusahaan dari tabel identitas (kolom icon & logo) dengan fallback default.
     *
     * @param string $type 'icon' (favicon/sidebar/kop kecil) atau 'doc' (logo dokumen memanjang)
     * @return string
     */
    function get_company_logo($type = 'icon')
    {
        $idt  = get_identitas();
        $logo = isset($idt->logo) ? trim((string) $idt->logo) : '';
        $icon = isset($idt->icon) ? trim((string) $idt->icon) : '';

        if ($type === 'doc') {
            $val      = ($logo !== '') ? $logo : $icon;
            $fallback = 'assets/images/logohfg.png';
        } else {
            $val      = ($icon !== '') ? $icon : $logo;
            $fallback = 'assets/images/iconhfg.png';
        }

        return _resolve_identitas_asset_url(
            $val,
            ['assets/images', 'uploads', 'uploads/identitas', 'assets/img'],
            $fallback
        );
    }
}

if (!function_exists('get_company_wallpaper')) {
    /**
     * Ambil URL wallpaper login dari tabel identitas (kolom gambar) dengan fallback default.
     *
     * @return string
     */
    function get_company_wallpaper()
    {
        $idt = get_identitas();

        return _resolve_identitas_asset_url(
            isset($idt->gambar) ? $idt->gambar : '',
            ['assets/img', 'assets/images', 'uploads', 'uploads/identitas'],
            'assets/img/wallpaper_harapan.jpg'
        );
    }
}

if (!function_exists('get_company_address')) {
    /**
     * Ambil alamat perusahaan dari tabel identitas dengan nilai fallback.
     *
     * @param string $default
     * @return string
     */
    function get_company_address($default = '')
    {
        $idt    = get_identitas();
        $alamat = isset($idt->alamat) ? trim((string) $idt->alamat) : '';
        return $alamat !== '' ? $alamat : $default;
    }
}

if (!function_exists('get_company_phone')) {
    /**
     * Ambil nomor telepon perusahaan dari tabel identitas dengan nilai fallback.
     *
     * @param string $default
     * @return string
     */
    function get_company_phone($default = '')
    {
        $idt  = get_identitas();
        $telp = isset($idt->no_telp) ? trim((string) $idt->no_telp) : '';
        return $telp !== '' ? $telp : $default;
    }
}

