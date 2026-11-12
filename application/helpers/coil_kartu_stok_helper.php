<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Helper Kartu Pemakaian Material per Coil (WRH. Production 2 / PRT).
 */

if (!function_exists('kartu_stok_extract_parent_spk')) {
    /**
     * Ekstrak nomor SPK induk dari nomor transaksi (mis. SPK-202611-0001/TRS-001 -> SPK-202611-0001).
     */
    function kartu_stok_extract_parent_spk($kode_trans)
    {
        $clean = trim((string) $kode_trans);
        if ($clean === '') {
            return '';
        }
        if (strpos($clean, '/') !== false) {
            $parts = explode('/', $clean, 2);
            return trim($parts[0]);
        }
        return $clean;
    }
}

if (!function_exists('kartu_stok_map_gudang_label')) {
    /**
     * Petakan kode gudang ke label lokasi yang mudah dibaca pada Kartu Pemakaian Material.
     */
    function kartu_stok_map_gudang_label($kd_gudang)
    {
        $map = [
            'PRO' => 'Produksi 1',
            'PUS' => 'Produksi 1',
            'WIP' => 'WIP',
            'HLD' => 'Hold',
            'SLI' => 'Slitting',
            'PRT' => 'prod. 2',
        ];
        $kd = strtoupper(trim((string) $kd_gudang));
        if (isset($map[$kd])) {
            return $map[$kd];
        }
        return $kd !== '' ? $kd : 'Produksi 1';
    }
}

if (!function_exists('kartu_stok_get_last_balance')) {
    /**
     * Ambil saldo akhir terakhir coil pada tabel warehouse_coil_kartu_stok.
     *
     * @return array ['exists' => bool, 'qty_akhir' => float]
     */
    function kartu_stok_get_last_balance($id_coil)
    {
        $CI =& get_instance();
        if (!$CI->db->table_exists('warehouse_coil_kartu_stok')) {
            return ['exists' => false, 'qty_akhir' => 0.0];
        }

        $row = $CI->db
            ->select('qty_akhir')
            ->where('id_coil', (int) $id_coil)
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get('warehouse_coil_kartu_stok')
            ->row();

        if (!$row) {
            return ['exists' => false, 'qty_akhir' => 0.0];
        }

        return [
            'exists'    => true,
            'qty_akhir' => round((float) $row->qty_akhir, 3),
        ];
    }
}

if (!function_exists('kartu_stok_record_entry')) {
    /**
     * Catat satu baris mutasi ke warehouse_coil_kartu_stok dengan running balance otomatis.
     *
     * @param array $params
     * @return float|false Saldo akhir setelah transaksi, atau false bila gagal
     */
    function kartu_stok_record_entry($params)
    {
        $CI =& get_instance();
        if (!$CI->db->table_exists('warehouse_coil_kartu_stok')) {
            return false;
        }

        $id_coil = isset($params['id_coil']) ? (int) $params['id_coil'] : 0;
        if ($id_coil <= 0) {
            return false;
        }

        $status    = (isset($params['status']) && strtolower(trim((string) $params['status'])) === 'in') ? 'in' : 'out';
        $qty_trans = isset($params['qty_trans']) ? round((float) $params['qty_trans'], 3) : 0.0;

        if (isset($params['qty_awal'])) {
            $qty_awal = round((float) $params['qty_awal'], 3);
        } else {
            $last     = kartu_stok_get_last_balance($id_coil);
            $qty_awal = $last['qty_akhir'];
        }

        $qty_akhir = $status === 'in'
            ? round($qty_awal + $qty_trans, 3)
            : round($qty_awal - $qty_trans, 3);

        $data = [
            'id_coil'              => $id_coil,
            'no_trs'               => isset($params['no_trs']) ? trim((string) $params['no_trs']) : '-',
            'id_material'          => isset($params['id_material']) ? trim((string) $params['id_material']) : '',
            'nm_material'          => isset($params['nm_material']) ? $params['nm_material'] : null,
            'no_coil'              => isset($params['no_coil']) ? $params['no_coil'] : null,
            'kode_internal'        => isset($params['kode_internal']) ? $params['kode_internal'] : null,
            'status'               => $status,
            'dari'                 => isset($params['dari']) ? trim((string) $params['dari']) : '-',
            'ke'                   => isset($params['ke']) ? trim((string) $params['ke']) : '-',
            'qty_trans'            => $qty_trans,
            'qty_awal'             => $qty_awal,
            'qty_akhir'            => $qty_akhir,
            'production_report_id' => !empty($params['production_report_id']) ? (int) $params['production_report_id'] : null,
            'keterangan'           => isset($params['keterangan']) ? $params['keterangan'] : null,
            'created_by'           => isset($params['created_by']) ? (string) $params['created_by'] : null,
            'created_at'           => !empty($params['created_at']) ? $params['created_at'] : date('Y-m-d H:i:s'),
        ];

        $CI->db->insert('warehouse_coil_kartu_stok', $data);
        return $qty_akhir;
    }
}

