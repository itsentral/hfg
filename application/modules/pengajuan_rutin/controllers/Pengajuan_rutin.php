<?php

if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

/*
 * @author Harboens
 * @copyright Copyright (c) 2020
 *
 * This is controller for Pengajuan Rutin
 */

$status = array();
$waktu = array();
class Pengajuan_rutin extends Admin_Controller
{
	//Permission
	protected $viewPermission 	= 'Pengajuan_Pembayaran_Rutin.View';
	protected $addPermission  	= 'Pengajuan_Pembayaran_Rutin.Add';
	protected $managePermission = 'Pengajuan_Pembayaran_Rutin.Manage';
	protected $deletePermission = 'Pengajuan_Pembayaran_Rutin.Delete';

	protected $waktu;

	public function __construct()
	{
		parent::__construct();
		$this->load->model(array('All/All_model', 'Pengajuan_rutin/Pengajuan_rutin_model'));
		$this->template->title('Master Pembayaran Rutin');
		$this->template->page_icon('fa fa-cubes');
		date_default_timezone_set('Asia/Bangkok');
		$this->waktu = array("bulan" => "bulan", "tahun" => "tahun");
	}

	public function index()
	{
		//        $this->auth->restrict($this->viewPermission);
		$departemen = '';
		$datauser = $this->All_model->GetInfoUser($this->auth->user_id());
		//		if($datauser) $departemen=$datauser->department_id;
		// $data = $this->Pengajuan_rutin_model->GetPengajuanRutin(array('a.created_by' => $this->auth->user_id()));

		// $this->db->select('a.*, IF(SUM(b.nilai) IS NULL, 0, SUM(b.nilai)) as nilai_total');
		// $this->db->from('tr_pengajuan_rutin a');
		// $this->db->join('tr_pengajuan_rutin_detail b', 'b.no_doc = a.no_doc', 'left');
		// // $this->db->where('a.created_by', $this->auth->user_id());
		// $this->db->group_by('a.no_doc');
		// $data = $this->db->get()->result();

		$get_departments = $this->db->get_where('ms_department', ['deleted_by' => null])->result();

		$arr_dept = [];
		foreach ($get_departments as $item) {
			$arr_dept[$item->id] = [
				'id' => $item->id,
				'nm_dept' => $item->nama,
				'nm_comp' => 'PT HARAPAN FAJAR GEMILANG'
			];
		}

		$datdept  = $this->All_model->GetDeptCombo($departemen);

		$data_detail = $this->Pengajuan_rutin_model->GetDataPengajuanRutinAll();
		$this->template->set('datdept', $datdept);
		// $this->template->set('results', $data);
		$this->template->set('dept', $arr_dept);
		$this->template->set('data_detail', $data_detail);
		$this->template->title('Pengajuan Pembayaran Periodik');
		$this->template->render('list');
	}

	public function app_list()
	{
		$departemen = '';
		//		$datauser=$this->All_model->GetInfoUser($this->auth->user_id());
		//		if($datauser) $departemen=$datauser->departemen;
		$data = $this->Pengajuan_rutin_model->GetPengajuanRutin(array('a.status' => 0, 'a.sts_reject' => null));
		$datdept  = $this->All_model->GetDeptCombo();

		$arr_dept = [];
		$get_dept = $this->db->get_where('ms_department', ['deleted_by' => null])->result();

		foreach ($get_dept as $item_dept) {
			$arr_dept[$item_dept->id] = $item_dept->nama;
		}

		$this->template->set('arr_dept', $arr_dept);
		$this->template->set('datdept', $datdept);
		$this->template->set('results', $data);
		$this->template->title('Pengajuan Pembayaran Periodik');
		$this->template->render('app_list');
	}

	public function create($key)
	{
		$this->auth->restrict($this->addPermission);
		$datdept  = $this->All_model->GetDeptCombo($key);
		$this->template->set('datdept', $datdept);
		$this->template->title('Input Pengajuan Pembayaran Periodik');
		$this->template->set('type', 'add');
		$this->template->set('app', '');
		if ($this->input->is_ajax_request()) {
			$this->template->set_layout(FALSE);
		}
		$this->template->render('input_form');
	}

	public function edit($id)
	{
		$data	= $this->Pengajuan_rutin_model->GetDataPengajuanRutin($id);
		if (!$data) {
			$this->template->set_message("Invalid Data", 'error');
			redirect('pengajuan_rutin');
		}
		$datdept  = $this->All_model->GetDeptCombo($data->departement);
		$data_detail = $this->Pengajuan_rutin_model->GetDataPengajuanRutinDetail($data->no_doc);
		$this->template->set('type', 'edit');
		$this->template->set('datdept', $datdept);
		$this->template->set('data', $data);
		$this->template->set('app', '');
		$this->template->set('data_detail', $data_detail);
		$this->template->title('Edit Pengajuan Pembayaran Rutin');
		if ($this->input->is_ajax_request()) {
			$this->template->set_layout(FALSE);
		}
		$this->template->render('input_form');
	}

	public function view($id, $app = '')
	{
		$data	= $this->Pengajuan_rutin_model->GetDataPengajuanRutin($id);
		if (!$data) {
			$this->template->set_message("Invalid Data", 'error');
			redirect('pengajuan_rutin');
		}
		$datdept  = $this->All_model->GetDeptCombo($data->departement);
		$data_detail = $this->Pengajuan_rutin_model->GetDataPengajuanRutinDetail($data->no_doc);
		$this->template->set('type', 'view');
		$this->template->set('datdept', $datdept);
		$this->template->set('data', $data);
		$this->template->set('app', $app);
		$this->template->set('data_detail', $data_detail);
		$this->template->title('View Pengajuan Pembayaran Rutin');
		if ($this->input->is_ajax_request()) {
			$this->template->set_layout(FALSE);
		}
		$this->template->render('input_form');
	}

	public function get_data()
	{
		$allbudget		= $this->input->post("allbudget");
		$dept       	= $this->input->post("dept");
		$tanggal           = $this->input->post("tanggal");
		$data = $this->Pengajuan_rutin_model->GetDataBudgetRutin($dept, $tanggal, $allbudget);
		$param = array(
			'save' => 1,
			'data' => $data,
			'tahun' => date("Y", strtotime($tanggal)),
			'bulan' => date("m", strtotime($tanggal)),
		);
		echo json_encode($param);
	}

	public function save_data()
	{

		$departement	= $this->input->post("departement");
		$id				= $this->input->post("id");
		$no_doc			= $this->input->post("no_doc");
		$tanggal_doc	= $this->input->post("tanggal_doc");
		// $tanggal_doc    = str_replace('/','-',$tanggal_doc);
		$tanggal_doc    = date('Y-m-d', strtotime($tanggal_doc));
		// print_r($tanggal_doc);
		// exit;

		$detail_id		= $this->input->post("detail_id");
		$id_budget		= $this->input->post("id_budget");
		$coa       		= $this->input->post("coa");
		$nama           = $this->input->post("nama");
		$tanggal		= $this->input->post("tanggal");
		$tipe  			= 'rutin';
		$details			= $this->input->post("details");
		$budget			= $this->input->post("budget");
		$nilai			= $this->input->post("nilai");
		$keterangan		= $this->input->post("keterangan");
		$bank_id		= $this->input->post("bank_id");
		$accnumber		= $this->input->post("accnumber");
		$accname		= $this->input->post("accname");
		$metode_pembelian		= $this->input->post("metode_pembelian");

		$this->db->trans_begin();

		try {
			if ($no_doc == '') {
				$no_doc = $this->All_model->GetAutoGenerate('format_nonpo');
				$dataheader =  array(
					'tipe' => $tipe,
					'no_doc' => $no_doc,
					'tanggal_doc' => $tanggal_doc,
					'departement' => $departement
					// 'nilai'=>0,
				);
				$this->Pengajuan_rutin_model->insert($dataheader);
			} else {
				$dataheader =  array(
					array(
						'id' => $id,
						'tanggal_doc' => $tanggal_doc,
						'sts_reject' => null,
						'sts' => 0,
						'reject_ket' => null
					)
				);
				$this->Pengajuan_rutin_model->update_batch($dataheader, 'id');
				if (is_array($detail_id)) {
					$delid = implode("','", $detail_id);
					$this->All_model->dataDelete('tr_pengajuan_rutin_detail', " id not in ('" . $delid . "') and no_doc='" . $no_doc . "'");
				} else {
					$this->All_model->dataDelete('tr_pengajuan_rutin_detail', "no_doc='" . $no_doc . "'");
				}
			}
			for ($x = 0; $x < count($detail_id); $x++) {
				$idf = $details[$x];
				if ($detail_id[$x] != '') {
					if ($nilai[$x] > 0) {
						$data = array(
							'id_budget' => $id_budget[$x],
							'coa' => $coa[$x],
							'nama' => $nama[$x],
							'tanggal' => $tanggal[$x],
							'budget' => $budget[$x],
							'nilai' => $nilai[$x],
							'keterangan' => $keterangan[$x],
							'bank_id' => $bank_id[$x],
							'accnumber' => $accnumber[$x],
							'accname' => $accname[$x],
							'metode_pembelian' => '1',
							'created_by' => $this->auth->user_id(),
							'created_on' => date("Y-m-d h:i:s"),
						);
						if (!empty($_FILES['doc_file_' . $idf]['name'])) {
							$_FILES['file']['name'] = $_FILES['doc_file_' . $idf]['name'];
							$_FILES['file']['type'] = $_FILES['doc_file_' . $idf]['type'];
							$_FILES['file']['tmp_name'] = $_FILES['doc_file_' . $idf]['tmp_name'];
							$_FILES['file']['error'] = $_FILES['doc_file_' . $idf]['error'];
							$_FILES['file']['size'] = $_FILES['doc_file_' . $idf]['size'];
							$config['upload_path'] = './assets/bayar_rutin/';
							$config['allowed_types'] = '*';
							$config['remove_spaces'] = TRUE;
							$config['encrypt_name'] = TRUE;

							$this->upload->initialize($config);
							if ($this->upload->do_upload('file')) {
								$uploadData = $this->upload->data();
								$filename = $uploadData['file_name'];
								$data['doc_file'] = $filename;
							} else {
								print_r($this->upload->display_errors());
								exit;
							}
						}
						$this->db->update('tr_pengajuan_rutin_detail', $data, array('id' => $detail_id[$x]));
					}
				} else {
					if ($nilai[$x] > 0) {
						$data =  array(
							'no_doc' => $no_doc,
							'id_budget' => $id_budget[$x],
							'coa' => $coa[$x],
							'nama' => $nama[$x],
							'tanggal' => $tanggal[$x],
							'budget' => $budget[$x],
							'nilai' => $nilai[$x],
							'keterangan' => $keterangan[$x],
							'bank_id' => $bank_id[$x],
							'accnumber' => $accnumber[$x],
							'accname' => $accname[$x],
							'metode_pembelian' => '1',
							'created_by' => $this->auth->user_id(),
							'created_on' => date("Y-m-d h:i:s"),
							'modified_by' => $this->auth->user_id(),
							'modified_on' => date("Y-m-d h:i:s"),
						);
						if (!empty($_FILES['doc_file_' . $idf]['name'])) {
							$_FILES['file']['name'] = $_FILES['doc_file_' . $idf]['name'];
							$_FILES['file']['type'] = $_FILES['doc_file_' . $idf]['type'];
							$_FILES['file']['tmp_name'] = $_FILES['doc_file_' . $idf]['tmp_name'];
							$_FILES['file']['error'] = $_FILES['doc_file_' . $idf]['error'];
							$_FILES['file']['size'] = $_FILES['doc_file_' . $idf]['size'];

							$config['upload_path'] = './assets/bayar_rutin/';
							$config['allowed_types'] = '*';
							$config['remove_spaces'] = TRUE;
							$config['encrypt_name'] = TRUE;

							$this->upload->initialize($config);
							if ($this->upload->do_upload('file')) {
								$uploadData = $this->upload->data();
								$filename = $uploadData['file_name'];
								$data['doc_file'] = $filename;
							} else {
								print_r($this->upload->display_errors());
								exit;
							}
						}
						$this->db->insert('tr_pengajuan_rutin_detail', $data);
					}
				}
			}

			$this->db->trans_commit();

			$this->output->set_status_header(200);
			echo json_encode([
				'save' => TRUE
			]);
		} catch (Exception $e) {
			$this->db->trans_rollback();

			$this->output->set_status_header(500);
			echo json_encode([
				'msg' => $e->getMessage()
			]);
		}

		// if ($this->db->trans_status()) {
		// 	$keterangan     = "SUKSES, tambah data ";
		// 	$status         = 1;
		// 	$nm_hak_akses   = $this->addPermission;
		// 	$kode_universal = 'NewData';
		// 	$jumlah         = $x;
		// 	$sql            = $this->db->last_query();
		// 	$result         = TRUE;
		// } else {
		// 	$keterangan     = "GAGAL, tambah data ";
		// 	$status         = 0;
		// 	$nm_hak_akses   = $this->addPermission;
		// 	$kode_universal = 'NewData';
		// 	$jumlah         = $x;
		// 	$sql            = $this->db->last_query();
		// 	$result = FALSE;
		// }
		// simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
		// $this->db->trans_complete();
		// $param = array(
		// 	'save' => $result
		// );
		// echo json_encode($param);
	}

	function hapus_data($id)
	{
		$this->auth->restrict($this->deletePermission);
		if ($id != '') {
			$this->db->trans_begin();
			$this->All_model->dataDelete('tr_pengajuan_rutin', array('no_doc' => $id));
			$this->All_model->dataDelete('tr_pengajuan_rutin_detail', array('no_doc' => $id));
			$result = $this->db->trans_status();
			$this->db->trans_complete();
			$keterangan     = "SUKSES, Delete data  ";
			$status         = 1;
			$nm_hak_akses   = $this->deletePermission;
			$kode_universal = $id;
			$jumlah = 1;
			$sql            = $this->db->last_query();
		} else {
			$result = 0;
			$keterangan     = "GAGAL, Delete data  ";
			$status         = 0;
			$nm_hak_akses   = $this->deletePermission;
			$kode_universal = $id;
			$jumlah = 1;
			$sql            = $this->db->last_query();
		}
		simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
		$param = array(
			'delete' => $result,
			'idx' => $id
		);
		echo json_encode($param);
	}

	public function get_data_app_list()
	{
		$post   = $this->input->post();
		if (empty($post)) {
			$post = $this->input->get();
		}
		$draw   = isset($post['draw'])   ? (int)$post['draw']   : 1;
		$length = isset($post['length']) ? (int)$post['length'] : 10;
		$start  = isset($post['start'])  ? (int)$post['start']  : 0;
		$search = isset($post['search']['value']) ? trim($post['search']['value']) : '';

		$column_order = [
			0 => '',
			1 => 'a.no_doc',
			2 => 'a.departement',
			3 => 'a.tanggal_doc',
			4 => 'total_nilai',
			5 => 'a.status'
		];

		// Count total
		$this->db->select('COUNT(DISTINCT a.id) as cnt');
		$this->db->from('tr_pengajuan_rutin a');
		$this->db->where('a.status', 0);
		$this->db->where('a.sts_reject', null);
		$count_all = (int)$this->db->get()->row()->cnt;

		// Count filtered
		$this->db->select('COUNT(DISTINCT a.id) as cnt');
		$this->db->from('tr_pengajuan_rutin a');
		$this->db->where('a.status', 0);
		$this->db->where('a.sts_reject', null);
		if ($search !== '') {
			$this->db->group_start();
			$this->db->like('a.no_doc', $search);
			$this->db->or_like('a.tanggal_doc', $search);
			$this->db->group_end();
		}
		$count_filtered = (int)$this->db->get()->row()->cnt;

		// Actual rows
		$this->db->select('a.*, IFNULL(SUM(b.nilai), a.nilai_total) as total_nilai');
		$this->db->from('tr_pengajuan_rutin a');
		$this->db->join('tr_pengajuan_rutin_detail b', 'b.no_doc = a.no_doc', 'left');
		$this->db->where('a.status', 0);
		$this->db->where('a.sts_reject', null);
		if ($search !== '') {
			$this->db->group_start();
			$this->db->like('a.no_doc', $search);
			$this->db->or_like('a.tanggal_doc', $search);
			$this->db->group_end();
		}
		$this->db->group_by('a.id');

		if (isset($post['order'][0]['column']) && isset($column_order[$post['order'][0]['column']]) && !empty($column_order[$post['order'][0]['column']])) {
			$col_idx = (int)$post['order'][0]['column'];
			$col_dir = strtolower($post['order'][0]['dir']) === 'asc' ? 'ASC' : 'DESC';
			$this->db->order_by($column_order[$col_idx], $col_dir);
		} else {
			$this->db->order_by('a.no_doc', 'desc');
		}

		$this->db->limit($length > 0 ? $length : 10, $start);
		$rows = $this->db->get()->result();

		$get_dept = $this->db->get_where('ms_department', ['deleted_by' => null])->result();
		$arr_dept = [];
		foreach ($get_dept as $d) {
			$arr_dept[$d->id] = $d->nama;
		}

		$ENABLE_VIEW   = has_permission('Approval_Pengajuan_Pembayaran_Rutin.View');
		$ENABLE_MANAGE = has_permission('Approval_Pengajuan_Pembayaran_Rutin.Manage');

		$data = [];
		$no = $start + 1;
		foreach ($rows as $record) {
			$nm_department = isset($arr_dept[$record->departement]) ? $arr_dept[$record->departement] : '-';
			$action = '<div class="btn-group btn-group-sm" role="group">';
			if ($ENABLE_VIEW) {
				$action .= '<a class="btn btn-outline-info btn-sm view" href="javascript:void(0)" title="Lihat Detail" data-id="' . $record->id . '"><i class="fa fa-eye"></i></a> ';
			}
			if ($record->status == 0 && $ENABLE_MANAGE) {
				$action .= '<a class="btn btn-success btn-sm approve" href="javascript:void(0)" title="Persetujuan / Approve" data-id="' . $record->id . '"><i class="fa fa-check"></i></a>';
			}
			$action .= '</div>';

			$data[] = [
				'no'          => $no++,
				'no_doc'      => '<span class="fw-semibold text-primary">' . htmlspecialchars($record->no_doc) . '</span>',
				'departemen'  => '<span class="fw-medium text-dark">' . strtoupper(htmlspecialchars($nm_department)) . '</span>',
				'tanggal_doc' => date('d F Y', strtotime($record->tanggal_doc)),
				'total_nilai' => '<span class="fw-bold text-end d-block">Rp ' . number_format($record->total_nilai, 0, ',', '.') . '</span>',
				'status'      => '<span class="badge bg-warning text-dark px-3 py-2 rounded-pill shadow-sm"><i class="fa fa-clock me-1"></i> Menunggu Approval</span>',
				'action'      => $action,
			];
		}

		echo json_encode([
			'draw'            => $draw,
			'recordsTotal'    => $count_all,
			'recordsFiltered' => $count_filtered,
			'data'            => $data,
		]);
	}

	// approve
	public function approve($id = '')
	{
		$result = false;
		if ($id != "") {
			$data = array(
				array(
					'id' => $id,
					'status' => 1,
				)
			);
			$result = $this->Pengajuan_rutin_model->update_batch($data, 'id');
			$keterangan     = "SUKSES, Approve data " . $id;
			$status         = 1;
			$nm_hak_akses   = $this->managePermission;
			$kode_universal = $id;
			$jumlah = 1;
			$sql            = $this->db->last_query();
			simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
		}
		$param = array(
			'save' => $result,
			'id' => $id
		);
		echo json_encode($param);
	}
	public function reject($id = '')
	{
		$result = false;
		if ($id != "") {
			$data = array(
				array(
					'id' => $id,
					'sts_reject' => 1,
					'reject_ket' => $this->input->post('reject_reason')
				)
			);
			$result = $this->Pengajuan_rutin_model->update_batch($data, 'id');
			$keterangan     = "SUKSES, Reject data " . $id;
			$status         = 1;
			$nm_hak_akses   = $this->managePermission;
			$kode_universal = $id;
			$jumlah = 1;
			$sql            = $this->db->last_query();
			simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
		}
		$param = array(
			'save' => $result,
			'id' => $id
		);
		echo json_encode($param);
	}

	public function get_pengajuan_periodik()
	{
		$post = $this->input->post();

		$draw = intval($post['draw']);
		$length = $post['length'];
		$start = $post['start'];
		$search = $post['search']['value'];

		$this->db->select('a.*, IF(SUM(b.nilai) IS NULL, 0, SUM(b.nilai)) as nilai_total');
		$this->db->from('tr_pengajuan_rutin a');
		$this->db->join('tr_pengajuan_rutin_detail b', 'b.no_doc = a.no_doc', 'left');
		$this->db->group_by('a.no_doc');
		$count_all = $this->db->get()->num_rows();

		$this->db->select('a.*, IF(SUM(b.nilai) IS NULL, 0, SUM(b.nilai)) as nilai_total');
		$this->db->from('tr_pengajuan_rutin a');
		$this->db->join('tr_pengajuan_rutin_detail b', 'b.no_doc = a.no_doc', 'left');

		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('a.no_doc', $search, 'both');
			$this->db->or_like('a.nilai_total', $search, 'both');
			$this->db->or_like('a.tanggal_doc', $search, 'both');
			$this->db->group_end();
		}

		$this->db->group_by('a.no_doc');
		$count_filter = $this->db->get()->num_rows();

		$this->db->select('a.*, IF(SUM(b.nilai) IS NULL, 0, SUM(b.nilai)) as nilai_total');
		$this->db->from('tr_pengajuan_rutin a');
		$this->db->join('tr_pengajuan_rutin_detail b', 'b.no_doc = a.no_doc', 'left');

		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('a.no_doc', $search, 'both');
			$this->db->or_like('a.nilai_total', $search, 'both');
			$this->db->or_like('a.tanggal_doc', $search, 'both');
			$this->db->group_end();
		}

		$this->db->group_by('a.no_doc');
		$this->db->order_by('a.created_on', 'desc');
		$this->db->limit($length, $start);

		$get_data = $this->db->get()->result();

		$no = (0 + $start);
		$hasil = [];

		foreach ($get_data as $item) {

			$no++;

			$get_departments = $this->db->get_where('ms_department', ['id' => $item->departement])->row();

			$nm_dept = (!empty($get_departments->nama)) ? $get_departments->nama : '';

			$nm_comp = 'PT HARAPAN FAJAR GEMILANG';

			$status = '';

			if ($item->sts_reject == '1') {
				$status = '<div class="badge bg-danger">Reject</div>';
			} else {
				if ($item->status == '1') {
					$status = '<div class="badge bg-success">Approved</div>';
				} else {
					if ($item->status == '2') {
						$status = '<div class="badge bg-primary">Diproses Finance</div>';

						$this->db->select('a.id');
						$this->db->from('payment_approve a');
						$this->db->where('a.no_doc', $item->no_doc);
						$count_payment_periodik = $this->db->get()->num_rows();

						if ($count_payment_periodik > 0) {
							$status = '<div class="badge bg-success">Paid</div>';
						}
					} else {
						$status = '<div class="badge bg-warning">Waiting Approval</div>';
					}
				}
			}

			$btn_view = '';
			$btn_edit = '';
			$btn_delete = '';

			if (has_permission($this->viewPermission)) {
				$btn_view = '<a class="btn btn-info btn-sm view" href="javascript:void(0)" title="View" onclick="data_view(' . $item->id . ')"><i class="fa fa-eye"></i></a>';
			}

			if ($item->status == 0) {
				if (has_permission($this->managePermission)) {
					$btn_edit = '<a href="javascript:void(0);" class="btn btn-sm btn-warning btn_edit" title="Edit" onclick="data_edit(' . $item->id . ')"><i class="fa fa-edit"></i></a>';
				}
				if (has_permission($this->deletePermission)) {
					$no_doc_delete = str_replace($item->no_doc, "'" . $item->no_doc . "'", $item->no_doc);
					$btn_delete = '<a class="btn btn-danger btn-sm delete" href="javascript:void(0);" title="hapus" onclick="data_delete(' . $no_doc_delete . ')"><i class="fa fa-trash"></i</a>';
				}
			}

			$keterangan_reject = '';
			if ($item->status < 1 && $item->sts_reject == 1) {
				$keterangan_reject = $item->reject_ket;
			}

			$action = $btn_view . ' ' . $btn_edit . ' ' . $btn_delete;

			$hasil[] = [
				'no' => $no,
				'department' => strtoupper($nm_dept . ' - ' . $nm_comp),
				'nomor' => $item->no_doc,
				'nominal' => number_format($item->nilai_total),
				'tanggal' => date('d F Y', strtotime($item->tanggal_doc)),
				'status' => $status,
				'keterangan_reject' => $keterangan_reject,
				'action' => $action
			];
		}

		$response = [
			'draw' => $draw,
			'recordsTotal' => $count_all,
			'recordsFiltered' => $count_filter,
			'data' => $hasil
		];

		echo json_encode($response);
	}
}
