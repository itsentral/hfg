<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('write_log')) {
    /**
     * Menulis Audit Log ke tabel `logs`
     *
     * @param string|array $module   Nama modul / controller atau array berisi parameter lengkap
     * @param string|null  $methode  Nama aksi / method (misal: 'Add', 'Edit', 'Delete')
     * @param string       $message  Keterangan log
     * @param mixed        $data     Data yang dikirim (array/object/string)
     * @param string|null  $sql      Perintah SQL (jika null, otomatis mengambil $CI->db->last_query())
     * @param int          $status   1 = success, 0 = failed
     * @return bool
     */
    function write_log($module, $methode = null, $message = '', $data = null, $sql = null, $status = 1)
    {
        $CI = &get_instance();

        try {
            // Dukungan jika parameter dikirim dalam bentuk 1 associative array
            if (is_array($module) && isset($module['module'])) {
                $params   = $module;
                $module   = isset($params['module']) ? $params['module'] : null;
                $methode  = isset($params['methode']) ? $params['methode'] : (isset($params['method']) ? $params['method'] : (isset($params['action']) ? $params['action'] : null));
                $message  = isset($params['message']) ? $params['message'] : '';
                $data     = isset($params['data']) ? $params['data'] : null;
                $sql      = isset($params['sql']) ? $params['sql'] : null;
                $status   = isset($params['status']) ? $params['status'] : 1;
            }

            // Pastikan library user_agent ter-load
            if (!isset($CI->agent)) {
                $CI->load->library('user_agent');
            }

            // Dapatkan username dan user_id dari parameter data (jika ada), auth, atau session
            $username = null;
            $userId   = null;

            // Jika dikirim lewat data (seperti saat login attempt belum ada session aktif)
            if (is_array($data)) {
                if (!empty($data['username'])) {
                    $username = $data['username'];
                }
                if (!empty($data['id_user'])) {
                    $userId = $data['id_user'];
                }
            }

            if (empty($username) && isset($CI->auth) && method_exists($CI->auth, 'is_login') && $CI->auth->is_login()) {
                if (method_exists($CI->auth, 'user_name')) {
                    $username = $CI->auth->user_name();
                }
                if (method_exists($CI->auth, 'user_id')) {
                    $userId = $CI->auth->user_id();
                }
            }

            if (empty($username) || empty($userId)) {
                $session = $CI->session->userdata('app_session');
                if (!empty($session)) {
                    if (empty($username) && !empty($session['username'])) {
                        $username = $session['username'];
                    }
                    if (empty($userId)) {
                        if (!empty($session['id_user'])) {
                            $userId = $session['id_user'];
                        } elseif (!empty($session['id'])) {
                            $userId = $session['id'];
                        }
                    }
                }
            }

            // Bersihkan data jika ada field sensitif (password, secret, dll)
            if (is_array($data) || is_object($data)) {
                $data_array = (array) $data;
                $sensitive_keys = array('password', 'pass', 'token', 'secret', 'key');
                foreach ($data_array as $k => $v) {
                    if (in_array(strtolower($k), $sensitive_keys, true)) {
                        $data_array[$k] = '******';
                    }
                }
                $data_string = json_encode($data_array, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            } else {
                $data_string = $data !== null ? (string)$data : null;
            }

            // Ambil SQL query jika tidak dispesifikasikan manual
            $sql_query = $sql;
            if ($sql_query === null && isset($CI->db)) {
                $sql_query = $CI->db->last_query();
            }

            // Ambil IP address (support IPv4 & IPv6)
            $ip_address = $CI->input->ip_address();
            if ($ip_address === '::1') {
                $ip_address = '127.0.0.1';
            }

            // Ambil info platform dan user agent
            $platform = isset($CI->agent) && method_exists($CI->agent, 'platform') ? $CI->agent->platform() : null;
            $agent    = isset($CI->agent) && method_exists($CI->agent, 'agent_string') ? $CI->agent->agent_string() : (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null);

            $insert_data = array(
                'module'     => $module,
                'methode'    => $methode,
                'username'   => $username,
                'data'       => $data_string,
                'sql'        => $sql_query,
                'ip_address' => $ip_address,
                'agent'      => $agent,
                'platform'   => $platform,
                'status'     => (int) $status,
                'message'    => $message,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => $userId ? (int) $userId : null
            );

            // Insert ke tabel logs
            if (isset($CI->db)) {
                return $CI->db->insert('logs', $insert_data);
            }

            return false;
        } catch (Exception $e) {
            // Log ke file jika insert database gagal agar tidak mengganggu transaksi utama
            log_message('error', 'Gagal menulis activity log: ' . $e->getMessage());
            return false;
        } catch (Throwable $t) {
            log_message('error', 'Gagal menulis activity log (Throwable): ' . $t->getMessage());
            return false;
        }
    }
}
