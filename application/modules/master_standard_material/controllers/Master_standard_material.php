<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Master Standard Material (FR-50, UC-N15, PG-08)
 *
 * Satu set nilai GLOBAL (satu baris, id = 1) yang dipakai menghitung Berat/Meter di BOM:
 *   ((Tebal Material - Standar Toleransi) x Lebar Coil x Standar Berat Jenis) + Coating
 * Satuan tidak ditampilkan karena klien tidak menginformasikannya.
 * Nama kolom tabel mengikuti ERD (halaman "Product Costing ERD", entitas master_standard_material).
 */
class Master_standard_material extends Admin_Controller
{
	//Permission
	protected $viewPermission   = 'Master_Standard_Material.View';
	protected $addPermission    = 'Master_Standard_Material.Add';
	protected $managePermission = 'Master_Standard_Material.Manage';

	public function __construct()
	{
		parent::__construct();

		$this->load->model(array(
			'Master_standard_material/Master_standard_material_model'
		));
		$this->template->title('Master Standard Material');
		$this->template->page_icon('fa fa-sliders');

		date_default_timezone_set('Asia/Bangkok');

		$this->id_user  = $this->auth->user_id();
		$this->datetime = date('Y-m-d H:i:s');
	}

	public function index()
	{
		$this->auth->restrict($this->viewPermission);

		$standard = $this->Master_standard_material_model->get_standard();

		$data = [
			'standard'   => $standard,
			'updated_by' => (!empty($standard['updated_by'])) ? get_name('users', 'username', 'id_user', $standard['updated_by']) : '-',
		];
		$this->template->set($data);
		$this->template->title('Master Standard Material');
		$this->template->render('index');
	}

	public function save()
	{
		$old = $this->Master_standard_material_model->get_standard();

		// Baris global belum ada -> membuat (Add); sudah ada -> mengubah (Manage)
		if (empty($old)) {
			$this->auth->restrict($this->addPermission);
		} else {
			$this->auth->restrict($this->managePermission);
		}

		if (!$this->input->post()) {
			echo json_encode(['pesan' => 'Tidak ada data yang dikirim.', 'status' => 0]);
			return;
		}

		$fields = [
			'standar_toleransi'   => 'Standar Toleransi',
			'standar_berat_jenis' => 'Standar Berat Jenis',
			'coating'             => 'Coating',
		];

		// Validasi server: angka >= 0, titik sebagai pemisah desimal (maks. 8 digit bulat dan 6 desimal = DECIMAL(14,6)).
		// Koma sengaja ditolak (bukan dihapus) agar "7,85" tidak berubah menjadi 785.
		$errors = [];
		$values = [];
		foreach ($fields as $field => $label) {
			$nilai = trim((string) $this->input->post($field));
			if ($nilai === '') {
				$errors[] = $label . ' wajib diisi.';
			} elseif (!preg_match('/^\d{1,8}(\.\d{1,6})?$/', $nilai)) {
				$errors[] = $label . ' harus berupa angka 0 atau lebih (gunakan titik untuk desimal, maksimal 8 digit bulat dan 6 digit desimal).';
			} else {
				$values[$field] = $nilai;
			}
		}
		if (!empty($errors)) {
			echo json_encode(['pesan' => implode(' ', $errors), 'status' => 0]);
			return;
		}

		$dataProcess = $values + [
			'latest_update' => $this->datetime,
			'updated_by'    => $this->id_user,
		];

		$label = (!empty($old)) ? 'Edit' : 'Add';

		$this->db->trans_start();
		$this->Master_standard_material_model->save_standard($dataProcess, !empty($old));
		$this->db->trans_complete();

		// Before-After: old diambil SEBELUM update; new = old digabung perubahan
		$logPayload = (!empty($old)) ? ['old' => $old, 'new' => array_merge($old, $dataProcess)] : $dataProcess;

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$result = [
				'pesan'  => 'Failed process data!',
				'status' => 0,
			];
			write_log('Master Standard Material', $label, 'Gagal ' . strtolower($label) . ' master standard material', $logPayload, null, 0);
		} else {
			$this->db->trans_commit();
			$result = [
				'pesan'  => 'Success process data!',
				'status' => 1,
			];
			write_log('Master Standard Material', $label, $label . ' master standard material (toleransi ' . $values['standar_toleransi'] . ', berat jenis ' . $values['standar_berat_jenis'] . ', coating ' . $values['coating'] . ')', $logPayload, null, 1);
		}

		echo json_encode($result);
	}
}
