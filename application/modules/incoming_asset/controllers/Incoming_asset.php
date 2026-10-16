<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Incoming_asset extends Admin_Controller
{
	protected $viewPermission 	= 'Incoming_Asset.View';
	protected $addPermission  	= 'Incoming_Asset.Add';
	protected $managePermission = 'Incoming_Asset.Manage';
	protected $deletePermission = 'Incoming_Asset.Delete';

	public function __construct()
	{
		parent::__construct();
		$this->load->model('master_model');
		$this->load->library(array('upload', 'Image_lib'));
		$this->template->title('Incoming Asset');
		$this->template->page_icon('fa fa-cubes');

		date_default_timezone_set('Asia/Bangkok');
	}

	public function index()
	{
		$this->asset();
	}

	public function asset()
	{
		$this->auth->restrict($this->viewPermission);

		$no_po			= $this->db->query("(
											SELECT 
												a.no_po, 
												'PO' AS ket, 
												a.nm_supplier AS nm_supplier 
											FROM 
												tran_po_detail b 
												LEFT JOIN tran_po_header a ON b.no_po = a.no_po
											WHERE 1=1
												AND b.id_barang LIKE 'PLA%'
												AND (a.status='WAITING IN' OR a.status='IN PARSIAL') 
											ORDER BY a.no_po ASC
											)
											UNION
											(
											SELECT 
												b.no_non_po AS no_po, 
												'NON-PO' AS ket, 
												'' AS nm_supplier 
											FROM 
												tran_non_po_header b 
											WHERE 
												b.category='asset' 
												AND (b.status !='COMPLETE') 
												AND b.app_status = 'Y' 
											ORDER BY b.no_non_po ASC
											)")->result_array();
		$list_po		= $this->db->group_by('no_ipp')->get_where('warehouse_adjustment', array('category' => 'incoming asset'))->result_array();
		$data_gudang 	= $this->db->group_by('id_gudang_ke')->get_where('warehouse_adjustment', array('category' => 'incoming asset'))->result_array();

		// Tarik data department langsung dari tabel ms_department
		$list_dept 		= $this->db->select('id, nama, nama AS nm_dept')->order_by('nama', 'ASC')->get_where('ms_department', array('deleted_by' => null))->result_array();
		// Tarik data costcenter dari tabel costcenter
		$list_costcenter = $this->db->order_by('nm_costcenter', 'ASC')->get_where('costcenter', array('deleted' => 'N'))->result_array();

		$data = array(
			'title'				=> 'Incoming Asset',
			'list_po'			=> $list_po,
			'data_gudang'		=> $data_gudang,
			'no_po'				=> $no_po,
			'list_dept'			=> $list_dept,
			'list_costcenter'	=> $list_costcenter
		);
		history('View incoming asset');
		$this->template->set($data);
		$this->template->render('asset');
	}

	public function server_side_incoming_asset()
	{
		$requestData	= $_REQUEST;
		$fetch			= $this->query_data_json_incoming_asset(
			$requestData['no_po'],
			$requestData['gudang'],
			$requestData['search']['value'],
			$requestData['order'][0]['column'],
			$requestData['order'][0]['dir'],
			$requestData['start'],
			$requestData['length']
		);
		$totalData		= $fetch['totalData'];
		$totalFiltered	= $fetch['totalFiltered'];
		$query			= $fetch['query'];

		$data	= array();
		$urut1  = 1;
		$urut2  = 0;
		$GET_USERNAME = get_detail_user();
		foreach ($query->result_array() as $row) {
			$total_data     = $totalData;
			$start_dari     = $requestData['start'];
			$asc_desc       = $requestData['order'][0]['dir'];
			if ($asc_desc == 'asc') {
				$nomor = $urut1 + $start_dari;
			}
			if ($asc_desc == 'desc') {
				$nomor = ($total_data - $start_dari) - $urut2;
			}

			$link = '-';
			if (!empty($row['doc'])) {
				$link = "<a href='" . base_url($row['doc']) . "' target='_blank' class='btn btn-xs btn-outline-primary' title='Download Document'><i class='fa fa-download'></i> Doc</a>";
			}

			$TANGGAL = (!empty($row['tanggal'])) ? $row['tanggal'] : $row['created_date'];
			$NM_LENGKAP = (!empty($GET_USERNAME[strtolower($row['created_by'])]['nm_lengkap'])) ? $GET_USERNAME[strtolower($row['created_by'])]['nm_lengkap'] : $row['created_by'];
			$print	= "<a href='" . base_url('incoming_asset/print_incoming_assets/' . $row['kode_trans']) . "' target='_blank' class='btn btn-sm btn-warning text-white' title='Print'><i class='fa fa-print'></i></a>";

			$nestedData 	= array();
			$nestedData[]	= "<div class='text-center'>" . $nomor . "</div>";
			$nestedData[]	= "<div class='text-center fw-bold'>" . $row['kode_trans'] . "</div>";
			$nestedData[]	= "<div class='text-center'>" . date('d-M-Y', strtotime($TANGGAL)) . "</div>";
			$nestedData[]	= "<div class='text-center'>" . $row['no_ipp'] . "</div>";
			
			$nm_dept = get_name('ms_department', 'nama', 'id', $row['id_gudang_ke']);
			if ($nm_dept == '0' || empty($nm_dept)) {
				$nm_dept = '-';
			}
			$nestedData[]	= "<div>" . strtoupper($nm_dept) . "</div>";
			$nestedData[]	= "<div>" . strtoupper($row['pic']) . "</div>";
			$nestedData[]	= "<div>" . (!empty($row['note']) ? strtoupper($row['note']) : '-') . "</div>";
			$nestedData[]	= "<div>" . strtoupper($NM_LENGKAP) . "</div>";
			$nestedData[]	= "<div class='text-center'>" . date('d-M-Y H:i', strtotime($row['created_date'])) . "</div>";
			$nestedData[]	= "<div class='text-center'>" . $link . "</div>";
			$nestedData[]	= "<div class='text-center d-flex justify-content-center gap-1'>
									<button type='button' class='btn btn-sm btn-info text-white detailAjust' title='View Incoming' data-kode_trans='" . $row['kode_trans'] . "' ><i class='fa fa-eye'></i></button>
                                    " . $print . "
								</div>";
			$data[] = $nestedData;
			$urut1++;
			$urut2++;
		}

		$json_data = array(
			"draw"            	=> intval($requestData['draw']),
			"recordsTotal"    	=> intval($totalData),
			"recordsFiltered" 	=> intval($totalFiltered),
			"data"            	=> $data
		);

		echo json_encode($json_data);
	}

	public function query_data_json_incoming_asset($no_po, $gudang, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
	{
		$where_no_po = '';
		if (!empty($no_po)) {
			$where_no_po = " AND a.no_ipp = '" . $this->db->escape_str($no_po) . "' ";
		}

		$where_gudang = '';
		if (!empty($gudang)) {
			$where_gudang = " AND a.id_gudang_ke = '" . $this->db->escape_str($gudang) . "' ";
		}

		$sql = "
			SELECT
				a.*
			FROM
				warehouse_adjustment a
		    WHERE 1=1 AND a.category = 'incoming asset' AND (a.deleted_by IS NULL OR a.deleted_by = '')
				" . $where_no_po . "
				" . $where_gudang . "
			AND(
				a.no_ipp LIKE '%" . $this->db->escape_like_str($like_value) . "%'
				or a.kode_trans LIKE '%" . $this->db->escape_like_str($like_value) . "%'
				OR a.id_gudang_ke LIKE '%" . $this->db->escape_like_str($like_value) . "%'
	        )
		";

		$data['totalData'] = $this->db->query($sql)->num_rows();
		$data['totalFiltered'] = $this->db->query($sql)->num_rows();
		$columns_order_by = array(
			0 => 'created_date',
			1 => 'kode_trans',
			2 => 'no_ipp'
		);

		$order_col = isset($columns_order_by[$column_order]) ? $columns_order_by[$column_order] : 'created_date';
		$order_dir = (strtoupper($column_dir) === 'ASC') ? 'ASC' : 'DESC';

		$sql .= " ORDER BY " . $order_col . " " . $order_dir . " ";
		$sql .= " LIMIT " . intval($limit_start) . " ," . intval($limit_length) . " ";

		$data['query'] = $this->db->query($sql);
		return $data;
	}

	public function modal_incoming_asset()
	{
		$data = $this->input->post();

		$no_po 			= $data['no_po'];
		$id_dept 		= isset($data['id_dept']) ? $data['id_dept'] : '';
		$id_costcenter 	= isset($data['id_costcenter']) ? $data['id_costcenter'] : '';
		$tanggal_trans 	= isset($data['tanggal_trans']) ? $data['tanggal_trans'] : date('Y-m-d');
		$pic 			= isset($data['pic']) ? strtolower($data['pic']) : '';
		$note 			= isset($data['note']) ? strtolower($data['note']) : '';

		$cek_type = substr($no_po, 0, 3);
		if ($cek_type == 'POX' || substr($no_po, 0, 2) == 'PO') {
			$result	= $this->db->where('qty_in < qty_po')->like('id_barang', 'PLA')->get_where('tran_po_detail', array('no_po' => $no_po))->result_array();
		} else {
			$result	= $this->db->where('qty_in < qty_rev')->get_where('tran_non_po_detail', array('no_non_po' => $no_po))->result_array();
		}

		$data = array(
			'no_po' 		=> $no_po,
			'id_dept' 		=> $id_dept,
			'id_costcenter' => $id_costcenter,
			'tanggal_trans' => $tanggal_trans,
			'pic' 			=> $pic,
			'note' 			=> $note,
			'result' 		=> $result
		);

		$this->load->view('modal_incoming_asset', $data);
	}

	public function process_incoming_asset()
	{
		$data 			= $this->input->post();
		$data_session	= $this->session->userdata;
		$no_po			= $data['no_po'];
		$id_dept		= isset($data['id_dept']) ? $data['id_dept'] : '';
		$id_costcenter	= isset($data['id_costcenter']) ? $data['id_costcenter'] : '';
		$pic			= isset($data['pic']) ? $data['pic'] : '';
		$note			= isset($data['note']) ? $data['note'] : '';
		$tanggal		= isset($data['tanggal']) ? $data['tanggal'] : date('Y-m-d');
		$addInMat		= isset($data['addInMat']) ? $data['addInMat'] : array();
		$adjustment 	= isset($data['adjustment']) ? $data['adjustment'] : 'IN';
		$Ym 			= date('ym');
		$dateTime		= date('Y-m-d H:i:s');
		
		$UserName = $this->auth->user_name() ?: ($data_session['ORI_User']['username'] ?? $this->session->userdata('app_session')['username'] ?? 'admin');
		$cek_type = substr($no_po, 0, 3);

		if ($adjustment == 'IN' && !empty($addInMat)) {
			$histHlp = "Adjustment incoming asset / " . $no_po;

			// pengurutan kode
			$srcMtr			= "SELECT MAX(kode_trans) as maxP FROM warehouse_adjustment WHERE kode_trans LIKE 'TRS" . $Ym . "%' ";
			$resultMtr		= $this->db->query($srcMtr)->result_array();
			$angkaUrut2		= isset($resultMtr[0]['maxP']) ? $resultMtr[0]['maxP'] : '';
			$urutan2		= (int)substr($angkaUrut2, 7, 4);
			$urutan2++;
			$urut2			= sprintf('%04s', $urutan2);
			$kode_trans		= "TRS" . $Ym . $urut2;

			$ArrUpdate		= array();
			$ArrDeatilAdj	= array();
			$ArrDeatilChk	= array();
			$ArrJurnal		= array();
			$SumMat = 0;
			foreach ($addInMat as $val => $valx) {
				$qtyOrder 	= (float)str_replace(',', '', $valx['qty_rev']);
				$qtyIN 		= (float)str_replace(',', '', $valx['qty_in']);
				$SumMat 	+= $qtyIN;

				// update detail purchase
				if ($cek_type == 'POX' || substr($no_po, 0, 2) == 'PO') {
					$result_det	= $this->db->get_where('tran_po_detail', array('id' => $valx['id']))->result_array();
				} else {
					$result_det	= $this->db->get_where('tran_non_po_detail', array('id' => $valx['id']))->result_array();
				}

				$current_qty_in = isset($result_det[0]['qty_in']) ? (float)$result_det[0]['qty_in'] : 0;
				$ArrUpdate[$val]['id'] 			= $valx['id'];
				$ArrUpdate[$val]['qty_in'] 		= $current_qty_in + $qtyIN;

				// detail adjustment
				$ArrDeatilAdj[$val]['no_ipp'] 			= $no_po;
				$ArrDeatilAdj[$val]['kode_trans'] 		= $kode_trans;
				$ArrDeatilAdj[$val]['id_po_detail'] 	= $valx['id'];
				$ArrDeatilAdj[$val]['nm_material'] 		= strtolower($valx['nm_barang']);
				$ArrDeatilAdj[$val]['nm_category'] 		= strtolower($valx['spec']);
				$ArrDeatilAdj[$val]['qty_order'] 		= $qtyOrder;
				$ArrDeatilAdj[$val]['qty_oke'] 			= $qtyIN;
				$ArrDeatilAdj[$val]['keterangan'] 		= strtolower($valx['keterangan']);
				$ArrDeatilAdj[$val]['no_ba'] 		    = null;
				$ArrDeatilAdj[$val]['ket_req_po'] 		= strtolower($valx['pemeriksa']);
				$ArrDeatilAdj[$val]['update_by'] 		= $UserName;
				$ArrDeatilAdj[$val]['update_date'] 		= $dateTime;
				$ArrDeatilAdj[$val]['check_qty_oke'] 	= $qtyIN;
				$ArrDeatilAdj[$val]['check_keterangan'] = strtolower($valx['keterangan']);

				// detail check
				$ArrDeatilChk[$val]['no_ipp'] 		= $no_po;
				$ArrDeatilChk[$val]['id_detail'] 	= NULL;
				$ArrDeatilChk[$val]['kode_trans'] 	= $kode_trans;
				$ArrDeatilChk[$val]['nm_material'] 	= strtolower($valx['nm_barang']);
				$ArrDeatilChk[$val]['nm_category'] 	= strtolower($valx['spec']);
				$ArrDeatilChk[$val]['qty_order'] 	= $qtyOrder;
				$ArrDeatilChk[$val]['qty_oke'] 		= $qtyIN;
				$ArrDeatilChk[$val]['keterangan'] 	= strtolower($valx['keterangan']);
				$ArrDeatilChk[$val]['update_by'] 	= $UserName;
				$ArrDeatilChk[$val]['update_date'] 	= $dateTime;

				// detail jurnal
				if ($cek_type == 'NPO') {
					$GET_UNITPRICE = $this->db->select('price_unit_rev AS unit_price, id_barang')->get_where('tran_non_po_detail', array('id' => $valx['id']))->result();
				} else {
					$GET_UNITPRICE = $this->db->select('net_price AS unit_price, id_barang')->get_where('tran_po_detail', array('id' => $valx['id']))->result();
				}
				$ArrJurnal[$val]['unit_price'] 		= (!empty($GET_UNITPRICE[0]->unit_price)) ? $GET_UNITPRICE[0]->unit_price : 0;
				$ArrJurnal[$val]['qty'] 			= $qtyIN;
				$ArrJurnal[$val]['no_po'] 			= $no_po;
				$ArrJurnal[$val]['nm_barang'] 		= strtolower($valx['nm_barang']);
				$ArrJurnal[$val]['id_barang'] 		= (!empty($GET_UNITPRICE[0]->id_barang)) ? $GET_UNITPRICE[0]->id_barang : NULL;
			}

			$file_name = NULL;
			if (!empty($_FILES["upload_doc"]["name"])) {
				$target_dir     = "assets/file/produksi/";
				$target_dir_u   = FCPATH . "assets/file/produksi/";
				if (!file_exists($target_dir_u)) {
					@mkdir($target_dir_u, 0777, true);
				}
				$imageFileType  = strtolower(pathinfo($_FILES["upload_doc"]["name"], PATHINFO_EXTENSION));
				$name_file      = 'doc_incoming_' . date('YmdHis');
				$nama_upload    = $target_dir_u . $name_file . "." . $imageFileType;
				$file_name    	= $target_dir . $name_file . "." . $imageFileType;

				if (!empty($_FILES["upload_doc"]["tmp_name"])) {
					move_uploaded_file($_FILES["upload_doc"]["tmp_name"], $nama_upload);
				}
			}

			$ArrInsertH = array(
				'kode_trans' 		=> $kode_trans,
				'no_ipp' 			=> $no_po,
				'tanggal' 			=> $tanggal,
				'category' 			=> 'incoming asset',
				'jumlah_mat' 		=> $SumMat,
				'kd_gudang_dari' 	=> 'PURCHASE',
				'id_gudang_ke' 		=> $id_dept,
				'kd_gudang_ke' 		=> $id_costcenter,
				'pic' 				=> $pic,
				'note' 				=> $note,
				'created_by' 		=> $UserName,
				'created_date' 		=> $dateTime,
				'checked' 			=> 'Y',
				'jumlah_mat_check' 	=> $SumMat,
				'doc' 				=> $file_name,
				'checked_by' 		=> $UserName,
				'checked_date' 		=> $dateTime
			);

			$ArrHeader2 = array(
				'status' => 'COMPLETE',
			);
			$ArrHeader3 = array(
				'status' => 'IN PARSIAL',
			);

			$this->db->trans_start();
			if ($cek_type == 'NPO') {
				if (!empty($ArrUpdate)) {
					$this->db->update_batch('tran_non_po_detail', $ArrUpdate, 'id');
				}
				$qCheck = "SELECT * FROM tran_non_po_detail WHERE no_non_po='" . $this->db->escape_str($no_po) . "' AND qty_in < qty_rev ";
				$NumChk = $this->db->query($qCheck)->num_rows();
				if ($NumChk < 1) {
					$this->db->where('no_non_po', $no_po);
					$this->db->update('tran_non_po_header', $ArrHeader2);
				} else {
					$this->db->where('no_non_po', $no_po);
					$this->db->update('tran_non_po_header', $ArrHeader3);
				}
			} else {
				if (!empty($ArrUpdate)) {
					$this->db->update_batch('tran_po_detail', $ArrUpdate, 'id');
				}
				$qCheck = "SELECT * FROM tran_po_detail WHERE no_po='" . $this->db->escape_str($no_po) . "' AND qty_in < qty_po ";
				$NumChk = $this->db->query($qCheck)->num_rows();
				if ($NumChk < 1) {
					$this->db->where('no_po', $no_po);
					$this->db->update('tran_po_header', $ArrHeader2);
				} else {
					$this->db->where('no_po', $no_po);
					$this->db->update('tran_po_header', $ArrHeader3);
				}
			}

			$this->db->insert('warehouse_adjustment', $ArrInsertH);
			if (!empty($ArrDeatilAdj)) {
				$this->db->insert_batch('warehouse_adjustment_detail', $ArrDeatilAdj);
			}
			if (!empty($ArrDeatilChk)) {
				$this->db->insert_batch('warehouse_adjustment_check', $ArrDeatilChk);
			}
			$this->db->trans_complete();

			if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
				$Arr_Data = array(
					'pesan'		=> 'Save process failed. Please try again later ...',
					'status'	=> 0
				);
			} else {
				$this->db->trans_commit();
				$Arr_Data = array(
					'pesan'		=> 'Save process success. Thanks ...',
					'status'	=> 1
				);
				if (function_exists('insert_jurnal_department')) {
					insert_jurnal_department($ArrJurnal, NULL, NULL, $kode_trans, 'incoming asset', 'incoming asset', 'incoming asset');
				}
				history($histHlp);
			}
		} else {
			$Arr_Data = array(
				'pesan'		=> 'No item data to process.',
				'status'	=> 0
			);
		}

		echo json_encode($Arr_Data);
	}

	public function modal_detail()
	{
		$kode_trans     = $this->uri->segment(3);
		$tanda     		= $this->uri->segment(4);

		$sql 			= "SELECT * FROM warehouse_adjustment_detail WHERE kode_trans='" . $this->db->escape_str($kode_trans) . "' ";
		$result			= $this->db->query($sql)->result_array();

		$sql_header 	= "SELECT * FROM warehouse_adjustment WHERE kode_trans='" . $this->db->escape_str($kode_trans) . "' ";
		$result_header	= $this->db->query($sql_header)->result();

		$data = array(
			'result' 		=> $result,
			'tanda' 		=> $tanda,
			'checked' 		=> isset($result_header[0]->checked) ? $result_header[0]->checked : '',
			'kode_trans' 	=> isset($result_header[0]->kode_trans) ? $result_header[0]->kode_trans : $kode_trans,
			'no_po' 		=> isset($result_header[0]->no_ipp) ? $result_header[0]->no_ipp : '',
			'dated' 		=> isset($result_header[0]->created_date) ? date('ymdhis', strtotime($result_header[0]->created_date)) : '',
			'resv' 			=> (!empty($result_header[0]->tanggal)) ? date('d F Y', strtotime($result_header[0]->tanggal)) : (isset($result_header[0]->created_date) ? date('d F Y', strtotime($result_header[0]->created_date)) : '')
		);

		$this->load->view('modal_detail', $data);
	}

	public function print_incoming_assets()
	{
		$kode_trans     = $this->uri->segment(3);
		$data_session	= $this->session->userdata;
		$printby		= $this->auth->user_name() ?: ($data_session['ORI_User']['username'] ?? $this->session->userdata('app_session')['username'] ?? 'admin');

		$data = array(
			'printby' 		=> $printby,
			'kode_trans' 	=> $kode_trans
		);

		history('Print Incoming Assets ' . $kode_trans);
		$this->load->view('print_incoming_assets', $data);
	}
}
