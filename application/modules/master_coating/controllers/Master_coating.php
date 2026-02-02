<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 *
 */
class Master_coating extends Admin_Controller
{
	//Permission
	protected $viewPermission 	= 'Master_coating.View';
	protected $addPermission  	= 'Master_coating.Add';
	protected $managePermission = 'Master_coating.Manage';
	protected $deletePermission = 'Master_coating.Delete';

	public function __construct()
	{
		parent::__construct();
		$this->template->title('Master Data Coating');
		date_default_timezone_set('Asia/Bangkok');
	}

	public function index()
	{
		$this->auth->restrict($this->viewPermission);

		$data = $this->db->get_where('ms_coating', array('deleted' => 'N'))->result();

		history("View data coating");
		$this->template->set('results', $data);
		$this->template->title('Master Coating');
		$this->template->render('index');
	}

	public function add($id = null)
	{
		if ($this->input->post()) {
			$data = $this->input->post();

			$session 	= $this->session->userdata('app_session');
			$username 	= $session['id_user'];
			$datetime 	= date('Y-m-d H:i:s');

			$id 		= $data['id'];
			$nama     	= trim(strtolower($data['nama']));

			$field_by   = (empty($id)) ? 'created_by' : 'updated_by';
			$field_date = (empty($id)) ? 'created_date' : 'updated_date';
			$field_hist = (empty($id)) ? 'Add' : 'Edit';

			$ArrHeader = array(
				'nama'		=> $nama,
				$field_by	=> $username,
				$field_date	=> $datetime
			);

			$this->db->trans_start();
			if (empty($id)) {
				$this->db->insert('ms_coating', $ArrHeader);
			}
			if (!empty($id)) {
				$this->db->where('id', $id);
				$this->db->update('ms_coating', $ArrHeader);
			}
			$this->db->trans_complete();

			if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
				$Arr_Data	= array(
					'pesan'		=> 'Process Failed !',
					'status'	=> 0
				);
			} else {
				$this->db->trans_commit();
				$Arr_Data	= array(
					'pesan'		=> 'Process Success !',
					'status'	=> 1
				);
				history($field_hist . " data unit " . $id);
			}

			echo json_encode($Arr_Data);
		} else {
			$session  = $this->session->userdata('app_session');
			$header   = $this->db->get_where('ms_coating', array('id' => $id))->result();

			$data = [
				'header' => $header,
			];
			$this->template->title('Add Master Coating');
			$this->template->page_icon('fa fa-edit');
			$this->template->render('add', $data);
		}
	}

	public function hapus()
	{
		$data = $this->input->post();
		$session 		= $this->session->userdata('app_session');
		$code_material  = $data['id'];

		$ArrHeader		= array(
			'deleted'			  => "Y",
			'deleted_by'	  => $session['id_user'],
			'deleted_date'	=> date('Y-m-d H:i:s')
		);

		$this->db->trans_start();
		$this->db->where('id', $code_material);
		$this->db->update('ms_coating', $ArrHeader);
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$Arr_Data	= array(
				'pesan'		=> 'Process Failed !',
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$Arr_Data	= array(
				'pesan'		=> 'Process Success !',
				'status'	=> 1
			);
			history("Delete data unit " . $code_material);
		}

		echo json_encode($Arr_Data);
	}
}
