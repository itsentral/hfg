<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
 * @author Harboens
 * @copyright Copyright (c) 2020
 *
 * This is model class for table "Purchase Request"
 */

class Expense_model extends BF_Model
{
	/**
	 * @var string  User Table Name
	 */
	protected $table_name = 'tr_expense';
	protected $key        = 'id';

	/**
	 * @var string Field name to use for the created time column in the DB table
	 * if $set_created is enabled.
	 */
	protected $created_field = 'created_on';

	/**
	 * @var string Field name to use for the modified time column in the DB
	 * table if $set_modified is enabled.
	 */
	protected $modified_field = 'modified_on';

	/**
	 * @var bool Set the created time automatically on a new record (if true)
	 */
	protected $set_created = true;

	/**
	 * @var bool Set the modified time automatically on editing a record (if true)
	 */
	protected $set_modified = true;

	/**
	 * @var string The type of date/time field used for $created_field and $modified_field.
	 * Valid values are 'int', 'datetime', 'date'.
	 */
	protected $date_format = 'datetime';

	/**
	 * @var bool If true, will log user id in $created_by_field, $modified_by_field,
	 * and $deleted_by_field.
	 */
	protected $log_user = true;

	/**
	 * Function construct used to load some library, do some actions, etc.
	 */
	public function __construct()
	{
		parent::__construct();

		// 		$this->hr = $this->load->database('hris', true);
	}

	// list data kasbon
	public function GetListDataKasbon($where = '')
	{
		$this->db->select('a.*, b.nm_lengkap as nmuser');
		$this->db->from('tr_kasbon a');
		$this->db->join('users b', 'a.nama = b.username', 'left');
		// $this->db->join('employee c', 'b.employee_id = c.id');
		if ($where != '') $this->db->where($where);
		$this->db->order_by('a.id', 'desc');
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	// get data kasbon
	public function GetDataKasbon($id)
	{
		$this->db->select('a.*');
		$this->db->from('tr_kasbon a');
		$this->db->where('a.id', $id);
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->row();
		} else {
			return false;
		}
	}

	// list data transport request
	public function GetListDataTransportRequest($id_user = '', $where = '')
	{
		$this->db->select('a.*, a.created_by as nmuser');
		$this->db->from('tr_transport_req a');
		if ($id_user != '') $this->db->where('a.created_by', $id_user);
		if ($where != '') $this->db->where($where);
		$this->db->order_by('a.id', 'desc');
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}
	// list data transport request
	public function GetListDataTransportRequestAll($id_user = '', $where = '')
	{
		$this->db->select('a.*, b.nm_lengkap as nmuser,c.tgl_doc as tgl_trans,c.keperluan');
		$this->db->from('tr_transport_req a');
		$this->db->join('tr_transport c', 'a.no_doc=c.no_req', 'left');
		$this->db->join('users b', 'b.id_user = a.created_by', 'left');
		if ($id_user !== '') $this->db->where('a.created_by', $id_user);
		if ($where !== '') $this->db->where($where);
		$this->db->order_by('a.id', 'desc');
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	// get data transport req
	public function GetDataTransportReq($id)
	{
		$this->db->select('a.*');
		$this->db->from('tr_transport_req a');
		$this->db->where('a.id', $id);
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->row();
		} else {
			return false;
		}
	}

	// get data transport req detail
	public function GetDataTransportInReq($id)
	{
		$this->db->select('a.*');
		$this->db->from('tr_transport a');
		$this->db->where('a.no_req', $id);
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}


	// list data transport
	public function GetListDatatransport($id_user = '')
	{
		$this->db->select('a.*');
		$this->db->from('tr_transport a');
		if ($id_user != '') $this->db->where('a.nama', $id_user);
		$this->db->order_by('a.id', 'desc');
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}
	// get data transport
	public function GetDataTransport($id)
	{
		$this->db->select('a.*');
		$this->db->from('tr_transport a');
		$this->db->where('a.id', $id);
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->row();
		} else {
			return false;
		}
	}

	// list data
	public function GetListData($where = '')
	{
		$this->db->select('a.*, b.username as nmuser, c.username as nmapproval');
		$this->db->from($this->table_name . ' a');
		$this->db->join('users b', 'a.nama=b.username', 'left');
		$this->db->join('users c', 'a.approval=c.username', 'left');
		if ($where != '') $this->db->where($where);
		$this->db->order_by('a.id', 'desc');
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}
	public function GetListDataAll($where = '')
	{
		$this->db->select('a.*, b.username as nmuser, c.username as nmapproval,d.tanggal,d.deskripsi');
		$this->db->from($this->table_name . ' a');
		$this->db->join('users b', 'a.nama=b.username', 'left');
		$this->db->join('users c', 'a.approval=c.username', 'left');
		$this->db->join('tr_expense_detail d', 'a.no_doc=d.no_doc', 'left');
		if ($where != '') $this->db->where($where);
		$this->db->order_by('a.id', 'desc');
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}
	// get data
	public function GetDataHeader($id)
	{
		$this->db->select('a.*');
		$this->db->from($this->table_name . ' a');
		$this->db->where('a.id', $id);
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->row();
		} else {
			return false;
		}
	}

	public function GetDataDetail($id)
	{
		$this->db->select('a.*');
		$this->db->from('tr_expense_detail a');
		$this->db->where('a.no_doc', $id);
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	public function GetDetailPurchaseRequest($id)
	{
		$this->db->select('a.*');
		$this->db->from('tr_expense_detail a');
		$this->db->where('a.id', $id);
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->row();
		} else {
			return false;
		}
	}

	public function GetBudget($coa, $tahun)
	{
		$this->db->select('a.*');
		$this->db->from('ms_budget a');
		$this->db->where('a.coa', $coa);
		$this->db->where('a.tahun', $tahun);
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->row();
		} else {
			return false;
		}
	}

	public function GetBudgetDivisi($type, $divisi, $tahun)
	{
		$this->db->select('a.*');
		$this->db->from('ms_coa_budget a');
		$this->db->where('a.coa', $type);
		$this->db->where('a.divisi', $divisi);
		$this->db->where('a.tahun', $tahun);
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->row();
		} else {
			return false;
		}
	}

	public function Update_budget($coa, $tgl, $nilai, $divisi, $nilai_pr = 0)
	{
		$bulan = date("n", strtotime($tgl));
		$tahun = date("Y", strtotime($tgl));

		$this->db->select('a.*');
		$this->db->from('ms_coa_budget a');
		$this->db->where('a.coa', $coa);
		$this->db->where('a.tahun', $tahun);
		$this->db->where('a.divisi', $divisi);
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			$data = $query->row();
			$terpakai_bulan = $data->{"terpakai_bulan_" . $bulan};
			$terpakai = $data->terpakai;
			$sisa = $data->sisa;
			$idbudget = $data->id;
			$upd_terpakai_bulan = ($terpakai_bulan + $nilai - $nilai_pr);
			$upd_terpakai = ($terpakai + $nilai - $nilai_pr);
			$upd_sisa = ($sisa - $nilai + $nilai_pr);
			$this->db->query("update ms_coa_budget set terpakai_bulan_" . $bulan . "=" . $upd_terpakai_bulan . ", terpakai=" . $upd_terpakai . ", sisa=" . $upd_sisa . " where id=" . $idbudget . " and coa='" . $coa . "' and tahun='" . $tahun . "'");
			return true;
		} else {
			return false;
		}
	}

	public function getArray($table, $WHERE = array(), $keyArr = '', $valArr = '')
	{
		if ($WHERE) {
			$query = $this->db->get_where($table, $WHERE);
		} else {
			$query = $this->db->get($table);
		}
		$dataArr	= $query->result_array();

		if (!empty($keyArr)) {
			$Arr_Data	= array();
			foreach ($dataArr as $key => $val) {
				$nilai_id					= $val[$keyArr];
				if (!empty($valArr)) {
					$nilai_val				= $val[$valArr];
					$Arr_Data[$nilai_id]	= $nilai_val;
				} else {
					$Arr_Data[$nilai_id]	= $val;
				}
			}

			return $Arr_Data;
		} else {
			return $dataArr;
		}
	}

	public function get_data_transport_input()
	{
		$draw = $this->input->post('draw');
		$length = $this->input->post('length');
		$start = $this->input->post('start');
		$search = $this->input->post('search');

		$this->db->select('a.id, a.no_doc, a.tgl_doc, a.nama, a.departement, a.keperluan, a.nopol, a.status, (a.bensin + a.tol + a.parkir + a.lainnya) as ttl_transport, a.created_by as nmuser, d.nm_dept');
		$this->db->from('tr_transport a');
		$this->db->join('department d', 'd.id = a.departement', 'left');
		$this->db->where('a.created_by', $this->auth->user_name());
		if (!empty($search['value'])) {
			$this->db->group_start();
			$this->db->like('a.no_doc', $search['value'], 'both');
			$this->db->or_like('a.tgl_doc', $search['value'], 'both');
			$this->db->or_like('a.nama', $search['value'], 'both');
			$this->db->or_like('a.nopol', $search['value'], 'both');
			$this->db->or_like('a.keperluan', $search['value'], 'both');
			$this->db->or_like('d.nm_dept', $search['value'], 'both');
			$this->db->group_end();
		}
		$this->db->group_by('a.id');
		$this->db->order_by('a.id', 'desc');

		$count_all = $this->db->count_all_results('', false);

		$this->db->limit($length, $start);
		$get_data = $this->db->get()->result();

		$hasil = [];
		$no = (0 + $start);

		foreach ($get_data as $item) {
			$no++;

			$status = '<span class="badge bg-warning text-dark">Baru</span>';
			if ($item->status == '1') {
				$status = '<span class="badge bg-success">Disetujui</span>';
			}
			if ($item->status == '2') {
				$status = '<span class="badge bg-info">Disetujui Management</span>';
			}
			if ($item->status == '3') {
				$status = '<span class="badge bg-primary">Selesai</span>';
			}
			if ($item->status == '9') {
				$status = '<span class="badge bg-danger">Ditolak</span>';
			}

			$action = '<div class="btn-group btn-group-sm" role="group">';

			if (has_permission('Transportasi.View')) {
				$action .= '<button class="btn btn-outline-info btn-sm view" type="button" title="View" onclick="data_view(' . $item->id . ')"><i class="fa fa-eye"></i></button>';
			}

			if (has_permission('Transportasi.Manage') && $item->status == 0) {
				$action .= ' <button class="btn btn-outline-warning btn-sm edit" type="button" title="Edit" onclick="data_edit(' . $item->id . ')"><i class="fa fa-edit"></i></button>';
			}

			if (has_permission('Transportasi.Delete') && $item->status == 0) {
				$action .= ' <button class="btn btn-outline-danger btn-sm delete" type="button" title="Hapus" onclick="data_delete(' . $item->id . ')"><i class="fa fa-trash"></i></button>';
			}

			$action .= '</div>';

			$hasil[] = [
				'no' => $no,
				'id_pengajuan' => $item->no_doc,
				'tanggal' => date('d F Y', strtotime($item->tgl_doc)),
				'nama' => $item->nama,
				'departement' => (!empty($item->nm_dept) ? $item->nm_dept : $item->departement),
				'keperluan' => $item->keperluan,
				'no_polisi' => $item->nopol,
				'total' => 'Rp ' . number_format($item->ttl_transport, 0, ',', '.'),
				'status' => $status,
				'action' => $action
			];
		}

		$response = [
			'draw' => intval($draw),
			'recordsTotal' => $count_all,
			'recordsFiltered' => $count_all,
			'data' => $hasil
		];

		echo json_encode($response);
	}
	public function get_data_transport_req_fin_list()
	{
		$post = $this->input->post();

		$viewPermission   = 'Pengajuan_Transportasi_Approval.View';
		$addPermission    = 'Pengajuan_Transportasi_Approval.Add';
		$managePermission = 'Pengajuan_Transportasi_Approval.Manage';
		$deletePermission = 'Pengajuan_Transportasi_Approval.Delete';

		$draw   = isset($post['draw']) ? $post['draw'] : 1;
		$length = isset($post['length']) ? $post['length'] : 10;
		$start  = isset($post['start']) ? $post['start'] : 0;
		$search = isset($post['search']) ? $post['search'] : [];

		$this->db->select('a.*, a.created_by as nmuser');
		$this->db->from('tr_transport_req a');
		$this->db->where('a.status', 0);

		if (!empty($search['value'])) {
			$this->db->group_start();
			$this->db->like('a.no_doc', $search['value'], 'both');
			$this->db->or_like('a.tgl_doc', $search['value'], 'both');
			$this->db->or_like('a.created_by', $search['value'], 'both');
			$this->db->or_like('a.nama', $search['value'], 'both');
			$this->db->group_end();
		}
		$this->db->order_by('a.no_doc', 'desc');

		$count_all = $this->db->count_all_results('', false);

		$this->db->limit($length, $start);

		$get_data = $this->db->get()->result();

		$hasil = [];
		$no = (0 + $start);

		foreach ($get_data as $item) {
			$no++;

			$status = '<span class="badge bg-warning text-dark"><i class="fa fa-clock me-1"></i>Menunggu Approval</span>';

			$nama_karyawan = !empty($item->nama) ? $item->nama : $item->nmuser;

			$action = '<div class="btn-group btn-group-sm">';
			if (has_permission($viewPermission)) {
				$action .= '<a class="btn btn-outline-secondary btn-sm print" href="' . base_url('expense/transport_req_print/' . $item->id) . '" target="_blank" title="Cetak"><i class="fa fa-print"></i></a>';
				$action .= '<a class="btn btn-outline-info btn-sm view" href="' . base_url('expense/transport_req_view/' . $item->id . '/_fin') . '" data-id="' . $item->id . '" title="Lihat Detail"><i class="fa fa-eye"></i></a>';
			}

			if (has_permission($managePermission) && $item->status == 0) {
				$action .= '<a class="btn btn-success btn-sm approve" href="' . base_url('expense/transport_req_edit/' . $item->id . '/_fin') . '" data-id="' . $item->id . '" title="Pengecekan / Approval Finance"><i class="fa fa-check"></i></a>';
			}
			$action .= '</div>';

			$hasil[] = [
				'no'           => $no,
				'no_transport' => '<span class="fw-semibold text-primary">' . htmlspecialchars($item->no_doc) . '</span>',
				'tanggal'      => date('d F Y', strtotime($item->tgl_doc)),
				'nama'         => htmlspecialchars($nama_karyawan),
				'total'        => '<span class="fw-bold text-end d-block">Rp ' . number_format($item->jumlah_expense, 0, ',', '.') . '</span>',
				'status'       => $status,
				'action'       => $action,
			];
		}

		$response = [
			'draw'            => intval($draw),
			'recordsTotal'    => $count_all,
			'recordsFiltered' => $count_all,
			'data'            => $hasil
		];

		echo json_encode($response);
	}

	public function get_data_transport_req()
	{
		$post = $this->input->post();

		// Fetch auth information BEFORE starting query builder to avoid resetting $this->db
		$is_admin   = $this->auth->is_admin();
		$user_name  = $this->auth->user_name();

		$viewPermission 	= 'Pengajuan_Transportasi.View';
		$addPermission  	= 'Pengajuan_Transportasi.Add';
		$managePermission = 'Pengajuan_Transportasi.Manage';
		$deletePermission = 'Pengajuan_Transportasi.Delete';

		$draw       = isset($post['draw']) ? $post['draw'] : 1;
		$length     = isset($post['length']) ? $post['length'] : 10;
		$start      = isset($post['start']) ? $post['start'] : 0;
		$search_val = isset($post['search']['value']) ? $post['search']['value'] : (isset($post['search']) && is_string($post['search']) ? $post['search'] : '');

		$this->db->select('a.*, a.created_by as nmuser, d.nm_dept');
		$this->db->from('tr_transport_req a');
		$this->db->join('department d', 'd.id = a.departement', 'left');
		if (!$is_admin) {
			$this->db->where('a.created_by', $user_name);
		}
		if (!empty($search_val)) {
			$this->db->group_start();
			$this->db->like('a.no_doc', $search_val, 'both');
			$this->db->or_like('a.tgl_doc', $search_val, 'both');
			$this->db->or_like('a.nama', $search_val, 'both');
			$this->db->or_like('d.nm_dept', $search_val, 'both');
			$this->db->or_like('a.created_by', $search_val, 'both');
			$this->db->group_end();
		}

		$count_all = $this->db->count_all_results('', false);

		$this->db->order_by('a.id', 'desc');
		if ($length != -1) {
			$this->db->limit($length, $start);
		}

		$get_data = $this->db->get()->result();

		$hasil = [];
		$no = (0 + $start);

		foreach ($get_data as $item) {
			$no++;

			$status = '<span class="badge bg-warning text-dark"><i class="fa fa-clock me-1"></i> Baru</span>';
			if ($item->status == '1') {
				$status = '<span class="badge bg-info text-white"><i class="fa fa-check me-1"></i> Disetujui Finance</span>';
			}
			if ($item->status == '2') {
				$status = '<span class="badge bg-primary"><i class="fa fa-check-double me-1"></i> Disetujui Management</span>';
			}
			if ($item->status == '3') {
				$status = '<span class="badge bg-success"><i class="fa fa-check-circle me-1"></i> Selesai</span>';
			}
			if ($item->status == '9') {
				$status = '<span class="badge bg-danger"><i class="fa fa-times-circle me-1"></i> Ditolak</span>';
			}

			$action = '<div class="btn-group btn-group-sm" role="group">';
			if (has_permission($viewPermission)) {
				$action .= ' <a class="btn btn-outline-secondary btn-sm" href="' . base_url('expense/transport_req_print/' . $item->id) . '" target="_blank" title="Print"><i class="fa fa-print"></i></a>';
				$action .= ' <a class="btn btn-outline-info btn-sm" href="javascript:void(0)" title="View" onclick="data_view(' . $item->id . ')"><i class="fa fa-eye"></i></a>';
			}

			if (has_permission($managePermission) && ($item->status == 0 || $item->status == 9)) {
				$action .= ' <a class="btn btn-outline-warning btn-sm" href="javascript:void(0)" title="Edit" onclick="data_edit(' . $item->id . ')"><i class="fa fa-edit"></i></a>';
			}

			if (has_permission($deletePermission) && ($item->status == 0 || $item->status == 9)) {
				$action .= ' <a class="btn btn-outline-danger btn-sm" href="javascript:void(0)" title="Hapus" onclick="data_delete(' . $item->id . ')"><i class="fa fa-trash"></i></a>';
			}
			$action .= '</div>';

			$hasil[] = [
				'no' => $no,
				'no_transport' => '<span class="fw-semibold text-primary">' . htmlspecialchars($item->no_doc) . '</span>',
				'tanggal' => date('d M Y', strtotime($item->tgl_doc)),
				'nama' => htmlspecialchars($item->nama ? $item->nama : $item->nmuser),
				'department' => htmlspecialchars($item->nm_dept ? $item->nm_dept : '-'),
				'total' => '<span class="fw-bold">Rp ' . number_format($item->jumlah_expense, 0, ',', '.') . '</span>',
				'status' => $status,
				'action' => $action
			];
		}

		$response = [
			'draw' => intval($draw),
			'recordsTotal' => $count_all,
			'recordsFiltered' => $count_all,
			'data' => $hasil
		];

		echo json_encode($response);
	}

	public function get_data_transport_req_all()
	{
		$post = $this->input->post();

		$draw       = isset($post['draw']) ? $post['draw'] : 1;
		$length     = isset($post['length']) ? $post['length'] : 10;
		$start      = isset($post['start']) ? $post['start'] : 0;
		$search_val = isset($post['search']['value']) ? $post['search']['value'] : (isset($post['search']) && is_string($post['search']) ? $post['search'] : '');
		$order      = isset($post['order']) ? $post['order'] : [];

		$this->db->select('a.id, a.no_doc, a.tgl_doc, a.date1, a.date2, a.jumlah_expense, a.status, a.nama, a.approved_on, d.nm_dept, a.created_by');
		$this->db->from('tr_transport_req a');
		$this->db->join('department d', 'd.id = a.departement', 'left');
		if (!empty($search_val)) {
			$this->db->group_start();
			$this->db->like('a.no_doc', $search_val, 'both');
			$this->db->or_like('a.tgl_doc', $search_val, 'both');
			$this->db->or_like('a.nama', $search_val, 'both');
			$this->db->or_like('d.nm_dept', $search_val, 'both');
			$this->db->or_like('a.approved_on', $search_val, 'both');
			$this->db->or_like('a.jumlah_expense', $search_val, 'both');
			$this->db->group_end();
		}

		$count_all = $this->db->count_all_results('', false);

		$column_order = [
			0 => 'a.id',
			1 => 'a.no_doc',
			2 => 'a.tgl_doc',
			3 => 'a.nama',
			4 => 'd.nm_dept',
			5 => 'a.approved_on',
			6 => 'a.jumlah_expense',
			7 => 'a.status'
		];

		if (!empty($order) && isset($order[0]['column']) && isset($column_order[$order[0]['column']])) {
			$this->db->order_by($column_order[$order[0]['column']], $order[0]['dir']);
		} else {
			$this->db->order_by('a.id', 'desc');
		}

		if ($length != -1) {
			$this->db->limit($length, $start);
		}

		$get_data = $this->db->get()->result();

		$hasil = [];
		$no = (0 + $start);

		foreach ($get_data as $item) {
			$no++;

			$tgl_doc = ($item->tgl_doc && $item->tgl_doc !== '0000-00-00') ? date('d M Y', strtotime($item->tgl_doc)) : '-';
			$approval_date = ($item->approved_on !== null && $item->approved_on !== '0000-00-00 00:00:00') ? date('d M Y H:i', strtotime($item->approved_on)) : '-';

			$status = '<span class="badge bg-warning text-dark"><i class="fa fa-clock me-1"></i> Baru</span>';
			if ($item->status == '1') {
				$status = '<span class="badge bg-info text-white"><i class="fa fa-check me-1"></i> Disetujui Finance</span>';
			}
			if ($item->status == '2') {
				$status = '<span class="badge bg-primary"><i class="fa fa-check-double me-1"></i> Disetujui Management</span>';
			}
			if ($item->status == '3') {
				$status = '<span class="badge bg-success"><i class="fa fa-check-circle me-1"></i> Selesai</span>';
			}
			if ($item->status == '9') {
				$status = '<span class="badge bg-danger"><i class="fa fa-times-circle me-1"></i> Ditolak</span>';
			}

			$action = '<div class="btn-group btn-group-sm" role="group">';
			$action .= '<a class="btn btn-outline-secondary btn-sm" href="' . base_url('expense/transport_req_print/' . $item->id) . '" target="_blank" title="Print"><i class="fa fa-print"></i></a>';
			$action .= '<a class="btn btn-outline-info btn-sm" href="javascript:void(0)" onclick="data_view(' . $item->id . ')" title="Lihat Detail"><i class="fa fa-eye"></i></a>';
			$action .= '</div>';

			$hasil[] = [
				'no'              => $no,
				'no_doc'          => '<span class="fw-semibold text-primary">' . htmlspecialchars($item->no_doc) . '</span>',
				'tanggal'         => $tgl_doc,
				'nama'            => htmlspecialchars($item->nama ? $item->nama : $item->created_by),
				'department'      => htmlspecialchars($item->nm_dept ? $item->nm_dept : '-'),
				'approval_date'   => $approval_date,
				'total_transport' => '<span class="fw-bold">Rp ' . number_format($item->jumlah_expense, 0, ',', '.') . '</span>',
				'status'          => $status,
				'action'          => $action
			];
		}

		$response = [
			'draw'            => intval($draw),
			'recordsTotal'    => intval($count_all),
			'recordsFiltered' => intval($count_all),
			'data'            => $hasil
		];

		echo json_encode($response);
	}

	public function GetListCoa($jenis_pengeluaran)
	{
		$this->db->select('a.coa');
		$this->db->from('coa_expense a');
		$this->db->where('a.jenis_pengeluaran', $jenis_pengeluaran);
		$query = $this->db->get()->row();

		if (empty($query) || empty($query->coa)) {
			// Fallback ke Expense jika Kasbon belum diset
			$this->db->select('a.coa');
			$this->db->from('coa_expense a');
			$this->db->where('a.jenis_pengeluaran', 'Expense');
			$query = $this->db->get()->row();
		}

		if (!empty($query->coa)) {
			$this->db->select('a.no_perkiraan, a.nama');
			$this->db->from(DBACC . '.coa_master a');
			$this->db->where_in('a.no_perkiraan', explode(';', $query->coa));
			$this->db->order_by('a.no_perkiraan', 'asc');
			$result = $this->db->get()->result_array();
			return $result;
		} else {
			$this->db->select('a.no_perkiraan, a.nama');
			$this->db->from(DBACC . '.coa_master a');
			$this->db->where('a.level', 5);
			$this->db->order_by('a.no_perkiraan', 'asc');
			$result = $this->db->get()->result_array();
			return $result;
		}
	}

	public function getCoaDetail($no_coa)
	{
		$this->db->select('a.nama');
		$this->db->from(DBACC . '.coa_master a');
		$this->db->where('a.no_perkiraan', $no_coa);
		$query = $this->db->get()->row();
		return $query;
	}

	public function getCoaTransport()
	{
		$this->db->select('a.coa');
		$this->db->from('coa_expense a');
		$this->db->where('a.jenis_pengeluaran', 'Transport');
		$get_kelompok_coa = $this->db->get()->row();

		if (!empty($get_kelompok_coa) && !empty($get_kelompok_coa->coa)) {
			$this->db->select('a.no_perkiraan as no_coa, a.nama as nm_coa');
			$this->db->from(DBACC . '.coa_master a');
			$this->db->where_in('a.no_perkiraan', explode(';', $get_kelompok_coa->coa));
			$this->db->order_by('a.no_perkiraan', 'asc');
			return $this->db->get()->result();
		} else {
			// Ambil langsung dari database accounting (DBACC . '.coa_master')
			$this->db->select('a.no_perkiraan as no_coa, a.nama as nm_coa');
			$this->db->from(DBACC . '.coa_master a');
			$this->db->where('a.level', '5');
			$this->db->group_start();
			$this->db->like('a.nama', 'transport', 'both');
			$this->db->or_like('a.nama', 'bensin', 'both');
			$this->db->or_like('a.nama', 'toll', 'both');
			$this->db->or_like('a.nama', 'parkir', 'both');
			$this->db->or_like('a.nama', 'bbm', 'both');
			$this->db->or_like('a.nama', 'kendaraan', 'both');
			$this->db->or_like('a.nama', 'perjalanan', 'both');
			$this->db->or_like('a.no_perkiraan', '6201-01-1', 'after');
			$this->db->or_like('a.no_perkiraan', '5204-01-1', 'after');
			$this->db->group_end();
			$this->db->order_by('a.no_perkiraan', 'asc');
			$res = $this->db->get()->result();

			if (!empty($res)) {
				return $res;
			}

			// Fallback: semua akun biaya operasional level 5
			$this->db->select('a.no_perkiraan as no_coa, a.nama as nm_coa');
			$this->db->from(DBACC . '.coa_master a');
			$this->db->where('a.level', '5');
			$this->db->group_start();
			$this->db->like('a.no_perkiraan', '6201-', 'after');
			$this->db->or_like('a.no_perkiraan', '5204-', 'after');
			$this->db->or_like('a.no_perkiraan', '5106-', 'after');
			$this->db->group_end();
			$this->db->order_by('a.no_perkiraan', 'asc');
			return $this->db->get()->result();
		}
	}

	public function GetDataWithJenis($jenis)
	{
		$this->db->select('*');
		$this->db->from('coa_expense');
		$this->db->where('jenis_pengeluaran', $jenis);
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->row();
		}

		$fallback = new stdClass();
		$fallback->jenis_pengeluaran = $jenis;
		$fallback->coa = '';
		return $fallback;
	}

	public function GetDepartmentName($id_dept)
	{
		if (empty($id_dept)) return '';
		$query = $this->db->get_where('department', ['id' => $id_dept]);
		if ($query->num_rows() > 0) {
			return $query->row()->nm_dept;
		}
		return '';
	}
}
