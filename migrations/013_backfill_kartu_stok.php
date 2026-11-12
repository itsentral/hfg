<?php
/**
 * Script Backfill CLI: Sinkronisasi histori Kartu Pemakaian Material
 * dari warehouse_stock_transaction_detail (PRT IN) & production_reports (final).
 *
 * Jalankan via CLI:
 *   php migrations/013_backfill_kartu_stok.php
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit("Forbidden: script ini hanya boleh dijalankan lewat CLI.\n");
}

$_SERVER['HTTP_HOST']   = $_SERVER['HTTP_HOST'] ?? 'localhost';
$_SERVER['SERVER_PORT'] = $_SERVER['SERVER_PORT'] ?? '80';

$root = dirname(__DIR__) . DIRECTORY_SEPARATOR;
define('ENVIRONMENT', 'development');
define('BASEPATH', $root . 'system' . DIRECTORY_SEPARATOR);
define('APPPATH', $root . 'application' . DIRECTORY_SEPARATOR);
define('FCPATH', $root);

// Parse optional CLI flags: --host=... --port=... --user=... --pass=... --db=...
$cli_opts = [];
foreach (array_slice($argv ?? [], 1) as $arg) {
    if (preg_match('/^--([a-z]+)=(.*)$/', $arg, $m)) {
        $cli_opts[$m[1]] = $m[2];
    }
}

// Muat konfigurasi DB & fungsi Common minimal tanpa memicu Admin_Controller redirect
require_once BASEPATH . 'core/Common.php';
require_once BASEPATH . 'core/Model.php';
require_once APPPATH . 'core/BF_Model.php';
require_once BASEPATH . 'database/DB.php';

include APPPATH . 'config/database.php';
$db_cfg = $db[$active_group];
if (isset($cli_opts['host'])) $db_cfg['hostname'] = $cli_opts['host'];
if (isset($cli_opts['port'])) $db_cfg['port']     = $cli_opts['port'];
if (isset($cli_opts['user'])) $db_cfg['username'] = $cli_opts['user'];
if (isset($cli_opts['pass'])) $db_cfg['password'] = $cli_opts['pass'];
if (isset($cli_opts['db']))   $db_cfg['database'] = $cli_opts['db'];
$db_cfg['db_debug'] = true;

$db_conn = DB($db_cfg);

class Cli_Loader
{
    private $ci;
    public function __construct($ci) { $this->ci = $ci; }
    public function database() { return true; }
    public function helper($name)
    {
        $names = is_array($name) ? $name : [$name];
        foreach ($names as $h) {
            $path = APPPATH . 'helpers/' . $h . '_helper.php';
            if (file_exists($path)) {
                require_once $path;
            }
        }
    }
    public function model($path, $alias = '')
    {
        $parts = explode('/', trim($path, '/'));
        if (count($parts) === 2) {
            $file = APPPATH . 'modules/' . $parts[0] . '/models/' . $parts[1] . '.php';
            $cls  = $parts[1];
        } else {
            $file = APPPATH . 'models/' . $parts[0] . '.php';
            $cls  = $parts[0];
        }
        $prop = $alias !== '' ? $alias : $cls;
        if (!isset($this->ci->$prop)) {
            require_once $file;
            $this->ci->$prop = new $cls();
        }
        return $this->ci->$prop;
    }
}

class Cli_Auth
{
    public function user_id() { return 1; }
}

class Cli_CI
{
    public $db;
    public $load;
    public $auth;
    public $Stock_from_warehouse_model;
    public $Production_report_model;
    public function __construct($db)
    {
        $this->db   = $db;
        $this->load = new Cli_Loader($this);
        $this->auth = new Cli_Auth();
    }
}

$GLOBALS['CLI_CI_INSTANCE'] = new Cli_CI($db_conn);
function &get_instance()
{
    return $GLOBALS['CLI_CI_INSTANCE'];
}

$ci =& get_instance();
$ci->load->model('stock_from_warehouse/Stock_from_warehouse_model');

$res = $ci->Stock_from_warehouse_model->backfill_kartu_stok();
echo "Backfill selesai. IN ditambahkan: {$res['in_added']}, Production Report diproses: {$res['reports_processed']}\n";
