<?php

if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

/*
 * @author Ichsan
 * @copyright Copyright (c) 2019, Ichsan
 *
 * This is controller for Master Supplier
 */

class Master_customers extends Admin_Controller
{
	//Permission
	protected $viewPermission 	= 'Master_customer.View';
	protected $addPermission  	= 'Master_customer.Add';
	protected $managePermission = 'Master_customer.Manage';
	protected $deletePermission = 'Master_customer.Delete';

	public function __construct()
	{
		parent::__construct();

		$this->load->library(array('upload', 'Image_lib'));
		$this->load->model(array(
			'Master_customers/Customer_model',
			'Aktifitas/aktifitas_model',
		));
		$this->template->title('Manage Data Supplier');
		$this->template->page_icon('fa fa-building-o');

		date_default_timezone_set('Asia/Bangkok');
	}

	public function index()
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-users');
		$deleted = 'active';
		$category = $this->Customer_model->get_data('child_customer_category', 'activation', $deleted);
		$customer 			= $this->Customer_model->getCustomer();
		$data = [
			'customer' => $customer,
			'category' => $category
		];
		$this->template->set('results', $data);
		$this->template->title('Master Customer');
		$this->template->render('index');
	}
	public function add()
	{
		$this->auth->restrict($this->viewPermission);
		$aktif = 'active';
		$category = $this->Customer_model->get_data('child_customer_category', 'activation', $aktif);
		$prov = $this->Customer_model->get_data('prov');
		$karyawan = $this->db->get_where('employee', array('department' => 2, 'deleted' => "N"))->result();
		$payment_terms = $this->db->get_where('list_help', array('group_by' => 'top invoice', 'sts' => "Y"))->result();

		$data = [
			'category' => $category,
			'prov' => $prov,
			'karyawan' => $karyawan,
			'payment_terms' => $payment_terms,
		];

		$this->template->set('results', $data);
		$this->template->title('Tambah Customer');
		$this->template->page_icon('fa fa-users');
		$this->template->render('add');
	}
	public function edit($id)
	{
		$this->auth->restrict($this->viewPermission);
		$aktif = 'active';
		$cus = $this->db->get_where('master_customers', array('id_customer' => $id))->result();
		$pic = $this->db->get_where('child_customer_pic', array('id_customer' => $id))->result();
		$cate = $this->db->get_where('child_category_customer', array('id_customer' => $id))->result();
		$exis = $this->db->get_where('child_customer_existing', array('id_customer' => $id))->result();
		$rate = $this->db->get_where('child_customer_rate', array('id_customer' => $id))->result();
		$category = $this->Customer_model->get_data('child_customer_category', 'activation', $aktif);
		$prov = $this->Customer_model->get_data('prov');
		$kabkot = $this->Customer_model->get_data('kabkot');
		$kec = $this->Customer_model->get_data('kec');
		$karyawan = $this->db->get_where('employee', array('department' => 2, 'deleted' => "N"))->result();
		$payment_terms = $this->db->get_where('list_help', array('group_by' => 'top invoice', 'sts' => "Y"))->result();

		$data = [
			'cus'	=> $cus,
			'category' => $category,
			'cate' => $cate,
			'exis' => $exis,
			'rate' => $rate,
			'kec' => $kec,
			'kabkot' => $kabkot,
			'prov' => $prov,
			'pic' => $pic,
			'payment_terms' => $payment_terms,
			'karyawan' => $karyawan
		];

		$this->template->set('results', $data);
		$this->template->title('Edit Customer');
		$this->template->page_icon('fa fa-users');
		$this->template->render('add');
	}
	public function save()
	{
		$this->auth->restrict($this->addPermission);
		$post = $this->input->post();

		// Ambil flags Y/N
		$flags = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu', 'berita_acara', 'faktur', 'tdp', 'real_po', 'ttd_specimen', 'payement_certificate', 'photo', 'siup', 'spk', 'delivery_order', 'need_npwp', 'ditagih', 'sj', 'invoice'];
		foreach ($flags as $field) {
			$$field = isset($post[$field]) ? 'Y' : 'N';
		}

		$chanel = [];
		if ($this->input->post('chanel_toko')) $chanel[] = $this->input->post('chanel_toko');
		if ($this->input->post('chanel_project')) $chanel[] = $this->input->post('chanel_project');

		$isUpdate = isset($post['id_customer']) && !empty($post['id_customer']);
		$code = $isUpdate ? $post['id_customer'] : $this->Customer_model->generate_id();

		$header = [
			'id_category_customer' => $post['id_category_customer'],
			'name_customer' => $post['name_customer'],
			'telephone' => $post['telephone'],
			'telephone_2' => $post['telephone_2'],
			'fax' => $post['fax'],
			'email' => $post['email'],
			'start_date' => $post['start_date'],
			'id_karyawan' => $post['id_karyawan'],
			'id_prov' => $post['id_prov'],
			'id_kabkot' => $post['id_kabkot'],
			'id_kec' => $post['id_kec'],
			'address_office' => $post['address_office'],
			'zip_code' => $post['zip_code'],
			'longitude' => $post['longitude'],
			'latitude' => $post['latitude'],
			'activation' => $post['activation'],
			// 'facility' => $post['facility'],
			'kategori_cust' => $post['kategori_cust'],
			'kategori_toko' => $post['kategori_toko'],
			'chanel_pemasaran' => implode(', ', $chanel),
			'persentase' => $post['persentase'],
			'tahun_mulai' => $post['tahun_mulai'],
			'name_bank' => $post['name_bank'],
			'no_rekening' => $post['no_rekening'],
			'nama_rekening' => $post['nama_rekening'],
			'alamat_bank' => $post['alamat_bank'],
			'swift_code' => $post['swift_code'],
			'npwp' => $post['npwp'],
			'npwp_name' => $post['npwp_name'],
			'npwp_address' => $post['npwp_address'],
			'payment_term' => $post['payment_term'],
			'nominal_dp' => $post['nominal_dp'],
			'sisa_pembayaran' => $post['sisa_pembayaran'],
			'start_recive' => $post['start_recive'],
			'end_recive' => $post['end_recive'],
			'adress_invoice' => $post['address_invoice'],
			'senin' => $senin,
			'selasa' => $selasa,
			'rabu' => $rabu,
			'kamis' => $kamis,
			'jumat' => $jumat,
			'sabtu' => $sabtu,
			'minggu' => $minggu,
			'berita_acara' => $berita_acara,
			'faktur' => $faktur,
			'sj' => $sj,
			'invoice' => $invoice,
			'tdp' => $tdp,
			'real_po' => $real_po,
			'ttd_specimen' => $ttd_specimen,
			'payement_certificate' => $payement_certificate,
			'photo' => $photo,
			'siup' => $siup,
			'spk' => $spk,
			'delivery_order' => $delivery_order,
			'need_npwp' => $need_npwp,
			'ditagih' => $ditagih,
			'deleted' => '0',
			'created_on' => date('Y-m-d H:i:s'),
			'created_by' => $this->auth->user_id()
		];

		$this->db->trans_begin();

		if ($isUpdate) {
			$this->db->where('id_customer', $code)->update('master_customers', $header);
			$this->db->delete('child_customer_pic', ['id_customer' => $code]);
			$this->db->delete('child_category_customer', ['id_customer' => $code]);
			$this->db->delete('child_customer_existing', ['id_customer' => $code]);
			$this->db->delete('child_customer_rate', ['id_customer' => $code]);
		} else {
			$header['id_customer'] = $code;
			$this->db->insert('master_customers', $header);
		}

		if (isset($post['data1'])) {
			foreach ($post['data1'] as $d1) {
				$this->db->insert('child_customer_pic', array_merge($d1, ['id_customer' => $code]));
			}
		}

		if (isset($post['data2'])) {
			foreach ($post['data2'] as $d2) {
				$this->db->insert('child_category_customer', [
					'id_customer' => $code,
					'name_category_customer' => $d2['id_category_customer'],
				]);
			}
		}

		if (isset($post['data3'])) {
			foreach ($post['data3'] as $d3) {
				$this->db->insert('child_customer_existing', array_merge($d3, ['id_customer' => $code]));
			}
		}

		if (isset($post['data4'])) {
			$data = $post['data4'];
			$data['id_customer'] = $code;

			$hasil_nilai = $this->hitung_nilai_customer($data);
			$data['score'] = $hasil_nilai['score'];
			$data['kelas'] = $hasil_nilai['kelas'];

			$this->db->insert('child_customer_rate', $data);
		}

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			echo json_encode(['status' => 0, 'pesan' => 'Gagal menyimpan data.']);
		} else {
			$this->db->trans_commit();
			echo json_encode(['status' => 1, 'pesan' => $isUpdate ? 'Berhasil update.' : 'Berhasil tambah data.']);
		}
	}

	public function view($id)
	{
		$this->auth->restrict($this->viewPermission);
		$aktif = 'active';
		$mode = 'view';
		$cus = $this->db->get_where('master_customers', array('id_customer' => $id))->result();
		$pic = $this->db->get_where('child_customer_pic', array('id_customer' => $id))->result();
		$cate = $this->db->get_where('child_category_customer', array('id_customer' => $id))->result();
		$exis = $this->db->get_where('child_customer_existing', array('id_customer' => $id))->result();
		$rate = $this->db->get_where('child_customer_rate', array('id_customer' => $id))->result();
		$category = $this->Customer_model->get_data('child_customer_category', 'activation', $aktif);
		$prov = $this->Customer_model->get_data('prov');
		$kabkot = $this->Customer_model->get_data('kabkot');
		$kec = $this->Customer_model->get_data('kec');
		$karyawan = $this->db->get_where('employee', array('department' => 2, 'deleted' => "N"))->result();
		$payment_terms = $this->db->get_where('list_help', array('group_by' => 'top invoice', 'sts' => "Y"))->result();

		$data = [
			'cus'	=> $cus,
			'category' => $category,
			'cate' => $cate,
			'exis' => $exis,
			'rate' => $rate,
			'kec' => $kec,
			'kabkot' => $kabkot,
			'prov' => $prov,
			'pic' => $pic,
			'payment_terms' => $payment_terms,
			'karyawan' => $karyawan,
			'mode' => $mode
		];

		$this->template->set('results', $data);
		$this->template->title('View Customer');
		$this->template->page_icon('fa fa-users');
		$this->template->render('add');
	}

	private function hitung_nilai_customer($data)
	{
		$skor = 0;

		// Ontime
		$ontime = strtolower($data['ontime'] ?? '');
		if ($ontime === 'yes') $skor += 20;
		elseif ($ontime === 'new') $skor += 10;

		// Toko Sendiri
		$toko = strtolower($data['toko_sendiri'] ?? '');
		if ($toko === 'yes') $skor += 15;

		// Armada Pickup
		$pickup = (int)($data['armada_pickup'] ?? 0);
		if ($pickup >= 5) $skor += 15;
		elseif ($pickup >= 3) $skor += 10;
		elseif ($pickup >= 1) $skor += 5;

		// Armada Truck
		if ((int)($data['armada_truck'] ?? 0) >= 1) $skor += 10;

		// Attitude
		$attitude = strtolower($data['attitude'] ?? '');
		if ($attitude === 'yes') $skor += 10;

		// Luas Tanah
		$luas = (int)($data['luas_tanah'] ?? 0);
		if ($luas > 1000) $skor += 10;
		elseif ($luas >= 500) $skor += 5;

		// PBB
		$pbb = strtolower($data['pbb'] ?? '');
		if ($pbb === 'yes') $skor += 10;

		// Klasifikasi
		if ($skor >= 70) $kelas = 'TITANIUM';
		elseif ($skor >= 60) $kelas = 'PLATINUM';
		elseif ($skor >= 45) $kelas = 'GOLD';
		elseif ($skor >= 30) $kelas = 'SILVER';
		else $kelas = 'BRONZE';

		return ['kelas' => $kelas, 'score' => $skor];
	}

	public function update_score_dan_kelas_customer()
	{
		$customers = $this->db->get('child_customer_rate')->result_array();

		foreach ($customers as $cust) {
			$score = 0;

			// Hitung skor
			if (strtolower($cust['ontime']) === 'yes') $score += 20;
			elseif (strtolower($cust['ontime']) === 'new') $score += 10;

			if (strtolower($cust['toko_sendiri']) === 'yes') $score += 15;

			$pickup = (int)$cust['armada_pickup'];
			if ($pickup >= 5) $score += 15;
			elseif ($pickup >= 3) $score += 10;
			elseif ($pickup >= 1) $score += 5;

			if ((int)$cust['armada_truck'] >= 1) $score += 10;

			if (strtolower($cust['attitude']) === 'yes') $score += 10;

			$luas = (int)$cust['luas_tanah'];
			if ($luas > 1000) $score += 10;
			elseif ($luas >= 500) $score += 5;

			if (strtolower($cust['pbb']) === 'yes') $score += 10;

			// Tentukan kelas
			if ($score >= 70) $kelas = 'TITANIUM';
			elseif ($score >= 60) $kelas = 'PLATINUM';
			elseif ($score >= 45) $kelas = 'GOLD';
			elseif ($score >= 30) $kelas = 'SILVER';
			else $kelas = 'BRONZE';

			// Update ke tabel
			$this->db->where('id', $cust['id'])->update('child_customer_rate', [
				'score' => $score,
				'kelas' => $kelas
			]);
		}

		echo "Selesai update score dan kelas semua customer.";
	}


	//TRASH

	public function viewCustomer($id)
	{
		$this->auth->restrict($this->viewPermission);
		$this->template->page_icon('fa fa-page');
		$aktif = 'active';
		$cus = $this->db->get_where('master_customers', array('id_customer' => $id))->result();
		$pic = $this->db->get_where('child_customer_pic', array('id_customer' => $id))->result();
		$cate = $this->db->get_where('child_category_customer', array('id_customer' => $id))->result();
		$exis = $this->db->get_where('child_customer_existing', array('id_customer' => $id))->result();
		$rate = $this->db->get_where('child_customer_rate', array('id_customer' => $id))->result();
		$category = $this->Customer_model->get_data('child_customer_category', 'activation', $aktif);
		$prov = $this->Customer_model->get_data('prov');
		$kabkot = $this->Customer_model->get_data('kabkot');
		$kec = $this->Customer_model->get_data('kec');
		$karyawan = $this->db->get_where('employee', array('department' => 2, 'deleted' => "N"))->result();
		$payment_terms = $this->db->get_where('list_help', array('group_by' => 'top invoice', 'sts' => "Y"))->result();

		$data = [
			'cus'	=> $cus,
			'category' => $category,
			'cate' => $cate,
			'exis' => $exis,
			'rate' => $rate,
			'kec' => $kec,
			'kabkot' => $kabkot,
			'prov' => $prov,
			'pic' => $pic,
			'payment_terms' => $payment_terms,
			'karyawan' => $karyawan
		];
		$this->template->set('results', $data);
		$this->template->title('View Customer');
		$this->template->render('view_customer');
	}
	public function editCustomer($id)
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-edit');
		$aktif = 'active';
		$cus = $this->db->get_where('master_customers', array('id_customer' => $id))->result();
		$pic = $this->db->get_where('child_customer_pic', array('id_customer' => $id))->result();
		$cate = $this->db->get_where('child_category_customer', array('id_customer' => $id))->result();
		$exis = $this->db->get_where('child_customer_existing', array('id_customer' => $id))->result();
		$rate = $this->db->get_where('child_customer_rate', array('id_customer' => $id))->result();
		$category = $this->Customer_model->get_data('child_customer_category', 'activation', $aktif);
		$prov = $this->Customer_model->get_data('prov');
		$kabkot = $this->Customer_model->get_data('kabkot');
		$kec = $this->Customer_model->get_data('kec');
		$karyawan = $this->db->get_where('employee', array('department' => 2, 'deleted' => "N"))->result();
		$payment_terms = $this->db->get_where('list_help', array('group_by' => 'top invoice', 'sts' => "Y"))->result();

		$data = [
			'cus'	=> $cus,
			'category' => $category,
			'cate' => $cate,
			'exis' => $exis,
			'rate' => $rate,
			'kec' => $kec,
			'kabkot' => $kabkot,
			'prov' => $prov,
			'pic' => $pic,
			'payment_terms' => $payment_terms,
			'karyawan' => $karyawan
		];
		$this->template->set('results', $data);
		$this->template->title('Edit Customer');
		$this->template->render('edit_customer');
	}
	public function EditCategory($id)
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-edit');
		$category = $this->db->get_where('child_customer_category', array('id_category_customer' => $id))->result();
		$data = [
			'category' => $category,
		];
		$this->template->set('results', $data);
		$this->template->title('Edit Customer');
		$this->template->render('edit_category');
	}
	public function ViewCategory($id)
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-edit');
		$category = $this->db->get_where('child_supplier_category', array('id_category_supplier' => $id))->result();
		$data = [
			'category' => $category,
		];
		$this->template->set('results', $data);
		$this->template->title('Edit Suplier');
		$this->template->render('view_category');
	}
	public function viewInventory()
	{
		$this->auth->restrict($this->viewPermission);
		$id 	= $this->input->post('id');
		$inven = $this->db->get_where('ms_inventory_category1', array('id_category1' => $id))->result();
		$deleted = '0';
		$komposisi = $this->db->get_where('ms_compotition', array('id_category1' => $id, 'deleted' => $deleted))->result();
		$lvl1 = $this->Inventory_2_model->get_data('ms_inventory_type');
		$data = [
			'inven' => $inven,
			'komposisi' => $komposisi,
			'lvl1' => $lvl1
		];
		$this->template->set('results', $data);
		$this->template->render('view_inventory');
	}
	public function addCustomer()
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$aktif = 'active';
		$category = $this->Customer_model->get_data('child_customer_category', 'activation', $aktif);
		$prov = $this->Customer_model->get_data('prov');
		$karyawan = $this->db->get_where('employee', array('department' => 2, 'deleted' => "N"))->result();
		$payment_terms = $this->db->get_where('list_help', array('group_by' => 'top invoice', 'sts' => "Y"))->result();

		$data = [
			'category' => $category,
			'prov' => $prov,
			'karyawan' => $karyawan,
			'payment_terms' => $payment_terms,
		];

		$this->template->set('results', $data);
		$this->template->title('Add Customer');
		$this->template->render('add_customer');
	}

	public function addInternational()
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$category = $this->Suplier_model->get_data('child_supplier_category');
		$negara = $this->Suplier_model->get_data('negara');
		$data = [
			'category' => $category,
			'negara' => $negara
		];
		$this->template->set('results', $data);
		$this->template->title('Add Suplier Local');
		$this->template->render('add_international');
	}
	public function addCategory()
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$this->template->title('Add Customer Local');
		$this->template->render('add_category');
	}

	public function delDetail()
	{
		$this->auth->restrict($this->deletePermission);
		$id = $this->input->post('id');
		// print_r($id);
		// exit();
		$data = [
			'deleted' 		=> '1',
			'deleted_by' 	=> $this->auth->user_id()
		];

		$this->db->trans_begin();
		$this->db->where('id_compotition', $id)->update("ms_compotition", $data);

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$status	= array(
				'pesan'		=> 'Gagal Save Item. Thanks ...',
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
				'pesan'		=> 'Success Save Item. Thanks ...',
				'status'	=> 1
			);
		}

		echo json_encode($status);
	}

	public function deleteCategory()
	{
		$this->auth->restrict($this->deletePermission);
		$id = $this->input->post('id');
		// print_r($id);
		// exit();
		$data = [
			'activation' 		=> 'inactive',
			'deleted_by' 	=> $this->auth->user_id()
		];
		$this->db->trans_begin();
		$this->db->where('id_category_customer', $id)->update("child_customer_category", $data);

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$status	= array(
				'pesan'		=> 'Gagal Save Item. Thanks ...',
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
				'pesan'		=> 'Success Save Item. Thanks ...',
				'status'	=> 1
			);
		}

		echo json_encode($status);
	}
	public function deletelokal()
	{
		$this->auth->restrict($this->deletePermission);
		$id = $this->input->post('id');

		$data = [
			'deleted' 		=> '1',
			'deleted_by' 	=> $this->auth->user_id()
		];
		$this->db->trans_begin();
		$this->db->where('id_customer', $id)->update("master_customers", $data);

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$status	= array(
				'pesan'		=> 'Gagal Save Item. Thanks ...',
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
				'pesan'		=> 'Success Save Item. Thanks ...',
				'status'	=> 1
			);
		}

		echo json_encode($status);
	}
	public function deleteinternational()
	{
		$this->auth->restrict($this->deletePermission);
		$id = $this->input->post('id');
		// print_r($id);
		// exit();
		$data = [
			'deleted' 		=> '1',
			'deleted_by' 	=> $this->auth->user_id()
		];
		$this->db->trans_begin();
		$this->db->where('id_suplier', $id)->update("master_supplier", $data);

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$status	= array(
				'pesan'		=> 'Gagal Save Item. Thanks ...',
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
				'pesan'		=> 'Success Save Item. Thanks ...',
				'status'	=> 1
			);
		}

		echo json_encode($status);
	}
	public function saveNewCategory()
	{
		$this->auth->restrict($this->addPermission);
		$post = $this->input->post();
		$code = $this->Customer_model->generate_Category();
		$this->db->trans_begin();
		$data = [
			'id_category_customer'		=> $code,
			'name_category_customer'	=> $post['name_category_customer'],
			'customer_code'				=> $post['customer_code'],
			'activation'				=> 'active',
			'created_on'				=> date('Y-m-d H:i:s'),
			'created_by'				=> $this->auth->user_id()
		];

		$insert = $this->db->insert("child_customer_category", $data);

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$status	= array(
				'pesan'		=> 'Gagal Save Item. Thanks ...',
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
				'pesan'		=> 'Success Save Item. Thanks ...',
				'status'	=> 1
			);
		}

		echo json_encode($status);
	}
	public function saveNewcustomer()
	{
		$this->auth->restrict($this->addPermission);
		$post = $this->input->post();
		if (isset($post['senin'])) {
			$senin = 'Y';
		} else {
			$senin = 'N';
		};
		if (isset($post['selasa'])) {
			$selasa = 'Y';
		} else {
			$selasa = 'N';
		};
		if (isset($post['rabu'])) {
			$rabu = 'Y';
		} else {
			$rabu = 'N';
		};
		if (isset($post['kamis'])) {
			$kamis = 'Y';
		} else {
			$kamis = 'N';
		};
		if (isset($post['jumat'])) {
			$jumat = 'Y';
		} else {
			$jumat = 'N';
		};
		if (isset($post['sabtu'])) {
			$sabtu = 'Y';
		} else {
			$sabtu = 'N';
		};
		if (isset($post['minggu'])) {
			$minggu = 'Y';
		} else {
			$minggu = 'N';
		};
		if (isset($post['berita_acara'])) {
			$berita_acara = 'Y';
		} else {
			$berita_acara = 'N';
		};
		if (isset($post['faktur'])) {
			$faktur = 'Y';
		} else {
			$faktur = 'N';
		};
		if (isset($post['tdp'])) {
			$tdp = 'Y';
		} else {
			$tdp = 'N';
		};
		if (isset($post['real_po'])) {
			$real_po = 'Y';
		} else {
			$real_po = 'N';
		};
		if (isset($post['ttd_specimen'])) {
			$ttd_specimen = 'Y';
		} else {
			$ttd_specimen = 'N';
		};
		if (isset($post['payement_certificate'])) {
			$payement_certificate = 'Y';
		} else {
			$payement_certificate = 'N';
		};
		if (isset($post['photo'])) {
			$photo = 'Y';
		} else {
			$photo = 'N';
		};
		if (isset($post['siup'])) {
			$siup = 'Y';
		} else {
			$siup = 'N';
		};
		if (isset($post['spk'])) {
			$spk = 'Y';
		} else {
			$spk = 'N';
		};
		if (isset($post['delivery_order'])) {
			$delivery_order = 'Y';
		} else {
			$delivery_order = 'N';
		};
		if (isset($post['need_npwp'])) {
			$need_npwp = 'Y';
		} else {
			$need_npwp = 'N';
		};
		if (isset($post['ditagih'])) {
			$ditagih = 'Y';
		} else {
			$ditagih = 'N';
		};
		if (isset($post['sj'])) {
			$sj = 'Y';
		} else {
			$sj = 'N';
		};
		if (isset($post['invoice'])) {
			$invoice = 'Y';
		} else {
			$invoice = 'N';
		};

		$chanel = [];
		if ($this->input->post('chanel_toko')) {
			$chanel[] = $this->input->post('chanel_toko'); // Toko dan User
		}
		if ($this->input->post('chanel_project')) {
			$chanel[] = $this->input->post('chanel_project'); // Project
		}

		$session = $this->session->userdata('app_session');
		$code = $this->Customer_model->generate_id();
		$this->db->trans_begin();
		$header1 =  array(
			'id_customer'	 		=> $code,
			'id_category_customer'	=> $post['id_category_customer'],
			'name_customer'		    => $post['name_customer'],
			'telephone'		    	=> $post['telephone'],
			'telephone_2'		    => $post['telephone_2'],
			'fax'		    		=> $post['fax'],
			'email'			    	=> $post['email'],
			'start_date'		    => $post['start_date'],
			'id_karyawan'		    => $post['id_karyawan'],
			'id_prov'		    	=> $post['id_prov'],
			'id_kabkot'		    	=> $post['id_kabkot'],
			'id_kec'		    	=> $post['id_kec'],
			'address_office'		=> $post['address_office'],
			'zip_code'		    	=> $post['zip_code'],
			'longitude'		    	=> $post['longitude'],
			'latitude'		    	=> $post['latitude'],
			'activation'		    => $post['activation'],
			'facility'		   		=> $post['facility'],
			'kategori_cust'			=> $post['kategori_cust'],
			'kategori_toko'			=> $post['kategori_toko'],
			'chanel_pemasaran' 		=> implode(', ', $chanel),
			'persentase'       		=> $post['persentase'],
			'tahun_mulai'		   	=> $post['tahun_mulai'],
			'name_bank'		    	=> $post['name_bank'],
			'no_rekening'		    => $post['no_rekening'],
			'nama_rekening'		    => $post['nama_rekening'],
			'alamat_bank'		    => $post['alamat_bank'],
			'swift_code'		    => $post['swift_code'],
			'npwp'		   			=> $post['npwp'],
			'npwp_name'		    	=> $post['npwp_name'],
			'npwp_address'		    => $post['npwp_address'],
			'payment_term'		    => $post['payment_term'],
			'nominal_dp'		    => $post['nominal_dp'],
			'sisa_pembayaran'		=> $post['sisa_pembayaran'],
			'start_recive'			=> $post['start_recive'],
			'end_recive'			=> $post['end_recive'],
			'adress_invoice'		=> $post['address_invoice'],
			'senin'					=> $senin,
			'selasa'				=> $selasa,
			'rabu'					=> $rabu,
			'kamis'					=> $kamis,
			'jumat'					=> $jumat,
			'sabtu'					=> $sabtu,
			'minggu'				=> $minggu,
			'berita_acara'			=> $berita_acara,
			'faktur'				=> $faktur,
			'sj'					=> $sj,
			'invoice'				=> $invoice,
			'tdp'					=> $tdp,
			'real_po'				=> $real_po,
			'ttd_specimen'			=> $ttd_specimen,
			'payement_certificate'	=> $payement_certificate,
			'photo'					=> $photo,
			'siup'					=> $siup,
			'spk'					=> $spk,
			'delivery_order'		=> $delivery_order,
			'need_npwp'				=> $need_npwp,
			'ditagih'				=> $ditagih,
			'deleted'				=> '0',
			'created_on'			=> date('Y-m-d H:i:s'),
			'created_by'			=> $this->auth->user_id()
		);
		//Add Data
		$this->db->insert('master_customers', $header1);
		$numb2 = 0;
		if (isset($_POST['data1']) && is_array($_POST['data1'])) {
			foreach ($_POST['data1'] as $d1) {
				$numb2++;
				$data = array(
					'id_customer'	=> $code,
					'name_pic'		=> $d1['name_pic'],
					'phone_pic'		=> $d1['phone_pic'],
					'email_pic'		=> $d1['email_pic'],
					'position_pic'	=> $d1['position_pic']
				);
				$this->db->insert('child_customer_pic', $data);
			}
		}

		$numb2 = 0;
		if (isset($_POST['data2']) && is_array($_POST['data2'])) {
			foreach ($_POST['data2'] as $d2) {
				$numb2++;
				$data = array(
					'id_customer'				=> $code,
					'name_category_customer'	=> $d2['id_category_customer'],
				);
				$this->db->insert('child_category_customer', $data);
			}
		}

		$numb2 = 0;
		if (isset($_POST['data3']) && is_array($_POST['data3'])) {
			foreach ($_POST['data3'] as $d3) {
				$numb2++;
				$data = array(
					'id_customer'	=> $code,
					'existing_pt'	=> $d3['existing_pt'],
					'existing_pic'	=> $d3['existing_pic'],
					'existing_telp'	=> $d3['existing_telp'],
				);
				$this->db->insert('child_customer_existing', $data);
			}
		}

		if (isset($_POST['data4']) && is_array($_POST['data4'])) {
			$d4 = $_POST['data4'];
			$data4 = array(
				'id_customer'     => $code,
				'ontime'          => $d4['ontime'],
				'toko_sendiri'    => $d4['toko_sendiri'],
				'armada_pickup'   => $d4['armada_pickup'],
				'armada_truck'    => $d4['armada_truck'],
				'attitude'        => $d4['attitude'],
				'luas_tanah'      => $d4['luas_tanah'],
				'pbb'             => $d4['pbb'],
			);
			$this->db->insert('child_customer_rate', $data4);
		}


		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$status	= array(
				'pesan'		=> 'Gagal Save Item. Thanks ...',
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
				'pesan'		=> 'Success Save Item. invenThanks ...',
				'status'	=> 1
			);
		}

		echo json_encode($status);
	}
	public function saveEditcustomer()
	{
		$this->auth->restrict($this->addPermission);
		$post = $this->input->post();
		if (isset($post['senin'])) {
			$senin = 'Y';
		} else {
			$senin = 'N';
		};
		if (isset($post['selasa'])) {
			$selasa = 'Y';
		} else {
			$selasa = 'N';
		};
		if (isset($post['rabu'])) {
			$rabu = 'Y';
		} else {
			$rabu = 'N';
		};
		if (isset($post['kamis'])) {
			$kamis = 'Y';
		} else {
			$kamis = 'N';
		};
		if (isset($post['jumat'])) {
			$jumat = 'Y';
		} else {
			$jumat = 'N';
		};
		if (isset($post['sabtu'])) {
			$sabtu = 'Y';
		} else {
			$sabtu = 'N';
		};
		if (isset($post['minggu'])) {
			$minggu = 'Y';
		} else {
			$minggu = 'N';
		};
		if (isset($post['berita_acara'])) {
			$berita_acara = 'Y';
		} else {
			$berita_acara = 'N';
		};
		if (isset($post['faktur'])) {
			$faktur = 'Y';
		} else {
			$faktur = 'N';
		};
		if (isset($post['tdp'])) {
			$tdp = 'Y';
		} else {
			$tdp = 'N';
		};
		if (isset($post['real_po'])) {
			$real_po = 'Y';
		} else {
			$real_po = 'N';
		};
		if (isset($post['ttd_specimen'])) {
			$ttd_specimen = 'Y';
		} else {
			$ttd_specimen = 'N';
		};
		if (isset($post['payement_certificate'])) {
			$payement_certificate = 'Y';
		} else {
			$payement_certificate = 'N';
		};
		if (isset($post['photo'])) {
			$photo = 'Y';
		} else {
			$photo = 'N';
		};
		if (isset($post['siup'])) {
			$siup = 'Y';
		} else {
			$siup = 'N';
		};
		if (isset($post['spk'])) {
			$spk = 'Y';
		} else {
			$spk = 'N';
		};
		if (isset($post['delivery_order'])) {
			$delivery_order = 'Y';
		} else {
			$delivery_order = 'N';
		};
		if (isset($post['need_npwp'])) {
			$need_npwp = 'Y';
		} else {
			$need_npwp = 'N';
		};
		if (isset($post['ditagih'])) {
			$ditagih = 'Y';
		} else {
			$ditagih = 'N';
		};
		if (isset($post['sj'])) {
			$sj = 'Y';
		} else {
			$sj = 'N';
		};
		if (isset($post['invoice'])) {
			$invoice = 'Y';
		} else {
			$invoice = 'N';
		};

		$chanel = [];
		if ($this->input->post('chanel_toko')) {
			$chanel[] = $this->input->post('chanel_toko'); // Toko dan User
		}
		if ($this->input->post('chanel_project')) {
			$chanel[] = $this->input->post('chanel_project'); // Project
		}

		$session = $this->session->userdata('app_session');
		$this->db->trans_begin();
		$header1 =  array(
			'id_category_customer'	=> $post['id_category_customer'],
			'name_customer'		    => $post['name_customer'],
			'telephone'		    	=> $post['telephone'],
			'telephone_2'		    => $post['telephone_2'],
			'fax'		    		=> $post['fax'],
			'email'			    	=> $post['email'],
			'start_date'		    => $post['start_date'],
			'id_karyawan'		    => $post['id_karyawan'],
			'id_prov'		    	=> $post['id_prov'],
			'id_kabkot'		    	=> $post['id_kabkot'],
			'id_kec'		    	=> $post['id_kec'],
			'address_office'		=> $post['address_office'],
			'zip_code'		    	=> $post['zip_code'],
			'longitude'		    	=> $post['longitude'],
			'latitude'		    	=> $post['latitude'],
			'activation'		    => $post['activation'],
			'facility'		   		=> $post['facility'],
			'kategori_cust'			=> $post['kategori_cust'],
			'kategori_toko'			=> $post['kategori_toko'],
			'chanel_pemasaran' 		=> implode(', ', $chanel),
			'persentase'       		=> $post['persentase'],
			'tahun_mulai'		   	=> $post['tahun_mulai'],
			'name_bank'		    	=> $post['name_bank'],
			'no_rekening'		    => $post['no_rekening'],
			'nama_rekening'		    => $post['nama_rekening'],
			'alamat_bank'		    => $post['alamat_bank'],
			'swift_code'		    => $post['swift_code'],
			'npwp'		   			=> $post['npwp'],
			'npwp_name'		    	=> $post['npwp_name'],
			'npwp_address'		    => $post['npwp_address'],
			'payment_term'		    => $post['payment_term'],
			'nominal_dp'		    => $post['nominal_dp'],
			'sisa_pembayaran'		=> $post['sisa_pembayaran'],
			'start_recive'			=> $post['start_recive'],
			'end_recive'			=> $post['end_recive'],
			'adress_invoice'		=> $post['address_invoice'],
			'senin'					=> $senin,
			'selasa'				=> $selasa,
			'rabu'					=> $rabu,
			'kamis'					=> $kamis,
			'jumat'					=> $jumat,
			'sabtu'					=> $sabtu,
			'minggu'				=> $minggu,
			'berita_acara'			=> $berita_acara,
			'faktur'				=> $faktur,
			'sj'					=> $sj,
			'invoice'				=> $invoice,
			'tdp'					=> $tdp,
			'real_po'				=> $real_po,
			'ttd_specimen'			=> $ttd_specimen,
			'payement_certificate'	=> $payement_certificate,
			'photo'					=> $photo,
			'siup'					=> $siup,
			'spk'					=> $spk,
			'delivery_order'		=> $delivery_order,
			'need_npwp'				=> $need_npwp,
			'ditagih'				=> $ditagih,
			'deleted'				=> '0',
			'created_on'			=> date('Y-m-d H:i:s'),
			'created_by'			=> $this->auth->user_id()
		);

		//Add Data
		$this->db->where('id_customer', $post['id_customer'])->update("master_customers", $header1);
		$code = $post['id_customer'];

		$this->db->delete('child_customer_pic', array('id_customer' => $post['id_customer']));
		$numb2 = 0;
		if (isset($_POST['data1']) && is_array($_POST['data1'])) {
			foreach ($_POST['data1'] as $d1) {
				$numb2++;
				$data =  array(
					'id_customer'	=> $code,
					'name_pic'		=> $d1['name_pic'],
					'phone_pic'		=> $d1['phone_pic'],
					'email_pic'		=> $d1['email_pic'],
					'position_pic'	=> $d1['position_pic']
				);
				//Add Data
				$this->db->insert('child_customer_pic', $data);
			}
		}

		$this->db->delete('child_category_customer', array('id_customer' => $post['id_customer']));
		$numb2 = 0;
		if (isset($_POST['data2']) && is_array($_POST['data2'])) {
			foreach ($_POST['data2'] as $d2) {
				$numb2++;
				$data =  array(
					'id_customer'				=> $code,
					'name_category_customer'	=> $d2['id_category_customer'],
				);
				//Add Data
				$this->db->insert('child_category_customer', $data);
			}
		}

		$this->db->delete('child_customer_existing', array('id_customer' => $post['id_customer']));
		$numb2 = 0;
		if (isset($_POST['data3']) && is_array($_POST['data3'])) {
			foreach ($_POST['data3'] as $d3) {
				$numb2++;
				$data =  array(
					'id_customer'				=> $code,
					'existing_pt'				=> $d3['existing_pt'],
					'existing_pic'				=> $d3['existing_pic'],
					'existing_telp'				=> $d3['existing_telp'],
				);
				//Add Data
				$this->db->insert('child_customer_existing', $data);
			}
		}

		$this->db->delete('child_customer_rate', array('id_customer' => $post['id_customer']));
		if (isset($_POST['data4']) && is_array($_POST['data4'])) {
			$d4 = $_POST['data4'];
			$data4 = array(
				'id_customer'     => $code,
				'ontime'          => $d4['ontime'],
				'toko_sendiri'    => $d4['toko_sendiri'],
				'armada_pickup'   => $d4['armada_pickup'],
				'armada_truck'    => $d4['armada_truck'],
				'attitude'        => $d4['attitude'],
				'luas_tanah'      => $d4['luas_tanah'],
				'pbb'             => $d4['pbb'],
			);
			$this->db->insert('child_customer_rate', $data4);
		}


		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$status	= array(
				'pesan'		=> 'Gagal Save Item. Thanks ...',
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
				'pesan'		=> 'Success Save Item. invenThanks ...',
				'status'	=> 1
			);
		}

		echo json_encode($status);
	}
	public function saveNewInternational()
	{


		$this->auth->restrict($this->addPermission);
		$post = $this->input->post();
		$session = $this->session->userdata('app_session');
		$code = $this->Suplier_model->generate_id();
		$this->db->trans_begin();
		$header1 =  array(
			'id_suplier'	 		=> $code,
			'id_category_supplier'	=> $post['id_category_supplier'],
			'suplier_location'		=> 'international',
			'name_suplier'		    => $post['name_suplier'],
			'telephone'		    	=> $post['telephone'],
			'telephone_2'		    => $post['telephone_2'],
			'fax'		    		=> $post['fax'],
			'email'			    	=> $post['email'],
			'start_date'		    => $post['start_date'],
			'id_negara'		    	=> $post['id_negara'],
			'international_prov'	=> $post['international_prov'],
			'international_kota'	=> $post['international_kota'],
			'address_office'		=> $post['address_office'],
			'zip_code'		    	=> $post['zip_code'],
			'longitude'		    	=> $post['longitude'],
			'latitude'		    	=> $post['latitude'],
			'activation'		    => $post['activation'],
			'name_bank'		    	=> $post['name_bank'],
			'no_rekening'		    => $post['no_rekening'],
			'nama_rekening'		    => $post['nama_rekening'],
			'alamat_bank'		    => $post['alamat_bank'],
			'swift_code'		    => $post['swift_code'],
			'payment_term'		    => $post['payment_term'],
			'deleted'				=> '0',
			'created_on'			=> date('Y-m-d H:i:s'),
			'created_by'			=> $this->auth->user_id()
		);
		//Add Data
		$this->db->insert('master_supplier', $header1);
		$numb2 = 0;
		foreach ($_POST['data1'] as $d1) {
			$numb2++;
			$data =  array(
				'id_suplier'	=> $code,
				'name_pic'		=> $d1[name_pic],
				'phone_pic'		=> $d1[phone_pic],
				'email_pic'		=> $d1[email_pic],
				'position_pic'	=> $d1[position_pic]
			);
			//Add Data
			$this->db->insert('child_supplier_pic', $data);
		}

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$status	= array(
				'pesan'		=> 'Gagal Save Item. Thanks ...',
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
				'pesan'		=> 'Success Save Item. invenThanks ...',
				'status'	=> 1
			);
		}

		echo json_encode($status);
	}
	public function saveEditLocal()
	{


		$this->auth->restrict($this->addPermission);
		$post = $this->input->post();
		$session = $this->session->userdata('app_session');
		$this->db->trans_begin();
		$header1 =  array(
			'id_category_supplier'	=> $post['id_category_supplier'],
			'suplier_location'		=> 'local',
			'name_suplier'		    => $post['name_suplier'],
			'telephone'		    	=> $post['telephone'],
			'telephone_2'		    => $post['telephone_2'],
			'fax'		    		=> $post['fax'],
			'email'			    	=> $post['email'],
			'start_date'		    => $post['start_date'],
			'id_prov'		    	=> $post['id_prov'],
			'id_kota'		    	=> $post['id_kota'],
			'address_office'		=> $post['address_office'],
			'zip_code'		    	=> $post['zip_code'],
			'longitude'		    	=> $post['longitude'],
			'latitude'		    	=> $post['latitude'],
			'activation'		    => $post['activation'],
			'name_bank'		    	=> $post['name_bank'],
			'no_rekening'		    => $post['no_rekening'],
			'nama_rekening'		    => $post['nama_rekening'],
			'alamat_bank'		    => $post['alamat_bank'],
			'swift_code'		    => $post['swift_code'],
			'npwp'		   			=> $post['npwp'],
			'npwp_name'		    	=> $post['npwp_name'],
			'npwp_address'		    => $post['npwp_address'],
			'payment_term'		    => $post['payment_term'],
			'deleted'				=> '0',
			'created_on'			=> date('Y-m-d H:i:s'),
			'created_by'			=> $this->auth->user_id()
		);
		//Add Data
		$this->db->where('id_suplier', $post['id_suplier'])->update("master_supplier", $header1);
		$this->db->delete('child_supplier_pic', array('id_suplier' => $post['id_suplier']));
		$numb2 = 0;
		foreach ($_POST['data1'] as $d1) {
			$numb2++;
			$code = $post['id_suplier'];
			$data =  array(
				'id_suplier'	=> $code,
				'name_pic'		=> $d1[name_pic],
				'phone_pic'		=> $d1[phone_pic],
				'email_pic'		=> $d1[email_pic],
				'position_pic'	=> $d1[position_pic]
			);
			//Add Data
			$this->db->insert('child_supplier_pic', $data);
		}

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$status	= array(
				'pesan'		=> 'Gagal Save Item. Thanks ...',
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
				'pesan'		=> 'Success Save Item. invenThanks ...',
				'status'	=> 1
			);
		}

		echo json_encode($status);
	}
	public function saveEditInternational()
	{


		$this->auth->restrict($this->addPermission);
		$post = $this->input->post();
		$session = $this->session->userdata('app_session');
		$this->db->trans_begin();
		$header1 =  array(
			'id_category_supplier'	=> $post['id_category_supplier'],
			'suplier_location'		=> 'international',
			'name_suplier'		    => $post['name_suplier'],
			'telephone'		    	=> $post['telephone'],
			'telephone_2'		    => $post['telephone_2'],
			'fax'		    		=> $post['fax'],
			'email'			    	=> $post['email'],
			'start_date'		    => $post['start_date'],
			'id_negara'		    	=> $post['id_negara'],
			'international_prov'	=> $post['international_prov'],
			'international_kota'	=> $post['international_kota'],
			'address_office'		=> $post['address_office'],
			'zip_code'		    	=> $post['zip_code'],
			'longitude'		    	=> $post['longitude'],
			'latitude'		    	=> $post['latitude'],
			'activation'		    => $post['activation'],
			'name_bank'		    	=> $post['name_bank'],
			'no_rekening'		    => $post['no_rekening'],
			'nama_rekening'		    => $post['nama_rekening'],
			'alamat_bank'		    => $post['alamat_bank'],
			'swift_code'		    => $post['swift_code'],
			'payment_term'		    => $post['payment_term'],
			'deleted'				=> '0',
			'created_on'			=> date('Y-m-d H:i:s'),
			'created_by'			=> $this->auth->user_id()
		);
		//Add Data
		$this->db->where('id_suplier', $post['id_suplier'])->update("master_supplier", $header1);
		$this->db->delete('child_supplier_pic', array('id_suplier' => $post['id_suplier']));
		$code = $post['id_suplier'];
		$numb2 = 0;
		foreach ($_POST['data1'] as $d1) {
			$numb2++;
			$data =  array(
				'id_suplier'	=> $code,
				'name_pic'		=> $d1[name_pic],
				'phone_pic'		=> $d1[phone_pic],
				'email_pic'		=> $d1[email_pic],
				'position_pic'	=> $d1[position_pic]
			);
			//Add Data
			$this->db->insert('child_supplier_pic', $data);
		}

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$status	= array(
				'pesan'		=> 'Gagal Save Item. Thanks ...',
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
				'pesan'		=> 'Success Save Item. invenThanks ...',
				'status'	=> 1
			);
		}

		echo json_encode($status);
	}
	public function saveEditCategory()
	{
		$this->auth->restrict($this->editPermission);
		$post = $this->input->post();
		$this->db->trans_begin();
		$data = [
			'name_category_customer'	=> $post['name_category_customer'],
			'customer_code'				=> $post['customer_code'],
			'modified_on'		=> date('Y-m-d H:i:s'),
			'modified_by'		=> $this->auth->user_id()
		];

		$this->db->where('id_category_customer', $post['id_category_customer'])->update("child_customer_category", $data);

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$status	= array(
				'pesan'		=> 'Gagal Save Item. Thanks ...',
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
				'pesan'		=> 'Success Save Item. Thanks ...',
				'status'	=> 1
			);
		}

		echo json_encode($status);
	}
	public function saveEditinventory()
	{
		$this->auth->restrict($this->addPermission);
		$session = $this->session->userdata('app_session');
		$this->db->trans_begin();

		$numb1 = 0;
		foreach ($_POST['hd1'] as $h1) {
			$numb1++;
			$produk = $_POST['hd1']['1']['id_inventory'];
			$header1 =  array(
				'id_type'		    => $h1[inventory_1],
				'nama'		        => $h1[nm_inventory],
				'modified_on'		=> date('Y-m-d H:i:s'),
				'modified_by'		=> $this->auth->user_id(),
				'deleted'			=> '0'
			);
			//Add Data
			$this->db->where('id_category1', $produk)->update("ms_inventory_category1", $header1);
		}
		if (empty($_POST['data1'])) {
		} else {
			$numb2 = 0;
			foreach ($_POST['data1'] as $d1) {
				$numb2++;

				$code = $_POST['hd1']['1']['id_inventory'];
				$data1 =  array(
					'id_category1' => $code,
					'name_compotition' => $d1[name_compotition],
					'deleted' => '0',
					'created_on' => date('Y-m-d H:i:s'),
					'created_by' => $session['id_user'],
				);
				//Add Data
				$this->db->insert('ms_compotition', $data1);
			}
		}
		$numb3 = 0;
		foreach ($_POST['data2'] as $d2) {
			$numb3++;

			$info = $d2['id_compotition'];
			$data2 =  array(
				'name_compotition' => $d2[name_compotition],
				'deleted' => '0',
				'modified_on' => date('Y-m-d H:i:s'),
				'modified_by' => $session['id_user'],
			);
			//Add Data
			$this->db->where('id_compotition', $info)->update("ms_compotition", $data2);
		}


		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$status	= array(
				'pesan'		=> 'Gagal Save Item. Thanks ...',
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
				'pesan'		=> 'Success Save Item. invenThanks ...',
				'status'	=> 1
			);
		}

		echo json_encode($status);
	}

	function getkota()
	{
		$id_prov = $_GET['id_prov'];
		$data = $this->db->like('id_kabkot', $id_prov, 'after')->get('kabkot')->result();

		echo "<option value=''>--Pilih--</option>";
		foreach ($data as $kabkot) {
			echo "<option value='$kabkot->id_kabkot'>$kabkot->kabkot</option>";
		}
	}
	function getkecamatan()
	{
		$id_kabkot = $_GET['id_kabkot'];
		$data = $this->db->like('id_kec', $id_kabkot, 'after')->get('kec')->result();

		echo "<option value=''>--Pilih--</option>";
		foreach ($data as $kec) {
			echo "<option value='$kec->id_kec'>$kec->kecamatan</option>";
		}
	}

	public function saveNewinventoryold()
	{
		$this->auth->restrict($this->addPermission);
		$post = $this->input->post();
		$code = $this->Suplier_model->generate_id();
		$this->db->trans_begin();
		$data = [
			'id_category1'	 	=> $code,
			'id_type'		    => $post['inventory_1'],
			'nama'		        => $post['nm_inventory'],
			'aktif'				=> 'aktif',
			'created_on'		=> date('Y-m-d H:i:s'),
			'created_by'		=> $this->auth->user_id(),
			'deleted'			=> '0'
		];

		$insert = $this->db->insert("ms_inventory_category1", $data);

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$status	= array(
				'pesan'		=> 'Gagal Save Item. Thanks ...',
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
				'pesan'		=> 'Success Save Item. invenThanks ...',
				'status'	=> 1
			);
		}

		echo json_encode($status);
	}

	public function excel_report_all()
	{
		set_time_limit(0);
		ini_set('memory_limit', '1024M');

		$this->load->library("PHPExcel");
		$objPHPExcel = new PHPExcel();

		// Konfigurasi style header tabel
		$tableHeader = [
			'font' => ['bold' => true],
			'alignment' => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER],
			'borders' => ['allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN]],
			'fill' => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => ['rgb' => 'CCCCCC']]
		];

		// Konfigurasi style body tabel
		$tableBody = [
			'borders' => ['allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN]],
			'alignment' => ['vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER]
		];

		$sheet = $objPHPExcel->getActiveSheet();
		$sheet->setTitle('Customer Report');

		// Mengambil data dari database
		$customers = $this->db
			->select('*')
			->get_where('master_customers', ['deleted' => 0])
			->result_array();

		// Menulis judul laporan
		$sheet->setCellValue('A1', 'Laporan Customer');
		$sheet->mergeCells('A1:J1');
		$sheet->getStyle('A1:J1')->applyFromArray($tableHeader);

		// Header kolom
		$headers = [
			'A' => 'ID Customer',
			'B' => 'Nama Customer',
			'C' => 'Telephone',
			'D' => 'Fax',
			'E' => 'Email',
			'F' => 'NPWP',
			'G' => 'Alamat NPWP',
			'H' => 'Nama Bank',
			'I' => 'No Rekening',
			'J' => 'Nama Pemilik Rekening'
		];

		$rowNum = 3;
		foreach ($headers as $col => $header) {
			$sheet->setCellValue($col . $rowNum, $header);
			$sheet->getStyle($col . $rowNum)->applyFromArray($tableHeader);
			$sheet->getColumnDimension($col)->setAutoSize(true);
		}

		// Mengisi data customer ke dalam Excel
		$rowNum = 4;
		foreach ($customers as $customer) {
			$sheet->setCellValue("A$rowNum", $customer['id_customer']);
			$sheet->setCellValue("B$rowNum", $customer['name_customer']);
			$sheet->setCellValue("C$rowNum", $customer['telephone']);
			$sheet->setCellValue("D$rowNum", $customer['fax']);
			$sheet->setCellValue("E$rowNum", $customer['email']);
			$sheet->setCellValue("F$rowNum", $customer['npwp']);
			$sheet->setCellValue("G$rowNum", $customer['npwp_address']);
			$sheet->setCellValue("H$rowNum", $customer['name_bank']);
			$sheet->setCellValue("I$rowNum", $customer['no_rekening']);
			$sheet->setCellValue("J$rowNum", $customer['nama_rekening']);
			$sheet->getStyle("A$rowNum:J$rowNum")->applyFromArray($tableBody);
			$rowNum++;
		}

		// Mulai menyimpan file Excel
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		ob_end_clean();
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="customer_report.xls"');

		// Unduh file
		$objWriter->save("php://output");
	}
}
