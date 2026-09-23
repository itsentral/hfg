<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * This library for authentication user
 */
class Auth
{
    protected $ci;
    protected $user;

    public function __construct()
    {
        $this->ci = &get_instance();
        $this->ci->load->library('session');
        $this->ci->lang->load('users/users');
        $this->ci->load->model(array(
            'users/users_model',
            'users/user_groups_model'
        ));

        $this->user = $this->ci->session->userdata('app_session');
    }

    public function is_login()
    {
        return ($this->user) ? TRUE : FALSE;
    }

    public function user_id()
    {
        return isset($this->user['id_user']) ? $this->user['id_user'] : '';
    }

    public function department_id()
    {
        return isset($this->user['department_id']) ? $this->user['department_id'] : '';
    }

    public function user_name()
    {
        return isset($this->user['username']) ? $this->user['username'] : '';
    }

    public function user_cab()
    {
        return isset($this->user['kdcab']) ? $this->user['kdcab'] : '';
    }

    public function nama()
    {
        return isset($this->user['nm_lengkap']) ? $this->user['nm_lengkap'] : '';
    }

    public function userdata()
    {
        $userdata =  $this->ci->users_model->select(array("users.*"))
            ->find($this->user_id());
        $user_groups = "";

        if ($this->is_admin()) {
            $user_groups = "Administrator";
        } else {
            $user_groups = $this->get_user_groups();
        }

        $userdata->groups = $user_groups;

        return $userdata;
    }

    public function login($username = "", $password = "")
    {
        if ($this->is_login()) {
            redirect('Dashboard');
        }

        $user     = $this->ci->users_model->find_by(array('username' => $username));

        // Pesan ambigu bahasa Inggris untuk tampilan user (OWASP - username enumeration prevention)
        $generic_fail_msg = 'Invalid username or password.';

        if (!$user) {
            $this->ci->template->set_message($generic_fail_msg, 'error');
            $this->ci->session->set_flashdata('error', $generic_fail_msg);
            write_log('Auth', 'Login', 'Gagal login: Username "' . $username . '" tidak ditemukan', ['username' => $username], null, 0);
            return FALSE;
        }

        if ($user->deleted == 1) {
            $this->ci->template->set_message($generic_fail_msg, 'error');
            $this->ci->session->set_flashdata('error', $generic_fail_msg);
            write_log('Auth', 'Login', 'Gagal login: Akun user "' . $username . '" telah dihapus', ['username' => $username, 'id_user' => $user->id_user], null, 0);
            return FALSE;
        }

        if ($user->st_aktif == 0) {
            $msg_inactive = 'Your account is currently inactive. Please contact the administrator.';
            $this->ci->template->set_message($msg_inactive, 'error');
            $this->ci->session->set_flashdata('error', $msg_inactive);
            write_log('Auth', 'Login', 'Gagal login: Akun user "' . $username . '" non-aktif', ['username' => $username, 'id_user' => $user->id_user], null, 0);
            return FALSE;
        }

        if (password_verify($password, $user->password)) {
            //Buat Session
            $array = array();
            foreach ($user as $key => $usr) {
                $array[$key] = $usr;
            }

            $this->ci->session->set_userdata('app_session', $array);
            //Set User Data
            $this->user = $this->ci->session->userdata('app_session');
            //Update Login Terakhir
            $ip_address = ($this->ci->input->ip_address()) == "::1" ? "127.0.0.1" : $this->ci->input->ip_address();
            $this->ci->users_model->update($this->user_id(), array('login_terakhir' => date('Y-m-d H:i:s'), 'ip' => $ip_address));

            // Log Sukses Login
            write_log('Auth', 'Login', 'User "' . $username . '" berhasil login', ['username' => $username, 'id_user' => $user->id_user], null, 1);

            $requested_page = $this->ci->session->userdata('requested_page');
            if ($requested_page != '') {
                redirect("dashboard");
            }

            redirect("dashboard");
        }

        $this->ci->template->set_message($generic_fail_msg, 'error');
        $this->ci->session->set_flashdata('error', $generic_fail_msg);
        write_log('Auth', 'Login', 'Gagal login: Password salah untuk username "' . $username . '"', ['username' => $username, 'id_user' => $user->id_user], null, 0);
        return FALSE;
    }

    public function logout()
    {
        $username = $this->user_name();
        $userId   = $this->user_id();

        if (!empty($username) || !empty($userId)) {
            write_log('Auth', 'Logout', 'User "' . $username . '" melakukan logout', ['username' => $username, 'id_user' => $userId], null, 1);
        }

        $this->ci->session->sess_destroy();
        redirect('login');
    }

    public function is_admin()
    {
        $id = $this->user_id();

        $data = $this->ci->users_model->join('user_groups', 'users.id_user = user_groups.id_user')
            ->find_by(array('users.id_user' => $id, 'id_group' => 1));

        if ($data) {
            return TRUE;
        }

        return FALSE;
    }

    public function get_user_groups()
    {
        $id = $this->user_id();

        $groups = $this->ci->user_groups_model->select("user_groups.id_group, groups.nm_group")
            ->join('groups', 'user_groups.id_group = groups.id_group')
            ->order_by('nm_group', 'ASC')
            ->find_all_by(array('id_user' => $id));

        $return = "";
        $arr    = array();
        if ($groups) {
            foreach ($groups as $key => $gr) {
                $arr[] = ucwords($gr->nm_group);
            }

            $return = implode(", ", $arr);
        }

        return $return;
    }

    public function has_permission($nm_permission = "")
    {
        if ($nm_permission == "") {
            return FALSE;
        }

        if ($this->is_admin()) {
            return TRUE;
        }

        $id = $this->user_id();

        $group_permissions = $this->ci->users_model->join('user_groups', 'users.id_user = user_groups.id_user')
            ->join('group_permissions', 'user_groups.id_group = group_permissions.id_group')
            ->join('permissions', 'group_permissions.id_permission = permissions.id_permission')
            ->find_by(array('nm_permission' => $nm_permission, 'users.id_user' => $id));
        if ($group_permissions) {
            return TRUE;
        }

        $user_permissions = $this->ci->users_model->join('user_permissions', 'users.id_user = user_permissions.id_user')
            ->join('permissions', 'user_permissions.id_permission = permissions.id_permission')
            ->find_by(array('nm_permission' => $nm_permission, 'users.id_user' => $id));
        if ($user_permissions) {
            return TRUE;
        }

        return FALSE;
    }

    public function restrict($permission = null, $uri = null) // This function copied from bonfire with modification
    {
        // If user isn't logged in, redirect to the login page.
        if ($this->is_login() === false) {
            $this->ci->template->set_message(lang('users_must_login'), 'error');
            redirect('login');
        }

        // Check whether the user has the proper permissions.
        if (empty($permission) || $this->has_permission($permission)) {
            return true;
        }

        // If the user is logged in, but does not have permission...
        // If $uri is not set, get the previous page from the session.
        if (! $uri) {
            $uri      = $this->ci->session->userdata('previous_page');
            $req_page = $this->ci->session->userdata('requested_page');

            // If previous page and current page are the same, but the user no longer
            // has permission, redirect to site URL to prevent an infinite loop.
            if ($uri == $req_page) {
                $uri = site_url();
            }
        }

        // Inform the user of the lack of permission and redirect.
        $this->ci->template->set_message(lang('users_no_permission'), 'error');
        redirect($uri);
    }
}
