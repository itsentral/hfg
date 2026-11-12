<?php

if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

/*
 * @author Harboens
 * @copyright Copyright (c) 2020
 *
 * This is controller for Budget Rutin
 */

$status = array();
$waktu = array();
class Budget_periodik extends Admin_Controller
{
	// Permission
	protected $viewPermission 	= 'Pembayaran_Periodik.View';
	protected $addPermission  	= 'Pembayaran_Periodik.Add';
	protected $managePermission = 'Pembayaran_Periodik.Manage';
	protected $deletePermission = 'Pembayaran_Periodik.Delete';

	public function __construct()
	{
		parent::__construct();
		$this->load->model(array('All/All_model', 'Budget_periodik/Budget_periodik_model'));
		$this->template->title('Master Pembayaran Periodik');
		$this->template->page_icon('fa fa-calendar-check');
		date_default_timezone_set('Asia/Bangkok');
		$this->waktu = array("bulan" => "bulan", "tahun" => "tahun");
	}

	public function index()
	{
		$this->auth->restrict($this->viewPermission);

		$data = $this->Budget_periodik_model->GetBudgetRutinGroup();
		$datdept = $this->All_model->GetDeptCombo();

		// Fetch all departments (active and historical) to avoid Undefined offset notice
		$all_dept = [];
		$q_all = $this->db->select('id, nama')->from('ms_department')->get()->result_array();
		if ($q_all) {
			foreach ($q_all as $d) {
				$all_dept[$d['id']] = strtoupper($d['nama']);
			}
		}

		$this->template->set('all_dept', $all_dept);
		$this->template->set('datdept', $datdept);
		$this->template->set('results', $data);
		$this->template->title('Master Pembayaran Periodik');
		$this->template->render('list');
	}

	public function create($key)
	{
		$this->auth->restrict($this->addPermission);
		$datdept = $this->All_model->GetDeptCombo($key);
		if (empty($datdept) || !isset($datdept[$key])) {
			$d = $this->db->get_where('ms_department', ['id' => $key])->row();
			if ($d) $datdept[$key] = strtoupper($d->nama);
		}
		$this->template->set('datdept', $datdept);
		$datcoa	= $this->All_model->GetCoaCombo();
		$data	= $this->Budget_periodik_model->GetBudgetRutin($key);
		$callcoa = $this->All_model->GetComboBudget();
		$this->template->set('callcoa', $callcoa);
		$this->template->set('data_detail', $data);
		$this->template->set('datcoa', $datcoa);
		$this->template->set('waktu', $this->waktu);
		$this->template->set('departement', $key);
		$this->template->set('mode', 'add');
		$this->template->title('Input Master Pembayaran Periodik');
		$this->template->render('input_form');
	}

	public function edit($key)
	{
		$this->auth->restrict($this->managePermission);
		$datdept = $this->All_model->GetDeptCombo($key);
		if (empty($datdept) || !isset($datdept[$key])) {
			$d = $this->db->get_where('ms_department', ['id' => $key])->row();
			if ($d) $datdept[$key] = strtoupper($d->nama);
		}
		$this->template->set('datdept', $datdept);
		$datcoa	= $this->All_model->GetCoaCombo();
		$data	= $this->Budget_periodik_model->GetBudgetRutin($key);
		$callcoa = $this->All_model->GetComboBudget();
		$this->template->set('callcoa', $callcoa);
		$this->template->set('data_detail', $data);
		$this->template->set('datcoa', $datcoa);
		$this->template->set('waktu', $this->waktu);
		$this->template->set('departement', $key);
		$this->template->set('mode', 'edit');
		$this->template->title('Edit Master Pembayaran Periodik');
		$this->template->render('input_form');
	}

	public function view($key)
	{
		$this->auth->restrict($this->viewPermission);
		$datdept = $this->All_model->GetDeptCombo($key);
		if (empty($datdept) || !isset($datdept[$key])) {
			$d = $this->db->get_where('ms_department', ['id' => $key])->row();
			if ($d) $datdept[$key] = strtoupper($d->nama);
		}
		$this->template->set('datdept', $datdept);
		$datcoa	= $this->All_model->GetCoaCombo();
		$data	= $this->Budget_periodik_model->GetBudgetRutin($key);
		$callcoa = $this->All_model->GetComboBudget();
		$this->template->set('callcoa', $callcoa);
		$this->template->set('data_detail', $data);
		$this->template->set('datcoa', $datcoa);
		$this->template->set('waktu', $this->waktu);
		$this->template->set('departement', $key);
		$this->template->set('mode', 'view');
		$this->template->title('Detail Master Pembayaran Periodik');
		$this->template->render('input_form');
	}

	public function save_data()
	{
		$detail_id		= $this->input->post("detail_id");
		$coa       		= $this->input->post("coa");
		$nama           = $this->input->post("nama");
		$tipe  			= $this->input->post("tipe");
		$bln  			= $this->input->post("bln");
		$thn  			= $this->input->post("thn");
		$nilai			= $this->input->post("nilai");
		$keterangan		= $this->input->post("keterangan");
		$departement	= $this->input->post("departement");
		$kode_id		= $this->input->post("kode_id");

		$this->db->trans_begin();

		$delid = !empty($detail_id) ? implode("','", array_filter($detail_id)) : '';
		if (!empty($delid)) {
			$this->All_model->dataDelete('ms_budget_rutin', "id not in ('" . $delid . "') and departement='" . $departement . "'");
		} else {
			$this->All_model->dataDelete('ms_budget_rutin', "departement='" . $departement . "'");
		}

		if (!empty($detail_id)) {
			for ($x = 0; $x < count($detail_id); $x++) {
				if (isset($tipe[$x]) && $tipe[$x] == 'bulan') {
					$tanggal = isset($bln[$x]) ? $bln[$x] : '';
				} else {
					$tanggal = isset($thn[$x]) ? $thn[$x] : '';
				}

				$val_raw = isset($nilai[$x]) ? $nilai[$x] : 0;
				$clean_val = str_replace(',', '', str_replace('.', '', $val_raw));
				$clean_val = is_numeric($clean_val) ? $clean_val : 0;

				$row_nama = isset($nama[$x]) ? trim($nama[$x]) : '';
				$row_coa  = isset($coa[$x]) ? trim($coa[$x]) : '';
				$row_tipe = isset($tipe[$x]) ? $tipe[$x] : 'bulan';
				$row_ket  = isset($keterangan[$x]) ? trim($keterangan[$x]) : '';
				$row_kode = isset($kode_id[$x]) ? trim($kode_id[$x]) : '';

				if (!empty($detail_id[$x])) {
					$data = array(
						array(
							'id'          => $detail_id[$x],
							'nama'        => $row_nama,
							'coa'         => $row_coa,
							'tipe'        => $row_tipe,
							'tanggal'     => $tanggal,
							'nilai'       => $clean_val,
							'keterangan'  => $row_ket,
							'kode_id'     => $row_kode,
							'departement' => $departement,
							'modified_by' => $this->auth->user_id(),
							'modified_on' => date('Y-m-d H:i:s')
						)
					);
					$this->Budget_periodik_model->update_batch($data, 'id');
				} else {
					$data = array(
						'nama'        => $row_nama,
						'coa'         => $row_coa,
						'tipe'        => $row_tipe,
						'tanggal'     => $tanggal,
						'nilai'       => $clean_val,
						'keterangan'  => $row_ket,
						'kode_id'     => $row_kode,
						'departement' => $departement,
						'created_by'  => $this->auth->user_id(),
						'created_on'  => date('Y-m-d H:i:s')
					);
					$this->Budget_periodik_model->insert($data);
				}
			}
		}

		// Alokasi
		$detail_alokasi	= $this->input->post("detail_alokasi");
		$kode_detail	= $this->input->post("kode_detail");
		$coa_alokasi	= $this->input->post("coa_alokasi");
		$nilai_alokasi	= $this->input->post("nilai_alokasi");
		$this->All_model->dataDelete('ms_budget_rutin_alokasi', array('departement' => $departement));
		if (isset($kode_detail) && is_array($kode_detail)) {
			for ($x = 0; $x < count($kode_detail); $x++) {
				$val_alok = isset($nilai_alokasi[$x]) ? $nilai_alokasi[$x] : 0;
				$clean_alok = str_replace(',', '', str_replace('.', '', $val_alok));
				$dataalokasi = array(
					'kode'        => $kode_detail[$x],
					'coa'         => isset($coa_alokasi[$x]) ? $coa_alokasi[$x] : '',
					'nilai'       => is_numeric($clean_alok) ? $clean_alok : 0,
					'departement' => $departement,
					'created_by'  => $this->auth->user_id(),
					'created_on'  => date('Y-m-d H:i:s')
				);
				$this->All_model->dataSave('ms_budget_rutin_alokasi', $dataalokasi);
			}
		}

		if ($this->db->trans_status()) {
			$keterangan     = "SUKSES, simpan data Master Pembayaran Periodik ";
			$status         = 1;
			$nm_hak_akses   = $this->addPermission;
			$kode_universal = 'SavePeriodik';
			$jumlah         = isset($detail_id) ? count($detail_id) : 0;
			$sql            = $this->db->last_query();
			$result         = TRUE;
		} else {
			$keterangan     = "GAGAL, simpan data Master Pembayaran Periodik ";
			$status         = 0;
			$nm_hak_akses   = $this->addPermission;
			$kode_universal = 'SavePeriodik';
			$jumlah         = 0;
			$sql            = $this->db->last_query();
			$result         = FALSE;
		}
		simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
		$this->db->trans_complete();
		$param = array(
			'save' => $result,
			'msg'  => $result ? 'Data berhasil disimpan' : 'Gagal menyimpan data'
		);
		echo json_encode($param);
	}

	function hapus_data($id)
	{
		$this->auth->restrict($this->deletePermission);
		if ($id != '') {
			$data = $this->All_model->GetOneData('ms_budget_rutin', array('id' => $id));
			if ($data && !empty($data->kode_id)) {
				$this->All_model->dataDelete('ms_budget_rutin_alokasi', array('kode' => $data->kode_id));
			}
			$result = $this->All_model->dataDelete('ms_budget_rutin', array('id' => $id));
			$keterangan     = "SUKSES, Delete data Budget ";
			$status         = 1;
			$nm_hak_akses   = $this->deletePermission;
			$kode_universal = $id;
			$jumlah         = 1;
			$sql            = $this->db->last_query();
		} else {
			$result         = 0;
			$keterangan     = "GAGAL, Delete data Budget ";
			$status         = 0;
			$nm_hak_akses   = $this->deletePermission;
			$kode_universal = $id;
			$jumlah         = 1;
			$sql            = $this->db->last_query();
		}
		simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
		$param = array(
			'delete' => $result,
			'idx'    => $id
		);
		echo json_encode($param);
	}

	function proses_budget_periodik()
	{
		$this->auth->restrict($this->managePermission);
		$data = $this->Budget_periodik_model->GetBudgetRutin();
		$this->db->trans_begin();
		if (!empty($data)) {
			$this->db->query("update ms_budget set bulan_1=0,bulan_2=0, bulan_3=0, bulan_4=0, bulan_5=0, bulan_6=0, bulan_7=0, bulan_8=0, bulan_9=0, bulan_10=0, bulan_11=0, bulan_12=0,total=0 WHERE tahun='" . date("Y") . "' and kategori='PEMBAYARAN PERIODIK'");
			foreach ($data as $record) {
				$databudget['tipe']        = $record->tipe;
				$databudget['coa']         = $record->coa;
				$databudget['tanggal']     = $record->tanggal;
				$databudget['nilai']       = $record->nilai;
				$databudget['departement'] = $record->departement;
				$databudget['kode_id']     = $record->kode_id;
				$databudget['tahun']       = date("Y");
				if ($record->kode_id != '') {
					$dtalok = $this->db->query("select * from ms_budget_rutin_alokasi where kode='" . $record->kode_id . "'")->result();
					if (!empty($dtalok)) {
						foreach ($dtalok as $rdetail) {
							$databudget['coa']   = $rdetail->coa;
							$databudget['nilai'] = ($rdetail->nilai * $record->nilai / 100);
							$this->Budget_periodik_model->updatebudget($databudget);
						}
					} else {
						$this->Budget_periodik_model->updatebudget($databudget);
					}
				} else {
					$this->Budget_periodik_model->updatebudget($databudget);
				}
			}
		}
		if ($this->db->trans_status()) {
			$keterangan     = "SUKSES, proses data ";
			$status         = 1;
			$nm_hak_akses   = $this->managePermission;
			$kode_universal = 'UpdateData';
			$jumlah         = 1;
			$sql            = $this->db->last_query();
			$result         = TRUE;
		} else {
			$keterangan     = "GAGAL, proses data ";
			$status         = 0;
			$nm_hak_akses   = $this->managePermission;
			$kode_universal = 'UpdateData';
			$jumlah         = 1;
			$sql            = $this->db->last_query();
			$result         = FALSE;
		}
		$this->db->trans_complete();
		simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
		$param = array(
			'save' => $result
		);
		echo json_encode($param);
	}
}
