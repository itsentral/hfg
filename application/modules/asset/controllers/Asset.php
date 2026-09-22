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
            $data        = $this->input->post();

            $id       = isset($data['id']) ? $data['id'] : '';
            $kd_asset = isset($data['kd_asset']) ? $data['kd_asset'] : '';

            $nmCategory     = $this->asset_model->getWhere('asset_category', 'id', $data['category']);
            $id_coa         = isset($data['id_coa']) ? $data['id_coa'] : '';
            $category       = isset($data['category']) ? $data['category'] : '';
            $penyusutan     = isset($data['penyusutan']) ? $data['penyusutan'] : 'Y';
            $category_pajak = isset($data['category_pajak']) ? $data['category_pajak'] : '';
            $branch         = isset($data['branch']) ? $data['branch'] : '';

            $tgl_oleh      = date('Y-m-d');
            $tgl_perolehan = date('Y-m-d');
            $Ym            = date('ym');

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
            if (!empty($data['depresiasi'])) {
                $max_tahun = (int) $data['depresiasi'];
            }

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
                $src_ac     = $this->db->query($qAc)->result_array();
                $max_id     = substr($src_ac[0]['maxP'], -6);
                $nil_id     = (int) $max_id + 1;
                $code_asset = 'ORI-' . $Ym . sprintf('%06s', $nil_id);
                $tanda2     = $code_asset;
            } else {
                $code_asset = $kd_asset;
                $tanda      = 'Update ';
                $tanda2     = $id;
            }

            // Photo upload handling
            $pic = '';
            if (isset($_FILES['foto']['name']) && !empty($_FILES['foto']['name'])) {
                $config = array(
                    'upload_path'      => './assets/foto/',
                    'allowed_types'    => 'gif|jpg|png|jpeg|JPG|PNG',
                    'file_name'        => $code_asset,
                    'file_ext_tolower' => TRUE,
                    'overwrite'        => TRUE,
                    'max_size'         => 2048,
                    'remove_spaces'    => TRUE
                );

                $tmp = explode('.', $_FILES['foto']['name']);
                $ext = end($tmp);
                $pic = $code_asset . '.' . strtolower($ext);

                $this->load->library('upload', $config);
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('foto')) {
                    $error = array('error' => $this->upload->display_errors());
                    $Arr_Kembali = array(
                        'status' => 3,
                        'pesan'  => $error['error']
                    );
                    echo json_encode($Arr_Kembali);
                    return false;
                }
            }

            $nilai_asset  = str_replace(',', '', $data['nilai_asset']);
            $val_asset    = str_replace(',', '', $data['value']);
            $qty          = isset($data['qty']) ? str_replace(',', '', $data['qty']) : 1;
            $nama_user    = isset($data['nama_user']) ? $data['nama_user'] : '';
            $lokasi_asset = $data['lokasi_asset'];
            $cost_center  = isset($data['cost_center']) ? $data['cost_center'] : '';

            $username = (isset($this->session->userdata['ORI_User']['username']) ? $this->session->userdata['ORI_User']['username'] : (isset($this->session->userdata['app_session']['username']) ? $this->session->userdata['app_session']['username'] : (isset($this->session->userdata['username']) ? $this->session->userdata['username'] : 'admin')));

            $ArrHeader = array(
                'kd_asset'       => $code_asset,
                'nm_asset'       => $data['nm_asset'],
                'tgl_perolehan'  => $tgl_perolehan,
                'tgl_depresiasi' => $tgl_oleh,
                'category'       => $category,
                'id_coa'         => $id_coa,
                'category_pajak' => $category_pajak,
                'penyusutan'     => $penyusutan_fix,
                'nilai_asset'    => $nilai_asset,
                'qty'            => $qty,
                'depresiasi'     => $nilai_dep,
                'value'          => $val_asset,
                'kdcab'          => $branch,
                'id_dept'        => $lokasi_asset,
                'department'     => get_name('department', 'nm_dept', 'id', $lokasi_asset),
                'id_costcenter'  => $cost_center,
                'cost_center'    => get_name('costcenter', 'nm_costcenter', 'id_costcenter', $cost_center),
                'nama_user'      => $nama_user,
                'created_by'     => $username,
                'created_date'   => date('Y-m-d H:i:s')
            );

            if (!empty($pic)) {
                $ArrHeader['foto'] = $pic;
            }

            $detailData     = array();
            $detailDataDash = array();
            $detailDataNilai = array();

            if ($penyusutan == 'Y') {
                $jumlah_bulan = $max_tahun * 12;

                $tgl_perolehan_dep = $tgl_oleh;
                if (date('d', strtotime($tgl_oleh)) > 15) {
                    $tgl_perolehan_dep = date('Y-m-d', strtotime('+1 month', strtotime($tgl_oleh)));
                }

                $nm_cat_name = (!empty($src_category[0]['nm_category'])) ? strtoupper($src_category[0]['nm_category']) : '';

                for ($i = 1; $i <= $jumlah_bulan; $i++) {
                    $tahun_dep  = date('Y', strtotime('+' . ($i - 1) . ' month', strtotime($tgl_perolehan_dep)));
                    $bulan_dep  = sprintf('%02s', date('m', strtotime('+' . ($i - 1) . ' month', strtotime($tgl_perolehan_dep))));

                    $detailData[$i]['kd_asset']       = $code_asset;
                    $detailData[$i]['nm_asset']       = $data['nm_asset'];
                    $detailData[$i]['category']       = $category;
                    $detailData[$i]['category_pajak'] = $category_pajak;
                    $detailData[$i]['nm_category']    = $nm_cat_name;
                    $detailData[$i]['bulan']          = $bulan_dep;
                    $detailData[$i]['tahun']          = $tahun_dep;
                    $detailData[$i]['nilai_susut']    = $val_asset;
                    $detailData[$i]['lokasi_asset']   = $lokasi_asset;
                    $detailData[$i]['cost_center']    = $cost_center;
                    $detailData[$i]['kdcab']          = $branch;
                    $detailData[$i]['flag']           = 'N';
                }

            }

            $this->db->trans_start();
            if (empty($id)) {
                $this->db->insert('asset', $ArrHeader);
                                if ($penyusutan == 'Y') {
                    $this->db->insert_batch('asset_generate', $detailData);
                }
            } else {
                $this->db->where('id', $id);
                $this->db->update('asset', $ArrHeader);
            }
            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                $Arr_Data = array('pesan' => 'Asset gagal disimpan ...', 'status' => 0);
            } else {
                $this->db->trans_commit();
                $Arr_Data = array('pesan' => 'Asset berhasil disimpan. Thanks ...', 'status' => 1);
                history($tanda . 'asset ' . $tanda2);
            }

            echo json_encode($Arr_Data);
        } else {
            $id     = $this->uri->segment(3);
            $header = $this->asset_model->getWhere('asset', 'id', $id);
            $data   = array(
                'title'      => (!empty($header)) ? 'Edit Asset' : 'Add Asset',
                'action'     => (!empty($header)) ? 'edit' : 'add',
                'data'       => $header,
                'list_cab'   => $this->asset_model->getList('asset_branch'),
                'list_coa'   => $this->asset_model->getList('asset_coa'),
                'list_pajak' => $this->asset_model->getList('asset_category_pajak'),
                'list_dept'  => $this->asset_model->getList('department'),
                'list_catg'  => $this->asset_model->getList('asset_category')
            );
            $this->template->title((!empty($header)) ? 'Edit Asset' : 'Add Asset');
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

        $username = (isset($this->session->userdata['ORI_User']['username']) ? $this->session->userdata['ORI_User']['username'] : (isset($this->session->userdata['app_session']['username']) ? $this->session->userdata['app_session']['username'] : (isset($this->session->userdata['username']) ? $this->session->userdata['username'] : 'admin')));

        $ArrUpHeader = array(
            'id_coa'        => $data['id_coa'],
            'category'      => $data['category'],
            'modified_by'   => $username,
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
        $kd_asset         = $data['kd_asset'];
        $lokasi_asset_new = $data['lokasi_asset_new'];
        $cost_center_new  = $data['cost_center_new'];

        $username = (isset($this->session->userdata['ORI_User']['username']) ? $this->session->userdata['ORI_User']['username'] : (isset($this->session->userdata['app_session']['username']) ? $this->session->userdata['app_session']['username'] : (isset($this->session->userdata['username']) ? $this->session->userdata['username'] : 'admin')));

        $ArrUpHeader = array(
            'kdcab'         => $branch,
            'id_dept'       => $lokasi_asset_new,
            'department'    => get_name('department', 'nm_dept', 'id', $lokasi_asset_new),
            'id_costcenter' => $cost_center_new,
            'cost_center'   => get_name('costcenter', 'nm_costcenter', 'id_costcenter', $cost_center_new),
            'modified_by'   => $username,
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

        $username = (isset($this->session->userdata['ORI_User']['username']) ? $this->session->userdata['ORI_User']['username'] : (isset($this->session->userdata['app_session']['username']) ? $this->session->userdata['app_session']['username'] : (isset($this->session->userdata['username']) ? $this->session->userdata['username'] : 'admin')));

        $ArrUpHeader = array(
            'deleted_by'   => $username,
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

    public function get_jangka_waktu()
    {
        $id       = $this->uri->segment(3);
        $query    = "SELECT * FROM asset_category_pajak WHERE id='" . $this->db->escape_str($id) . "' ";
        $Q_result = $this->db->query($query)->result();
        $data     = (!empty($Q_result)) ? $Q_result[0]->jangka_waktu : 0;
        echo json_encode(array('jangka_waktu' => $data));
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
