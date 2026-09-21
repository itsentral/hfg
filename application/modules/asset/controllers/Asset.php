<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Asset extends Admin_Controller
{
    protected $viewPermission   = 'Asset.View';
    protected $addPermission    = 'Asset.Add';
    protected $managePermission = 'Asset.Manage';
    protected $deletePermission = 'Asset.Delete';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('asset/Asset_model', 'asset_model');
        $this->load->model('master_model');
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);

        $data = array(
            'title'    => 'Index Of Assets',
            'action'   => 'asset',
            'kategori' => $this->asset_model->getList('asset_category')
        );

        history('View index asset');
        $this->template->title('Index Of Assets');
        $this->template->render('index', $data);
    }

    public function data_side()
    {
        $this->asset_model->getDataJSON();
    }

    public function modal_view()
    {
        $id = $this->uri->segment(3);
        $qData = "SELECT a.*, b.nm_costcenter FROM asset a LEFT JOIN costcenter b ON a.id_costcenter=b.id_costcenter WHERE a.id='" . $this->db->escape_str($id) . "'";
        $dataD = $this->db->query($qData)->result_array();

        $data = array(
            'title'      => 'Detail Asset',
            'action'     => 'asset',
            'dataD'      => $dataD,
            'list_cab'   => $this->asset_model->getList('asset_branch'),
            'list_pajak' => $this->asset_model->getList('asset_category_pajak'),
            'list_dept'  => $this->asset_model->getList('department'),
            'list_catg'  => $this->asset_model->getList('asset_category'),
            'list_coa'   => $this->asset_model->getList('asset_coa')
        );
        history('View detail asset ' . $id);
        $this->load->view('asset/modal_view', $data);
    }

    public function add()
    {
        if ($this->input->post()) {
            $Arr_Kembali = array();
            $data = $this->input->post();
            $db2  = $this->db;

            $id       = isset($data['id']) ? $data['id'] : '';
            $kd_asset = isset($data['kd_asset']) ? $data['kd_asset'] : '';

            $nmCategory     = $this->asset_model->getWhere('asset_category', 'id', $data['category']);
            $id_coa         = isset($data['id_coa']) ? $data['id_coa'] : '';
            $category       = isset($data['category']) ? $data['category'] : '';
            $penyusutan     = isset($data['penyusutan']) ? $data['penyusutan'] : 'Y';
            $category_pajak = isset($data['category_pajak']) ? $data['category_pajak'] : '';
            $branch         = isset($data['branch']) ? $data['branch'] : '';

            $tgl_oleh     = date('Y-m-d');
            $tgl_perolehan = date('Y-m-d');
            $Ym           = date('ym');

            if (!empty($data['tanggal'])) {
                $tgl_oleh = date('Y-m-d', strtotime($data['tanggal']));
                $Year     = date('y', strtotime($data['tanggal']));
                $Month    = date('m', strtotime($data['tanggal']));
                $Ym       = $Year . $Month;
            }

            if (!empty($data['tanggal_oleh'])) {
                $tgl_perolehan = date('Y-m-d', strtotime($data['tanggal_oleh']));
            }

            $src_category = $this->db->get_where('asset_category', array('id' => $category))->result_array();
            $max_tahun    = (!empty($src_category[0]['max_tahun'])) ? $src_category[0]['max_tahun'] : 0;

            $penyusutan_fix = $penyusutan;
            if ($penyusutan == 'N') {
                $nilai_dep = 0;
            } else {
                $nilai_dep = $max_tahun;
            }

            $tanda  = 'Insert ';
            $tanda2 = '';

            if (empty($id)) {
                $qAc        = "SELECT max(kd_asset) as maxP FROM asset WHERE kd_asset LIKE 'ORI-" . $Ym . "%' ";
                $num_ac     = $this->db->query($qAc)->num_rows();
                $src_ac     = $this->db->query($qAc)->result_array();
                $max_id     = substr($src_ac[0]['maxP'], -6);
                $nil_id     = (int) $max_id + 1;
                $code_asset = 'ORI-' . $Ym . sprintf('%06s', $nil_id);
                $tanda2     = $code_asset;

                $qCty   = "SELECT * FROM vehicle_tool_category WHERE id='" . $category . "' ";
                $num_cty = $db2->query($qCty)->num_rows();
                $src_cty = $db2->query($qCty)->result_array();
            } else {
                $code_asset = $kd_asset;
                $tanda      = 'Update ';
                $tanda2     = $id;
            }

            $nilai_asset = str_replace(',', '', $data['nilai_asset']);
            $val_asset   = str_replace(',', '', $data['value']);

            $lokasi_asset = $data['lokasi_asset'];
            $cost_center  = isset($data['cost_center']) ? $data['cost_center'] : '';

            $ArrHeader = array(
                'kd_asset'       => $code_asset,
                'nm_asset'       => $data['nm_asset'],
                'tgl_perolehan'  => $tgl_perolehan,
                'category'       => $category,
                'id_coa'         => $id_coa,
                'category_pajak' => $category_pajak,
                'penyusutan'     => $penyusutan_fix,
                'nilai_asset'    => $nilai_asset,
                'depresiasi'     => $nilai_dep,
                'value'          => $val_asset,
                'kdcab'          => $branch,
                'id_dept'        => $lokasi_asset,
                'department'     => get_name('department', 'nm_dept', 'id', $lokasi_asset),
                'id_costcenter'  => $cost_center,
                'cost_center'    => get_name('costcenter', 'nm_costcenter', 'id_costcenter', $cost_center),
                'created_by'     => (isset($this->session->userdata['ORI_User']['username']) ? $this->session->userdata['ORI_User']['username'] : (isset($this->session->userdata['app_session']['username']) ? $this->session->userdata['app_session']['username'] : (isset($this->session->userdata['username']) ? $this->session->userdata['username'] : 'admin'))),
                'created_date'   => date('Y-m-d H:i:s')
            );

            $ArrHeaderInstalasi = array(
                'id'            => $code_asset,
                'name'          => $data['nm_asset'],
                'category_id'   => $category,
                'acquisition'   => $nilai_asset,
                'address'       => 'PENGADAAN ASSET',
                'description'   => $data['nm_asset'],
                'created_by'    => (isset($this->session->userdata['ORI_User']['username']) ? $this->session->userdata['ORI_User']['username'] : (isset($this->session->userdata['app_session']['username']) ? $this->session->userdata['app_session']['username'] : (isset($this->session->userdata['username']) ? $this->session->userdata['username'] : 'admin'))),
                'created_date'  => date('Y-m-d H:i:s')
            );

            $ArrPrice = array(
                'id_price'     => $code_asset,
                'category_id'  => $category,
                'price'        => $nilai_asset,
                'unit'         => 'MONTH',
                'created_by'   => (isset($this->session->userdata['ORI_User']['username']) ? $this->session->userdata['ORI_User']['username'] : (isset($this->session->userdata['app_session']['username']) ? $this->session->userdata['app_session']['username'] : (isset($this->session->userdata['username']) ? $this->session->userdata['username'] : 'admin'))),
                'created_date' => date('Y-m-d H:i:s')
            );

            $ArrCategory = array(
                'id'   => $category,
                'name' => (!empty($src_category[0]['nm_category'])) ? $src_category[0]['nm_category'] : ''
            );

            $detailData     = array();
            $detailDataDash = array();
            $detailDataNilai = array();

            if ($penyusutan == 'Y') {
                $jumlah_bulan = $max_tahun * 12;

                $tgl_perolehan_dep = $tgl_oleh;
                if (date('d', strtotime($tgl_oleh)) > 15) {
                    $tgl_perolehan_dep = date('Y-m-d', strtotime('+1 month', strtotime($tgl_oleh)));
                }

                for ($i = 1; $i <= $jumlah_bulan; $i++) {
                    $tahun_dep  = date('Y', strtotime('+' . ($i - 1) . ' month', strtotime($tgl_perolehan_dep)));
                    $bulan_dep  = sprintf('%02s', date('m', strtotime('+' . ($i - 1) . ' month', strtotime($tgl_perolehan_dep))));
                    $bulan_dep2 = date('n', strtotime('+' . ($i - 1) . ' month', strtotime($tgl_perolehan_dep)));

                    $sisa_nilai = $nilai_asset - ($val_asset * $i);
                    if ($i == $jumlah_bulan) {
                        $sisa_nilai = 0;
                    }

                    $detailData[$i]['kd_asset']     = $code_asset;
                    $detailData[$i]['bulan']        = $bulan_dep2;
                    $detailData[$i]['tahun']        = $tahun_dep;
                    $detailData[$i]['nilai_susut']  = $val_asset;
                    $detailData[$i]['sisa_nilai']   = $sisa_nilai;
                    $detailData[$i]['kdcab']        = $branch;
                    $detailData[$i]['lokasi_asset'] = $lokasi_asset;

                    $detailDataDash[$i]['kd_asset']     = $code_asset;
                    $detailDataDash[$i]['bulan']        = $bulan_dep2;
                    $detailDataDash[$i]['tahun']        = $tahun_dep;
                    $detailDataDash[$i]['nilai_susut']  = $val_asset;
                    $detailDataDash[$i]['sisa_nilai']   = $sisa_nilai;
                    $detailDataDash[$i]['kdcab']        = $branch;
                    $detailDataDash[$i]['lokasi_asset'] = $lokasi_asset;
                    $detailDataDash[$i]['cost_center']  = $cost_center;
                }

                $detailDataNilai[1]['kd_asset']    = $code_asset;
                $detailDataNilai[1]['sisa_nilai']  = $nilai_asset;
                $detailDataNilai[1]['kdcab']       = $branch;
                $detailDataNilai[1]['id_dept']     = $lokasi_asset;
            } else {
                $detailDataNilai[1]['kd_asset']   = $code_asset;
                $detailDataNilai[1]['sisa_nilai'] = $nilai_asset;
                $detailDataNilai[1]['kdcab']      = $branch;
                $detailDataNilai[1]['id_dept']    = $lokasi_asset;
            }

            $this->db->trans_start();
            if (empty($id)) {
                $this->db->insert('asset', $ArrHeader);
                $this->db->insert_batch('asset_nilai', $detailDataNilai);
                if ($penyusutan == 'Y') {
                    $this->db->insert_batch('asset_generate', $detailData);
                }
            } else {
                $this->db->where('id', $id);
                $this->db->update('asset', $ArrHeader);
            }

            if (isset($num_cty) && $num_cty < 1) {
                $db2->insert('vehicle_tool_category', $ArrCategory);
            }

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                $Arr_Data = array(
                    'pesan'  => 'Asset gagal disimpan ...',
                    'status' => 0
                );
            } else {
                $this->db->trans_commit();
                $Arr_Data = array(
                    'pesan'  => 'Asset berhasil disimpan. Thanks ...',
                    'status' => 1
                );
                history($tanda . 'asset ' . $tanda2);
            }

            echo json_encode($Arr_Data);
        } else {
            $id     = $this->uri->segment(3);
            $header = $this->asset_model->getWhere('asset', 'id', $id);
            $data   = array(
                'title'      => 'Add Asset',
                'action'     => 'add',
                'data'       => $header,
                'list_cab'   => $this->asset_model->getList('asset_branch'),
                'list_coa'   => $this->asset_model->getList('asset_coa'),
                'list_pajak' => $this->asset_model->getList('asset_category_pajak'),
                'list_dept'  => $this->asset_model->getList('department'),
                'list_catg'  => $this->asset_model->getList('asset_category')
            );
            $this->template->title('Add Asset');
            $this->template->render('add', $data);
        }
    }

    public function edit()
    {
        $id     = $this->uri->segment(3);
        $header = $this->asset_model->getWhere('asset', 'id', $id);
        $data   = array(
            'title'      => 'Edit Asset',
            'action'     => 'edit',
            'data'       => $header,
            'list_cab'   => $this->asset_model->getList('asset_branch'),
            'list_coa'   => $this->asset_model->getList('asset_coa'),
            'list_pajak' => $this->asset_model->getList('asset_category_pajak'),
            'list_dept'  => $this->asset_model->getList('department'),
            'list_catg'  => $this->asset_model->getList('asset_category')
        );
        $this->template->title('Edit Asset');
        $this->template->render('add', $data);
    }

    public function edited()
    {
        $data     = $this->input->post();
        $kd_asset = $data['kd_asset'];

        $ArrUpHeader = array(
            'id_coa'        => $data['id_coa'],
            'category'      => $data['category'],
            'modified_by'   => (isset($this->session->userdata['ORI_User']['username']) ? $this->session->userdata['ORI_User']['username'] : (isset($this->session->userdata['app_session']['username']) ? $this->session->userdata['app_session']['username'] : (isset($this->session->userdata['username']) ? $this->session->userdata['username'] : 'admin'))),
            'modified_date' => date('Y-m-d H:i:s')
        );

        $this->db->trans_start();
        $this->db->where('kd_asset', $kd_asset);
        $this->db->update('asset', $ArrUpHeader);
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $Arr_Data = array('pesan' => 'Asset gagal disimpan ...', 'status' => 0);
        } else {
            $this->db->trans_commit();
            $Arr_Data = array('pesan' => 'Asset berhasil disimpan. Thanks ...', 'status' => 1);
            history('Update asset ' . $kd_asset);
        }

        echo json_encode($Arr_Data);
    }

    public function move_asset()
    {
        $data             = $this->input->post();
        $branch           = $data['branch'];
        $kd_asset       = $data['kd_asset'];
        $lokasi_asset_new = $data['lokasi_asset_new'];
        $cost_center_new  = $data['cost_center_new'];

        $ArrUpHeader = array(
            'kdcab'         => $branch,
            'id_dept'       => $lokasi_asset_new,
            'department'    => get_name('department', 'nm_dept', 'id', $lokasi_asset_new),
            'id_costcenter' => $cost_center_new,
            'cost_center'   => get_name('costcenter', 'nm_costcenter', 'id_costcenter', $cost_center_new),
            'modified_by'   => (isset($this->session->userdata['ORI_User']['username']) ? $this->session->userdata['ORI_User']['username'] : (isset($this->session->userdata['app_session']['username']) ? $this->session->userdata['app_session']['username'] : (isset($this->session->userdata['username']) ? $this->session->userdata['username'] : 'admin'))),
            'modified_date' => date('Y-m-d H:i:s')
        );

        $ArrUpGen = array(
            'kdcab'        => $branch,
            'lokasi_asset' => $lokasi_asset_new,
            'cost_center'  => $cost_center_new
        );

        $this->db->trans_start();
        $this->db->where('kd_asset', $kd_asset);
        $this->db->update('asset', $ArrUpHeader);

        $this->db->where(array('kd_asset' => $kd_asset, 'flag' => 'N'));
        $this->db->update('asset_generate', $ArrUpGen);
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $Arr_Data = array('pesan' => 'Asset gagal dipindahkan ...', 'status' => 0);
        } else {
            $this->db->trans_commit();
            $Arr_Data = array('pesan' => 'Asset berhasil dipindahkan. Thanks ...', 'status' => 1);
            history('Move asset ' . $kd_asset);
        }

        echo json_encode($Arr_Data);
    }

    public function delete_asset()
    {
        $kd_asset = $this->uri->segment(3);

        $ArrUpHeader = array(
            'deleted_by'   => (isset($this->session->userdata['ORI_User']['username']) ? $this->session->userdata['ORI_User']['username'] : (isset($this->session->userdata['app_session']['username']) ? $this->session->userdata['app_session']['username'] : (isset($this->session->userdata['username']) ? $this->session->userdata['username'] : 'admin'))),
            'deleted_date' => date('Y-m-d H:i:s')
        );

        $ArrUpGen = array('flag' => 'L');

        $this->db->trans_start();
        $this->db->where('kd_asset', $kd_asset);
        $this->db->update('asset', $ArrUpHeader);

        $this->db->where(array('kd_asset' => $kd_asset, 'flag' => 'N'));
        $this->db->update('asset_generate', $ArrUpGen);

        $this->db->where(array('kd_asset' => $kd_asset, 'flag' => 'X'));
        $this->db->update('asset_generate', $ArrUpGen);
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $Arr_Data = array('pesan' => 'Asset gagal dihapus ...', 'status' => 0);
        } else {
            $this->db->trans_commit();
            $Arr_Data = array('pesan' => 'Asset berhasil dihapus. Thanks ...', 'status' => 1);
            history('Delete asset ' . $kd_asset);
        }

        echo json_encode($Arr_Data);
    }

    public function list_center()
    {
        $id = $this->uri->segment(3);
        $cs = $this->uri->segment(4);
        $query    = "SELECT * FROM costcenter WHERE id_dept='" . $this->db->escape_str($id) . "' AND deleted='N' ORDER BY nm_costcenter ASC";
        $Q_result = $this->db->query($query)->result();
        $option   = "<option value='0'>Select Costcenter</option>";
        if (!empty($Q_result)) {
            foreach ($Q_result as $row) {
                $selx = ($row->id_costcenter == $cs) ? 'selected' : '';
                $option .= "<option value='" . $row->id_costcenter . "' " . $selx . ">" . strtoupper($row->nm_costcenter) . "</option>";
            }
        }
        echo json_encode(array('option' => $option));
    }

    public function excel_asset($kategori = '0')
    {
        $where_kategori = "";
        if (!empty($kategori) && $kategori !== '0') {
            $where_kategori = " AND a.category = '" . $this->db->escape_str($kategori) . "' ";
        }

        $sql = "
            SELECT
                a.*,
                b.nm_category,
                c.nm_dept,
                d.nm_costcenter
            FROM
                asset a
                LEFT JOIN asset_category b ON a.category = b.id
                LEFT JOIN department c ON a.id_dept = c.id
                LEFT JOIN costcenter d ON a.id_costcenter = d.id_costcenter
            WHERE 1=1
                AND a.deleted_date IS NULL
                " . $where_kategori . "
            ORDER BY a.id DESC
        ";

        $data = array(
            'data' => $this->db->query($sql)->result_array()
        );

        $this->load->view('asset/excel_asset', $data);
    }

    public function download_excel($kategori = '0')
    {
        $this->excel_asset($kategori);
    }
}
