<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Users extends Front_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model(array('identitas_model'));
        $this->load->library('users/auth');

        // isi dari constants.php (atau dari config kalau kamu pakai recaptcha.php)
        $this->site_key   = defined('RECAPTCHA_SITE_KEY')   ? RECAPTCHA_SITE_KEY   : '';
        $this->secret_key = defined('RECAPTCHA_SECRET_KEY') ? RECAPTCHA_SECRET_KEY : '';

        $this->id_user  = $this->auth->user_id();
    }

    public function index()
    {
        redirect('users/setting');
    }

    public function login()
    {
        if ($this->auth->is_login()) {
            history("Login");
            redirect('/');
        }

        $identitas = $this->identitas_model->find_by(['ididentitas' => 1]);

        if ($this->input->post('login')) {
            $token = $this->input->post('g-recaptcha-response', true);
            list($ok, $info) = $this->verify_recaptcha_v3($token, 'login', 0.5);
            if (!$ok) {
                $this->session->set_flashdata('error', 'Verifikasi reCAPTCHA gagal: ' . $info);
                redirect('users/login');
                return;
            }
            $username = $this->input->post('username', true);
            $password = $this->input->post('password', true);
            $this->auth->login($username, $password);
        }

        // kirim site key ke view (dipakai di <script ... render=>)
        $this->template->set('recaptcha_site_key', $this->site_key);
        $this->template->set('idt', $identitas);
        $this->template->set_theme('default');
        $this->template->set_layout('login');
        $this->template->title('Login');
        $this->template->render('login_animate');
    }

    public function logout()
    {
        if (!empty($this->id_user)) {
            history("Logout");
        }
        $this->auth->logout();
    }

    /** Verifikasi reCAPTCHA v3 */
    private function verify_recaptcha_v3($token, $expectedAction = 'login', $threshold = 0.5)
    {
        if (empty($this->secret_key)) return [false, 'Secret key kosong'];
        if (empty($token))            return [false, 'Token kosong'];

        $postData = http_build_query([
            'secret'   => $this->secret_key,
            'response' => $token,
            'remoteip' => $this->input->ip_address()
        ]);

        $ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $postData,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
        ]);
        $raw = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($raw === false) return [false, 'cURL error: ' . $err];

        $res = json_decode($raw, true);
        if (empty($res['success'])) return [false, 'Gagal: ' . json_encode($res['error-codes'] ?? [])];

        if (!empty($expectedAction) && isset($res['action']) && $res['action'] !== $expectedAction) {
            return [false, 'Action mismatch'];
        }

        $score = isset($res['score']) ? (float)$res['score'] : 0.0;
        if ($score < $threshold) return [false, 'Skor rendah (' . $score . ')'];

        return [true, $score];
    }
}
