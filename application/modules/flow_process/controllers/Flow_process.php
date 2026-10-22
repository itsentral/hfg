<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Flow Process & Cycletime (FR-42, UC-N10, PG-10)
 *
 * Satu flow per Product Category (code_lv2) berisi daftar proses produksi. Per proses:
 *   Man Hour (jam/meter) = Cycletime (menit/meter) x Man Power / 60
 * Header menyimpan jumlah cycletime dan Total Man Hour semua proses. Semua nilai turunan dihitung di
 * SERVER; nilai yang dikirim klien untuk Man Hour tidak dipercaya.
 * Nama tabel dan kolom mengikuti ERD (flow_process_header, flow_process_detail).
 */
class Flow_process extends Admin_Controller
{
	//Permission
	protected $viewPermission   = 'Flow_Process.View';
	protected $addPermission    = 'Flow_Process.Add';
	protected $managePermission = 'Flow_Process.Manage';
	protected $deletePermission = 'Flow_Process.Delete';

	public function __construct()
	{
		parent::__construct();

		$this->load->model(array(
			'Flow_process/Flow_process_model'
		));
		$this->template->title('Flow Process & Cycletime');
		$this->template->page_icon('fa fa-sitemap');

		date_default_timezone_set('Asia/Bangkok');

		$this->id_user  = $this->auth->user_id();
		$this->datetime = date('Y-m-d H:i:s');
	}

	public function index()
	{
		$this->auth->restrict($this->viewPermission);

		$list       = $this->Flow_process_model->get_list();
		$categories = $this->Flow_process_model->get_categories();

		$users = [];
		foreach ($list as $row) {
			if (!isset($users[$row['updated_by']])) {
				$users[$row['updated_by']] = $this->Flow_process_model->get_username($row['updated_by']);
			}
		}

		$data = [
			'list'       => $list,
			'categories' => $categories,
			'users'      => $users,
		];
		$this->template->set($data);
		$this->template->title('Flow Process & Cycletime');
		$this->template->render('index');
	}

	/**
	 * Isi modal (HTML parsial). $id kosong = tambah; $mode 'view' = hanya lihat.
	 */
	public function form($id = null, $mode = null)
	{
		if ($mode === 'view') {
			$this->auth->restrict($this->viewPermission);
		} elseif (empty($id)) {
			$this->auth->restrict($this->addPermission);
		} else {
			$this->auth->restrict($this->managePermission);
		}

		$header  = (!empty($id)) ? $this->Flow_process_model->get_header($id) : [];
		$details = (!empty($header)) ? $this->Flow_process_model->get_details($id) : [];
		$categories = $this->Flow_process_model->get_categories();

		// Kategori yang sudah punya flow tidak ditawarkan lagi saat tambah
		$tersedia = $categories;
		foreach ($this->Flow_process_model->get_list() as $row) {
			unset($tersedia[$row['code_lv2']]);
		}

		$data = [
			'header'       => $header,
			'details'      => $details,
			'categories'   => $categories,
			'tersedia'     => $tersedia,
			'costcentres'  => $this->Flow_process_model->get_costcentres(),
			'mode'         => ($mode === 'view') ? 'view' : ((empty($id)) ? 'add' : 'edit'),
		];
		$this->load->view('flow_process/form', $data);
	}

	public function save()
	{
		$id = $this->input->post('id');

		if (empty($id)) {
			$this->auth->restrict($this->addPermission);
		} else {
			$this->auth->restrict($this->managePermission);
		}

		if (!$this->input->post()) {
			echo json_encode(['pesan' => 'Tidak ada data yang dikirim.', 'status' => 0]);
			return;
		}

		$categories = $this->Flow_process_model->get_categories();

		// ---- Header: Product Category
		$oldHeader  = [];
		$oldDetails = [];
		if (!empty($id)) {
			$oldHeader = $this->Flow_process_model->get_header($id);
			if (empty($oldHeader)) {
				echo json_encode(['pesan' => 'Data flow process tidak ditemukan.', 'status' => 0]);
				return;
			}
			$oldDetails = $this->Flow_process_model->get_details($id);
			$code_lv2   = $oldHeader['code_lv2']; // Product Category tidak dapat diganti saat ubah
		} else {
			$code_lv2 = trim((string) $this->input->post('code_lv2'));
		}

		$errors = [];
		if ($code_lv2 === '' || $code_lv2 === '0') {
			$errors[] = 'Product Category wajib dipilih.';
		} elseif (empty($id) && !isset($categories[$code_lv2])) {
			$errors[] = 'Product Category tidak ditemukan.';
		} elseif (empty($id) && !empty($this->Flow_process_model->get_header_by_code($code_lv2))) {
			$errors[] = 'Product Category ini sudah memiliki Flow Process. Gunakan Edit.';
		}

		// ---- Detail: daftar proses
		$areas    = (array) $this->input->post('costcentre_area');
		$names    = (array) $this->input->post('process_name');
		$machines = (array) $this->input->post('machine');
		$cts      = (array) $this->input->post('cycletime');
		$mps      = (array) $this->input->post('man_power');

		$details       = [];
		$sum_cycletime = 0.0;
		$sum_man_hour  = 0.0;
		$jumlah        = max(count($areas), count($names), count($machines), count($cts), count($mps));
		for ($i = 0; $i < $jumlah; $i++) {
			$area    = isset($areas[$i]) ? trim((string) $areas[$i]) : '';
			$name    = isset($names[$i]) ? trim((string) $names[$i]) : '';
			$machine = isset($machines[$i]) ? trim((string) $machines[$i]) : '';
			$ct_txt  = isset($cts[$i]) ? trim((string) $cts[$i]) : '';
			$mp_txt  = isset($mps[$i]) ? trim((string) $mps[$i]) : '';

			// Baris yang sepenuhnya kosong diabaikan
			if ($area === '' && $name === '' && $machine === '' && $ct_txt === '' && $mp_txt === '') {
				continue;
			}
			$no = count($details) + 1;

			if ($name === '') {
				$errors[] = 'Proses #' . $no . ': Process Name wajib diisi.';
			}
			if (strlen($name) > 150 || strlen($area) > 150 || strlen($machine) > 150) {
				$errors[] = 'Proses #' . $no . ': Costcentre/Area, Process Name, dan Machine maksimal 150 karakter.';
			}
			// Titik sebagai pemisah desimal; koma ditolak (bukan dihapus) agar "0,5" tidak berubah menjadi 5
			if (!preg_match('/^\d{1,8}(\.\d{1,6})?$/', $ct_txt)) {
				$errors[] = 'Proses #' . $no . ': Cycletime harus berupa angka 0 atau lebih (gunakan titik untuk desimal, maksimal 6 digit desimal).';
			}
			if (!preg_match('/^\d{1,4}$/', $mp_txt)) {
				$errors[] = 'Proses #' . $no . ': Man Power harus berupa bilangan bulat 0 atau lebih.';
			}

			$ct = (float) $ct_txt;
			$mp = (int) $mp_txt;
			$mh = round($ct * $mp / 60, 8);   // jam/meter, dihitung server

			$details[] = [
				'costcentre_area' => ($area === '') ? null : $area,
				'process_name'    => $name,
				'machine'         => ($machine === '') ? null : $machine,
				'cycletime'       => sprintf('%.6f', $ct),
				'man_power'       => $mp,
				'total_man_hour'  => sprintf('%.8f', $mh),
			];
			$sum_cycletime += $ct;
			$sum_man_hour  += $mh;
		}
		if (empty($details)) {
			$errors[] = 'Minimal satu proses harus diisi.';
		}

		if (!empty($errors)) {
			echo json_encode(['pesan' => implode(' ', $errors), 'status' => 0]);
			return;
		}

		$header = [
			'code_lv2'       => $code_lv2,
			'cycletime'      => sprintf('%.6f', round($sum_cycletime, 6)),
			'total_man_hour' => sprintf('%.8f', round($sum_man_hour, 8)),
			'latest_update'  => $this->datetime,
			'updated_by'     => $this->id_user,
		];

		$label = (!empty($id)) ? 'Edit' : 'Add';
		$nama_kategori = isset($categories[$code_lv2]) ? $categories[$code_lv2] : $code_lv2;

		$hasil = $this->Flow_process_model->save_flow($id, $header, $details);

		// Before-After: old diambil SEBELUM update; new = old digabung perubahan; detail_changes per baris proses
		if (!empty($id)) {
			$logPayload = [
				'old'            => $oldHeader,
				'new'            => array_merge($oldHeader, $header),
				'detail_changes' => $this->build_detail_changes($oldDetails, $details),
			];
		} else {
			$logPayload = $header + ['detail' => $details];
		}

		if ($hasil === false) {
			$result = ['pesan' => 'Failed process data!', 'status' => 0];
			write_log('Flow Process', $label, 'Gagal ' . strtolower($label) . ' flow process: ' . $nama_kategori, $logPayload, null, 0);
		} else {
			$result = ['pesan' => 'Success process data!', 'status' => 1];
			write_log('Flow Process', $label, $label . ' flow process: ' . $nama_kategori . ' (' . count($details) . ' proses, total man hour ' . $header['total_man_hour'] . ')', $logPayload, null, 1);
		}

		echo json_encode($result);
	}

	public function delete()
	{
		$this->auth->restrict($this->deletePermission);

		$id     = $this->input->post('id');
		$header = (!empty($id)) ? $this->Flow_process_model->get_header($id) : [];
		if (empty($header)) {
			echo json_encode(['pesan' => 'Data flow process tidak ditemukan.', 'status' => 0]);
			return;
		}
		$details    = $this->Flow_process_model->get_details($id);
		$categories = $this->Flow_process_model->get_categories();
		$nama       = isset($categories[$header['code_lv2']]) ? $categories[$header['code_lv2']] : $header['code_lv2'];
		$snapshot   = $header + ['detail' => $details]; // snapshot lengkap sebelum dihapus

		if ($this->Flow_process_model->delete_flow($id)) {
			$result = ['pesan' => 'Success process data!', 'status' => 1];
			write_log('Flow Process', 'Delete', 'Hapus flow process: ' . $nama . ' (' . count($details) . ' proses)', $snapshot, null, 1);
		} else {
			$result = ['pesan' => 'Failed process data!', 'status' => 0];
			write_log('Flow Process', 'Delete', 'Gagal hapus flow process: ' . $nama, $snapshot, null, 0);
		}
		echo json_encode($result);
	}

	/**
	 * Perubahan per baris proses untuk log (urutan baris dibandingkan satu per satu).
	 * Format: ['Proses [nama] - Field' => ['old' => ..., 'new' => ...]]
	 */
	private function build_detail_changes($oldDetails, $newDetails)
	{
		$fields  = ['costcentre_area', 'process_name', 'machine', 'cycletime', 'man_power', 'total_man_hour'];
		$numeric = ['cycletime', 'man_power', 'total_man_hour'];
		$changes = [];
		$jumlah  = max(count($oldDetails), count($newDetails));
		for ($i = 0; $i < $jumlah; $i++) {
			$old = isset($oldDetails[$i]) ? $oldDetails[$i] : null;
			$new = isset($newDetails[$i]) ? $newDetails[$i] : null;

			if ($old === null) {
				$changes['Proses [' . $new['process_name'] . '] - ditambahkan'] = [
					'old' => null,
					'new' => $new['process_name'] . ' | cycletime ' . $new['cycletime'] . ' | man power ' . $new['man_power'],
				];
				continue;
			}
			if ($new === null) {
				$changes['Proses [' . $old['process_name'] . '] - dihapus'] = [
					'old' => $old['process_name'] . ' | cycletime ' . $old['cycletime'] . ' | man power ' . $old['man_power'],
					'new' => null,
				];
				continue;
			}
			foreach ($fields as $f) {
				$a = $old[$f];
				$b = $new[$f];
				$sama = in_array($f, $numeric)
					? (abs((float) $a - (float) $b) < 1e-9)
					: ((string) $a === (string) $b);
				if (!$sama) {
					$changes['Proses [' . $old['process_name'] . '] - ' . $f] = ['old' => $a, 'new' => $b];
				}
			}
		}
		return $changes;
	}
}
