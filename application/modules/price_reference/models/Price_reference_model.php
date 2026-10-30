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
            prd.max_price_reference, prd.newest_price_reference, prd.average_price_reference
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

        foreach ($rows as &$r) {
            // Gunakan max_price_reference dari price_reference_detail jika ada, fallback ke price_ref_use_usd
            if ((float)$r->max_price_reference > 0) {
                $r->price_ref_use_usd = (float)$r->max_price_reference;
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
     * Ambil riwayat bulanan dari tabel ERD history_purchase
     */
    public function get_monthly_history($id_material, $code_lv4 = null)
    {
        if (!$this->db->table_exists($this->table_history)) {
            return [];
        }

        $identifiers = array_unique(array_filter([$id_material, $code_lv4]));
        if (empty($identifiers)) {
            return [];
        }

        $this->db->from($this->table_history);
        if (count($identifiers) > 1) {
            $this->db->where_in('id_material', $identifiers);
        } else {
            $this->db->where('id_material', reset($identifiers));
        }
        $this->db->order_by('id', 'ASC');
        $rows = $this->db->get()->result();

        $history = [];
        foreach ($rows as $r) {
            $bln = (int)$r->period_month;
            if ($bln >= 1 && $bln <= 12) {
                $price = (float)$r->price_unit;
                // Jika transaksi lokal (IDR) dan ada kurs > 1, konversikan ke ekuivalen USD untuk acuan Price Reference
                if (isset($r->mata_uang) && strtoupper(trim($r->mata_uang)) === 'IDR' && isset($r->kurs) && (float)$r->kurs > 1) {
                    $price = round($price / (float)$r->kurs, 6);
                }
                $history[$bln] = $price;
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

        $this->db->select('a.no_po, b.tanggal, a.hargasatuan as harga_usd, a.qty, b.id_suplier, s.nama as nm_supplier');
        $this->db->from('dt_trans_po a');
        $this->db->join('tr_purchase_order b', 'b.no_po = a.no_po', 'left');
        $this->db->join('new_supplier s', 's.kode_supplier = b.id_suplier', 'left');
        $this->db->where_in('a.idmaterial', $identifiers);
        $this->db->where('a.hargasatuan >', 0);
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
