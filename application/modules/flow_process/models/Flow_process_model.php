<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Model Flow Process & Cycletime (FR-42, UC-N10).
 * Tabel: flow_process_header (satu baris per Product Category = code_lv2) dan flow_process_detail (proses).
 * Nama tabel dan kolom mengikuti ERD (halaman "Product Costing ERD").
 *
 * Nama kategori dibaca TERPISAH dari product_lvl_2 (bukan JOIN) karena collation kolom code_lv2 belum
 * dikonfirmasi (D0) dan JOIN lintas collation dapat memunculkan error "Illegal mix of collations".
 */
class Flow_process_model extends CI_Model
{
	const HEADER = 'flow_process_header';
	const DETAIL = 'flow_process_detail';

	public function __construct()
	{
		parent::__construct();
	}

	/** Semua header + jumlah proses (array). */
	public function get_list()
	{
		$headers = $this->db->order_by('id', 'asc')->get(self::HEADER)->result_array();
		if (empty($headers)) {
			return [];
		}
		$counts = [];
		$rows = $this->db->select('id_flow_process_header, COUNT(*) AS jumlah', false)
			->group_by('id_flow_process_header')
			->get(self::DETAIL)->result_array();
		foreach ($rows as $r) {
			$counts[$r['id_flow_process_header']] = (int) $r['jumlah'];
		}
		foreach ($headers as $i => $h) {
			$headers[$i]['jumlah_proses'] = isset($counts[$h['id']]) ? $counts[$h['id']] : 0;
		}
		return $headers;
	}

	public function get_header($id)
	{
		$row = $this->db->get_where(self::HEADER, ['id' => $id])->row_array();
		return (!empty($row)) ? $row : [];
	}

	public function get_header_by_code($code_lv2)
	{
		$row = $this->db->get_where(self::HEADER, ['code_lv2' => $code_lv2])->row_array();
		return (!empty($row)) ? $row : [];
	}

	public function get_details($id_header)
	{
		return $this->db->order_by('id', 'asc')->get_where(self::DETAIL, ['id_flow_process_header' => $id_header])->result_array();
	}

	/** Product Category aktif: [code_lv2 => nama] (urut nama). */
	public function get_categories()
	{
		$rows = $this->db->order_by('nama', 'asc')
			->get_where('product_lvl_2', ['deleted_date' => null, 'category' => 'product'])->result_array();
		$out = [];
		foreach ($rows as $r) {
			$out[$r['code_lv2']] = $r['nama'];
		}
		return $out;
	}

	/** Nama cost centre aktif (untuk dropdown Costcentre / Area). */
	public function get_costcentres()
	{
		$rows = $this->db->get_where('ms_costcenter', ['deleted_by' => null])->result_array();
		$out = [];
		foreach ($rows as $r) {
			if (!empty($r['nama_costcenter'])) {
				$out[] = $r['nama_costcenter'];
			}
		}
		sort($out);
		return $out;
	}

	/** Master asset kategori machine (category = 4 atau nm_category = MACHINE). */
	public function get_machines()
	{
		return $this->db->select('a.id, a.kd_asset, a.nm_asset')
			->from('asset a')
			->join('asset_category b', 'a.category = b.id', 'left')
			->where('a.deleted', 'N')
			->where('a.deleted_date IS NULL', null, false)
			->group_start()
				->where('a.category', 4)
				->or_like('b.nm_category', 'MACHINE')
			->group_end()
			->order_by('a.nm_asset', 'ASC')
			->get()->result_array();
	}

	public function get_username($id_user)
	{
		if (empty($id_user)) {
			return '-';
		}
		$row = $this->db->select('username')->get_where('users', ['id_user' => $id_user])->row();
		return (!empty($row)) ? $row->username : '-';
	}

	/** Simpan header + detail dalam satu transaksi. Detail lama diganti seluruhnya. Mengembalikan id header. */
	public function save_flow($id, $header, $details)
	{
		$this->db->trans_start();
		if (empty($id)) {
			$this->db->insert(self::HEADER, $header);
			$id = $this->db->insert_id();
		} else {
			$this->db->where('id', $id)->update(self::HEADER, $header);
			$this->db->where('id_flow_process_header', $id)->delete(self::DETAIL);
		}
		foreach ($details as $d) {
			$d['id_flow_process_header'] = $id;
			$this->db->insert(self::DETAIL, $d);
		}
		$this->db->trans_complete();
		return ($this->db->trans_status() === false) ? false : $id;
	}

	/** Hapus header + detail dalam satu transaksi. */
	public function delete_flow($id)
	{
		$this->db->trans_start();
		$this->db->where('id_flow_process_header', $id)->delete(self::DETAIL);
		$this->db->where('id', $id)->delete(self::HEADER);
		$this->db->trans_complete();
		return ($this->db->trans_status() !== false);
	}
}
