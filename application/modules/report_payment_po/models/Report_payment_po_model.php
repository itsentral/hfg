<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Report_payment_po_model extends BF_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Mengambil data ringkasan header per No. PO
     * Kolom: No. PO, Supplier, Currency, Nilai PO, PO Terbayar, Outstanding PO
     */
    public function get_header_data($no_po = null)
    {
        // Ambil data PO dari tr_receive_invoice yang terhubung ke tr_purchase_order & new_supplier
        $this->db->select("
            ri.no_surat as no_po,
            po.no_po as id_po,
            COALESCE(po.matauang, 'IDR') as currency,
            COALESCE(s.nama, po.id_suplier, '-') as nm_supplier,
            COALESCE(po.hargatotal, 0) as nilai_po
        ");
        $this->db->from("tr_receive_invoice ri");
        $this->db->join("tr_purchase_order po", "ri.no_surat = po.no_surat", "left");
        $this->db->join("new_supplier s", "po.id_suplier = s.kode_supplier", "left");
        $this->db->where_in("ri.status", ['request payment', 'approve checker', 'approve management', 'payment']);
        if (!empty($no_po)) {
            $this->db->like("ri.no_surat", trim($no_po));
        }
        $this->db->group_by("ri.no_surat");
        $this->db->order_by("ri.no_surat", "ASC");

        $headers = $this->db->get()->result();

        // Ambil detail pembayaran untuk menghitung total terbayar per No. PO
        $detail_all = $this->get_report_data($no_po);
        $payments_per_po = [];
        foreach ($detail_all as $d) {
            $p_no = $d['no_po'];
            if (!isset($payments_per_po[$p_no])) {
                $payments_per_po[$p_no] = 0;
            }
            $payments_per_po[$p_no] += (float) ($d['invoice_pay'] ?? 0);
        }

        $results = [];
        foreach ($headers as $h) {
            $po_no       = $h->no_po;
            $nilai_po    = (float) $h->nilai_po;
            $po_terbayar = isset($payments_per_po[$po_no]) ? (float) $payments_per_po[$po_no] : 0;
            $outstanding = $nilai_po - $po_terbayar;

            $h->nilai_po       = $nilai_po;
            $h->po_terbayar    = $po_terbayar;
            $h->outstanding_po = $outstanding;

            $results[] = $h;
        }

        return $results;
    }

    /**
     * Mengambil detail transaksi pembayaran untuk 1 PO tertentu
     */
    public function get_po_detail_data($no_po)
    {
        // 1. Ambil info header PO
        $this->db->select("
            ri.no_surat as no_po,
            po.no_po as id_po,
            COALESCE(po.matauang, 'IDR') as currency,
            COALESCE(s.nama, po.id_suplier, '-') as nm_supplier,
            COALESCE(po.hargatotal, 0) as nilai_po
        ");
        $this->db->from("tr_receive_invoice ri");
        $this->db->join("tr_purchase_order po", "ri.no_surat = po.no_surat", "left");
        $this->db->join("new_supplier s", "po.id_suplier = s.kode_supplier", "left");
        $this->db->where("ri.no_surat", $no_po);
        $this->db->limit(1);
        $header = $this->db->get()->row();

        // 2. Ambil detail transaksi 18 kolom untuk PO ini
        $details = $this->get_report_data($no_po);

        $total_terbayar = 0;
        foreach ($details as $d) {
            $total_terbayar += (float) ($d['invoice_pay'] ?? 0);
        }

        $nilai_po = !empty($header) ? (float) $header->nilai_po : 0;
        $outstanding = $nilai_po - $total_terbayar;

        return [
            'header'         => $header,
            'details'        => $details,
            'nilai_po'       => $nilai_po,
            'po_terbayar'    => $total_terbayar,
            'outstanding_po' => $outstanding,
        ];
    }

    /**
     * Mengambil data detail transaksi 18 kolom (PO, ROS, Receive Invoice, Payment)
     */
    public function get_report_data($no_po = null)
    {
        // 1. Get Base PO Data from tr_receive_invoice
        $this->db->select('
            a.no_surat as no_po,
            a.tipe as tipe_po, 
            a.nilai_invoice as value_po, 
            a.tipe as tipe_top, 
            a.id_top,
            top.progress as value_pct,
            ros.gl_persediaan_intransit as total_material, 
            a.gl_unbill as unbill, 
            ros.gl_selisih_kurs as selisih_kurs_receive_1,
            a.nilai_invoice as receive_invoice_value, 
            a.kurs as receive_kurs, 
            a.jumlah_rupiah as value_receive_idr,
            a.gl_selisih as selisih_kurs_receive_2,
            a.id as id_receive_invoice,
            a.tipe
        ');
        $this->db->from('tr_receive_invoice a');
        $this->db->join('tr_top_po top', 'top.id = a.id_top', 'left');
        $this->db->join('tr_ros_header ros', 'ros.id = a.id_ros', 'left');
        if (!empty($no_po)) {
            $this->db->where('a.no_surat', $no_po);
        }
        $this->db->where_in('a.status', ['request payment', 'approve checker', 'approve management', 'payment']);
        $this->db->order_by('a.id', 'ASC');

        $receives = $this->db->get()->result();

        // Output array
        $result = [];

        foreach ($receives as $rec) {
            $row = [
                'no_po' => $rec->no_po,
                'tipe_po' => $rec->tipe_po,
                'value_po' => $rec->value_po,
                'tipe_top' => $rec->tipe_top,
                'value_pct' => $rec->value_pct,
                'total_material' => $rec->total_material,
                'unbill' => $rec->unbill,
                'selisih_kurs_receive_1' => $rec->selisih_kurs_receive_1,
                'receive_invoice_value' => $rec->receive_invoice_value,
                'receive_kurs' => $rec->receive_kurs,
                'value_receive_idr' => $rec->value_receive_idr,
                'selisih_kurs_receive_2' => $rec->selisih_kurs_receive_2,

                // Fields to be filled by payment_approve
                'selisih_kurs_pay' => 0,
                'invoice_pay' => 0,
                'kurs_pay' => 0,
                'currency_pay' => '',
                'payment_idr' => 0,
                'admin_bank' => 0,
                'selisih_kurs_admin' => 0,

                // Type category
                'category' => ''
            ];

            // DP logic
            if (strtolower($rec->tipe) == 'dp' || strpos(strtolower($rec->tipe_top), 'dp') !== false || strpos(strtolower($rec->tipe_top), 'uang muka') !== false) {
                $row['category'] = 'dp';
            } elseif (strtolower($rec->tipe) == 'import') {
                $row['category'] = 'import';
            } elseif (strtolower($rec->tipe) == 'local') {
                $row['category'] = 'local';
            } else {
                $row['category'] = strtolower($rec->tipe);
            }

            // Get payment details
            $this->db->select('p.nominal_asli as invoice_pay, p.kurs_payment as kurs_pay, p.mata_uang as currency_pay, p.total_payment as payment_idr, p.bank_charge as admin_bank, p.gl_selisih_kurs as selisih_kurs_admin');
            $this->db->from('payment_approve_details pd');
            $this->db->join('payment_approve p', 'p.no_doc = pd.payment_id', 'left');
            $this->db->where('pd.id_receive_invoice', $rec->id_receive_invoice);
            $pay_detail = $this->db->get()->row();

            if (!empty($pay_detail)) {
                $row['invoice_pay'] = $pay_detail->invoice_pay;
                $row['kurs_pay'] = $pay_detail->kurs_pay;
                $row['currency_pay'] = $pay_detail->currency_pay;
                $row['payment_idr'] = $pay_detail->payment_idr;
                $row['admin_bank'] = $pay_detail->admin_bank;
                $row['selisih_kurs_admin'] = $pay_detail->selisih_kurs_admin;
            }

            $result[] = $row;
        }

        return $result;
    }
}
