<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('get_po_no_surat')) {
    /**
     * Mengambil nomor surat resmi PO berdasarkan no_po (referensi id)
     *
     * @param string|int $no_po
     * @return string|null
     */
    function get_po_no_surat($no_po)
    {
        if (empty($no_po)) return null;
        $CI = &get_instance();
        if (isset($CI->db)) {
            $row = $CI->db->select('no_surat')
                          ->from('tr_purchase_order')
                          ->where('no_po', $no_po)
                          ->limit(1)
                          ->get()
                          ->row();
            if ($row && !empty($row->no_surat)) {
                return $row->no_surat;
            }
        }
        return null;
    }
}

if (!function_exists('is_numeric_field_name')) {
    /**
     * Mengecek apakah nama field mengindikasikan nilai numerik / kuantitas / nominal
     *
     * @param string $fieldName
     * @return bool
     */
    function is_numeric_field_name($fieldName)
    {
        $numericKeywords = [
            'harga', 'price', 'total', 'subtotal', 'qty', 'jumlah', 'nominal',
            'diskon', 'disc', 'ppn', 'pajak', 'tax', 'kurs', 'rate', 'cost',
            'tarif', 'persen', 'percent', 'weight', 'berat', 'width', 'lebar',
            'length', 'panjang', 'thickness', 'tebal', 'kuota', 'saldo', 'budget',
            'nilai', 'dpp', 'revisi', 'count'
        ];
        $lower = strtolower((string) $fieldName);
        foreach ($numericKeywords as $kw) {
            if (strpos($lower, $kw) !== false) {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('calculate_log_diff')) {
    /**
     * Menghitung perbedaan (diff) nilai lama vs nilai baru hanya untuk field yang benar-benar berubah.
     * - Mengabaikan perbedaan format angka (contoh: 0.00 vs 0, 3744000000.0000 vs 3744000000).
     * - Mengabaikan perbedaan NULL / string kosong vs 0 pada kolom-kolom numerik (harga, total, qty, diskon, dll).
     *
     * @param array|object $oldData
     * @param array|object $newData
     * @return array
     */
    function calculate_log_diff($oldData, $newData)
    {
        $old = (array) $oldData;
        $new = (array) $newData;

        $changes = [];
        $ignoredKeys = [
            'update_by', 'update_date', 'updated_by', 'updated_at', 'updated_date',
            'modified_by', 'modified_at', 'modified_date', 'modified_on',
            'created_by', 'created_date', 'created_on', 'created_at'
        ];

        foreach ($new as $key => $newVal) {
            if (in_array(strtolower($key), $ignoredKeys, true)) {
                continue;
            }

            if (array_key_exists($key, $old)) {
                $oldVal = $old[$key];

                // Jika tipe array (misal nested / items), biarkan logika pemroses lain atau compare langsung
                if (is_array($oldVal) || is_array($newVal)) {
                    if (json_encode($oldVal) !== json_encode($newVal)) {
                        $changes[$key] = [
                            'old' => is_array($oldVal) ? json_encode($oldVal) : $oldVal,
                            'new' => is_array($newVal) ? json_encode($newVal) : $newVal
                        ];
                    }
                    continue;
                }

                // Normalisasi string
                $oldStr = ($oldVal === null) ? '' : trim((string) $oldVal);
                $newStr = ($newVal === null) ? '' : trim((string) $newVal);

                // Cek kesamaan string langsung
                if ($oldStr === $newStr) {
                    continue;
                }

                // Normalisasi numerik (hilangkan koma ribuan)
                $cleanOld = str_replace(',', '', $oldStr);
                $cleanNew = str_replace(',', '', $newStr);

                // 1. Keduanya berupa nilai numerik
                if (is_numeric($cleanOld) && is_numeric($cleanNew)) {
                    // Jika nilai numeriknya sama (selisih mendekati 0), abaikan
                    if (abs((float)$cleanOld - (float)$cleanNew) < 0.0000001) {
                        continue;
                    }
                }

                // 2. Salah satu NULL / kosong ('') dan yang lain bernilai 0 / 0.00 pada field numerik
                $isOldZero  = (is_numeric($cleanOld) && abs((float)$cleanOld) < 0.0000001);
                $isNewZero  = (is_numeric($cleanNew) && abs((float)$cleanNew) < 0.0000001);
                $isOldEmpty = ($oldVal === null || $oldStr === '');
                $isNewEmpty = ($newVal === null || $newStr === '');

                if (($isOldEmpty && $isNewZero) || ($isOldZero && $isNewEmpty)) {
                    if (is_numeric_field_name($key)) {
                        // Dianggap sama (nilai default/kosong sama-sama bernilai 0)
                        continue;
                    }
                }

                $changes[$key] = [
                    'old' => $oldVal,
                    'new' => $newVal
                ];
            }
        }

        return $changes;
    }
}

if (!function_exists('write_log')) {
    /**
     * Menulis Audit Log ke tabel `logs`
     *
     * @param string|array $module   Nama modul / controller atau array berisi parameter lengkap
     * @param string|null  $methode  Nama aksi / method (misal: 'Add', 'Edit', 'Delete')
     * @param string       $message  Keterangan log
     * @param mixed        $data     Data yang dikirim (array/object/string, atau ['old' => ..., 'new' => ...])
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

            // Pemrosesan no_surat jika pesan mengandung referensi no_po
            if (is_string($message) && !empty($message)) {
                $poCandidates = [];

                if (is_array($data)) {
                    if (!empty($data['no_po'])) {
                        $poCandidates[] = $data['no_po'];
                    }
                    if (!empty($data['new']) && is_array($data['new']) && !empty($data['new']['no_po'])) {
                        $poCandidates[] = $data['new']['no_po'];
                    }
                }

                if (preg_match('/(?:PO|No PO|no_po)[:\s]+([A-Za-z0-9\-\/]+)/i', $message, $matches)) {
                    $poCandidates[] = trim($matches[1]);
                }

                foreach (array_unique($poCandidates) as $candidatePo) {
                    $noSurat = get_po_no_surat($candidatePo);
                    if (!empty($noSurat) && $noSurat !== $candidatePo) {
                        $message = str_replace($candidatePo, $noSurat, $message);
                    }
                }
            }

            // Proteksi field sensitif
            $sensitive_keys = array('password', 'pass', 'token', 'secret', 'key');
            $maskSensitive = function ($item) use (&$maskSensitive, $sensitive_keys) {
                if (is_array($item) || is_object($item)) {
                    $arr = (array) $item;
                    foreach ($arr as $k => $v) {
                        if (in_array(strtolower($k), $sensitive_keys, true)) {
                            $arr[$k] = '******';
                        } elseif (is_array($v) || is_object($v)) {
                            $arr[$k] = $maskSensitive($v);
                        }
                    }
                    return $arr;
                }
                return $item;
            };

            $data_string = null;

            if (is_array($data) || is_object($data)) {
                $data_array = (array) $data;

                // Cek apakah data bertipe komparasi Before-After (mengandung key 'old' dan 'new')
                if (array_key_exists('old', $data_array) && array_key_exists('new', $data_array)) {
                    $oldClean = $maskSensitive($data_array['old']);
                    $newClean = $maskSensitive($data_array['new']);

                    $changes = calculate_log_diff($oldClean, $newClean);

                    // Tambahan: jika ada perubahan item detail yang disertakan langsung di data (misal 'detail_changes')
                    if (!empty($data_array['detail_changes']) && is_array($data_array['detail_changes'])) {
                        foreach ($data_array['detail_changes'] as $dKey => $dChange) {
                            $changes[$dKey] = $dChange;
                        }
                    }

                    $structuredData = [
                        'type'          => 'diff',
                        'total_changes' => count($changes),
                        'changes'       => $changes,
                        'before'        => $oldClean,
                        'after'         => $newClean
                    ];

                    $data_string = json_encode($structuredData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                } else {
                    $clean = $maskSensitive($data_array);
                    $data_string = json_encode($clean, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                }
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
            log_message('error', 'Gagal menulis activity log: ' . $e->getMessage());
            return false;
        } catch (Throwable $t) {
            log_message('error', 'Gagal menulis activity log (Throwable): ' . $t->getMessage());
            return false;
        }
    }
}
