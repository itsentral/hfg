<?php
defined('BASEPATH') || exit('No direct script access allowed');

if (!function_exists('get_param')) {
    /**
     * Ambil nilai parameter konfigurasi dari ms_config_param
     * @param string $key
     * @param mixed $default Nilai default jika key tidak ditemukan
     * @return mixed
     */
    function get_param($key, $default = null)
    {
        $CI = &get_instance();
        $row = $CI->db->get_where('ms_config_param', ['param_key' => $key])->row();
        return $row ? $row->param_value : $default;
    }
}

if (!function_exists('send_notification')) {
    /**
     * Kirim notifikasi in-app ke user tertentu
     * @param int    $user_id
     * @param string $judul
     * @param string $pesan
     * @param string $no_referensi
     * @param string $modul
     * @return bool
     */
    function send_notification($user_id, $judul, $pesan, $no_referensi = '', $modul = '')
    {
        $CI = &get_instance();
        return $CI->db->insert('ms_notification', [
            'user_id'      => $user_id,
            'judul'        => $judul,
            'pesan'        => $pesan,
            'no_referensi' => $no_referensi,
            'modul'        => $modul,
            'is_read'      => 0,
            'created_at'   => date('Y-m-d H:i:s'),
        ]);
    }
}

if (!function_exists('send_notification_to_users')) {
    /**
     * Kirim notifikasi ke banyak user sekaligus
     * @param array  $user_ids
     * @param string $judul
     * @param string $pesan
     * @param string $no_referensi
     * @param string $modul
     */
    function send_notification_to_users(array $user_ids, $judul, $pesan, $no_referensi = '', $modul = '')
    {
        foreach ($user_ids as $user_id) {
            send_notification($user_id, $judul, $pesan, $no_referensi, $modul);
        }
    }
}
