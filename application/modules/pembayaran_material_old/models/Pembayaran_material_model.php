<?php
class Pembayaran_material_model extends BF_Model
{

	protected $consultant;
	protected $accounting;
	protected $hris;

	public function __construct()
	{
		parent::__construct();

		// $this->consultant = $this->load->database('consultant', true);
		$this->accounting = $this->load->database('accounting', true);
		// $this->hris = $this->load->database('hris', true);
	}
	public function get_data_json_request_payment_header($sqlwhere = '')
	{
		$sql = "SELECT a.*, b.nm_supplier FROM purchase_order_request_payment_header a left join supplier b on a.id_supplier =b.id_supplier WHERE 1=1 " . ($sqlwhere == '' ? '' : " and " . $sqlwhere) . " order by a.id desc ";
		$query = $this->db->query($sql);
		return $query->result();
	}
	public function get_data_json_request_payment($sqlwhere = '')
	{

		$sql = "SELECT a.*, b.nm_supplier FROM purchase_order_request_payment a left join supplier b on a.id_supplier =b.id_supplier WHERE 1=1 " . ($sqlwhere == '' ? '' : " and " . $sqlwhere) . " order by a.id desc ";
		$query = $this->db->query($sql);
		return $query->result();
	}
	public function get_data_json_request_payment_nm($sqlwhere = '')
	{

		$sql = "SELECT a.*, b.nm_supplier FROM purchase_order_request_payment_nm a left join supplier b on a.id_supplier =b.id_supplier WHERE 1=1 " . ($sqlwhere == '' ? '' : " and " . $sqlwhere) . " order by a.no_po desc ";
		$query = $this->db->query($sql);
		return $query->result();
	}
	public function get_data_json_jurnal($sqlwhere = '')
	{

		$sql = "SELECT nomor,tanggal,no_reff,stspos FROM jurnaltras a WHERE 1=1 " . ($sqlwhere == '' ? '' : " and " . $sqlwhere) . " group by nomor,tanggal,no_reff,stspos order by no_reff desc ";
		$query = $this->db->query($sql);
		return $query->result();
	}

	public function generate_id_payment_paid($tanggal)
	{
		$prefix = "PY-";

		$tahun_bulan = date('my-', strtotime($tanggal));

		$format_awal = $prefix . $tahun_bulan;

		$sql = "
        SELECT MAX(id) AS max_id 
        FROM tr_payment_paid 
        WHERE id LIKE ?
    	";
		$row = $this->db->query($sql, [$format_awal . '%'])->row();

		$kode_terakhir = $row ? $row->max_id : null;

		if ($kode_terakhir) {
			$urutan = (int) substr($kode_terakhir, strlen($format_awal), 4);
		} else {
			$urutan = 0;
		}
		$urutan++;

		$kode_baru = $format_awal . sprintf("%04s", $urutan);
		return $kode_baru;
	}


	public function get_list_req_payment()
	{
		$post = $this->input->post();

		$draw = $post['draw'];
		$length = $post['length'];
		$start = $post['start'];
		$search = $post['search'];
		$jenis_payment = $post['jenis_payment'];

		$hasil = [];

		if ($jenis_payment == 1) {
			$this->db->select('a.id, a.tanggal, a.no_doc, a.currency, a.jumlah, a.keperluan, b.created_by as requestor');
			$this->db->from('payment_approve a');
			$this->db->join('tr_expense b', 'b.no_doc = a.no_doc');
			$this->db->where('a.status <>', 2);
			$this->db->where('b.exp_inv_po', 1);
			if (!empty($search['value'])) {
				$this->db->group_start();
				$this->db->like('a.no_doc', $search['value'], 'both');
				$this->db->or_like('b.created_by', $search['value'], 'both');
				$this->db->or_like('a.keperluan', $search['value'], 'both');
				$this->db->or_like('a.currency', $search['value'], 'both');
				$this->db->or_like('a.jumlah', $search['value'], 'both');
				$this->db->group_end();
			}
			$this->db->order_by('a.tanggal', 'desc');
			$this->db->group_by('a.id');

			$db_clone = clone $this->db;
			$count_all = $db_clone->count_all_results();

			$this->db->limit($length, $start);
			$get_data = $this->db->get()->result();

			$hasil = [];

			$no = (0 + $start);
			foreach ($get_data as $item) {
				$no++;
				$no_incoming = [];
				$no_po = [];
				$nm_supplier = [];

				if (!empty($get_rec_invoice)) {
					if (strpos($get_rec_invoice->no_po, 'TRS1') !== false) {
						$arr_no_incoming = str_replace(', ', ',', $get_rec_invoice->no_po);
						$get_no_po = $this->db
							->select('a.no_ipp')
							->from('tr_incoming_check a')
							->where_in('a.kode_trans', explode(',', $arr_no_incoming))
							->get()
							->result();

						$arr_no_po = [];
						foreach ($get_no_po as $item_no_po) {
							$arr_no_po[] = $item_no_po->no_ipp;
						}

						$arr_no_po = implode(',', $arr_no_po);
						$arr_no_po = str_replace(', ', ',', $arr_no_po);

						$get_no_surat = $this->db->query("SELECT a.no_surat FROM tr_purchase_order a WHERE a.no_po IN ('" . str_replace(",", "','", $arr_no_po) . "')")->result();
						foreach ($get_no_surat as $item_no_surat) {
							$no_po[] = $item_no_surat->no_surat;
						}
					} else {
						$no_po[] = $get_rec_invoice->no_po;
					}
				}

				if (!empty($no_po)) {
					$get_nm_supplier = $this->db
						->select('b.nama as nm_supplier')
						->from('tr_purchase_order a')
						->join('new_supplier b', 'b.kode_supplier = a.id_suplier', 'left')
						->where_in('a.no_surat', $no_po)
						->group_by('b.nama')
						->get()
						->result();
					foreach ($get_nm_supplier as $item_supplier) {
						$nm_supplier[] = $item_supplier->nm_supplier;
					}
				}

				$nm_supplier = implode(', ', $nm_supplier);

				$get_choosed_payment = $this->db->get_where('tr_choosed_payment', ['id_user' => $this->auth->user_id(), 'id_payment' => $item->id])->result();
				$checked = (count($get_choosed_payment) > 0) ? 'checked' : null;

				$option = '<input type="checkbox" class="check_payment" value="' . $item->id . '" ' . $checked . '>';

				$hasil[] = [
					'no' => $no,
					'no_dokumen' => $item->no_doc,
					'tgl' => date('d F Y', strtotime($item->tanggal)),
					'keperluan' => $item->keperluan,
					'currency' => $item->currency,
					'total_invoice' => number_format($item->jumlah),
					'requestor' => $item->requestor,
					'option' => $option
				];
			}
		} else {
			$this->db->select('a.id, a.tanggal, a.no_doc, a.currency, a.jumlah, a.keperluan, a.tipe');
			$this->db->from('payment_approve a');
			$this->db->join('tr_expense b', 'b.no_doc = a.no_doc', 'left');
			$this->db->join('tr_kasbon c', 'c.no_doc = a.no_doc', 'left');
			$this->db->join('tr_transport_req d', 'd.no_doc = a.no_doc', 'left');
			$this->db->where('a.status <>', 2);
			$this->db->group_start();
			$this->db->where('b.exp_inv_po <>', 1);
			$this->db->or_where('b.exp_inv_po', null);
			$this->db->group_end();
			if (!empty($search['value'])) {
				$this->db->group_start();
				$this->db->like('a.no_doc', $search['value'], 'both');
				$this->db->or_like('a.tanggal', $search['value'], 'both');
				$this->db->or_like('a.keperluan', $search['value'], 'both');
				$this->db->or_like('a.currency', $search['value'], 'both');
				$this->db->or_like('a.jumlah', $search['value'], 'both');
				$this->db->or_like('b.created_by', $search['value'], 'both');
				$this->db->or_like('c.created_by', $search['value'], 'both');
				$this->db->or_like('d.created_by', $search['value'], 'both');
				$this->db->group_end();
			}
			$this->db->order_by('a.tanggal', 'desc');
			$this->db->group_by('a.id');

			$db_clone = clone $this->db;
			$count_all = $db_clone->count_all_results();

			$this->db->limit($length, $start);
			$get_data = $this->db->get()->result();

			$hasil = [];

			$no = (0 + $start);
			foreach ($get_data as $item) {
				$no++;

				$get_choosed_payment = $this->db->get_where('tr_choosed_payment', ['id_user' => $this->auth->user_id(), 'id_payment' => $item->id])->result();

				$checked = (count($get_choosed_payment) > 0) ? 'checked' : null;

				$option = '<input type="checkbox" class="check_payment" value="' . $item->id . '" ' . $checked . '>';

				$requestor = '';
				if ($item->tipe == 'kasbon') {
					$get_kasbon = $this->db->get_where('tr_kasbon', array('no_doc' => $item->no_doc))->row();

					$requestor = (!empty($get_kasbon)) ? $get_kasbon->nama : '';
				}
				if ($item->tipe == 'expense') {
					$get_expense = $this->db->get_where('tr_expense', array('no_doc' => $item->no_doc))->row();

					$requestor = (!empty($get_expense)) ? $get_expense->nama : '';
				}
				if ($item->tipe == 'transport' || $item->tipe == 'transportasi') {
					$get_transport_req = $this->db->get_where('tr_transport_req', array('no_doc' => $item->no_doc))->row();

					$requestor = (!empty($get_transport_req)) ? $get_transport_req->nama : '';
				}

				$hasil[] = [
					'no' => $no,
					'no_dokumen' => $item->no_doc,
					'tgl' => date('d F Y', strtotime($item->tanggal)),
					'keperluan' => $item->keperluan,
					'currency' => $item->currency,
					'total_invoice' => number_format($item->jumlah),
					'requestor' => $requestor,
					'option' => $option
				];
			}
		}

		$response = [
			'draw' => intval($draw),
			'recordsTotal' => $count_all,
			'recordsFiltered' => $count_all,
			'data' => $hasil
		];

		echo json_encode($response);
	}

	public function set_jurnal()
	{
		$post = $this->input->post();

		$id_payment          = $post['id_payment'];
		$bank                = $post['bank'];
		if (!is_array($bank)) {
			$bank = [$bank];
		}

		$mata_uang    = strtoupper(trim($post['mata_uang'] ?? 'IDR'));
		$kurs_payment = (float) str_replace(',', '', $post['kurs_payment'] ?? '1');
		if ($kurs_payment <= 0) $kurs_payment = 1;

		// kurs_invoice per baris (array, urutan sama dengan payment_approve rows)
		$kurs_invoice_list = $post['kurs_invoice_list'] ?? [];
		if (!is_array($kurs_invoice_list)) $kurs_invoice_list = [];

		$hasil_jurnal = '';
		$ttl_debit    = 0;
		$ttl_kredit   = 0;
		$no_jurnal    = 1;

		$bank_main_credit = (float) str_replace(',', '', ($post['payment_bank'] ?? '0'));
		$admin_credit     = (float) str_replace(',', '', ($post['payment_bank_charge'] ?? '0'));
		$admin_debit      = (float) str_replace(',', '', ($post['bank_charge'] ?? '0'));

		// Jika USD, kalikan nilai bank dengan kurs_payment untuk dapat nilai IDR
		if ($mata_uang === 'USD') {
			$bank_main_credit = (int) round($bank_main_credit * $kurs_payment);
			$admin_credit     = (int) round($admin_credit     * $kurs_payment);
			$admin_debit      = (int) round($admin_debit      * $kurs_payment);
		}

		// BANK (ambil sekali)
		$this->accounting->select('a.no_perkiraan as no_coa, a.nama as nm_coa');
		$this->accounting->from('coa_master a');
		$this->accounting->where_in('a.no_perkiraan', $bank);
		$coa_bank = $this->accounting->get()->row();

		// Ambil payment list
		$this->db->select('a.*');
		$this->db->from('payment_approve a');
		$this->db->where_in('a.id', explode(',', $id_payment));
		$get_payment = $this->db->get()->result();

		// Accumulator untuk 1x baris bank utama
		$total_bank_main = 0;
		$ket_bank        = []; // biar keterangannya gabung (optional)

		// Helper untuk append baris (7 kolom konsisten)
		$addRow = function ($coa, $nm_coa, $keterangan, $debit, $kredit) use (&$hasil_jurnal, &$no_jurnal) {
			$tanggal_view = date('d/m/Y');
			$tanggal_db   = date('Y-m-d');

			$debit  = (float) $debit;
			$kredit = (float) $kredit;

			$hasil_jurnal .= '<tr>';

			$hasil_jurnal .= '<td class="text-center">';
			$hasil_jurnal .= $tanggal_view;
			$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tanggal_jurnal]" value="' . $tanggal_db . '">';
			$hasil_jurnal .= '</td>';

			$hasil_jurnal .= '<td class="text-center">';
			$hasil_jurnal .= 'BUK';
			$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tipe]" value="BUK">';
			$hasil_jurnal .= '</td>';

			$hasil_jurnal .= '<td class="text-center">';
			$hasil_jurnal .= $coa;
			$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][coa]" value="' . $coa . '">';
			$hasil_jurnal .= '</td>';

			$hasil_jurnal .= '<td>';
			$hasil_jurnal .= $nm_coa;
			$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][nm_coa]" value="' . $nm_coa . '">';
			$hasil_jurnal .= '</td>';

			$hasil_jurnal .= '<td>';
			$hasil_jurnal .= $keterangan;
			$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][keterangan]" value="' . htmlspecialchars($keterangan, ENT_QUOTES) . '">';
			$hasil_jurnal .= '</td>';

			$hasil_jurnal .= '<td class="text-right">';
			$hasil_jurnal .= number_format($debit, 0, ',', '.');
			$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][debit]" value="' . $debit . '">';
			$hasil_jurnal .= '</td>';

			$hasil_jurnal .= '<td class="text-right">';
			$hasil_jurnal .= number_format($kredit, 0, ',', '.');
			$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][kredit]" value="' . $kredit . '">';
			$hasil_jurnal .= '</td>';

			$hasil_jurnal .= '</tr>';

			$no_jurnal++;
		};

		foreach ($get_payment as $idx_payment => $item_payment) :

			// kurs invoice untuk baris ini (dari dt[N][kurs_invoice] di view)
			$kurs_invoice = (float) ($kurs_invoice_list[$idx_payment] ?? 1);
			if ($kurs_invoice <= 0) $kurs_invoice = 1;

			// =========================
			// KASBON (DETAIL ONLY)
			// =========================
			if ($item_payment->tipe == 'kasbon') :

				$get_kasbon = $this->db->get_where('tr_kasbon', ['no_doc' => $item_payment->no_doc])->row();

				$this->accounting->select('a.no_perkiraan as no_coa, a.nama as nm_coa');
				$this->accounting->from('coa_master a');
				$this->accounting->where_in('a.no_perkiraan', [$get_kasbon->coa]);
				$coa_kasbon = $this->accounting->get()->row();

				$amount = (float) $item_payment->jumlah;

				// Dr Kasbon (detail)
				$addRow(
					$coa_kasbon->no_coa,
					$coa_kasbon->nm_coa,
					'Kasbon : ' . $item_payment->no_doc,
					$amount,
					0
				);

				$ttl_debit       += $amount;
				$total_bank_main += $amount;
				$ket_bank[]       = 'Kasbon ' . $item_payment->no_doc;

			endif;

			// =========================
			// TRANSPORT (DETAIL ONLY)
			// =========================
			if ($item_payment->tipe == 'transport') :

				$get_transport = $this->db->get_where('tr_transport', ['no_req' => $item_payment->no_doc])->row();

				$arr_coa_jurnal = ['6103-01-01', '6103-01-02', '6103-01-03'];
				$arr_coa_nm     = ['Biaya BBM Pengiriman', 'Biaya Tol', 'Biaya Parkir'];

				$bbm    = (float) $get_transport->bensin;
				$tol    = (float) $get_transport->tol;
				$parkir = (float) $get_transport->parkir;

				$arr_nilai = [$bbm, $tol, $parkir];

				$sum_transport = 0;

				foreach ($arr_coa_jurnal as $i => $no_coa) {
					$nm_coa = $arr_coa_nm[$i];
					$nilai  = (float) $arr_nilai[$i];
					if ($nilai <= 0) continue;

					$addRow(
						$no_coa,
						$nm_coa,
						'Transport ' . $item_payment->no_doc,
						$nilai,
						0
					);

					$sum_transport += $nilai;
					$ttl_debit     += $nilai;
				}

				if ($sum_transport > 0) {
					$total_bank_main += $sum_transport;
					$ket_bank[]       = 'Transport ' . $item_payment->no_doc;
				}

			endif;

			// =========================
			// EXPENSE (DETAIL ONLY)
			// =========================
			if ($item_payment->tipe == 'expense') :

				$get_expense = $this->db->get_where('tr_expense', ['no_doc' => $item_payment->no_doc])->row();
				$informasi   = $get_expense->informasi ?? ('Expense ' . $item_payment->no_doc);

				// Ambil detail expense
				$this->db->select('coa, keterangan, total_harga');
				$this->db->from('tr_expense_detail');
				$this->db->where('no_doc', $item_payment->no_doc);
				$detail = $this->db->get()->result_array();

				// Map nama COA
				$coa_list = array_unique(array_column($detail, 'coa'));
				$coa_map  = [];

				if (!empty($coa_list)) {
					$this->accounting->select('a.no_perkiraan, a.nama');
					$this->accounting->from('coa_master a');
					$this->accounting->where_in('a.no_perkiraan', $coa_list);
					$coa_rows = $this->accounting->get()->result_array();

					foreach ($coa_rows as $row) {
						$coa_map[$row['no_perkiraan']] = $row['nama'];
					}
				}

				// CASE 1: pettycash -> tetap detail debit sesuai COA
				if (!empty($get_expense->pettycash)) {

					$sum_expense = 0;
					foreach ($detail as $row) {
						$coa        = $row['coa'];
						$nama_coa   = $coa_map[$coa] ?? $coa;
						$ket        = $row['keterangan'] ?: $informasi;
						$nilai      = (float) $row['total_harga'];

						if ($nilai <= 0) continue;

						$addRow($coa, $nama_coa, $ket, $nilai, 0);

						$sum_expense += $nilai;
						$ttl_debit   += $nilai;
					}

					if ($sum_expense > 0) {
						$total_bank_main += $sum_expense;
						$ket_bank[]       = 'Expense ' . $item_payment->no_doc;
					}
				}
				// CASE 2: expense berisi pembayaran PO (exp_inv_po)
				else if (!empty($get_expense->exp_inv_po)) {

					$nilai_usd = (float) $item_payment->jumlah;

					// COA hutang: IDR = 2101-01-01, USD = 2101-01-02
					$coa_hutang    = ($mata_uang === 'USD') ? '2101-01-02' : '2101-01-01';
					$nm_coa_hutang = ($mata_uang === 'USD') ? 'Hutang Pembelian Belum Ditagih ($)' : 'Hutang Pembelian Belum Ditagih';

					if ($mata_uang === 'USD') {
						// Debit hutang = nilai_usd × kurs_invoice
						$nilai_hutang_idr = (int) round($nilai_usd * $kurs_invoice);

						$addRow($coa_hutang, $nm_coa_hutang, $informasi, $nilai_hutang_idr, 0);
						$ttl_debit       += $nilai_hutang_idr;
						$total_bank_main += $nilai_usd; // akumulasi USD untuk kredit bank

						// Selisih kurs = (kurs_payment - kurs_invoice) × nilai_usd
						$selisih_kurs     = ($kurs_payment - $kurs_invoice) * $nilai_usd;
						$selisih_kurs_abs = (int) round(abs($selisih_kurs));

						if ($selisih_kurs_abs > 0) {
							$addRow(
								'7201-01-07',
								'Selisih Kurs',
								'Selisih Kurs Pembayaran DP (Kurs Invoice: ' . number_format($kurs_invoice, 2) . ', Kurs Payment: ' . number_format($kurs_payment, 2) . ')',
								($selisih_kurs > 0) ? $selisih_kurs_abs : 0,
								($selisih_kurs < 0) ? $selisih_kurs_abs : 0
							);
							if ($selisih_kurs > 0) {
								$ttl_debit  += $selisih_kurs_abs;
							} else {
								$ttl_kredit += $selisih_kurs_abs;
							}
						}

						$ket_bank[] = 'PO/Inv via Expense ' . $item_payment->no_doc;
					} else {
						// IDR — langsung pakai nilai tanpa konversi
						$addRow($coa_hutang, $nm_coa_hutang, $informasi, $nilai_usd, 0);
						$ttl_debit       += $nilai_usd;
						$total_bank_main += $nilai_usd;
						$ket_bank[]       = 'PO/Inv via Expense ' . $item_payment->no_doc;
					}
				}
				// CASE 3: expense normal (detail debit sesuai COA)
				else {

					$sum_expense = 0;
					foreach ($detail as $row) {
						$coa       = $row['coa'];
						$nama_coa  = $coa_map[$coa] ?? $coa;
						$nilai     = (float) $row['total_harga'];

						if ($nilai <= 0) continue;

						$addRow(
							$coa,
							$nama_coa,
							$informasi,
							$nilai,
							0
						);

						$sum_expense += $nilai;
						$ttl_debit   += $nilai;
					}

					if ($sum_expense > 0) {
						$total_bank_main += $sum_expense;
						$ket_bank[]       = 'Expense ' . $item_payment->no_doc;
					}
				}

			endif;

		endforeach;

		// =========================
		// BARIS GLOBAL (CUMA SEKALI)
		// =========================

		// 1) Dr biaya admin bank (1x)
		if ($admin_debit > 0) {
			$addRow(
				'7201-01-02',
				'Biaya Adm Bank & Buku Cek/Giro',
				'Biaya Admin Bank',
				$admin_debit,
				0
			);
			$ttl_debit += $admin_debit;
		}

		// 2) Cr bank pembayaran utama (1x)
		if ($total_bank_main > 0) {
			$ket = 'Pembayaran: ' . implode(' | ', array_unique($ket_bank));

			// Jika USD, kredit bank = total_bank_main (USD) × kurs_payment
			$kredit_bank = ($mata_uang === 'USD')
				? (int) round($total_bank_main * $kurs_payment)
				: $bank_main_credit;

			$addRow(
				$coa_bank->no_coa,
				$coa_bank->nm_coa,
				$ket,
				0,
				$kredit_bank
			);
			$ttl_kredit += $kredit_bank;
		}

		// 3) Cr bank pembayaran admin (1x)
		if ($admin_credit > 0) {
			$addRow(
				$coa_bank->no_coa,
				$coa_bank->nm_coa,
				'Pembayaran Biaya Admin Bank',
				0,
				$admin_credit
			);
			$ttl_kredit += $admin_credit;
		}

		$response = [
			'hasil_jurnal' => $hasil_jurnal,
			'ttl_debit'    => $ttl_debit,
			'ttl_kredit'   => $ttl_kredit
		];

		echo json_encode($response);
	}


	// public function set_jurnal()
	// {
	// 	$post = $this->input->post();

	// 	$id_payment = $post['id_payment'];
	// 	$bank = $post['bank'];
	// 	$payment_bank = $post['payment_bank'];
	// 	$payment_bank_charge = $post['payment_bank_charge'];
	// 	$bank_charge = $post['bank_charge'];

	// 	$hasil_jurnal = '';
	// 	$ttl_debit = 0;
	// 	$ttl_kredit = 0;
	// 	$ttl_nilai = 0;

	// 	$this->db->select('a.*');
	// 	$this->db->from('payment_approve a');
	// 	$this->db->where_in('a.id', explode(',', $id_payment));
	// 	$get_payment = $this->db->get()->result();

	// 	foreach ($get_payment as $item_payment) :
	// 		if ($item_payment->tipe == 'kasbon') :
	// 			$get_kasbon = $this->db->get_where('tr_kasbon', ['no_doc' => $item_payment->no_doc])->row();
	// 			$this->accounting->select('a.no_perkiraan as no_coa, a.nama as nm_coa');
	// 			$this->accounting->from('coa_master a');
	// 			$this->accounting->where_in('a.no_perkiraan', $get_kasbon->coa);
	// 			$coa_kasbon = $this->accounting->get()->row();

	// 			$this->accounting->select('a.no_perkiraan as no_coa, a.nama as nm_coa');
	// 			$this->accounting->from('coa_master a');
	// 			$this->accounting->where_in('a.no_perkiraan', $bank);
	// 			$coa_bank = $this->accounting->get()->row();

	// 			$debit = $item_payment->jumlah;
	// 			$kredit = str_replace(',', '', $payment_bank);
	// 			$charge = str_replace(',', '', $bank_charge);

	// 			// baris 1
	// 			$no_jurnal = 1;
	// 			$hasil_jurnal .= '<tr>';

	// 			$hasil_jurnal .= '<td class="text-center">';
	// 			$hasil_jurnal .= date('d/m/Y');
	// 			$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tanggal_jurnal]" value="' . date('Y-m-d') . '">';
	// 			$hasil_jurnal .= '</td>';

	// 			$hasil_jurnal .= '<td class="text-center">';
	// 			$hasil_jurnal .= 'BUK';
	// 			$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tipe]" value="BUK">';
	// 			$hasil_jurnal .= '</td>';

	// 			$hasil_jurnal .= '<td class="text-center">';
	// 			$hasil_jurnal .= $coa_kasbon->no_coa;
	// 			$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][coa]" value="' . $coa_bank->no_coa . '">';
	// 			$hasil_jurnal .= '</td>';

	// 			$hasil_jurnal .= '<td class="text-center">';
	// 			$hasil_jurnal .= $coa_kasbon->nm_coa;
	// 			$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][nm_coa]" value="' . $coa_kasbon->nm_coa . '">';
	// 			$hasil_jurnal .= '</td>';

	// 			$hasil_jurnal .= '<td class="text-right">';
	// 			$hasil_jurnal .= number_format($debit);
	// 			$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][debit]" value="' . $debit . '">';
	// 			$hasil_jurnal .= '</td>';

	// 			$hasil_jurnal .= '<td class="text-right">';
	// 			$hasil_jurnal .= '0';
	// 			$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][kredit]" value="0">';
	// 			$hasil_jurnal .= '</td>';

	// 			$hasil_jurnal .= '</tr>';
	// 			$no_jurnal++;

	// 			// baris 2
	// 			$hasil_jurnal .= '<tr>';

	// 			$hasil_jurnal .= '<td class="text-center">';
	// 			$hasil_jurnal .= date('d/m/Y');
	// 			$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tanggal_jurnal]" value="' . date('Y-m-d') . '">';
	// 			$hasil_jurnal .= '</td>';

	// 			$hasil_jurnal .= '<td class="text-center">';
	// 			$hasil_jurnal .= 'BUK';
	// 			$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tipe]" value="BUK">';
	// 			$hasil_jurnal .= '</td>';

	// 			$hasil_jurnal .= '<td class="text-center">';
	// 			$hasil_jurnal .= '7201-01-02';
	// 			$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][coa]" value="7201-01-02">';
	// 			$hasil_jurnal .= '</td>';

	// 			$hasil_jurnal .= '<td class="text-center">';
	// 			$hasil_jurnal .= 'Biaya Adm Bank & Buku Cek/Giro';
	// 			$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][nm_coa]" value="Biaya Adm Bank & Buku Cek/Giro">';
	// 			$hasil_jurnal .= '</td>';

	// 			$hasil_jurnal .= '<td class="text-right">';
	// 			$hasil_jurnal .= number_format($charge);
	// 			$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][debit]" value="' . $charge . '">';
	// 			$hasil_jurnal .= '</td>';

	// 			$hasil_jurnal .= '<td class="text-right">';
	// 			$hasil_jurnal .= '0';
	// 			$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][kredit]" value="0">';
	// 			$hasil_jurnal .= '</td>';

	// 			$hasil_jurnal .= '</tr>';
	// 			$no_jurnal++;

	// 			//baris 3
	// 			$hasil_jurnal .= '<tr>';

	// 			$hasil_jurnal .= '<td class="text-center">';
	// 			$hasil_jurnal .= date('d/m/Y');
	// 			$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tanggal_jurnal]" value="' . date('Y-m-d') . '">';
	// 			$hasil_jurnal .= '</td>';

	// 			$hasil_jurnal .= '<td class="text-center">';
	// 			$hasil_jurnal .= 'BUK';
	// 			$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tipe]" value="BUK">';
	// 			$hasil_jurnal .= '</td>';

	// 			$hasil_jurnal .= '<td class="text-center">';
	// 			$hasil_jurnal .= $coa_bank->no_coa;
	// 			$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][coa]" value="' . $coa_bank->no_coa . '">';
	// 			$hasil_jurnal .= '</td>';

	// 			$hasil_jurnal .= '<td class="text-center">';
	// 			$hasil_jurnal .= $coa_bank->nm_coa;
	// 			$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][nm_coa]" value="' . $coa_bank->nm_coa . '">';
	// 			$hasil_jurnal .= '</td>';

	// 			$hasil_jurnal .= '<td class="text-right">';
	// 			$hasil_jurnal .= '0';
	// 			$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][debit]" value="0">';
	// 			$hasil_jurnal .= '</td>';

	// 			$hasil_jurnal .= '<td class="text-right">';
	// 			$hasil_jurnal .= number_format($kredit);
	// 			$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][kredit]" value="' . $kredit . '">';
	// 			$hasil_jurnal .= '</td>';

	// 			$hasil_jurnal .= '</tr>';

	// 			//baris 3
	// 			$hasil_jurnal .= '<tr>';

	// 			$hasil_jurnal .= '<td class="text-center">';
	// 			$hasil_jurnal .= date('d/m/Y');
	// 			$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tanggal_jurnal]" value="' . date('Y-m-d') . '">';
	// 			$hasil_jurnal .= '</td>';

	// 			$hasil_jurnal .= '<td class="text-center">';
	// 			$hasil_jurnal .= 'BUK';
	// 			$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tipe]" value="BUK">';
	// 			$hasil_jurnal .= '</td>';

	// 			$hasil_jurnal .= '<td class="text-center">';
	// 			$hasil_jurnal .= $coa_bank->no_coa;
	// 			$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][coa]" value="' . $coa_bank->no_coa . '">';
	// 			$hasil_jurnal .= '</td>';

	// 			$hasil_jurnal .= '<td class="text-center">';
	// 			$hasil_jurnal .= $coa_bank->nm_coa;
	// 			$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][nm_coa]" value="' . $coa_bank->nm_coa . '">';
	// 			$hasil_jurnal .= '</td>';

	// 			$hasil_jurnal .= '<td class="text-right">';
	// 			$hasil_jurnal .= number_format($charge);
	// 			$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][debit]" value="' . $charge . '">';
	// 			$hasil_jurnal .= '</td>';

	// 			$hasil_jurnal .= '<td class="text-right">';
	// 			$hasil_jurnal .= '0';
	// 			$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][kredit]" value="0">';
	// 			$hasil_jurnal .= '</td>';

	// 			$hasil_jurnal .= '</tr>';

	// 			$ttl_debit += $debit + $charge;
	// 			$ttl_kredit += $kredit + $charge;
	// 			$no_jurnal++;
	// 		endif;

	// 		if ($item_payment->tipe == 'transport') {
	// 			// ambil detail transport untuk biaya bensin,tol, parkir
	// 			$get_transport = $this->db->get_where('tr_transport', ['no_req' => $item_payment->no_doc])->row();

	// 			// ambil coa bank
	// 			$this->accounting->select('a.no_perkiraan as no_coa, a.nama as nm_coa');
	// 			$this->accounting->from('coa_master a');
	// 			$this->accounting->where_in('a.no_perkiraan', $bank);
	// 			$coa_bank = $this->accounting->get()->row();

	// 			$debit = $item_payment->jumlah;
	// 			$kredit = str_replace(',', '', $payment_bank);
	// 			$payment_charge = str_replace(',', '', $payment_bank_charge);
	// 			$charge = str_replace(',', '', $bank_charge);

	// 			$arr_coa_jurnal = [
	// 				'6103-01-01',
	// 				'6103-01-02',
	// 				'6103-01-03',
	// 			];

	// 			$arr_coa_nm = [
	// 				'Biaya BBM Pengiriman',
	// 				'Biaya Tol',
	// 				'Biaya Parkir',
	// 			];

	// 			$bbm    = $get_transport->bensin;
	// 			$tol    = $get_transport->tol;
	// 			$parkir = $get_transport->parkir;

	// 			// array nilai sesuai urutan COA
	// 			$arr_nilai = [
	// 				$bbm,
	// 				$tol,
	// 				$parkir,
	// 			];

	// 			$no_jurnal = 1;
	// 			foreach ($arr_coa_jurnal as $i => $no_coa) {

	// 				$nm_coa = $arr_coa_nm[$i];
	// 				$nilai  = $arr_nilai[$i];

	// 				$hasil_jurnal .= '<tr>';

	// 				$hasil_jurnal .= '<td class="text-center">';
	// 				$hasil_jurnal .= date('d/m/Y');
	// 				$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tanggal_jurnal]" value="' . date('Y-m-d') . '">';
	// 				$hasil_jurnal .= '</td>';

	// 				$hasil_jurnal .= '<td class="text-center">';
	// 				$hasil_jurnal .= 'BUK';
	// 				$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tipe]" value="BUK">';
	// 				$hasil_jurnal .= '</td>';

	// 				$hasil_jurnal .= '<td class="text-center">';
	// 				$hasil_jurnal .= $no_coa;
	// 				$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][coa]" value="' . $no_coa . '">';
	// 				$hasil_jurnal .= '</td>';

	// 				$hasil_jurnal .= '<td class="text-center">';
	// 				$hasil_jurnal .= $nm_coa;
	// 				$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][nm_coa]" value="' . $nm_coa . '">';
	// 				$hasil_jurnal .= '</td>';

	// 				$hasil_jurnal .= '<td class="text-right">';
	// 				$hasil_jurnal .= number_format($nilai, 0, ',', '.');
	// 				$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][debit]" value="' . $nilai . '">';
	// 				$hasil_jurnal .= '</td>';

	// 				$hasil_jurnal .= '<td class="text-right">';
	// 				$hasil_jurnal .= '0';
	// 				$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][kredit]" value="0">';
	// 				$hasil_jurnal .= '</td>';

	// 				$hasil_jurnal .= '</tr>';

	// 				$ttl_nilai += $nilai;
	// 				$no_jurnal++;
	// 			}

	// 			// Jurnal Adm Bank
	// 			$hasil_jurnal .= '<tr>';

	// 			$hasil_jurnal .= '<td class="text-center">';
	// 			$hasil_jurnal .= date('d/m/Y');
	// 			$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tanggal_jurnal]" value="' . date('Y-m-d') . '">';
	// 			$hasil_jurnal .= '</td>';

	// 			$hasil_jurnal .= '<td class="text-center">';
	// 			$hasil_jurnal .= 'BUK';
	// 			$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tipe]" value="BUK">';
	// 			$hasil_jurnal .= '</td>';

	// 			$hasil_jurnal .= '<td class="text-center">';
	// 			$hasil_jurnal .= '7201-01-02';
	// 			$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][coa]" value="7201-01-02">';
	// 			$hasil_jurnal .= '</td>';

	// 			$hasil_jurnal .= '<td class="text-center">';
	// 			$hasil_jurnal .= 'Biaya Adm Bank & Buku Cek/Giro';
	// 			$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][nm_coa]" value="Biaya Adm Bank & Buku Cek/Giro">';
	// 			$hasil_jurnal .= '</td>';

	// 			$hasil_jurnal .= '<td class="text-right">';
	// 			$hasil_jurnal .= number_format($charge);
	// 			$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][debit]" value="' . $charge . '">';
	// 			$hasil_jurnal .= '</td>';

	// 			$hasil_jurnal .= '<td class="text-right">';
	// 			$hasil_jurnal .= '0';
	// 			$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][kredit]" value="0">';
	// 			$hasil_jurnal .= '</td>';

	// 			$hasil_jurnal .= '</tr>';
	// 			$no_jurnal++;

	// 			// Jurnal Bank Pembayaran
	// 			$hasil_jurnal .= '<tr>';

	// 			$hasil_jurnal .= '<td class="text-center">';
	// 			$hasil_jurnal .= date('d/m/Y');
	// 			$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tanggal_jurnal]" value="' . date('Y-m-d') . '">';
	// 			$hasil_jurnal .= '</td>';

	// 			$hasil_jurnal .= '<td class="text-center">';
	// 			$hasil_jurnal .= 'BUK';
	// 			$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tipe]" value="BUK">';
	// 			$hasil_jurnal .= '</td>';

	// 			$hasil_jurnal .= '<td class="text-center">';
	// 			$hasil_jurnal .= $coa_bank->no_coa;
	// 			$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][coa]" value="' . $coa_bank->no_coa . '">';
	// 			$hasil_jurnal .= '</td>';

	// 			$hasil_jurnal .= '<td class="text-center">';
	// 			$hasil_jurnal .= $coa_bank->nm_coa;
	// 			$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][nm_coa]" value="' . $coa_bank->nm_coa . '">';
	// 			$hasil_jurnal .= '</td>';

	// 			$hasil_jurnal .= '<td class="text-right">';
	// 			$hasil_jurnal .= '0';
	// 			$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][debit]" value="0">';
	// 			$hasil_jurnal .= '</td>';

	// 			$hasil_jurnal .= '<td class="text-right">';
	// 			$hasil_jurnal .= number_format($kredit);
	// 			$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][kredit]" value="' . $kredit . '">';
	// 			$hasil_jurnal .= '</td>';

	// 			$hasil_jurnal .= '</tr>';

	// 			$no_jurnal++;

	// 			// Jurnal Bank Pembayaran admin
	// 			$hasil_jurnal .= '<tr>';

	// 			$hasil_jurnal .= '<td class="text-center">';
	// 			$hasil_jurnal .= date('d/m/Y');
	// 			$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tanggal_jurnal]" value="' . date('Y-m-d') . '">';
	// 			$hasil_jurnal .= '</td>';

	// 			$hasil_jurnal .= '<td class="text-center">';
	// 			$hasil_jurnal .= 'BUK';
	// 			$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tipe]" value="BUK">';
	// 			$hasil_jurnal .= '</td>';

	// 			$hasil_jurnal .= '<td class="text-center">';
	// 			$hasil_jurnal .= $coa_bank->no_coa;
	// 			$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][coa]" value="' . $coa_bank->no_coa . '">';
	// 			$hasil_jurnal .= '</td>';

	// 			$hasil_jurnal .= '<td class="text-center">';
	// 			$hasil_jurnal .= $coa_bank->nm_coa;
	// 			$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][nm_coa]" value="' . $coa_bank->nm_coa . '">';
	// 			$hasil_jurnal .= '</td>';

	// 			$hasil_jurnal .= '<td class="text-right">';
	// 			$hasil_jurnal .= '0';
	// 			$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][debit]" value="0">';
	// 			$hasil_jurnal .= '</td>';

	// 			$hasil_jurnal .= '<td class="text-right">';
	// 			$hasil_jurnal .= number_format($payment_charge);
	// 			$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][kredit]" value="' . $payment_charge . '">';
	// 			$hasil_jurnal .= '</td>';

	// 			$hasil_jurnal .= '</tr>';

	// 			$ttl_debit += $ttl_nilai + $charge;
	// 			$ttl_kredit += $kredit + $payment_charge;
	// 		}

	// 		if ($item_payment->tipe == 'expense') {
	// 			// Ambil header expense
	// 			$get_expense = $this->db->get_where('tr_expense', ['no_doc' => $item_payment->no_doc])->row();

	// 			// Ambil detail expense
	// 			$this->db->select('coa, keterangan, total_harga');
	// 			$this->db->from('tr_expense_detail');
	// 			$this->db->where('no_doc', $item_payment->no_doc);
	// 			$detail = $this->db->get()->result_array();

	// 			// list coa dari detail
	// 			$coa_list = array_unique(array_column($detail, 'coa'));

	// 			// Ambil coa berdasrakan detail expense
	// 			$this->accounting->select('a.no_perkiraan, a.nama');
	// 			$this->accounting->from('coa_master a');
	// 			$this->accounting->where_in('a.no_perkiraan', $coa_list);
	// 			$coa_rows = $this->accounting->get()->result_array();

	// 			// field dari coa master digabung buat detail
	// 			$coa_map = [];
	// 			foreach ($coa_rows as $row) {
	// 				$coa_map[$row['no_perkiraan']] = $row['nama'];
	// 			}

	// 			// ambil coa bank
	// 			$this->accounting->select('a.no_perkiraan as no_coa, a.nama as nm_coa');
	// 			$this->accounting->from('coa_master a');
	// 			$this->accounting->where_in('a.no_perkiraan', $bank);
	// 			$coa_bank = $this->accounting->get()->row();

	// 			$debit = $item_payment->jumlah;
	// 			$kredit = str_replace(',', '', $payment_bank);
	// 			$charge = str_replace(',', '', $bank_charge);
	// 			$payment_charge = str_replace(',', '', $payment_bank_charge);
	// 			$informasi = $get_expense->informasi;

	// 			if (!empty($get_expense->pettycash)) {
	// 				$no_jurnal = 1;
	// 				foreach ($detail as $row) {
	// 					$coa       = $row['coa'];
	// 					$nama_coa  = isset($coa_map[$coa]) ? $coa_map[$coa] : null;
	// 					$keterangan = $row['keterangan'];

	// 					$hasil_jurnal .= '<tr>';

	// 					$hasil_jurnal .= '<td class="text-center">';
	// 					$hasil_jurnal .= date('d/m/Y');
	// 					$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tanggal_jurnal]" value="' . date('Y-m-d') . '">';
	// 					$hasil_jurnal .= '</td>';

	// 					$hasil_jurnal .= '<td class="text-center">';
	// 					$hasil_jurnal .= 'BUK';
	// 					$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tipe]" value="BUK">';
	// 					$hasil_jurnal .= '</td>';

	// 					$hasil_jurnal .= '<td class="text-center">';
	// 					$hasil_jurnal .= $coa;
	// 					$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][coa]" value="' . $coa . '">';
	// 					$hasil_jurnal .= '</td>';

	// 					$hasil_jurnal .= '<td>';
	// 					$hasil_jurnal .= $nama_coa;
	// 					$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][nm_coa]" value="' . $nama_coa . '">';
	// 					$hasil_jurnal .= '</td>';

	// 					$hasil_jurnal .= '<td>';
	// 					$hasil_jurnal .= $keterangan;
	// 					$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][keterangan]" value="' . $keterangan . '">';
	// 					$hasil_jurnal .= '</td>';

	// 					$hasil_jurnal .= '<td class="text-right">';
	// 					$hasil_jurnal .= number_format($row['total_harga']);
	// 					$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][debit]" value="' . $row['total_harga'] . '">';
	// 					$hasil_jurnal .= '</td>';

	// 					$hasil_jurnal .= '<td class="text-right">';
	// 					$hasil_jurnal .= '0';
	// 					$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][kredit]" value="0">';
	// 					$hasil_jurnal .= '</td>';

	// 					$hasil_jurnal .= '</tr>';

	// 					$no_jurnal++;
	// 				}

	// 				$hasil_jurnal .= '<tr>';

	// 				$hasil_jurnal .= '<td class="text-center">';
	// 				$hasil_jurnal .= date('d/m/Y');
	// 				$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tanggal_jurnal]" value="' . date('Y-m-d') . '">';
	// 				$hasil_jurnal .= '</td>';

	// 				$hasil_jurnal .= '<td class="text-center">';
	// 				$hasil_jurnal .= 'BUK';
	// 				$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tipe]" value="BUK">';
	// 				$hasil_jurnal .= '</td>';

	// 				$hasil_jurnal .= '<td class="text-center">';
	// 				$hasil_jurnal .= '7201-01-02';
	// 				$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][coa]" value="7201-01-02">';
	// 				$hasil_jurnal .= '</td>';

	// 				$hasil_jurnal .= '<td>';
	// 				$hasil_jurnal .= 'Biaya Adm Bank & Buku Cek/Giro';
	// 				$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][nm_coa]" value="Biaya Adm Bank & Buku Cek/Giro">';
	// 				$hasil_jurnal .= '</td>';

	// 				$hasil_jurnal .= '<td>';
	// 				$hasil_jurnal .= 'Admin Bank';
	// 				$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][keterangan]" value="Admin Bank">';
	// 				$hasil_jurnal .= '</td>';

	// 				$hasil_jurnal .= '<td class="text-right">';
	// 				$hasil_jurnal .= number_format($charge);
	// 				$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][debit]" value="' . $charge . '">';
	// 				$hasil_jurnal .= '</td>';

	// 				$hasil_jurnal .= '<td class="text-right">';
	// 				$hasil_jurnal .= '0';
	// 				$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][kredit]" value="0">';
	// 				$hasil_jurnal .= '</td>';

	// 				$hasil_jurnal .= '</tr>';
	// 				$no_jurnal++;

	// 				$hasil_jurnal .= '<tr>';

	// 				$hasil_jurnal .= '<td class="text-center">';
	// 				$hasil_jurnal .= date('d/m/Y');
	// 				$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tanggal_jurnal]" value="' . date('Y-m-d') . '">';
	// 				$hasil_jurnal .= '</td>';

	// 				$hasil_jurnal .= '<td class="text-center">';
	// 				$hasil_jurnal .= 'BUK';
	// 				$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tipe]" value="BUK">';
	// 				$hasil_jurnal .= '</td>';

	// 				$hasil_jurnal .= '<td class="text-center">';
	// 				$hasil_jurnal .= $coa_bank->no_coa;
	// 				$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][coa]" value="' . $coa_bank->no_coa . '">';
	// 				$hasil_jurnal .= '</td>';

	// 				$hasil_jurnal .= '<td>';
	// 				$hasil_jurnal .= $coa_bank->nm_coa;
	// 				$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][nm_coa]" value="' . $coa_bank->nm_coa . '">';
	// 				$hasil_jurnal .= '</td>';

	// 				$hasil_jurnal .= '<td>';
	// 				$hasil_jurnal .= 'Biaya Expense : ' . $item_payment->no_doc;
	// 				$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][keterangan]" value="Biaya Expense : ' . $item_payment->no_doc . '">';
	// 				$hasil_jurnal .= '</td>';

	// 				$hasil_jurnal .= '<td class="text-right">';
	// 				$hasil_jurnal .= '0';
	// 				$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][debit]" value="0">';
	// 				$hasil_jurnal .= '</td>';

	// 				$hasil_jurnal .= '<td class="text-right">';
	// 				$hasil_jurnal .= number_format($kredit);
	// 				$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][kredit]" value="' . $kredit . '">';
	// 				$hasil_jurnal .= '</td>';

	// 				$hasil_jurnal .= '</tr>';
	// 				$no_jurnal++;

	// 				$hasil_jurnal .= '<tr>';

	// 				$hasil_jurnal .= '<td class="text-center">';
	// 				$hasil_jurnal .= date('d/m/Y');
	// 				$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tanggal_jurnal]" value="' . date('Y-m-d') . '">';
	// 				$hasil_jurnal .= '</td>';

	// 				$hasil_jurnal .= '<td class="text-center">';
	// 				$hasil_jurnal .= 'BUK';
	// 				$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tipe]" value="BUK">';
	// 				$hasil_jurnal .= '</td>';

	// 				$hasil_jurnal .= '<td class="text-center">';
	// 				$hasil_jurnal .= $coa_bank->no_coa;
	// 				$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][coa]" value="' . $coa_bank->no_coa . '">';
	// 				$hasil_jurnal .= '</td>';

	// 				$hasil_jurnal .= '<td>';
	// 				$hasil_jurnal .= $coa_bank->nm_coa;
	// 				$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][nm_coa]" value="' . $coa_bank->nm_coa . '">';
	// 				$hasil_jurnal .= '</td>';

	// 				$hasil_jurnal .= '<td>';
	// 				$hasil_jurnal .= 'Pembayaran Admin Bank';
	// 				$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][keterangan]" value="Pembayaran Admin Bank">';
	// 				$hasil_jurnal .= '</td>';

	// 				$hasil_jurnal .= '<td class="text-right">';
	// 				$hasil_jurnal .= '0';
	// 				$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][debit]" value="0">';
	// 				$hasil_jurnal .= '</td>';

	// 				$hasil_jurnal .= '<td class="text-right">';
	// 				$hasil_jurnal .= number_format($payment_charge);
	// 				$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][kredit]" value="' . $payment_charge . '">';
	// 				$hasil_jurnal .= '</td>';


	// 				$hasil_jurnal .= '</tr>';

	// 				$ttl_debit += $debit + $charge;
	// 				$ttl_kredit += $kredit + $payment_charge;
	// 			} else {
	// 				if (!empty($get_expense->exp_inv_po)) {
	// 					$get_inv_po = $this->db->get_where('tr_invoice_po', ['id' => $get_expense->no_doc])->row();
	// 					$get_po = $this->db->get_where('tr_purchase_order', ['no_surat' => $get_inv_po->no_po])->row();
	// 					// $get_top_po = $this->db->get_where('tr_top_po', ['id' => $get_inv_po->id_top])->row();

	// 					if ($get_po->tipe == 'pr depart') {
	// 						$get_detail_po = $this->db->get_where('dt_trans_po', ['no_po' => $get_po->no_po])->row();

	// 						$this->db->select('a.*');
	// 						$this->db->from('rutin_non_planning_header a');
	// 						$this->db->join('rutin_non_planning_detail b', 'b.no_pengajuan = a.no_pengajuan');
	// 						$this->db->where('b.id', $get_detail_po->idpr);
	// 						$get_pr_header = $this->db->get()->row();

	// 						$this->hris->select('a.id as id_comp, a.name as nm_comp');
	// 						$this->hris->from('companies a');
	// 						$this->hris->join('departments b', 'b.company_id = a.id');
	// 						$this->hris->where('b.id', $get_pr_header->id_dept);
	// 						$get_comp = $this->hris->get()->row();

	// 						$this->hris->select('a.id as id_div, a.name as nm_div');
	// 						$this->hris->from('divisions a');
	// 						$this->hris->join('departments b', 'b.division_id = a.id');
	// 						$this->hris->where('b.id', $get_pr_header->id_dept);
	// 						$get_div = $this->hris->get()->row();

	// 						$id_div = (!empty($get_div)) ? $get_div->id_div : '';
	// 						$nm_div = (!empty($get_div)) ? $get_div->nm_div : '';

	// 						if ($get_comp->id_comp == 'COM003' || $get_comp->id_comp == 'COM012') {
	// 							$get_company = $this->consultant->get_where('kons_tr_company', ['id' => '4'])->row();

	// 							$id_company = (!empty($get_company)) ? $get_company->id : '';
	// 							$nm_company = (!empty($get_company)) ? $get_company->nm_company : '';
	// 						}
	// 						if ($get_comp->id_comp == 'COM006') {
	// 							$get_company = $this->consultant->get_where('kons_tr_company', ['id' => '4'])->row();

	// 							$id_company = (!empty($get_company)) ? $get_company->id : '';
	// 							$nm_company = (!empty($get_company)) ? $get_company->nm_company : '';
	// 						}
	// 					} else {
	// 						// ini untuk jurnal pembayaran PO yang masuk ke expense
	// 						$this->accounting->select('a.no_perkiraan as no_coa, a.nama as nm_coa');
	// 						$this->accounting->from('coa_master a');
	// 						$this->accounting->where_in('a.no_perkiraan', $bank);
	// 						$coa_bank = $this->accounting->get()->row();

	// 						$informasi = $get_expense->informasi;

	// 						$no_jurnal = 1;
	// 						//baris ke 1
	// 						$hasil_jurnal .= '<tr>';

	// 						$hasil_jurnal .= '<td class="text-center">';
	// 						$hasil_jurnal .= date('d/m/Y');
	// 						$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tanggal_jurnal]" value="' . date('Y-m-d') . '">';
	// 						$hasil_jurnal .= '</td>';

	// 						$hasil_jurnal .= '<td class="text-center">';
	// 						$hasil_jurnal .= 'BUK';
	// 						$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tipe]" value="BUK">';
	// 						$hasil_jurnal .= '</td>';

	// 						$hasil_jurnal .= '<td class="text-center">';
	// 						$hasil_jurnal .= '2104-01-01';
	// 						$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][coa]" value="2104-01-01">';
	// 						$hasil_jurnal .= '</td>';

	// 						$hasil_jurnal .= '<td>';
	// 						$hasil_jurnal .= 'Hutang Pembelian Belum Ditagih';
	// 						$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][nm_coa]" value="Hutang Pembelian Belum Ditagih">';
	// 						$hasil_jurnal .= '</td>';

	// 						$hasil_jurnal .= '<td>';
	// 						$hasil_jurnal .= $informasi;
	// 						$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][keterangan]" value="' . $informasi . '">';
	// 						$hasil_jurnal .= '</td>';

	// 						$hasil_jurnal .= '<td class="text-right">';
	// 						$hasil_jurnal .= number_format($debit);
	// 						$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][debit]" value="' . $debit . '">';
	// 						$hasil_jurnal .= '</td>';

	// 						$hasil_jurnal .= '<td class="text-right">';
	// 						$hasil_jurnal .= '0';
	// 						$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][kredit]" value="0">';
	// 						$hasil_jurnal .= '</td>';

	// 						$hasil_jurnal .= '</tr>';
	// 						$no_jurnal++;

	// 						//baris kedua
	// 						$hasil_jurnal .= '<tr>';

	// 						$hasil_jurnal .= '<td class="text-center">';
	// 						$hasil_jurnal .= date('d/m/Y');
	// 						$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tanggal_jurnal]" value="' . date('Y-m-d') . '">';
	// 						$hasil_jurnal .= '</td>';

	// 						$hasil_jurnal .= '<td class="text-center">';
	// 						$hasil_jurnal .= 'BUK';
	// 						$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tipe]" value="BUK">';
	// 						$hasil_jurnal .= '</td>';

	// 						$hasil_jurnal .= '<td class="text-center">';
	// 						$hasil_jurnal .= '7201-01-02';
	// 						$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][coa]" value="7201-01-02">';
	// 						$hasil_jurnal .= '</td>';

	// 						$hasil_jurnal .= '<td>';
	// 						$hasil_jurnal .= 'Biaya Adm Bank & Buku Cek/Giro';
	// 						$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][nm_coa]" value="Biaya Adm Bank & Buku Cek/Giro">';
	// 						$hasil_jurnal .= '</td>';

	// 						$hasil_jurnal .= '<td>';
	// 						$hasil_jurnal .= 'Biaya Admin';
	// 						$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][keterangan]" value="Biaya Admin">';
	// 						$hasil_jurnal .= '</td>';

	// 						$hasil_jurnal .= '<td class="text-right">';
	// 						$hasil_jurnal .= number_format($charge);
	// 						$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][debit]" value="' . $charge . '">';
	// 						$hasil_jurnal .= '</td>';

	// 						$hasil_jurnal .= '<td class="text-right">';
	// 						$hasil_jurnal .= '0';
	// 						$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][kredit]" value="0">';
	// 						$hasil_jurnal .= '</td>';

	// 						$hasil_jurnal .= '</tr>';
	// 						$no_jurnal++;

	// 						//baris ketiga
	// 						$hasil_jurnal .= '<tr>';

	// 						$hasil_jurnal .= '<td class="text-center">';
	// 						$hasil_jurnal .= date('d/m/Y');
	// 						$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tanggal_jurnal]" value="' . date('Y-m-d') . '">';
	// 						$hasil_jurnal .= '</td>';

	// 						$hasil_jurnal .= '<td class="text-center">';
	// 						$hasil_jurnal .= 'BUK';
	// 						$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tipe]" value="BUK">';
	// 						$hasil_jurnal .= '</td>';

	// 						$hasil_jurnal .= '<td class="text-center">';
	// 						$hasil_jurnal .= $coa_bank->no_coa;
	// 						$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][coa]" value="' . $coa_bank->no_coa . '">';
	// 						$hasil_jurnal .= '</td>';

	// 						$hasil_jurnal .= '<td>';
	// 						$hasil_jurnal .= $coa_bank->nm_coa;
	// 						$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][nm_coa]" value="' . $coa_bank->nm_coa . '">';
	// 						$hasil_jurnal .= '</td>';

	// 						$hasil_jurnal .= '<td>';
	// 						$hasil_jurnal .= $informasi;
	// 						$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][keterangan]" value="' . $informasi . '">';
	// 						$hasil_jurnal .= '</td>';

	// 						$hasil_jurnal .= '<td class="text-right">';
	// 						$hasil_jurnal .= '0';
	// 						$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][debit]" value="0">';
	// 						$hasil_jurnal .= '</td>';

	// 						$hasil_jurnal .= '<td class="text-right">';
	// 						$hasil_jurnal .= number_format($kredit);
	// 						$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][kredit]" value="' . $kredit . '">';
	// 						$hasil_jurnal .= '</td>';

	// 						$hasil_jurnal .= '</tr>';
	// 						$no_jurnal++;

	// 						//baris keempat
	// 						$hasil_jurnal .= '<tr>';

	// 						$hasil_jurnal .= '<td class="text-center">';
	// 						$hasil_jurnal .= date('d/m/Y');
	// 						$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tanggal_jurnal]" value="' . date('Y-m-d') . '">';
	// 						$hasil_jurnal .= '</td>';

	// 						$hasil_jurnal .= '<td class="text-center">';
	// 						$hasil_jurnal .= 'BUK';
	// 						$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tipe]" value="BUK">';
	// 						$hasil_jurnal .= '</td>';

	// 						$hasil_jurnal .= '<td class="text-center">';
	// 						$hasil_jurnal .= $coa_bank->no_coa;
	// 						$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][coa]" value="' . $coa_bank->no_coa . '">';
	// 						$hasil_jurnal .= '</td>';

	// 						$hasil_jurnal .= '<td>';
	// 						$hasil_jurnal .= $coa_bank->nm_coa;
	// 						$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][nm_coa]" value="' . $coa_bank->nm_coa . '">';
	// 						$hasil_jurnal .= '</td>';

	// 						$hasil_jurnal .= '<td>';
	// 						$hasil_jurnal .= 'Pembayaran Biaya Admin';
	// 						$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][keterangan]" value="Pembayaran Biaya Admin">';
	// 						$hasil_jurnal .= '</td>';

	// 						$hasil_jurnal .= '<td class="text-right">';
	// 						$hasil_jurnal .= '0';
	// 						$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][debit]" value="0">';
	// 						$hasil_jurnal .= '</td>';

	// 						$hasil_jurnal .= '<td class="text-right">';
	// 						$hasil_jurnal .= number_format($payment_charge);
	// 						$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][kredit]" value="' . $payment_charge . '">';
	// 						$hasil_jurnal .= '</td>';

	// 						$hasil_jurnal .= '</tr>';

	// 						$ttl_debit += $debit + $charge;
	// 						$ttl_kredit += $kredit + $payment_charge;
	// 					}
	// 				} else {
	// 					$no_jurnal = 1;
	// 					foreach ($detail as $row) {
	// 						$coa       = $row['coa'];
	// 						$nama_coa  = isset($coa_map[$coa]) ? $coa_map[$coa] : null;
	// 						$debit_detail = $row['total_harga'];

	// 						$hasil_jurnal .= '<tr>';

	// 						$hasil_jurnal .= '<td class="text-center">';
	// 						$hasil_jurnal .= date('d/m/Y');
	// 						$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tanggal_jurnal]" value="' . date('Y-m-d') . '">';
	// 						$hasil_jurnal .= '</td>';

	// 						$hasil_jurnal .= '<td class="text-center">';
	// 						$hasil_jurnal .= 'BUK';
	// 						$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tipe]" value="BUK">';
	// 						$hasil_jurnal .= '</td>';

	// 						$hasil_jurnal .= '<td class="text-center">';
	// 						$hasil_jurnal .= $coa;
	// 						$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][coa]" value="' . $coa . '">';
	// 						$hasil_jurnal .= '</td>';

	// 						$hasil_jurnal .= '<td class="text-center">';
	// 						$hasil_jurnal .= $nama_coa;
	// 						$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][nm_coa]" value="' . $nama_coa . '">';
	// 						$hasil_jurnal .= '</td>';

	// 						$hasil_jurnal .= '<td>';
	// 						$hasil_jurnal .= $informasi;
	// 						$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][keterangan]" value="' . $informasi . '">';
	// 						$hasil_jurnal .= '</td>';

	// 						$hasil_jurnal .= '<td class="text-right">';
	// 						$hasil_jurnal .= number_format($debit_detail);
	// 						$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][debit]" value="' . $debit_detail . '">';
	// 						$hasil_jurnal .= '</td>';

	// 						$hasil_jurnal .= '<td class="text-right">';
	// 						$hasil_jurnal .= '0';
	// 						$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][kredit]" value="0">';
	// 						$hasil_jurnal .= '</td>';

	// 						$hasil_jurnal .= '</tr>';

	// 						$no_jurnal++;
	// 					}

	// 					$hasil_jurnal .= '<tr>';

	// 					$hasil_jurnal .= '<td class="text-center">';
	// 					$hasil_jurnal .= date('d/m/Y');
	// 					$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tanggal_jurnal]" value="' . date('Y-m-d') . '">';
	// 					$hasil_jurnal .= '</td>';

	// 					$hasil_jurnal .= '<td class="text-center">';
	// 					$hasil_jurnal .= 'BUK';
	// 					$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tipe]" value="BUK">';
	// 					$hasil_jurnal .= '</td>';

	// 					$hasil_jurnal .= '<td class="text-center">';
	// 					$hasil_jurnal .= '7201-01-02';
	// 					$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][coa]" value="7201-01-02">';
	// 					$hasil_jurnal .= '</td>';

	// 					$hasil_jurnal .= '<td class="text-center">';
	// 					$hasil_jurnal .= 'Biaya Adm Bank & Buku Cek/Giro';
	// 					$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][nm_coa]" value="Biaya Adm Bank & Buku Cek/Giro">';
	// 					$hasil_jurnal .= '</td>';

	// 					$hasil_jurnal .= '<td>';
	// 					$hasil_jurnal .= 'Biaya Admin';
	// 					$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][keterangan]" value="Biaya Admin">';
	// 					$hasil_jurnal .= '</td>';

	// 					$hasil_jurnal .= '<td class="text-right">';
	// 					$hasil_jurnal .= number_format($charge);
	// 					$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][debit]" value="' . $charge . '">';
	// 					$hasil_jurnal .= '</td>';

	// 					$hasil_jurnal .= '<td class="text-right">';
	// 					$hasil_jurnal .= '0';
	// 					$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][kredit]" value="0">';
	// 					$hasil_jurnal .= '</td>';

	// 					$hasil_jurnal .= '</tr>';
	// 					$no_jurnal++;

	// 					// baris sebelum paling bawah
	// 					$hasil_jurnal .= '<tr>';

	// 					$hasil_jurnal .= '<td class="text-center">';
	// 					$hasil_jurnal .= date('d/m/Y');
	// 					$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tanggal_jurnal]" value="' . date('Y-m-d') . '">';
	// 					$hasil_jurnal .= '</td>';

	// 					$hasil_jurnal .= '<td class="text-center">';
	// 					$hasil_jurnal .= 'BUK';
	// 					$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tipe]" value="BUK">';
	// 					$hasil_jurnal .= '</td>';

	// 					$hasil_jurnal .= '<td class="text-center">';
	// 					$hasil_jurnal .= $coa_bank->no_coa;
	// 					$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][coa]" value="' . $coa_bank->no_coa . '">';
	// 					$hasil_jurnal .= '</td>';

	// 					$hasil_jurnal .= '<td class="text-center">';
	// 					$hasil_jurnal .= $coa_bank->nm_coa;
	// 					$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][nm_coa]" value="' . $coa_bank->nm_coa . '">';
	// 					$hasil_jurnal .= '</td>';

	// 					$hasil_jurnal .= '<td>';
	// 					$hasil_jurnal .= 'Biaya Expense : ' . $item_payment->no_doc;
	// 					$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][keterangan]" value="Biaya Expense : ' . $item_payment->no_doc . '">';
	// 					$hasil_jurnal .= '</td>';

	// 					$hasil_jurnal .= '<td class="text-right">';
	// 					$hasil_jurnal .= '0';
	// 					$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][debit]" value="0">';
	// 					$hasil_jurnal .= '</td>';

	// 					$hasil_jurnal .= '<td class="text-right">';
	// 					$hasil_jurnal .= number_format($kredit);
	// 					$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][kredit]" value="' . $kredit . '">';
	// 					$hasil_jurnal .= '</td>';

	// 					$hasil_jurnal .= '</tr>';

	// 					$no_jurnal++;

	// 					// baris sebelum paling bawah
	// 					$hasil_jurnal .= '<tr>';

	// 					$hasil_jurnal .= '<td class="text-center">';
	// 					$hasil_jurnal .= date('d/m/Y');
	// 					$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tanggal_jurnal]" value="' . date('Y-m-d') . '">';
	// 					$hasil_jurnal .= '</td>';

	// 					$hasil_jurnal .= '<td class="text-center">';
	// 					$hasil_jurnal .= 'BUK';
	// 					$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][tipe]" value="BUK">';
	// 					$hasil_jurnal .= '</td>';

	// 					$hasil_jurnal .= '<td class="text-center">';
	// 					$hasil_jurnal .= $coa_bank->no_coa;
	// 					$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][coa]" value="' . $coa_bank->no_coa . '">';
	// 					$hasil_jurnal .= '</td>';

	// 					$hasil_jurnal .= '<td class="text-center">';
	// 					$hasil_jurnal .= $coa_bank->nm_coa;
	// 					$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][nm_coa]" value="' . $coa_bank->nm_coa . '">';
	// 					$hasil_jurnal .= '</td>';

	// 					$hasil_jurnal .= '<td>';
	// 					$hasil_jurnal .= 'Pembayaran Biaya Admin';
	// 					$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][keterangan]" value="Pembayaran Biaya Admin">';
	// 					$hasil_jurnal .= '</td>';

	// 					$hasil_jurnal .= '<td class="text-right">';
	// 					$hasil_jurnal .= '0';
	// 					$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][debit]" value="0">';
	// 					$hasil_jurnal .= '</td>';

	// 					$hasil_jurnal .= '<td class="text-right">';
	// 					$hasil_jurnal .= number_format($payment_charge);
	// 					$hasil_jurnal .= '<input type="hidden" name="jurnal_ls[' . $no_jurnal . '][kredit]" value="' . $payment_charge . '">';
	// 					$hasil_jurnal .= '</td>';

	// 					$hasil_jurnal .= '</tr>';

	// 					$ttl_debit += $debit + $charge;
	// 					$ttl_kredit += $kredit + $payment_charge;
	// 				}
	// 			}
	// 		}
	// 	endforeach;

	// 	$response = [
	// 		'hasil_jurnal' => $hasil_jurnal,
	// 		'ttl_debit' => $ttl_debit,
	// 		'ttl_kredit' => $ttl_kredit
	// 	];

	// 	echo json_encode($response);
	// }

	public function generate_id_invoice_jurnal($nomor)
	{
		$bulan_roman = int_to_roman(date('m'));
		$tahun2      = date('y');

		$sql = "
				SELECT MAX(no_jurnal) AS maxP
				FROM tr_jurnal
				WHERE no_jurnal LIKE ?
    			";
		$like = "%{$bulan_roman}-{$tahun2}%";

		$row = $this->db->query($sql, [$like])->row_array();

		$angkaUrut2 = $row['maxP'];

		if ($angkaUrut2) {
			$urutan2 = (int) substr($angkaUrut2, 0, 5);
		} else {
			$urutan2 = 0;
		}

		$urutan2 = $urutan2 + $nomor;

		$urut2     = sprintf('%05s', $urutan2);
		$kode_trans = $urut2 . '-AJV-' . $bulan_roman . '-' . $tahun2;

		return $kode_trans;
	}


	public function check_transport_payment($id_payment)
	{
		$this->db->select('a.*');
		$this->db->from('payment_approve a');
		$this->db->join('tr_transport_req b', 'b.no_doc = a.no_doc');
		$this->db->where_in('a.id', $id_payment);
		$get_transport = $this->db->get()->result();

		$result = (!empty($get_transport)) ? 1 : 0;

		return $result;
	}

	public function jurnal_refill_petty_cash($id_payment, $id_bank = null)
	{
		$this->db->select('a.*');
		$this->db->from('payment_approve a');
		$this->db->join('tr_transport_req b', 'b.no_doc = a.no_doc');
		$this->db->join('users c', 'c.nm_lengkap = a.created_by');
		$this->db->where_in('a.id', $id_payment);
		$this->db->group_by('a.id');
		$get_transport_val = $this->db->get()->result();

		return $get_transport_val;
	}

	public function set_jurnal_refill()
	{
		$post = $this->input->post();

		$id_payment = $post['id_payment'];
		$bank = $post['bank'];


		$hasil = '';

		$this->db->select('a.*');
		$this->db->from('payment_approve a');
		$this->db->where_in('a.id', explode(',', $id_payment));
		$get_payment = $this->db->get()->result();

		$ttl_debit = 0;
		$ttl_kredit = 0;

		foreach ($get_payment as $item_payment) {
			// if ($item_payment->tipe == 'transport') {
			// 	$this->db->select('b.title_id');
			// 	$this->db->from('tr_transport_req a');
			// 	$this->db->join('users b', 'b.nm_lengkap = a.created_by');
			// 	$this->db->where('a.no_doc', $item_payment->no_doc);
			// 	$get_check_transport_title_user = $this->db->get()->row();

			// 	$id_divisi = '';
			// 	$nm_divisi = '';

			// 	if ($get_check_transport_title_user->title_id == 'TIT009') {
			// 		$arr_coa_jurnal_refill = ['1010-10-2'];

			// 		$this->hris->select('a.id as id_title, a.name as nm_title');
			// 		$this->hris->from('titles a');
			// 		$this->hris->where('a.id', $get_check_transport_title_user->title_id);
			// 		$get_titles = $this->hris->get()->row();

			// 		$id_divisi = (!empty($get_titles)) ? $get_titles->id_title : '';
			// 		$nm_divisi = (!empty($get_titles)) ? $get_titles->nm_title : '';

			// 		$nm_bank = '';

			// 		if (!empty($bank)) {
			// 			$this->db->select('a.rekening, a.nama, a.coa_bank, b.nama_bank as nm_bank');
			// 			$this->db->from('ms_bank a');
			// 			$this->db->join('list_bank b', 'b.id = a.bank', 'left');
			// 			$this->db->where('a.id', $bank);
			// 			$get_bank = $this->db->get()->row();

			// 			$nm_bank = $get_bank->rekening . ' a/n ' . $get_bank->nm_bank;

			// 			$arr_coa_jurnal_refill[] = $get_bank->coa_bank;
			// 		}

			// 		$this->accounting->select('a.no_perkiraan as no_coa, a.nama as nm_coa');
			// 		$this->accounting->from('coa_master a');
			// 		$this->accounting->where_in('a.no_perkiraan', $arr_coa_jurnal_refill);
			// 		$get_coa_jurnal_refill = $this->accounting->get()->result();

			// 		$no_jurnal = 0;
			// 		foreach ($get_coa_jurnal_refill as $item_coa) {
			// 			$no_jurnal++;

			// 			$debit = 0;
			// 			$kredit = 0;

			// 			$keterangan = 'Refill Pettycash - ' . $item_payment->no_doc;
			// 			if ($item_coa->no_coa == '1010-10-2') {
			// 				$debit = $item_payment->jumlah;
			// 			} else {
			// 				$kredit = $item_payment->jumlah;
			// 				$keterangan = $nm_bank . ' - ' . $item_payment->no_doc;
			// 			}

			// 			$this->consultant->select('a.id, a.nm_company');
			// 			$this->consultant->from('kons_tr_company a');
			// 			$this->consultant->where('a.id', 4);
			// 			$get_company = $this->consultant->get()->row();

			// 			$id_company = (!empty($get_company)) ? $get_company->id : '';
			// 			$nm_company = (!empty($get_company)) ? $get_company->nm_company : '';

			// 			$hasil .= '<tr>';

			// 			$hasil .= '<td class="text-center">';
			// 			$hasil .= date('d F Y');
			// 			$hasil .= '<input type="hidden" name="jurnal_refill_pettycash[' . $no_jurnal . '][tanggal_jurnal]" value="' . date('Y-m-d') . '">';
			// 			$hasil .= '</td>';

			// 			$hasil .= '<td class="text-center">';
			// 			$hasil .= $nm_company;
			// 			$hasil .= '<input type="hidden" name="jurnal_refill_pettycash[' . $no_jurnal . '][id_company]" value="' . $id_company . '">';
			// 			$hasil .= '<input type="hidden" name="jurnal_refill_pettycash[' . $no_jurnal . '][nm_company]" value="' . $nm_company . '">';
			// 			$hasil .= '</td>';

			// 			$hasil .= '<td class="text-center">';
			// 			$hasil .= $nm_divisi;
			// 			$hasil .= '<input type="hidden" name="jurnal_refill_pettycash[' . $no_jurnal . '][id_divisi]" value="' . $id_divisi . '">';
			// 			$hasil .= '<input type="hidden" name="jurnal_refill_pettycash[' . $no_jurnal . '][nm_divisi]" value="' . $nm_divisi . '">';
			// 			$hasil .= '</td>';

			// 			$hasil .= '<td class="text-center">';
			// 			$hasil .= $item_coa->no_coa;
			// 			$hasil .= '<input type="hidden" name="jurnal_refill_pettycash[' . $no_jurnal . '][no_coa]" value="' . $item_coa->no_coa . '">';
			// 			$hasil .= '</td>';

			// 			$hasil .= '<td class="text-center">';
			// 			$hasil .= $item_coa->nm_coa;
			// 			$hasil .= '<input type="hidden" name="jurnal_refill_pettycash[' . $no_jurnal . '][nm_coa]" value="' . $item_coa->nm_coa . '">';
			// 			$hasil .= '</td>';

			// 			$hasil .= '<td class="text-center">';
			// 			$hasil .= $keterangan;
			// 			$hasil .= '<input type="hidden" name="jurnal_refill_pettycash[' . $no_jurnal . '][keterangan]" value="' . $keterangan . '">';
			// 			$hasil .= '</td>';

			// 			$hasil .= '<td class="text-right">';
			// 			$hasil .= number_format($debit);
			// 			$hasil .= '<input type="hidden" name="jurnal_refill_pettycash[' . $no_jurnal . '][debit]" value="' . $debit . '">';
			// 			$hasil .= '</td>';

			// 			$hasil .= '<td class="text-right">';
			// 			$hasil .= number_format($kredit);
			// 			$hasil .= '<input type="hidden" name="jurnal_refill_pettycash[' . $no_jurnal . '][kredit]" value="' . $kredit . '">';
			// 			$hasil .= '</td>';

			// 			$hasil .= '</tr>';

			// 			$ttl_debit += $debit;
			// 			$ttl_kredit += $kredit;
			// 		}
			// 	}
			// }

			if ($item_payment->type == 'expense') {
				if (!empty($get_expense->pettycash)) {
					//isi code nya disini ntar kalo udah disuruh 
				}
			}
		}

		$response = [
			'hasil' => $hasil,
			'ttl_debit' => $ttl_debit,
			'ttl_kredit' => $ttl_kredit
		];

		echo json_encode($response);
	}
}
