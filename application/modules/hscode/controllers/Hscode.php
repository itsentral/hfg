<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Hscode extends Admin_Controller
{
    //Permission
    protected $viewPermission   = 'Hscode.View';
    protected $addPermission    = 'Hscode.Add';
    protected $managePermission = 'Hscode.Manage';
    protected $deletePermission = 'Hscode.Delete';

    public function __construct()
    {
        parent::__construct();

        $this->load->model(array(
            'Hscode/Hscode_model'
        ));
        $this->template->title('Manage HS Code');

        date_default_timezone_set('Asia/Bangkok');
    }

    public function index()
    {
        $this->template->render('index');
    }

    public function data_side_hscode()
    {
        $this->Hscode_model->get_json_hscode();
    }

    public function add()
    {
        $this->auth->restrict($this->viewPermission);

        $countries = $this->Hscode_model->get_data('countries');

        $this->template->set([
            'countries' => $countries,
        ]);
        $this->template->title('Add HS Code');
        $this->template->render('add');
    }

    public function edit($id)
    {
        $this->auth->restrict($this->managePermission);
        $hs             = $this->db->get_where('hscode', ['id' => $id])->row();
        $countries      = $this->Hscode_model->get_data('countries');
        $requirements   = $this->db->get_where('hscode_requirement', ['hscode_id' => $hs->id])->result_array();
        $ArrRQ          = [];

        $origins = $this->db->get_where('hscode_origin', ['hscode_id' => $id])->result_array();
        foreach ($origins as &$origin) {
            $origin['details'] = $this->db->get_where('hscode_bm_origin', ['hscode_origin_id' => $origin['id']])->result_array();
        }

        foreach ($requirements as $rq) {
            $ArrRQ[$rq['type']][] = $rq;
        }

        $this->template->set([
            'hs' => $hs,
            'countries' => $countries,
            'ArrRQ' => $ArrRQ,
            'origins' => $origins,
        ]);
        $this->template->title('Edit HS Code');
        $this->template->render('add');
    }

    public function save()
    {
        $this->auth->restrict($this->addPermission);
        $post = $this->input->post();
        $data = $post;

        $data['id'] = $post['id'] ?: $this->Hscode_model->generate_id();

        $RQ = isset($post['requirement']) ? $post['requirement'] : '';
        unset($data['requirement']);
        unset($data['origin_bm']);

        $data['lartas'] = ($data['lartas']) ?: null;;
        $this->db->trans_begin();

        if (isset($post['id']) && $post['id'] == '') {
            $data['created_at'] = $data['modified_at'] = date('Y-m-d H:i:s');
            $data['created_by'] = $data['modified_by'] = $this->auth->user_id();
            $this->db->insert('hscode', $data);
        } else {
            $data['modified_at'] = date('Y-m-d H:i:s');
            $data['modified_by'] = $this->auth->user_id();
            $this->db->update('hscode', $data, ['id' => $data['id']]);
        }

        //save bm origin 
        if (isset($post['origin_bm']) && $post['origin_bm']) {
            foreach ($post['origin_bm'] as $originId => $originData) {
                $dataOrigin = [
                    'hscode_id' => $data['id'],
                    'origin_id' => $originData['country_id'],
                ];

                if (isset($originData['id']) && $originData['id']) {
                    $check = $this->db->get_where('hscode_origin', ['id' => $originData['id']])->num_rows();
                    if ($check > 0) {
                        $dataOrigin['modified_by'] = $this->auth->user_id();
                        $dataOrigin['modified_at'] = date('Y-m-d H:i:s');
                        $this->db->update('hscode_origin', $dataOrigin, ['id' => $originData['id']]);
                    }
                } else {
                    $dataOrigin['created_by'] = $dataOrigin['modified_by'] = $this->auth->user_id();
                    $dataOrigin['created_at'] = $dataOrigin['modified_at'] = date('Y-m-d H:i:s');
                    $this->db->insert('hscode_origin', $dataOrigin);
                    $originData['id'] = $this->db->insert_id();
                }

                if (isset($originData['details']) && $originData['details']) {
                    foreach ($originData['details'] as $detailId => $detailData) {
                        $dataDetail = [
                            'hscode_origin_id' => $originData['id'],
                            'bm_name' => $detailData['bm_name'],
                            'bm_value' => $detailData['bm_value'],
                            'bm_document' => $detailData['bm_document'],
                        ];

                        if (isset($detailData['id']) && $detailData['id']) {
                            $check = $this->db->get_where('hscode_bm_origin', ['id' => $detailData['id']])->num_rows();
                            if ($check > 0) {
                                $dataDetail['modified_by'] = $this->auth->user_id();
                                $dataDetail['modified_at'] = date('Y-m-d H:i:s');
                                $this->db->update('hscode_bm_origin', $dataDetail, ['id' => $detailData['id']]);
                            }
                        } else {
                            $dataDetail['created_by'] = $dataDetail['modified_by'] = $this->auth->user_id();
                            $dataDetail['created_at'] = $dataDetail['modified_at'] = date('Y-m-d H:i:s');
                            $this->db->insert('hscode_bm_origin', $dataDetail);
                        }
                    }
                }
            }
        }

        if ($RQ) {
            foreach ($RQ as $rq) {
                $dataRq = [
                    'hscode_id' => $data['id'],
                    'name' => $rq['name'],
                    'description' => $rq['description'],
                    'type' => $rq['type'],
                ];

                if (isset($rq['id']) && $rq['id']) {
                    $check = $this->db->get_where('hscode_requirement', ['id' => $rq['id']])->num_rows();
                    if ($check > 0) {
                        $dataRq['modified_by'] = $this->auth->user_id();
                        $dataRq['modified_at'] = date('Y-m-d H:i:s');
                        $this->db->update('hscode_requirement', $dataRq, ['id' => $rq['id']]);
                    }
                } else {
                    $dataRq['created_by'] = $dataRq['modified_by'] = $this->auth->user_id();
                    $dataRq['created_at'] = $dataRq['modified_at'] = date('Y-m-d H:i:s');
                    $this->db->insert('hscode_requirement', $dataRq);
                }
            }
        }

        if ($this->db->trans_status() === false) {
            $errorMsg = $this->db->error()['message'];
            $this->db->trans_rollback();
            $return = [
                'msg' => 'Failed save data HS Code.  Please try again.',
                'status' => 0,
            ];
            $keterangan = 'FAILED save data HS Code ' . $data['id'] . ', HS Code name : ' . $data['description'] . '. ' . $errorMsg;
            $status = 1;
            $nm_hak_akses = $this->addPermission;
            $kode_universal = $data['id'];
            $jumlah = 1;
            $sql = $this->db->last_query();
        } else {
            $this->db->trans_commit();
            $return = [
                'msg' => 'Success Save data Customer.',
                'status' => 1,
            ];
            $keterangan = 'SUCCESS save data Customer ' . $data['id'] . ', HS Code name : ' . $data['description'];
            $status = 1;
            $nm_hak_akses = $this->addPermission;
            $kode_universal = $data['id'];
            $jumlah = 1;
            $sql = $this->db->last_query();
        }
        simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
        echo json_encode($return);
    }

    public function update_kuota()
    {
        $id = $this->input->post('id');
        $tambah_kuota = $this->input->post('tambah_kuota');

        if (empty($id) || empty($tambah_kuota)) {
            echo json_encode(['status' => 0, 'pesan' => 'Data tidak lengkap']);
            return;
        }

        $this->db->trans_begin();

        // $oldData = $this->db->get_where('hscode', ['id' => $id])->row();

        $this->db->set('kuota_internal', 'kuota_internal + ' . (float)$tambah_kuota, FALSE);
        $this->db->where('id', $id);
        $this->db->update('hscode');

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            echo json_encode(['status' => 0, 'pesan' => 'Gagal mengupdate kuota']);
        } else {
            $this->db->trans_commit();
            history("Add New Kuota HS ID: " . $id . " sebesar " . $tambah_kuota);
            echo json_encode(['status' => 1, 'pesan' => 'Kuota berhasil ditambahkan']);
        }
    }

    public function delete()
    {
        $id = $this->input->post('id');
        $data = $this->db->get_where('hscode', ['id' => $id])->row_array();

        $this->db->trans_begin();
        $sql = $this->db->update('hscode', ['status' => '0', 'deleted_at' => date('Y-m-d H:i:s'), 'deleted_by' => $this->auth->user_id()], ['id' => $id]);
        $errMsg = $this->db->error()['message'];
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            $keterangan = 'FAILD ' . $errMsg;
            $status = 0;
            $nm_hak_akses = $this->addPermission;
            $kode_universal = $data['id'];
            $jumlah = 1;
            $sql = $this->db->last_query();
            $return = [
                'msg' => 'Failed delete data HS Codes. Please try again. ' . $errMsg,
                'status' => 0,
            ];
        } else {
            $this->db->trans_commit();
            $return = [
                'msg' => 'Delete data HS Codes.',
                'status' => 1,
            ];
            $keterangan = 'Delete data HS Codes ' . $data['id'] . ', HS Codes name : ' . $data['description'];
            $status = 1;
            $nm_hak_akses = $this->addPermission;
            $kode_universal = $data['id'];
            $jumlah = 1;
            $sql = $this->db->last_query();
        }
        simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
        echo json_encode($return);
    }
}
