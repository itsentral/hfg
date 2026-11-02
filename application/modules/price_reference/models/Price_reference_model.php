<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Price_reference_model extends CI_Model
{
    // Tabel-tabel sesuai ERD erd-revisi.drawio (tab: Product Costing ERD)
    protected $table_material = 'new_inventory_4';
    protected $table_header   = 'price_reference_header';
    protected $table_detail   = 'price_reference_detail';
    protected $table_history  = 'history_purchase';
    protected $table_kurs     = 'mata_uang';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Ambil daftar material aktif untuk tampilan depan Price Reference
     */
    public function get_materials($search = null)
    {
        $this->db->select('
            m.id, m.code_lv4, m.code, m.nama, 
            m.price_ref_use, m.price_ref_use_usd, m.price_ref_date_use, m.kurs,
            prd.max_price_reference, prd.newest_price_reference, prd.average_price_reference,
            (
                SELECT MAX(hp.price_unit)
                FROM history_purchase hp
                LEFT JOIN tr_purchase_order po ON po.no_po = hp.id_purchase_order
                WHERE (hp.id_material = m.id OR hp.id_material = m.code_lv4)
                  AND (po.loi IS NULL OR LOWER(po.loi) = "import")
            ) AS max_history_usd,
            (
                SELECT MAX(dt.hargasatuan)
                FROM dt_trans_po dt
                JOIN tr_purchase_order po2 ON po2.no_po = dt.no_po
                WHERE (dt.idmaterial = m.id OR dt.idmaterial = m.code_lv4)
                  AND dt.hargasatuan > 0
                  AND LOWER(po2.loi) = "import"
            ) AS max_po_usd
        ');
        $this->db->from($this->table_material . ' m');
        $this->db->join($this->table_detail . ' prd', 'prd.id_material = m.id OR prd.id_material = m.code_lv4', 'left');
        $this->db->where('m.deleted_date IS NULL', null, false);
        $this->db->where('m.category', 'material');

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('m.code', $search);
            $this->db->or_like('m.code_lv4', $search);
            $this->db->or_like('m.nama', $search);
            $this->db->group_end();
        }

        $this->db->group_by('m.id');
        $this->db->order_by('m.nama', 'ASC');
        $rows = $this->db->get()->result();

        $kurs_info   = $this->get_usd_kurs();
        $active_kurs = (float)$kurs_info['kurs'];

        foreach ($rows as &$r) {
            $usd_candidates = array_filter([
                (float)$r->max_price_reference,
                (float)$r->price_ref_use_usd,
                (float)$r->max_history_usd,
                (float)$r->max_po_usd,
            ], function($v) { return $v > 0; });

            if (!empty($usd_candidates)) {
                $r->price_ref_use_usd = max($usd_candidates);
            }

            // Jika price_ref_use (IDR) belum terisi atau 0, hitung otomatis dari price_ref_use_usd * kurs
            if ((float)$r->price_ref_use <= 0 && (float)$r->price_ref_use_usd > 0) {
                $calc_kurs = ($r->kurs && (float)$r->kurs > 0) ? (float)$r->kurs : $active_kurs;
                $r->price_ref_use = round((float)$r->price_ref_use_usd * $calc_kurs, 2);
            }

            $has_price = ((float)$r->price_ref_use_usd > 0 || (float)$r->price_ref_use > 0);
            if ($has_price) {
                $r->status_label = 'Oke';
                $r->status_badge = 'success';
            } else {
                $r->status_label = 'Not Set';
                $r->status_badge = 'warning';
            }
        }
        return $rows;
    }

    /**
     * Ambil 1 data material berdasarkan id
     */
    public function get_material_by_id($id)
    {
        return $this->db->get_where($this->table_material, [
            'id'           => $id,
            'deleted_date' => null
        ])->row();
    }

    /**
     * Ambil riwayat bulanan dari tabel ERD history_purchase dan transaksi PO Import nyata
     */
    public function get_monthly_history($id_material, $code_lv4 = null)
    {
        $identifiers = array_unique(array_filter([$id_material, $code_lv4]));
        if (empty($identifiers)) {
            return [];
        }

        $history = [];

        // 1. Ambil dari tabel ERD history_purchase (penerimaan fisik / finalize incoming PO Import)
        if ($this->db->table_exists($this->table_history)) {
            $this->db->select('h.*, po.no_surat, po.loi');
            $this->db->from($this->table_history . ' h');
            $this->db->join('tr_purchase_order po', 'po.no_po = h.id_purchase_order', 'left');
            if (count($identifiers) > 1) {
                $this->db->where_in('h.id_material', $identifiers);
            } else {
                $this->db->where('h.id_material', reset($identifiers));
            }
            $this->db->where('(po.loi IS NULL OR LOWER(po.loi) = "import")');
            $this->db->order_by('h.id', 'ASC');
            $rows_hp = $this->db->get()->result();

            foreach ($rows_hp as $r) {
                $bln = (int)$r->period_month;
                if ($bln >= 1 && $bln <= 12) {
                    $price = (float)$r->price_unit;
                    // Jika transaksi lokal (IDR) dan ada kurs > 1, konversikan ke ekuivalen USD
                    if (isset($r->mata_uang) && strtoupper(trim($r->mata_uang)) === 'IDR' && isset($r->kurs) && (float)$r->kurs > 1) {
                        $price = round($price / (float)$r->kurs, 6);
                    }
                    if (!isset($history[$bln]) || $price > $history[$bln]) {
                        $history[$bln] = $price;
                    }
                }
            }
        }

        // 2. Ambil dari tabel transaksi PO Import nyata (dt_trans_po) berdasarkan bulan tanggal PO
        $this->db->select('MONTH(po.tanggal) AS period_month, a.hargasatuan AS price_unit');
        $this->db->from('dt_trans_po a');
        $this->db->join('tr_purchase_order po', 'po.no_po = a.no_po', 'left');
        if (count($identifiers) > 1) {
            $this->db->where_in('a.idmaterial', $identifiers);
        } else {
            $this->db->where('a.idmaterial', reset($identifiers));
        }
        $this->db->where('a.hargasatuan >', 0);
        $this->db->where('LOWER(po.loi)', 'import');
        $this->db->order_by('po.tanggal', 'ASC');
        $rows_po = $this->db->get()->result();

        foreach ($rows_po as $po_item) {
            $bln = (int)$po_item->period_month;
            if ($bln >= 1 && $bln <= 12) {
                $price = (float)$po_item->price_unit;
                if (!isset($history[$bln]) || $price > $history[$bln]) {
                    $history[$bln] = $price;
                }
            }
        }

        return $history;
    }

    /**
     * Ambil detail Price Reference dari tabel ERD price_reference_header & detail
     */
    public function get_price_reference_detail($id_material, $code_lv4 = null)
    {
        if (!$this->db->table_exists($this->table_header) || !$this->db->table_exists($this->table_detail)) {
            return null;
        }

        $identifiers = array_unique(array_filter([$id_material, $code_lv4]));
        if (empty($identifiers)) {
            return null;
        }

        $this->db->select('
            h.id as header_id, h.id_material, h.id_kurs, h.price_reference_type, h.latest_update,
            d.id as detail_id, d.max_price_reference, d.newest_price_reference, d.average_price_reference
        ');
        $this->db->from($this->table_header . ' h');
        $this->db->join($this->table_detail . ' d', 'd.id_price_reference_header = h.id', 'left');
        if (count($identifiers) > 1) {
            $this->db->where_in('h.id_material', $identifiers);
        } else {
            $this->db->where('h.id_material', reset($identifiers));
        }
        $this->db->order_by('h.id', 'DESC');
        return $this->db->get()->row();
    }

    /**
     * Ambil riwayat transaksi PO nyata dari dt_trans_po
     */
    public function get_po_history($id_material, $code_lv4 = null)
    {
        $identifiers = array_filter([$id_material, $code_lv4]);
        if (empty($identifiers)) {
            return [];
        }

        $this->db->select('a.no_po, b.no_surat, b.tanggal, a.hargasatuan as harga_usd, a.qty, b.id_suplier, s.nama as nm_supplier');
        $this->db->from('dt_trans_po a');
        $this->db->join('tr_purchase_order b', 'b.no_po = a.no_po', 'left');
        $this->db->join('new_supplier s', 's.kode_supplier = b.id_suplier', 'left');
        $this->db->where_in('a.idmaterial', $identifiers);
        $this->db->where('a.hargasatuan >', 0);
        $this->db->where('LOWER(b.loi)', 'import');
        $this->db->order_by('b.tanggal', 'DESC');
        $this->db->limit(10);
        return $this->db->get()->result();
    }

    /**
     * Ambil Kurs USD aktif dari tabel mata_uang
     */
    public function get_usd_kurs()
    {
        $row = $this->db->select('id_kurs, kurs')->where('kode', 'USD')->get($this->table_kurs)->row();
        if ($row && (float)$row->kurs > 0) {
            return [
                'id_kurs' => (int)$row->id_kurs,
                'kurs'    => (float)$row->kurs
            ];
        }
        return [
            'id_kurs' => 1,
            'kurs'    => 17863.0
        ];
    }
}
