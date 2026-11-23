<?php
// Executes real model/helper methods against SQLite :memory: only. Never loads database.php.
require __DIR__ . '/test_production_report_source_stock.php';
class BF_Model extends CI_Model {}
require __DIR__ . '/../application/modules/request_list/models/Request_list_model.php';
require __DIR__ . '/../application/modules/unpack_coil/models/Unpack_coil_model.php';
require __DIR__ . '/../application/modules/warehouse/models/Warehouse_model.php';
require __DIR__ . '/../application/helpers/coil_kartu_stok_helper.php';
require __DIR__ . '/../application/helpers/production_stock_repost_helper.php';
function &get_instance() { return $GLOBALS['stock_test_ci']; }
class StockFlowReportModel extends Production_report_model {
    public function get_costing_rates_for_variant($code_lv4_or_id, $no_uniq_id = null) {
        return ['no_uniq_id'=>null,'man_power'=>0,'foh'=>0,'bahan_pendukung'=>0];
    }
}

function scan_fixture($db = null)
{
    $m = new Request_list_model();
    $m->db = $db ?: new StockTestDatabase(); $m->load = new StockTestLoader();
    $GLOBALS['stock_test_ci'] = $m;
    $m->db->insert('tr_spk_material_header', ['spk_no' => 'SPK-1', 'status' => 'Released', 'created_at' => '2026-01-01']);
    $m->db->insert('tr_spk_material_detail', ['id' => 1, 'spk_no' => 'SPK-1']);
    $m->db->insert('warehouse_pack', ['id' => 1, 'pack_code' => 'PACK-1', 'id_gudang' => 1, 'kd_gudang' => 'PRO', 'status' => 1]);
    foreach ([1,2] as $id) {
        $c = coil($id, 1, 'PRO'); $c['status_proses'] = 'in_warehouse'; $c['id_pack'] = 1;
        $m->db->insert('warehouse_stock_coil', $c);
    }
    $m->db->insert('warehouse_stock', stock(1, 1, 'PRO', 100, 20));
    $m->db->insert('warehouse_stock', stock(2, 3, 'PRT', 20, 40));
    return $m;
}
function rejects(callable $call, $fragment)
{
    try { $call(); } catch (RuntimeException $e) {
        verify(stripos($e->getMessage(), $fragment) !== false, $e->getMessage()); return;
    }
    throw new RuntimeException('Expected rejection: ' . $fragment);
}
function receipt_fixture($db, $id, $qty, $kd, $rate = 20)
{
    $wh = material_stock_warehouse($db, $kd);
    $c = coil($id, $wh['id'], $kd); $c['net_weight'] = $qty;
    $c['no_ipp'] = 'PR-0001'; $c['parent_coil_id'] = 1; $c['is_baby_coil'] = 1;
    $c['status_proses'] = $kd === 'WIP' ? 'wip' : 'on_hold';
    $db->insert('warehouse_stock_coil', $c);
    $changes = [];
    return material_stock_post($db, $c, $wh, $qty, 'IN', [
        'kode_trans' => 'PR-0001', 'user' => 7, 'production_report_id' => 1, 'rate' => $rate,
        'source' => material_stock_warehouse($db, 'PRT'), 'destination' => $wh,
        'to_status' => $c['status_proses'], 'description' => 'Hasil produksi',
    ], $changes);
}

$flow_tests = [];
$flow_tests['real scan confirmation conserves qty/value and records both warehouses'] = function () {
    $m = scan_fixture();
    $result = $m->confirm_scan_pack_to_prt('PACK-1', 'SPK-1', 7);
    verify($result['status'] === true, $result['message']);
    $stocks = $m->db->rows('warehouse_stock');
    verify($stocks[0]['qty_stock'] == 80 && $stocks[0]['total_nilai'] == 1600, 'Wrong PRO');
    verify($stocks[1]['qty_stock'] == 40 && $stocks[1]['total_nilai'] == 1200 && $stocks[1]['harga_beli'] == 30, 'Wrong PRT average');
    verify(count($m->db->rows('warehouse_stock_transaction_summary')) === 4, 'Missing two-sided summary');
    foreach ($m->db->rows('warehouse_stock_transaction_detail') as $detail) {
        verify($detail['id_summary'] > 0 && $detail['id_coil'] > 0 && $detail['price_per_coil'] == 200, 'Unlinked/wrong detail value');
    }
    verify(count($m->db->rows('warehouse_coil_kartu_stok')) === 2, 'Missing IN cards');
    verify(count($m->db->rows('warehouse_stock_per_day')) === 2, 'Missing daily books');
    $before = $m->db->tables;
    verify($m->confirm_scan_pack_to_prt('PACK-1', 'SPK-1', 7)['status'] === false, 'Repeated scan accepted');
    verify($m->db->tables === $before, 'Repeated scan changed postings');
};
class StockFailingDatabase extends StockTestDatabase {
    public function insert($table, $data) {
        if ($table === 'warehouse_stock_transaction_detail' && ($data['status_qc'] ?? '') === 'IN') {
            throw new RuntimeException('Injected IN write failure');
        }
        return parent::insert($table, $data);
    }
}
$flow_tests['failed IN rolls back OUT, coil pack cards and snapshots'] = function () {
    $m = scan_fixture(new StockFailingDatabase()); $before = $m->db->tables;
    $result = $m->confirm_scan_pack_to_prt('PACK-1', 'SPK-1', 7);
    verify($result['status'] === false && strpos($result['message'], 'Injected') !== false, 'Failure not propagated');
    verify($m->db->tables === $before, 'Partial transfer committed');
};
$flow_tests['missing master and wrong WIP identity fail closed'] = function () {
    $m = scan_fixture(); $m->db->where('kd_gudang','PRT')->delete('warehouse'); $before = $m->db->tables;
    verify($m->confirm_scan_pack_to_prt('PACK-1', 'SPK-1', 7)['status'] === false, 'Missing master accepted');
    verify($m->db->tables === $before, 'Missing master changed data');
    $db = new StockTestDatabase(); $db->insert('warehouse_stock', stock(1, 3, 'WIP'));
    rejects(function () use ($db) { material_stock_row($db, 'MAT-A', material_stock_warehouse($db, 'WIP')); }, 'inconsistent');
};
$flow_tests['WIP and Hold receipts create independent books and moving averages'] = function () {
    $db = new StockTestDatabase(); $db->insert('warehouse_stock', stock(1, 3, 'PRT', 90, 20));
    $db->insert('warehouse_stock', stock(2, 4, 'WIP', 10, 40));
    receipt_fixture($db, 2, 10, 'WIP'); receipt_fixture($db, 3, 5, 'HLD');
    $rows = $db->rows('warehouse_stock');
    verify($rows[0]['qty_stock'] == 90, 'WIP receipt altered PRT');
    verify($rows[1]['id_gudang'] == 4 && $rows[1]['qty_stock'] == 20 && $rows[1]['harga_beli'] == 30, 'Wrong WIP average');
    verify($rows[2]['id_gudang'] == 5 && $rows[2]['qty_stock'] == 5 && $rows[2]['total_nilai'] == 100, 'Missing Hold book');
};
$flow_tests['costbook always follows source book even when coil has another price'] = function () {
    $db = new StockTestDatabase(); $db->insert('warehouse_stock', stock(1, 1, 'PRO', 10, 999));
    $db->insert('warehouse_stock', stock(2, 4, 'WIP', 10, 33)); $c = coil(1, 4, 'WIP'); $c['harga_beli'] = 900;
    verify(material_stock_costbook($db, $c) == 33, 'Used coil/other warehouse rate');
};
$flow_tests['unpacking preserves book packing weight separately from actual weight'] = function () {
    $m = new Unpack_coil_model(); $m->db = new StockTestDatabase();
    $parent = coil(1, 3, 'PRT');
    $id = $m->insert_baby_stock_coil($parent, 'BC-1', 'BC-1', 9.95, 11, 20, 'UNP-1', 0, 0, 10);
    $baby = $m->db->get_where('warehouse_stock_coil',['id'=>$id])->row_array();
    $m->db->insert('tr_unpack_coil_baby', ['id_coil_baru'=>$id,'net_weight_pl'=>10,'is_delete'=>0]);
    verify($baby['net_weight'] == 9.95 && $baby['qty'] == 10 && $baby['total_nilai'] == 200, 'Actual/book mixed');
    verify($baby['status_proses'] === 'unpacked' && material_stock_book_weight($m->db, $baby) == 10, 'Wrong unpack source');
};
$flow_tests['repost restores only its own material WIP Hold and clears linked postings'] = function () {
    $m = model([coil(1,3,'PRT')],[stock(1,3,'PRT')]);
    $m->db->trans_begin(); verify(run_out($m,[material(1)]) === true, 'Consumption failed');
    receipt_fixture($m->db, 2, 2, 'WIP'); receipt_fixture($m->db, 3, 1, 'HLD');
    $audit=[]; production_stock_unpost($m->db,1,7,$audit); $m->db->trans_commit();
    verify($m->db->rows('warehouse_stock')[0]['qty_stock'] == 100, 'Source not restored');
    verify(count($m->db->rows('warehouse_stock_coil')) === 1 && $m->db->rows('warehouse_stock_coil')[0]['status'] == 1, 'Generated coils not cleared/source inactive');
    verify(count($m->db->rows('warehouse_stock_transaction_summary')) === 0 && count($audit) > 0, 'Stale ledger/missing audit');
    verify(run_out($m,[material(1)]) === true && $m->db->rows('warehouse_stock')[0]['qty_stock'] == 90, 'Repost did not consume once');
};
$flow_tests['downstream receipt prevents final edit and rollback keeps data'] = function () {
    $m = model([coil(1,3,'PRT')],[stock(1,3,'PRT')]); run_out($m,[material(1)]);
    $m->db->insert('warehouse_stock_transaction_summary',['id_material'=>'MAT-A','id_gudang'=>3,'production_report_id'=>2]);
    $before=$m->db->tables; $m->db->trans_begin(); $audit=[];
    rejects(function () use ($m,&$audit) { production_stock_unpost($m->db,1,7,$audit); }, 'subsequent');
    $m->db->trans_rollback(); verify($m->db->tables === $before,'Failed correction changed data');
};
$flow_tests['legacy final is not guessed or mass repaired'] = function () {
    $db=new StockTestDatabase(); $audit=[];
    rejects(function () use ($db,&$audit) { production_stock_unpost($db,99,7,$audit); }, 'legacy');
};
$flow_tests['journal callback failure rolls back the report save itself'] = function () {
    $m=model([],[]); $before=$m->db->tables;
    $id=$m->save_production_report(['status_draft'=>1],[],[],[],[],null,7,function () { throw new RuntimeException('Injected journal failure'); });
    verify($id === false && strpos((string)$m->get_stock_out_error(),'Injected') !== false,'Callback failure not propagated: ' . $m->get_stock_out_error());
    verify($m->db->tables === $before,'Report committed despite failed journal');
};
$flow_tests['actual WIP and Hold model receipt uses the source warehouse average'] = function () {
    $m=model([coil(1,3,'PRT')],[stock(1,3,'PRT',100,25)]);
    $m->db->trans_begin();
    $m->process_production_sisa_hold_coil_in(1,[],[
        ['scrap_type'=>'sisa_coil','target_coil_code'=>'COIL-1','berat_total'=>2],
        ['scrap_type'=>'hold_coil','target_coil_code'=>'COIL-1','berat_total'=>1],
    ],[material(1)],7);
    $rows=$m->db->rows('warehouse_stock');
    verify(count($rows)===3 && $rows[1]['id_gudang']==4 && $rows[1]['total_nilai']==50,'Real WIP receipt wrong');
    verify($rows[2]['id_gudang']==5 && $rows[2]['total_nilai']==25,'Real Hold receipt wrong');
    $m->db->trans_rollback();
};
$flow_tests['full final save and edit repost stock FG WIP and cards exactly once'] = function () {
    $fixture=model([coil(1,3,'PRT')],[stock(1,3,'PRT')]);
    $m=new StockFlowReportModel(); $m->db=$fixture->db; $m->load=$fixture->load;
    $GLOBALS['stock_test_ci']=$m;
    $header=['status_draft'=>0,'id_tr_spk_detail'=>null,'tgl_produksi'=>'2026-11-20'];
    $materials=[material(1)];
    $scraps=[['scrap_type'=>'sisa_coil','target_coil_code'=>'COIL-1','berat_total'=>1]];
    $items=[['source_material_coil'=>'COIL-1','product_lvl_4_id'=>1,'qty'=>1,'berat_per_pcs'=>9,'berat_total'=>9,'category_type'=>'kw_1']];
    $id=$m->save_production_report($header,$materials,$items,$scraps,[],null,7);
    verify($id===1,'Final save failed: ' . $m->get_stock_out_error());
    verify($m->db->rows('warehouse_stock')[0]['qty_stock']==90,'Wrong initial source withdrawal');
    $items[0]['berat_per_pcs']=8; $items[0]['berat_total']=8; $scraps[0]['berat_total']=2;
    $id=$m->save_production_report($header,$materials,$items,$scraps,[],1,7);
    verify($id===1,'Edit final failed: ' . $m->get_stock_out_error());
    $rows=$m->db->rows('warehouse_stock');
    verify($rows[0]['qty_stock']==90 && $rows[1]['qty_stock']==2,'Final edit doubled material/WIP');
    verify(count($m->db->rows('warehouse_stock_transaction_summary'))===2,'Final edit left old summaries');
    verify(count($m->db->rows('warehouse_stock_coil'))===2,'Final edit duplicated generated coil');
    $fg=$m->db->rows('fg_stock');
    verify(count($fg)===1 && $fg[0]['qty_stok']==1 && $fg[0]['total_berat']==8 && $fg[0]['total_nilai']==160,'Final edit left stale FG quantities/value');
};
$flow_tests['held source requires purchasing release and stays editable after release'] = function () {
    $c=coil(1,5,'HLD'); $c['status_proses']='on_hold';
    $m=model([$c],[stock(1,5,'HLD')]);
    verify(run_out($m,[material(1)])===false,'Unreleased Hold consumed');
    $m->db->where('id',1)->update('warehouse_stock_coil',['status_proses'=>'in_warehouse']);
    verify(count($m->get_coils_by_source('hold'))===1,'Released Hold disappeared from picker');
    verify(run_out($m,[material(1)])===true,'Released Hold rejected');
    $audit=[]; production_stock_unpost($m->db,1,7,$audit);
    verify(run_out($m,[material(1)])===true,'Correction forgot purchasing release');
};
$flow_tests['mother PRT and partial packing withdrawal are rejected'] = function () {
    $c=coil(1,3,'PRT'); $c['is_baby_coil']=0;
    $m=model([$c],[stock(1,3,'PRT')]);verify(run_out($m,[material(1)])===false,'Unpacked mother bypassed');
    $m=model([coil(1,3,'PRT')],[stock(1,3,'PRT')]);$before=$m->db->tables;
    verify(run_out($m,[material(1,9)])===false,'Partial coil silently marked consumed');
    verify($m->db->tables===$before,'Bad packing weight changed data');
};
class StockOldPrecisionDatabase extends StockTestDatabase {
    public function query($sql,$args=[]) {
        if(strpos($sql,'information_schema.COLUMNS')!==false)return new StockTestResult([['numeric_scale'=>2]]);
        return parent::query($sql,$args);
    }
}
$flow_tests['two decimal source schema blocks posting before any mutation'] = function () {
    $m=scan_fixture(new StockOldPrecisionDatabase());$before=$m->db->tables;
    $result=$m->confirm_scan_pack_to_prt('PACK-1','SPK-1',7);
    verify($result['status']===false && strpos($result['message'],'precision')!==false,'Old precision accepted');
    verify($m->db->tables===$before,'Precision guard left partial transfer');
};
$flow_tests['backdate document preserves posting-day snapshot'] = function () {
    $db=new StockTestDatabase();$c=coil(1,3,'PRT');$db->insert('warehouse_stock',stock(1,3,'PRT'));
    $wh=material_stock_warehouse($db,'PRT');$audit=[];
    material_stock_post($db,$c,$wh,1,'OUT',['kode_trans'=>'BACKDATE','tanggal'=>'2000-01-01','user'=>7,
        'source'=>$wh,'destination'=>$wh,'to_status'=>'consumed','description'=>'Backdated document'],$audit);
    verify($db->rows('warehouse_stock_transaction_summary')[0]['tanggal']==='2000-01-01','Document date lost');
    verify(substr($db->rows('warehouse_stock_per_day')[0]['hist_date'],0,10)===date('Y-m-d'),'Current balance applied to old snapshot');
};
$flow_tests['full warehouse withdrawal transfers remaining rounding cents exactly'] = function () {
    $m=scan_fixture();$m->db->where('id',1)->update('warehouse_stock',['qty_stock'=>20,'qty_free'=>20,'total_nilai'=>400.01]);
    $result=$m->confirm_scan_pack_to_prt('PACK-1','SPK-1',7);verify($result['status']===true,$result['message']);
    $stocks=$m->db->rows('warehouse_stock');
    verify($stocks[0]['qty_stock']==0 && $stocks[0]['total_nilai']==0,'Orphan value left at zero qty');
    verify($stocks[1]['total_nilai']==1200.01,'Transfer lost or created rounding cents');
};
$flow_tests['daily stock value counts last physical position and breaks snapshot ties'] = function () {
    $m=(new ReflectionClass(Warehouse_model::class))->newInstanceWithoutConstructor();
    $m->db=new StockTestDatabase();
    $m->db->insert('warehouse_stock',stock(1,3,'PRT'));
    foreach ([100,200] as $value) $m->db->insert('warehouse_stock_per_day',[
        'id_material'=>'MAT-A','nm_material'=>'Material A','id_gudang'=>3,'kd_gudang'=>'PRT',
        'qty_stock'=>$value/20,'harga_beli'=>20,'total_nilai'=>$value,'hist_date'=>'2026-11-20 10:00:00',
    ]);
    foreach (['2026-11-18','2026-11-19','2026-11-20'] as $day) {
        foreach (['COIL-1','COIL-2'] as $coil) $m->db->insert('warehouse_coil_per_day',[
            'id_material'=>'MAT-A','nm_material'=>'Material A','id_gudang'=>3,'kd_gudang'=>'PRT',
            'no_coil'=>$coil,'status'=>$day==='2026-11-20' && $coil==='COIL-1' ? 'OUT':'IN','hist_date'=>$day.' 10:00:00',
        ]);
    }
    $_POST=['date_snap'=>'2026-11-20','id_gudang'=>3]; $_REQUEST=['draw'=>1,'start'=>0,'length'=>25];
    ob_start();$m->get_json_stock_value_perday('PRT');$data=json_decode(ob_get_clean(),true);
    verify(count($data['data'])===1,'Snapshot tie duplicated material');
    verify(strip_tags($data['data'][0][5])==='1','Historical IN snapshots counted as repeated coils');
    verify($m->get_grand_total_stock_value_perday('PRT',3,'','2026-11-20')==200,'Daily total double-counted tied snapshots');
    $_POST=[];$_REQUEST=[];
};
$flow_tests['global scrap preserves proportional flow with weighted source warehouse rates'] = function () {
    $fixture=model([coil(1,3,'PRT'),coil(2,4,'WIP')],[stock(1,3,'PRT',100,20),stock(2,4,'WIP',100,40)]);
    $m=new StockFlowReportModel();$m->db=$fixture->db;$m->load=$fixture->load;$GLOBALS['stock_test_ci']=$m;
    $id=$m->save_production_report(['status_draft'=>0,'id_tr_spk_detail'=>null],
        [material(1),material(2)],[],[['scrap_type'=>'wrapping','berat_total'=>20]],[],null,7);
    verify($id===1,'Global scrap flow rejected: ' . $m->get_stock_out_error());
    $fg=$m->db->rows('fg_stock');
    verify(count($fg)===1 && $fg[0]['total_berat']==20 && $fg[0]['total_nilai']==240,'Global scrap used one warehouse price');
    $scrap=$m->db->rows('production_report_scraps');
    verify($scrap[0]['costbook_rate']==30,'Global proportional rate not weighted');
};
$flow_tests['failed journal after full final posting rolls back every stock table'] = function () {
    $fixture=model([coil(1,3,'PRT')],[stock(1,3,'PRT')]);
    $m=new StockFlowReportModel();$m->db=$fixture->db;$m->load=$fixture->load;$GLOBALS['stock_test_ci']=$m;
    $before=$m->db->tables;
    $items=[['source_material_coil'=>'COIL-1','product_lvl_4_id'=>1,'qty'=>1,'berat_per_pcs'=>9,'berat_total'=>9,'category_type'=>'kw_1']];
    $id=$m->save_production_report(['status_draft'=>0,'id_tr_spk_detail'=>null],[material(1)],$items,
        [['scrap_type'=>'sisa_coil','target_coil_code'=>'COIL-1','berat_total'=>1]],[],null,7,
        function () { throw new RuntimeException('Injected final journal failure'); });
    verify($id===false && strpos($m->get_stock_out_error(),'Injected')!==false,'Final callback failure ignored');
    verify($m->db->tables===$before,'Final callback left material/FG/WIP/card or report mutations');
};
$flow_tests['report source ID and displayed coil code cannot refer to different coils'] = function () {
    $m=model([coil(1,3,'PRT')],[stock(1,3,'PRT')]);$before=$m->db->tables;
    $row=material(1);$row['coil_code']='OTHER-COIL';
    $id=$m->save_production_report(['status_draft'=>1],[$row],[],[],[],null,7);
    verify($id===false && strpos($m->get_stock_out_error(),'do not match')!==false,'Mismatched identity accepted');
    verify($m->db->tables===$before,'Mismatched identity changed data');
};
foreach ($flow_tests as $name=>$test) { $test(); echo 'PASS ' . $name . PHP_EOL; }
echo count($flow_tests) . ' additional SQLite memory flow scenarios passed; no MySQL/configuration loaded.' . PHP_EOL;
