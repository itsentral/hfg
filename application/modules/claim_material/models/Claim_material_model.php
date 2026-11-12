<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Claim_material_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Ambil daftar claim material dengan filter
     */
    public function get_claims($supplier_id = null, $asal = null, $q = null)
    {
        $this->db->select("
            cm.*,
            COALESCE(s.nama, ros.nm_supplier, cm.id_suplier) AS nama_supplier,
            s.email AS supplier_email,
            COALESCE(c.nm_material, dt.namamaterial, cm.id_material) AS nama_material,
            po.matauang AS mata_uang_po,
            (SELECT COUNT(cmb.id) FROM claim_material_bukti cmb WHERE cmb.id_claim_material = cm.id) AS total_bukti,
            (SELECT COUNT(cmd.id) FROM claim_material_decision cmd WHERE cmd.id_claim_material = cm.id) AS total_decision
        ", false);
        $this->db->from('claim_material cm');
        $this->db->join('new_supplier s', 's.id = cm.id_suplier OR s.kode_supplier = cm.id_suplier', 'left');
        $this->db->join('tr_ros_header ros', 'ros.no_po = cm.no_po', 'left');
        $this->db->join('warehouse_stock_coil c', 'c.id = cm.id_baby_coil OR c.no_coil = cm.no_coil_packing_list', 'left');
        $this->db->join('tr_purchase_order po', 'po.no_po = cm.no_po', 'left');
        $this->db->join('dt_trans_po dt', 'dt.no_po = cm.no_po AND dt.idmaterial = cm.id_material', 'left');

        if (!empty($supplier_id)) {
            $this->db->group_start()
                ->where('cm.id_suplier', $supplier_id)
                ->or_where('s.nama', $supplier_id)
                ->or_where('s.kode_supplier', $supplier_id)
                ->group_end();
        }

        if (!empty($asal)) {
            $this->db->where('cm.asal', $asal);
        }

        if (!empty($q)) {
            $this->db->group_start()
                ->like('cm.no_claim', $q)
                ->or_like('cm.no_coil_packing_list', $q)
                ->or_like('cm.no_po', $q)
                ->or_like('cm.problem', $q)
                ->or_like('s.nama', $q)
                ->group_end();
        }

        $this->db->group_by('cm.id');
        $this->db->order_by('cm.id', 'DESC');
        $claims = $this->db->get()->result_array();

        // Ambil bukti berkas dan decisions per claim
        foreach ($claims as &$row) {
            $row['bukti'] = $this->get_claim_bukti($row['id']);
            $row['decisions'] = $this->get_claim_decisions($row['id']);
        }

        return $claims;
    }

    /**
     * Ambil detail single claim beserta bukti dan decisions
     */
    public function get_claim_detail($id)
    {
        $this->db->select("
            cm.*,
            COALESCE(s.nama, ros.nm_supplier, cm.id_suplier) AS nama_supplier,
            s.email AS supplier_email,
            COALESCE(c.nm_material, dt.namamaterial, cm.id_material) AS nama_material,
            po.matauang AS mata_uang_po,
            mu.kurs AS kurs_default
        ", false);
        $this->db->from('claim_material cm');
        $this->db->join('new_supplier s', 's.id = cm.id_suplier OR s.kode_supplier = cm.id_suplier', 'left');
        $this->db->join('tr_ros_header ros', 'ros.no_po = cm.no_po', 'left');
        $this->db->join('warehouse_stock_coil c', 'c.id = cm.id_baby_coil OR c.no_coil = cm.no_coil_packing_list', 'left');
        $this->db->join('tr_purchase_order po', 'po.no_po = cm.no_po', 'left');
        $this->db->join('dt_trans_po dt', 'dt.no_po = cm.no_po AND dt.idmaterial = cm.id_material', 'left');
        $this->db->join('mata_uang mu', 'mu.kode = po.matauang', 'left');
        $this->db->where('cm.id', $id);
        $claim = $this->db->get()->row_array();

        if ($claim) {
            $claim['bukti'] = $this->get_claim_bukti($claim['id']);
            $claim['decisions'] = $this->get_claim_decisions($claim['id']);
        }

        return $claim;
    }

    /**
     * Ambil multiple claims by IDs
     */
    public function get_claims_by_ids($ids = [])
    {
        if (empty($ids)) return [];

        $this->db->select("
            cm.*,
            COALESCE(s.nama, ros.nm_supplier, cm.id_suplier) AS nama_supplier,
            s.email AS supplier_email,
            COALESCE(c.nm_material, dt.namamaterial, cm.id_material) AS nama_material,
            po.matauang AS mata_uang_po,
            mu.kurs AS kurs_default
        ", false);
        $this->db->from('claim_material cm');
        $this->db->join('new_supplier s', 's.id = cm.id_suplier OR s.kode_supplier = cm.id_suplier', 'left');
        $this->db->join('tr_ros_header ros', 'ros.no_po = cm.no_po', 'left');
        $this->db->join('warehouse_stock_coil c', 'c.id = cm.id_baby_coil OR c.no_coil = cm.no_coil_packing_list', 'left');
        $this->db->join('tr_purchase_order po', 'po.no_po = cm.no_po', 'left');
        $this->db->join('dt_trans_po dt', 'dt.no_po = cm.no_po AND dt.idmaterial = cm.id_material', 'left');
        $this->db->join('mata_uang mu', 'mu.kode = po.matauang', 'left');
        $this->db->where_in('cm.id', $ids);
        $this->db->order_by('cm.id', 'ASC');
        $claims = $this->db->get()->result_array();

        foreach ($claims as &$row) {
            $row['bukti'] = $this->get_claim_bukti($row['id']);
            $row['decisions'] = $this->get_claim_decisions($row['id']);
        }

        return $claims;
    }

    /**
     * Ambil berkas bukti per claim
     */
    public function get_claim_bukti($claim_id)
    {
        return $this->db->where('id_claim_material', $claim_id)
            ->order_by('id', 'ASC')
            ->get('claim_material_bukti')
            ->result_array();
    }

    /**
     * Ambil keputusan claim per claim
     */
    public function get_claim_decisions($claim_id)
    {
        return $this->db->where('id_claim_material', $claim_id)
            ->order_by('id', 'ASC')
            ->get('claim_material_decision')
            ->result_array();
    }

    /**
     * Ambil ringkasan statistik KPI
     */
    public function get_summary_stats()
    {
        $total = $this->db->count_all('claim_material');

        $belum_email = $this->db->where('status_claim', 'Claim Baru')
            ->count_all_results('claim_material');

        $sudah_putus = $this->db->query("
            SELECT COUNT(DISTINCT id_claim_material) AS cnt FROM claim_material_decision
        ")->row()->cnt ?? 0;

        $menunggu_decision = max(0, $total - $sudah_putus);

        return [
            'total'             => (int)$total,
            'belum_email'       => (int)$belum_email,
            'menunggu_decision' => (int)$menunggu_decision,
            'sudah_diputuskan'  => (int)$sudah_putus,
        ];
    }

    /**
     * Ambil list supplier unik yang ada di data claim
     */
    public function get_suppliers_list()
    {
        $this->db->select("DISTINCT cm.id_suplier, COALESCE(s.nama, ros.nm_supplier, cm.id_suplier) AS nama_supplier", false);
        $this->db->from('claim_material cm');
        $this->db->join('new_supplier s', 's.id = cm.id_suplier OR s.kode_supplier = cm.id_suplier', 'left');
        $this->db->join('tr_ros_header ros', 'ros.no_po = cm.no_po', 'left');
        $this->db->order_by('nama_supplier', 'ASC');
        return $this->db->get()->result_array();
    }

    /**
     * Proses kirim email keluhan ke supplier (FR-77)
     */
    public function send_email_complaint($claim_id, $email_to, $email_subject, $email_body, $user_name = '')
    {
        $claim = $this->db->where('id', $claim_id)->get('claim_material')->row_array();
        if (!$claim) return ['status' => false, 'message' => 'Data claim tidak ditemukan'];

        $old_claim = $claim;

        $data_update = [
            'status_claim'       => 'Email Terkirim',
            'tgl_email_terkirim' => date('Y-m-d H:i:s'),
        ];

        $this->db->where('id', $claim_id)->update('claim_material', $data_update);

        // Audit log
        if (function_exists('write_log')) {
            $logPayload = [
                'old'           => $old_claim,
                'new'           => array_merge($old_claim, $data_update),
                'email_to'      => $email_to,
                'email_subject' => $email_subject,
            ];
            write_log('Claim Material', 'Email', 'Kirim email keluhan claim ke supplier: ' . $claim['no_claim'] . ' (' . $email_to . ')', $logPayload, null, 1);
        }

        return ['status' => true, 'message' => 'Email keluhan berhasil dikirim ke ' . $email_to];
    }

    /**
     * Simpan Process Decision (FR-78, FR-79, FR-80, FR-81, FR-82)
     */
    public function save_process_decision($claim_ids, $kesepakatan, $decisions_per_claim, $kurs_per_claim, $user_name = '')
    {
        $this->db->trans_start();

        $processed_claims = [];

        foreach ($claim_ids as $cid) {
            $claim = $this->db->where('id', $cid)->get('claim_material')->row_array();
            if (!$claim) continue;

            $old_claim = $claim;
            $decisions = $decisions_per_claim[$cid] ?? [];
            if (empty($decisions)) continue;

            // Hapus keputusan lama bila ada (re-process)
            $this->db->where('id_claim_material', $cid)->delete('claim_material_decision');

            $has_kompensasi = false;
            $has_retur = false;
            $total_dec_kg = 0;

            foreach ($decisions as $dec) {
                $jalur = $dec['jalur'] ?? '';
                $aksi  = $dec['aksi'] ?? '';
                $kg    = (float)($dec['berat_kg'] ?? 0);
                $total_dec_kg += $kg;

                if ($jalur === 'Kompensasi') $has_kompensasi = true;
                if ($jalur === 'Retur') $has_retur = true;

                $nilai_dn    = isset($dec['nilai_debit_note']) ? (float)$dec['nilai_debit_note'] : null;
                $nilai_kw    = isset($dec['nilai_material_kw']) ? (float)$dec['nilai_material_kw'] : null;
                $nilai_scrap = isset($dec['nilai_biaya_scrap']) ? (float)$dec['nilai_biaya_scrap'] : null;

                $dec_data = [
                    'id_claim_material' => $cid,
                    'jalur'             => $jalur,
                    'aksi'              => $aksi,
                    'berat_kg'          => $kg,
                    'nilai_debit_note'  => $nilai_dn,
                    'nilai_material_kw' => $nilai_kw,
                    'nilai_biaya_scrap' => $nilai_scrap,
                    'keterangan'        => $dec['keterangan'] ?? null,
                    'created_at'        => date('Y-m-d H:i:s'),
                    'created_by'        => $user_name,
                ];

                $this->db->insert('claim_material_decision', $dec_data);
                $decision_id = $this->db->insert_id();

                // 1. Jika aksi = 'Debit Note' (Kompensasi), buat draf Debit Note (FR-79)
                if ($aksi === 'Debit Note') {
                    $kurs = (float)($kurs_per_claim[$cid] ?? 1.0);
                    if ($kurs <= 0) $kurs = 1.0;

                    $dn_no = $this->generate_debit_note_no();
                    $tot_nilai = $nilai_dn !== null ? $nilai_dn : 0.0;
                    $tot_nilai_idr = $tot_nilai * $kurs;

                    $dn_data = [
                        'no_debit_note'             => $dn_no,
                        'tgl_terbit'                => date('Y-m-d'),
                        'id_suplier'                => $claim['id_suplier'],
                        'id_kurs'                   => 64, // IDR default
                        'kurs'                      => $kurs,
                        'total_nilai'               => $tot_nilai,
                        'total_nilai_idr'           => $tot_nilai_idr,
                        'jalur'                     => 'Kompensasi',
                        'asal'                      => $claim['asal'],
                        'status_dokumen'            => 'DN Belum Terbit',
                        'id_claim_material'         => $cid,
                        'id_claim_material_decision'=> $decision_id,
                        'created_at'                => date('Y-m-d H:i:s'),
                        'created_by'                => $user_name,
                    ];
                    $this->db->insert('debit_note', $dn_data);
                }

                // 2. Jika aksi = 'Lanjut Produksi', release coil di warehouse_stock_coil (FR-80)
                if ($aksi === 'Lanjut Produksi') {
                    $this->db->group_start();
                    if (!empty($claim['id_gudang_material_hold'])) {
                        $this->db->where('id', (int)$claim['id_gudang_material_hold']);
                    }
                    if (!empty($claim['id_baby_coil'])) {
                        $this->db->or_where('id', (int)$claim['id_baby_coil'])
                                 ->or_where('parent_coil_id', (int)$claim['id_baby_coil']);
                    }
                    if (!empty($claim['no_coil_packing_list'])) {
                        $clean = trim($claim['no_coil_packing_list']);
                        $this->db->or_where('no_coil', $clean)
                                 ->or_where('no_coil', $clean . '-HOLD');
                    }
                    $this->db->group_end();
                    $this->db->where('kd_gudang', 'HLD')
                             ->update('warehouse_stock_coil', [
                                 'status_proses' => 'in_warehouse'
                             ]);
                }

                // 2b. Jika aksi = 'Retur', tandai status proses coil di warehouse_stock_coil
                if ($aksi === 'Retur') {
                    $this->db->group_start();
                    if (!empty($claim['id_gudang_material_hold'])) {
                        $this->db->where('id', (int)$claim['id_gudang_material_hold']);
                    }
                    if (!empty($claim['id_baby_coil'])) {
                        $this->db->or_where('id', (int)$claim['id_baby_coil'])
                                 ->or_where('parent_coil_id', (int)$claim['id_baby_coil']);
                    }
                    if (!empty($claim['no_coil_packing_list'])) {
                        $clean = trim($claim['no_coil_packing_list']);
                        $this->db->or_where('no_coil', $clean)
                                 ->or_where('no_coil', $clean . '-HOLD');
                    }
                    $this->db->group_end();
                    $this->db->where('kd_gudang', 'HLD')
                             ->update('warehouse_stock_coil', [
                                 'status_proses' => 'retur'
                             ]);
                }

                // 3. Jika aksi = 'Jual Material', masukkan ke stok_gudang_slitting (FR-81)
                if ($aksi === 'Jual Material') {
                    $nilai_material = $kg * (float)$claim['harga_beli_per_kg'];
                    $slit_data = [
                        'id_claim_material_decision' => $decision_id,
                        'id_material'                => $claim['id_material'],
                        'berat_kg'                   => $kg,
                        'nilai_material'             => $nilai_material,
                        'nilai_material_kw'          => $nilai_kw !== null ? $nilai_kw : 0.0,
                        'nilai_biaya_scrap'          => $nilai_scrap !== null ? $nilai_scrap : ($nilai_material - ($nilai_kw ?: 0.0)),
                        'tgl_masuk'                  => date('Y-m-d'),
                        'created_at'                 => date('Y-m-d H:i:s'),
                        'created_by'                 => $user_name,
                    ];
                    $this->db->insert('stok_gudang_slitting', $slit_data);
                }
            }

            // Tentukan status_claim baru
            $new_status = 'Kompensasi';
            if ($has_kompensasi && $has_retur) {
                $new_status = 'Kompensasi + Retur';
            } elseif ($has_retur) {
                $new_status = 'Retur';
            }

            $update_claim = [
                'status_claim'        => $new_status,
                'kesepakatan_supplier'=> $kesepakatan,
            ];
            $this->db->where('id', $cid)->update('claim_material', $update_claim);

            $processed_claims[] = [
                'id'       => $cid,
                'no_claim' => $claim['no_claim'],
                'old'      => $old_claim,
                'new'      => array_merge($old_claim, $update_claim),
                'decisions'=> $decisions,
            ];
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return ['status' => false, 'message' => 'Gagal menyimpan keputusan claim di database'];
        }

        // Audit Log
        if (function_exists('write_log')) {
            foreach ($processed_claims as $pc) {
                $logPayload = [
                    'old'            => $pc['old'],
                    'new'            => $pc['new'],
                    'kesepakatan'    => $kesepakatan,
                    'detail_changes' => $pc['decisions'],
                ];
                write_log('Claim Material', 'Edit', 'Process Decision Claim: ' . $pc['no_claim'] . ' (' . $pc['new']['status_claim'] . ')', $logPayload, null, 1);
            }
        }

        return ['status' => true, 'message' => 'Keputusan claim berhasil disimpan'];
    }

    /**
     * Generate Nomor Debit Note (format: DN-YYMM-XXXX)
     */
    public function generate_debit_note_no()
    {
        $prefix = 'DN-' . date('ym') . '-';
        $last = $this->db->like('no_debit_note', $prefix, 'after')
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get('debit_note')
            ->row();
        $seq = 1;
        if ($last && !empty($last->no_debit_note)) {
            $parts = explode('-', $last->no_debit_note);
            if (isset($parts[2])) {
                $seq = (int)$parts[2] + 1;
            }
        }
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Ambil data Material On Hold (FR-75, UC-N35)
     */
    public function get_on_hold_materials()
    {
        $this->db->select("
            cm.id,
            cm.no_claim,
            cm.id_suplier,
            cm.id_material,
            cm.no_po,
            cm.no_coil_packing_list,
            cm.berat_kg,
            cm.tgl_produksi,
            cm.alasan_produksi,
            cm.status_claim,
            COALESCE(s.nama, ros.nm_supplier, cm.id_suplier) AS nama_supplier,
            COALESCE(c.nm_material, dt.namamaterial, cm.id_material) AS nama_material,
            c.status_proses AS status_proses_coil
        ", false);
        $this->db->from('claim_material cm');
        $this->db->join('new_supplier s', 's.id = cm.id_suplier OR s.kode_supplier = cm.id_suplier', 'left');
        $this->db->join('tr_ros_header ros', 'ros.no_po = cm.no_po', 'left');
        $this->db->join('warehouse_stock_coil c', 'c.id = cm.id_baby_coil OR c.no_coil = cm.no_coil_packing_list', 'left');
        $this->db->join('dt_trans_po dt', 'dt.no_po = cm.no_po AND dt.idmaterial = cm.id_material', 'left');
        $this->db->where('cm.asal', 'Hold Coil');
        $this->db->order_by('cm.id', 'DESC');
        $rows = $this->db->get()->result_array();

        foreach ($rows as &$r) {
            $dec = $this->get_claim_decisions($r['id']);
            $status_material = 'Hold';
            if (!empty($dec)) {
                $aksis = array_column($dec, 'aksi');
                if (in_array('Lanjut Produksi', $aksis)) {
                    $status_material = 'Dapat Dipakai (Release)';
                } elseif (in_array('Jual Material', $aksis)) {
                    $status_material = 'Dijual (Gudang Slitting)';
                } elseif (in_array('Retur', $aksis)) {
                    $status_material = 'Dalam Retur';
                } elseif (in_array('Debit Note', $aksis)) {
                    $status_material = 'Dikompensasi';
                }
            }
            $r['status_material'] = $status_material;
            $r['decisions'] = $dec;
        }

        return $rows;
    }

    /**
     * Ambil data Material Dijual - Gudang Slitting (FR-81, FR-96)
     */
    public function get_slitting_materials()
    {
        $this->db->select("
            sgs.*,
            cmd.id_claim_material,
            cm.no_claim,
            cm.no_coil_packing_list,
            cm.id_suplier,
            COALESCE(s.nama, ros.nm_supplier, cm.id_suplier) AS nama_supplier,
            COALESCE(c.nm_material, dt.namamaterial, sgs.id_material) AS nama_material
        ", false);
        $this->db->from('stok_gudang_slitting sgs');
        $this->db->join('claim_material_decision cmd', 'cmd.id = sgs.id_claim_material_decision', 'left');
        $this->db->join('claim_material cm', 'cm.id = cmd.id_claim_material', 'left');
        $this->db->join('new_supplier s', 's.id = cm.id_suplier OR s.kode_supplier = cm.id_suplier', 'left');
        $this->db->join('tr_ros_header ros', 'ros.no_po = cm.no_po', 'left');
        $this->db->join('warehouse_stock_coil c', 'c.id = cm.id_baby_coil OR c.no_coil = cm.no_coil_packing_list', 'left');
        $this->db->join('dt_trans_po dt', 'dt.no_po = cm.no_po AND dt.idmaterial = cm.id_material', 'left');
        $this->db->order_by('sgs.id', 'DESC');
        return $this->db->get()->result_array();
    }

    /**
     * Ambil data Report Biaya Kompensasi vs Biaya Scrap (FR-87)
     */
    public function get_report_biaya()
    {
        $this->db->select("
            cmd.id AS decision_id,
            cmd.id_claim_material,
            cmd.jalur,
            cmd.aksi,
            cmd.berat_kg,
            cmd.nilai_debit_note,
            cmd.nilai_material_kw,
            cmd.nilai_biaya_scrap,
            cm.no_claim,
            cm.no_coil_packing_list,
            cm.asal,
            cm.harga_beli_per_kg,
            COALESCE(s.nama, ros.nm_supplier, cm.id_suplier) AS nama_supplier,
            COALESCE(c.nm_material, dt.namamaterial, cm.id_material) AS nama_material
        ", false);
        $this->db->from('claim_material_decision cmd');
        $this->db->join('claim_material cm', 'cm.id = cmd.id_claim_material', 'inner');
        $this->db->join('new_supplier s', 's.id = cm.id_suplier OR s.kode_supplier = cm.id_suplier', 'left');
        $this->db->join('tr_ros_header ros', 'ros.no_po = cm.no_po', 'left');
        $this->db->join('warehouse_stock_coil c', 'c.id = cm.id_baby_coil OR c.no_coil = cm.no_coil_packing_list', 'left');
        $this->db->join('dt_trans_po dt', 'dt.no_po = cm.no_po AND dt.idmaterial = cm.id_material', 'left');
        $this->db->where_in('cmd.aksi', ['Debit Note', 'Jual Material']);
        $this->db->order_by('cmd.id', 'DESC');
        $rows = $this->db->get()->result_array();

        $report = [];
        foreach ($rows as $r) {
            $biaya_material = (float)$r['berat_kg'] * (float)$r['harga_beli_per_kg'];
            $beban = 0;
            $akun = 'Biaya Scrap';

            if ($r['aksi'] === 'Debit Note') {
                $nilai_komp = (float)($r['nilai_debit_note'] ?? 0);
                $beban = max(0, $biaya_material - $nilai_komp);
                $akun = ($r['asal'] === 'KW 2 (Supplier)') ? 'Biaya KW 2 Supplier' : 'Biaya Scrap';
                $report[] = [
                    'jenis'                 => 'Debit Note',
                    'no_claim'              => $r['no_claim'],
                    'nama_supplier'         => $r['nama_supplier'],
                    'nama_material'         => $r['nama_material'],
                    'no_coil_packing_list'  => $r['no_coil_packing_list'],
                    'asal'                  => $r['asal'],
                    'berat_kg'              => (float)$r['berat_kg'],
                    'biaya_material'        => $biaya_material,
                    'nilai_kompensasi'      => $nilai_komp,
                    'nilai_material_kw'     => null,
                    'beban_ditanggung_hfg'  => $beban,
                    'akun_beban'            => $akun,
                ];
            } elseif ($r['aksi'] === 'Jual Material') {
                $nilai_kw = (float)($r['nilai_material_kw'] ?? 0);
                $beban = (float)($r['nilai_biaya_scrap'] ?? ($biaya_material - $nilai_kw));
                $akun = 'Biaya Scrap';
                $report[] = [
                    'jenis'                 => 'Jual Material',
                    'no_claim'              => $r['no_claim'],
                    'nama_supplier'         => $r['nama_supplier'],
                    'nama_material'         => $r['nama_material'],
                    'no_coil_packing_list'  => $r['no_coil_packing_list'],
                    'asal'                  => $r['asal'],
                    'berat_kg'              => (float)$r['berat_kg'],
                    'biaya_material'        => $biaya_material,
                    'nilai_kompensasi'      => null,
                    'nilai_material_kw'     => $nilai_kw,
                    'beban_ditanggung_hfg'  => $beban,
                    'akun_beban'            => $akun,
                ];
            }
        }

        return $report;
    }

    /**
     * Jurnal peristiwa per claim (FR-93)
     */
    public function get_claim_journals($claim_id)
    {
        $claim = $this->get_claim_detail($claim_id);
        if (!$claim) return [];

        $journals = [];
        $decisions = $claim['decisions'];

        foreach ($decisions as $d) {
            $biaya_material = (float)$d['berat_kg'] * (float)$claim['harga_beli_per_kg'];

            if ($d['aksi'] === 'Jual Material') {
                $kw = (float)$d['nilai_material_kw'];
                $scrap = (float)$d['nilai_biaya_scrap'];
                $tot = $kw + $scrap;
                $journals[] = [
                    'title'  => 'Jual Material (Inventory Slitting)',
                    'posted' => true,
                    'lines'  => [
                        ['Material KW (Inventory)', $kw, 0],
                        ['Biaya Scrap (Rugi)', $scrap, 0],
                        ['Gudang Bahan Persediaan Produksi', 0, $tot]
                    ]
                ];
            }

            if ($d['aksi'] === 'Debit Note') {
                $nilai_dn = (float)$d['nilai_debit_note'];
                $akun_kredit = ($claim['asal'] === 'KW 2 (Supplier)') ? 'Biaya KW 2 Supplier' : 'Biaya Scrap';
                $journals[] = [
                    'title'  => 'Debit Note Terbit (Kompensasi)',
                    'posted' => true,
                    'lines'  => [
                        ['Debit Note (Kompensasi)', $nilai_dn, 0],
                        [$akun_kredit, 0, $nilai_dn]
                    ]
                ];
            }

            if ($d['aksi'] === 'Retur') {
                $nilai_retur = (float)$d['berat_kg'] * (float)$claim['costbook_per_kg'];
                $nilai_po = (float)$d['berat_kg'] * (float)$claim['price_per_unit_po'];
                $selisih = $nilai_po - $nilai_retur;

                $lines = [
                    ['Debit Note (Retur)', $nilai_po, 0],
                    ['Nilai Material Retur (In Transit)', 0, $nilai_retur],
                ];
                if ($selisih > 0) {
                    $lines[] = ['Selisih Costbook vs Price/Unit PO', 0, $selisih];
                } elseif ($selisih < 0) {
                    $lines[] = ['Selisih Costbook vs Price/Unit PO', abs($selisih), 0];
                }

                $journals[] = [
                    'title'  => 'Confirm Surat Jalan Retur (Retur ke Supplier)',
                    'posted' => false,
                    'lines'  => $lines
                ];
            }
        }

        return $journals;
    }
}

