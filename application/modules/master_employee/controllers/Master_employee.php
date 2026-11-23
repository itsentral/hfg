<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Master_employee extends Admin_Controller
{
	//Permission
	protected $viewPermission   = "Master_Karyawan.View";
	protected $addPermission    = "Master_Karyawan.Add";
	protected $managePermission = "Master_Karyawan.Manage";
	protected $deletePermission = "Master_Karyawan.Delete";

	public function __construct()
	{
		parent::__construct();
		$this->load->model(array(
			'Master_employee/Employee_model'
		));
		$this->template->title('Master Employee');

		date_default_timezone_set("Asia/Bangkok");

		$this->id_user  = $this->auth->user_id();
		$this->datetime = date('Y-m-d H:i:s');
	}

	public function index()
	{
		$this->auth->restrict($this->viewPermission);
		$where = [
			'deleted_date' => NULL
		];
		$listData = $this->Employee_model->get_data($where);

		$data = [
			'result' =>  $listData
		];
		$this->template->set($data);
		$this->template->title('Master Employee');
		$this->template->render('index');
	}

	public function add($id = null, $tanda = null)
	{
		if (empty($id)) {
			$this->auth->restrict($this->addPermission);
		} else {
			$this->auth->restrict($this->managePermission);
		}
		if ($this->input->post()) {
			$this->_save();
		} else {

			$restHeader 	= $this->db->get_where('employee', array('id' => $id))->result();
			$bank 			= $this->db->get('bank')->result_array();
			$department		= $this->db->order_by('nama')->get_where('ms_department', array('status' => '1', 'deleted_date' => NULL))->result_array();
			$pendidikan		= $this->db->order_by('id')->get_where('list_help', array('group_by' => 'pendidikan', 'sts' => 'Y'))->result_array();
			$agama			= $this->db->order_by('id')->get_where('list_help', array('group_by' => 'agama', 'sts' => 'Y'))->result_array();
			$gender			= $this->db->order_by('id')->get_where('list_help', array('group_by' => 'gender', 'sts' => 'Y'))->result_array();
			$sts_karyawan	= $this->db->order_by('id')->get_where('list_help', array('group_by' => 'status karyawan', 'sts' => 'Y'))->result_array();
			$status			= $this->db->order_by('id')->get_where('list_help', array('group_by' => 'status aktif', 'sts' => 'Y'))->result_array();

			$data = [
				'departmentx'	=> $department,
				'pendidikanx'	=> $pendidikan,
				'bankx'			=> $bank,
				'agamax'		=> $agama,
				'genderx'		=> $gender,
				'sts_karyawanx'	=> $sts_karyawan,
				'statusx'		=> $status,
				'header' 		=> $restHeader,
				'tanda' 		=> $tanda,
			];

			$this->template->set($data);
			$this->template->title('Add Employee');
			$this->template->render('add');
		}
	}

	// ---------------------------------------------------------------
	// SIMPAN (Add / Edit) + VALIDASI
	// ---------------------------------------------------------------

	/** Nilai enum kolom employee (harus sama dengan definisi tabel) */
	private $enum_values = [
		'gender'       => ['L', 'P'],
		'agama'        => ['islam', 'protestan', 'katholik', 'hindu', 'budha', 'konghucu', 'lainnya'],
		'pendidikan'   => ['sd', 'smp', 'sma', 'diploma', 'sarjana', 'master', 'doktoral', 'etc', 'smk', 'd3', 'd4', 's1', 'smu'],
		'sts_karyawan' => ['tetap', 'kontrak', 'magang', 'training', 'lainnya'],
		'status'       => ['Y', 'N'],
	];

	/** Panjang maksimal kolom varchar employee */
	private $max_length = [
		'nm_karyawan' => 40, 'tmp_lahir' => 30, 'no_ktp' => 30, 'ktp_alamat' => 255, 'domisili_alamat' => 255,
		'no_ponsel' => 18, 'email' => 45, 'npwp' => 50, 'bpjs' => 50, 'rek_number' => 50,
	];

	const DEPT_PRODUKSI_ID = '8';
	const SUB_DEPARTMENTS  = ['Setter', 'Helper'];
	const TTD_MAX_BYTES    = 2097152; // 2 MB

	private function _save()
	{
		$id = (int) $this->input->post('id');

		// 1. Ambil & normalisasi input (trim; kosong atau placeholder '0' -> NULL)
		$fields = ['nm_karyawan', 'no_ktp', 'tmp_lahir', 'tgl_lahir', 'gender', 'agama', 'department', 'sub_department', 'pendidikan',
			'ktp_alamat', 'domisili_alamat', 'ktp_kode_pos', 'domisili_kode_pos', 'no_ponsel', 'email', 'npwp', 'bpjs',
			'bank_account', 'rek_number', 'sts_karyawan', 'status', 'tgl_join', 'tgl_end'];
		$in = [];
		foreach ($fields as $f) {
			$v = $this->input->post($f);
			$v = is_string($v) ? trim($v) : '';
			// Pemisah yang lazim diketik user dibuang dari isian nomor
			if (in_array($f, ['no_ktp', 'npwp', 'bpjs', 'rek_number'], true)) {
				$v = preg_replace('/[\s.\-]/', '', $v ?? '');
			} elseif ($f === 'no_ponsel') {
				$v = preg_replace('/[\s.\-()]/', '', $v ?? '');
			} elseif ($f === 'email') {
				$v = strtolower($v);
			} elseif ($f === 'nm_karyawan') {
				$v = preg_replace('/\s+/', ' ', $v ?? '');
			}
			$in[$f] = ($v === '' || $v === '0') ? null : $v;
		}
		if ($in['status'] === null) {
			$in['status'] = 'Y';
		}
		if ($in['department'] !== self::DEPT_PRODUKSI_ID) {
			$in['sub_department'] = null;
		}

		// 2. Validasi
		$old = null;
		if ($id > 0) {
			$old = $this->db->get_where('employee', ['id' => $id])->row_array();
			if (!$old || !empty($old['deleted_date'])) {
				return $this->_json_error('Employee data was not found or has been deleted.');
			}
		}
		$errors = $this->_validate_employee($in, $id);
		$upload = $this->_validate_ttd($errors);
		if ($errors) {
			return $this->_json_error('Please check the fields marked in red.', $errors);
		}

		// 3. Simpan file tanda tangan (hanya setelah semua validasi lolos)
		$ttd_path = null;
		if ($upload) {
			$dir = 'uploads/employee/';
			if (!is_dir(FCPATH . $dir)) {
				@mkdir(FCPATH . $dir, 0775, true);
			}
			$ttd_path = $dir . 'ttd-' . uniqid() . '-' . date('Ymdhis') . '.' . $upload['ext'];
			if (!move_uploaded_file($upload['tmp'], FCPATH . $ttd_path)) {
				return $this->_json_error('Failed to save the signature file. Please try again.', ['tanda_tangan' => 'Failed to save the file.']);
			}
		}

		$row = $in;
		if ($ttd_path) {
			$row['tanda_tangan'] = $ttd_path;
		}

		$this->db->trans_start();
		if ($id > 0) {
			$row['updated_by']   = $this->id_user;
			$row['updated_date'] = $this->datetime;
			$nik    = $old['nik'];
			$action = 'Update';
			$this->db->where('id', $id)->update('employee', $row);
		} else {
			// NIK otomatis IDyy00001; baris dikunci agar dua user tidak mendapat nomor yang sama
			$prefix = 'ID' . date('y');
			$max    = $this->db->query("SELECT MAX(nik) AS maxP FROM employee WHERE nik LIKE ? FOR UPDATE", [$prefix . '%'])->row_array();
			$nik    = $prefix . sprintf('%05d', ((int) substr((string) $max['maxP'], 4, 5)) + 1);
			$row['nik']          = $nik;
			$row['deleted']      = 'N';
			$row['created_by']   = $this->id_user;
			$row['created_date'] = $this->datetime;
			$action = 'Insert';
			$this->db->insert('employee', $row);
		}
		$this->db->trans_complete();

		$logPayload = ($id > 0) ? ['old' => $old, 'new' => array_merge($old ?? [], $row ?? [])] : $row;
		if ($this->db->trans_status() === FALSE) {
			if ($ttd_path) {
				@unlink(FCPATH . $ttd_path);
			}
			write_log('Master Employee', $action, 'Gagal ' . strtolower($action) . ' employee: ' . $nik . ' (' . $in['nm_karyawan'] . ')', $logPayload, null, 0);
			return $this->_json_error('Failed to save the data. Please try again or contact the administrator.');
		}
		write_log('Master Employee', $action, $action . ' employee: ' . $nik . ' (' . $in['nm_karyawan'] . ')', $logPayload, null, 1);
		echo json_encode(['status' => 1, 'pesan' => 'Employee data saved successfully (' . $nik . ').']);
	}

	/**
	 * @return array [nama_field => pesan]
	 */
	private function _validate_employee(array $in, $id)
	{
		$e = [];
		if ($in['nm_karyawan'] === null) {
			$e['nm_karyawan'] = 'Employee name is required.';
		}

		foreach ($this->max_length as $f => $max) {
			if ($in[$f] !== null && mb_strlen($in[$f] ?? '') > $max) {
				$e[$f] = 'Maximum ' . $max . ' characters.';
			}
		}
		foreach ($this->enum_values as $f => $allowed) {
			if ($in[$f] !== null && !in_array($in[$f], $allowed ?? [], true)) {
				$e[$f] = 'Invalid option.';
			}
		}

		if ($in['no_ktp'] !== null && !isset($e['no_ktp'])) {
			if (!preg_match('/^\d{16}$/', $in['no_ktp'] ?? '')) {
				$e['no_ktp'] = 'ID Number must be 16 digits.';
			} else {
				$dup = $this->db->select('nik, nm_karyawan')
					->where('no_ktp', $in['no_ktp'])
					->where('deleted_date IS NULL', null, false)
					->where('id !=', (int) $id)
					->get('employee')->row();
				if ($dup) {
					$e['no_ktp'] = 'ID Number is already used by employee ' . $dup->nm_karyawan . ' (' . $dup->nik . ').';
				}
			}
		}
		if ($in['npwp'] !== null && !isset($e['npwp']) && !preg_match('/^\d{15,16}$/', $in['npwp'] ?? '')) {
			$e['npwp'] = 'NPWP must be 15 or 16 digits.';
		}
		if ($in['bpjs'] !== null && !isset($e['bpjs']) && !preg_match('/^\d{8,20}$/', $in['bpjs'] ?? '')) {
			$e['bpjs'] = 'BPJS Number must be 8-20 digits.';
		}
		if ($in['rek_number'] !== null && !isset($e['rek_number']) && !preg_match('/^\d{5,30}$/', $in['rek_number'] ?? '')) {
			$e['rek_number'] = 'Account Number must be 5-30 digits.';
		}
		foreach (['ktp_kode_pos', 'domisili_kode_pos'] as $f) {
			if ($in[$f] !== null && !preg_match('/^\d{5}$/', $in[$f] ?? '')) {
				$e[$f] = 'Postcode must be 5 digits.';
			}
		}
		if ($in['no_ponsel'] !== null && !isset($e['no_ponsel']) && !preg_match('/^\+?\d{8,15}$/', $in['no_ponsel'] ?? '')) {
			$e['no_ponsel'] = 'Contact Number must be 8-15 digits (may start with +).';
		}
		if ($in['email'] !== null && !isset($e['email']) && !filter_var($in['email'], FILTER_VALIDATE_EMAIL)) {
			$e['email'] = 'Invalid email format.';
		}

		if ($in['department'] !== null) {
			$ok = $this->db->where('id', $in['department'])->where('deleted_date IS NULL', null, false)->count_all_results('ms_department');
			if (!$ok) {
				$e['department'] = 'Invalid department.';
			}
		}
		if ($in['sub_department'] !== null && !in_array($in['sub_department'], self::SUB_DEPARTMENTS, true)) {
			$e['sub_department'] = 'Invalid role.';
		}
		if ($in['bank_account'] !== null && !$this->db->where('code', $in['bank_account'])->count_all_results('bank')) {
			$e['bank_account'] = 'Invalid bank.';
		}

		foreach (['tgl_lahir', 'tgl_join', 'tgl_end'] as $f) {
			if ($in[$f] === null) {
				continue;
			}
			$d = DateTime::createFromFormat('!Y-m-d', $in[$f]);
			if (!$d || $d->format('Y-m-d') !== $in[$f] || $in[$f] < '1900-01-01') {
				$e[$f] = 'Invalid date (format YYYY-MM-DD).';
			}
		}
		if ($in['tgl_lahir'] !== null && !isset($e['tgl_lahir']) && $in['tgl_lahir'] > date('Y-m-d')) {
			$e['tgl_lahir'] = 'Date of Birth cannot be later than today.';
		}
		if ($in['tgl_join'] !== null && $in['tgl_lahir'] !== null && !isset($e['tgl_join']) && !isset($e['tgl_lahir']) && $in['tgl_join'] <= $in['tgl_lahir']) {
			$e['tgl_join'] = 'Join Date must be after Date of Birth.';
		}
		if ($in['tgl_end'] !== null && $in['tgl_join'] !== null && !isset($e['tgl_end']) && !isset($e['tgl_join']) && $in['tgl_end'] < $in['tgl_join']) {
			$e['tgl_end'] = 'End Date cannot be before Join Date.';
		}

		return $e;
	}

	/**
	 * Validasi file tanda tangan (opsional). Pesan error ditambahkan ke $errors.
	 *
	 * @return array|null ['tmp' => path, 'ext' => jpg|png], atau null bila tidak ada file / tidak valid
	 */
	private function _validate_ttd(array &$errors)
	{
		if (empty($_FILES['tanda_tangan']) || (int) $_FILES['tanda_tangan']['error'] === UPLOAD_ERR_NO_FILE) {
			return null;
		}
		$file = $_FILES['tanda_tangan'];
		if ((int) $file['error'] !== UPLOAD_ERR_OK) {
			$errors['tanda_tangan'] = in_array((int) $file['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)
				? 'File size is too large (maximum 2 MB).'
				: 'File upload failed. Please choose the file again.';
			return null;
		}
		if ($file['size'] > self::TTD_MAX_BYTES) {
			$errors['tanda_tangan'] = 'File size is too large (maximum 2 MB).';
			return null;
		}
		$mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
		$map  = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
		if (!isset($map[$mime]) || @getimagesize($file['tmp_name']) === false) {
			$errors['tanda_tangan'] = 'File must be a JPG/JPEG/PNG image.';
			return null;
		}
		return ['tmp' => $file['tmp_name'], 'ext' => $map[$mime]];
	}

	private function _json_error($pesan, array $errors = [])
	{
		echo json_encode(['status' => 0, 'pesan' => $pesan, 'errors' => (object) $errors]);
	}

	public function delete()
	{
		$this->auth->restrict($this->deletePermission);

		$id = $this->input->post('id');
		$data = [
			'deleted'      => 'Y',
			'deleted_by'   => $this->id_user,
			'deleted_date' => $this->datetime
		];

		$this->db->trans_begin();
		$this->db->where('id', $id)->update("employee", $data);

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$status	= array(
				'pesan'		=> 'Failed process data!',
				'status'	=> 0
			);
			write_log('Master Employee', 'Delete', 'Gagal delete employee ID: ' . $id, ['id' => $id], null, 0);
		} else {
			$this->db->trans_commit();
			$status	= array(
				'pesan'		=> 'Success process data!',
				'status'	=> 1
			);
			write_log('Master Employee', 'Delete', 'Delete employee ID: ' . $id, ['id' => $id, 'data' => $data], null, 1);
		}
		echo json_encode($status);
	}
}
