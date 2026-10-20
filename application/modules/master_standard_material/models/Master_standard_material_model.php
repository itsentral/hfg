<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Model Master Standard Material. Tabel: master_standard_material (satu baris global, id = 1).
 * Kolom mengikuti ERD: id, standar_toleransi, standar_berat_jenis, coating, latest_update, updated_by.
 */
class Master_standard_material_model extends CI_Model
{
	const TABEL     = 'master_standard_material';
	const ID_GLOBAL = 1;

	public function __construct()
	{
		parent::__construct();
	}

	/** Baris global (array) atau array kosong bila belum ada. */
	public function get_standard()
	{
		$row = $this->db->get_where(self::TABEL, ['id' => self::ID_GLOBAL])->row_array();
		return (!empty($row)) ? $row : [];
	}

	/** Simpan baris global: update bila sudah ada, insert dengan id = 1 bila belum ada. */
	public function save_standard($data, $sudah_ada)
	{
		if ($sudah_ada) {
			$this->db->where('id', self::ID_GLOBAL)->update(self::TABEL, $data);
		} else {
			$this->db->insert(self::TABEL, ['id' => self::ID_GLOBAL] + $data);
		}
	}
}
