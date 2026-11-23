<?php
// In-memory regression only: no application bootstrap, credentials, network, or database.
define('BASEPATH', __DIR__);
#[AllowDynamicProperties]
class CI_Model { public function __construct() {} }
require __DIR__ . '/../application/modules/production_report/models/Production_report_model.php';

require __DIR__ . '/lib/StockTestDatabase.php';
require __DIR__ . '/../application/helpers/material_stock_helper.php';

function verify($condition, $message)
{
    if (!$condition) throw new RuntimeException($message);
}
function coil($id, $wh, $kd)
{
    return ['id' => $id, 'id_material' => 'MAT-A', 'nm_material' => 'Material A',
        'id_gudang' => $wh, 'kd_gudang' => $kd, 'no_coil' => 'COIL-' . $id,
        'net_weight' => 10, 'gross_weight' => 11, 'length' => 20, 'status' => 1,
        'status_proses' => ['HLD'=>'in_warehouse','WIP'=>'wip'][$kd] ?? 'unpacked',
        'is_baby_coil' => $kd === 'PRT' ? 1 : 0, 'kode_internal' => 'INT-' . $id, 'no_ipp' => 'UNP-1'];
}
function stock($id, $wh, $kd, $qty = 100, $cb = 20)
{
    return ['id' => $id, 'code_lv4' => 'MAT-A', 'id_gudang' => $wh, 'kd_gudang' => $kd,
        'qty_stock' => $qty, 'total_nilai' => $qty * $cb, 'harga_beli' => $cb,
        'qty_free' => $qty, 'outgoing' => 0];
}
function material($id, $qty = 10)
{
    return ['id_unpack_baby_coil' => $id, 'coil_code' => 'COIL-' . $id, 'nett_weight_packing' => $qty];
}
function model($coils, $stocks, $details = [])
{
    $m = new Production_report_model();
    $m->db = new StockTestDatabase();
    $m->load = new StockTestLoader();
    foreach ($coils as $row) {
        $m->db->insert('warehouse_stock_coil', $row);
        if (!empty($row['is_baby_coil']) && $row['kd_gudang']==='PRT') {
            $m->db->insert('tr_unpack_coil_baby', ['id_coil_baru'=>$row['id'],'net_weight_pl'=>$row['net_weight'],'is_delete'=>0]);
        }
    }
    foreach ($stocks as $row) $m->db->insert('warehouse_stock', $row);
    foreach ($details as $row) $m->db->insert('warehouse_stock_transaction_detail', $row);
    return $m;
}
function run_out($m, $materials)
{
    return $m->process_production_stock_out(1, ['tgl_produksi' => '2026-11-20'], $materials, 7);
}

$tests = [];
$tests['PRT consumption does not change PRO'] = function () {
    $m = model([coil(1, 3, 'PRT')], [stock(1, 1, 'PRO'), stock(2, 3, 'PRT')]);
    verify(run_out($m, [material(1)]) === true, 'Expected success');
    verify($m->db->tables['warehouse_stock'][0]['qty_stock'] === 100, 'PRO changed');
    $prt = $m->db->tables['warehouse_stock'][1];
    verify($prt['qty_stock'] == 90 && $prt['total_nilai'] == 1800 && $prt['harga_beli'] == 20, 'PRT qty/value/costbook wrong');
    verify($prt['qty_free'] == 90 && $prt['outgoing'] == 10, 'Counters wrong');
    verify(count($m->get_stock_out_changes()) >= 7, 'Missing before/after audit');
};
$tests['same material in PRT WIP HLD stays separate'] = function () {
    $m = model([coil(1, 3, 'PRT'), coil(2, 4, 'WIP'), coil(3, 5, 'HLD')],
        [stock(1, 3, 'PRT', 100, 20), stock(2, 4, 'WIP', 100, 30), stock(3, 5, 'HLD', 100, 40)]);
    verify(run_out($m, [material(1), material(2), material(3)]) === true, 'Expected success');
    $rows = $m->db->tables['warehouse_stock_transaction_summary'];
    verify(count($rows) === 3, 'Warehouses merged');
    verify(array_column($rows, 'total_harga') == [200, 300, 400], 'Wrong warehouse valuation');
};
$tests['scan detail does not suppress consumption; repeat is idempotent'] = function () {
    $scan = ['id' => 1, 'kode_trans' => 'PR-0001', 'id_material' => 'MAT-A',
        'id_gudang' => 3, 'no_coil' => 'COIL-1', 'status_qc' => 'IN', 'to_status' => 'in_transit'];
    $m = model([coil(1, 3, 'PRT')], [stock(1, 3, 'PRT')], [$scan]);
    $m->db->insert('warehouse_stock_transaction_summary', ['kode_trans' => 'PR-0001']);
    verify(run_out($m, [material(1)]) === true, 'Scan incorrectly blocked consumption');
    $after = $m->db->tables;
    verify(run_out($m, [material(1)]) === true, 'Repeated submit failed');
    verify($m->db->tables === $after, 'Repeated consumption changed balances/ledger');
};
$tests['missing PRT never falls back to PRO'] = function () {
    $m = model([coil(1, 3, 'PRT')], [stock(1, 1, 'PRO')]);
    $before = $m->db->tables;
    verify(run_out($m, [material(1)]) === false, 'Missing PRT accepted');
    verify($m->db->tables === $before && strpos($m->get_stock_out_error(), 'PRT') !== false, 'Wrong mutation/error');
};
$tests['insufficient qty value negative price duplicate and wrong warehouse identity rejected'] = function () {
    $bad = stock(1, 3, 'PRT'); $bad['total_nilai'] = 1;
    $missing = stock(1, 3, 'PRT'); $missing['total_nilai'] = null;
    foreach ([[stock(1, 3, 'PRT', 5)], [$bad], [$missing], [stock(1, 3, 'PRT', 100, -1)],
        [stock(1, 3, 'PRT'), stock(2, 3, 'PRT')], [stock(1, 4, 'PRT')]] as $stocks) {
        $m = model([coil(1, 3, 'PRT')], $stocks);
        $before = $m->db->tables;
        verify(run_out($m, [material(1)]) === false, 'Invalid stock accepted');
        verify($m->db->tables === $before, 'Invalid stock mutated data');
    }
};
$tests['decimal full consumption and zero cost retain valid existing behavior'] = function () {
    $a = coil(1, 3, 'PRT'); $a['net_weight'] = 0.1;
    $b = coil(2, 3, 'PRT'); $b['net_weight'] = 0.2;
    $m = model([$a, $b], [stock(1, 3, 'PRT', 0.3, 20)]);
    verify(run_out($m, [material(1, 0.1), material(2, 0.2)]) === true, 'Float residue blocked valid consumption');
    verify($m->db->tables['warehouse_stock'][0]['qty_stock'] == 0, 'Expected zero qty');
    $m = model([coil(1, 3, 'PRT')], [stock(1, 3, 'PRT', 100, 0)]);
    verify(run_out($m, [material(1)]) === true, 'Zero cost existing behavior changed');
};
$tests['unknown ID does not fall back to matching coil code'] = function () {
    $m = model([coil(1, 3, 'PRT')], [stock(1, 3, 'PRT')]);
    $mat = material(1); $mat['id_unpack_baby_coil'] = 999;
    verify(run_out($m, [$mat]) === false, 'Invalid explicit ID accepted');
};
$tests['same warehouse multiple coils aggregated once'] = function () {
    $m = model([coil(1, 3, 'PRT'), coil(2, 3, 'PRT')], [stock(1, 3, 'PRT')]);
    verify(run_out($m, [material(1), material(2)]) === true, 'Expected success');
    $rows = $m->db->tables['warehouse_stock_transaction_summary'];
    verify(count($rows) === 1 && $rows[0]['jumlah_coil'] === 2 && $rows[0]['qty_akhir'] == 80, 'Wrong aggregation');
};
$tests['duplicate coil and consumed by another transaction rejected'] = function () {
    $m = model([coil(1, 3, 'PRT')], [stock(1, 3, 'PRT')]);
    verify(run_out($m, [material(1), material(1)]) === false, 'Duplicate coil accepted');
    $c = coil(1, 3, 'PRT'); $c['status'] = 0; $c['status_proses'] = 'consumed';
    $m = model([$c], [stock(1, 3, 'PRT')]);
    verify(run_out($m, [material(1)]) === false, 'Inactive coil accepted');
};

foreach ($tests as $name => $test) { $test(); echo 'PASS ' . $name . PHP_EOL; }
echo count($tests) . ' in-memory scenarios passed; no database connected.' . PHP_EOL;
