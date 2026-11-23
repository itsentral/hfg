<?php
defined('BASEPATH') || exit('No direct script access allowed');

/** Material ledger. All writes must share the caller's transaction. */
function material_stock_require_schema($db)
{
    foreach (['warehouse_stock_transaction_summary' => 'production_report_id',
        'warehouse_stock_transaction_detail' => 'id_summary', 'warehouse_history' => 'id_summary',
        'fg_stock_ledger' => 'production_report_id'] as $table => $field) {
        if (!$db->field_exists($field, $table)) {
            throw new RuntimeException('Stock migration 017 has not been applied. Contact the administrator before recording stock movements.');
        }
    }
    if (!$db->field_exists('id_coil', 'warehouse_stock_transaction_detail')) {
        throw new RuntimeException('Stock migration 017 (coil ID) has not been applied.');
    }
    $scale = $db->query("SELECT MIN(NUMERIC_SCALE) AS numeric_scale FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN ('warehouse_stock','warehouse_stock_per_day')
          AND COLUMN_NAME = 'qty_stock'")->row_array();
    if ((int)($scale['numeric_scale'] ?? 0) < 3) {
        throw new RuntimeException('Stock quantity precision is less than three decimal places. Apply migration 017 to avoid rounding coil weights.');
    }
}

function material_stock_warehouse($db, $code, $lock = true)
{
    $rows = $db->query(
        "SELECT * FROM warehouse WHERE kd_gudang = ? AND status = 'Y'" . ($lock ? ' FOR UPDATE' : ''),
        [$code]
    )->result_array();
    if (count($rows) !== 1) {
        throw new RuntimeException('Warehouse master ' . $code . ' is missing or duplicated. Apply the warehouse master migration and reconcile stock.');
    }
    return $rows[0];
}

function material_stock_row($db, $material, array $warehouse, $lock = true)
{
    $rows = $db->query(
        'SELECT * FROM warehouse_stock WHERE code_lv4 = ? AND (id_gudang = ? OR kd_gudang = ?) ORDER BY id' . ($lock ? ' FOR UPDATE' : ''),
        [$material, $warehouse['id'], $warehouse['kd_gudang']]
    )->result_array();
    if (count($rows) > 1 || ($rows && ((string)$rows[0]['id_gudang'] !== (string)$warehouse['id'] || $rows[0]['kd_gudang'] !== $warehouse['kd_gudang']))) {
        throw new RuntimeException('Material stock identity/balance ' . $material . ' in warehouse ' . $warehouse['kd_gudang'] . ' is duplicated or inconsistent. Stock reconciliation is required.');
    }
    return $rows ? $rows[0] : null;
}

function material_stock_costbook($db, array $coil, $lock = false)
{
    $wh = material_stock_warehouse($db, $coil['kd_gudang'], $lock);
    if ((string)$wh['id'] !== (string)$coil['id_gudang']) {
        throw new RuntimeException('Warehouse identity for coil ' . $coil['no_coil'] . ' does not match the warehouse master.');
    }
    $stock = material_stock_row($db, $coil['id_material'], $wh, $lock);
    if (!$stock || $stock['harga_beli'] === null || (float)$stock['harga_beli'] < 0) {
        throw new RuntimeException('Material costbook ' . $coil['id_material'] . ' in warehouse ' . $coil['kd_gudang'] . ' is missing or invalid.');
    }
    return (float)$stock['harga_beli'];
}

function material_stock_book_weight($db, array $coil)
{
    // Saldo buku berasal dari packing list; berat aktual tetap disimpan terpisah.
    if (!empty($coil['is_baby_coil']) && $coil['kd_gudang'] === 'PRT') {
        $rows = $db->query(
            'SELECT net_weight_pl FROM tr_unpack_coil_baby WHERE id_coil_baru = ? AND is_delete = 0',
            [$coil['id']]
        )->result_array();
        if (count($rows) !== 1 || (float)$rows[0]['net_weight_pl'] <= 0) {
            throw new RuntimeException('Packing List weight for baby coil ' . $coil['no_coil'] . ' is missing or ambiguous.');
        }
        return round((float)$rows[0]['net_weight_pl'], 3);
    }
    return round((float)$coil['net_weight'], 3);
}

function material_stock_write($result)
{
    if ($result === false) {
        throw new RuntimeException('Failed to record stock. The entire transaction has been rolled back.');
    }
}

function material_stock_snapshot($db, array $stock, $user, $now)
{
    // Snapshot adalah posisi saat posting, bukan saldo hari ini ditempel ke tanggal dokumen lama.
    $snap = $db->query(
        'SELECT id FROM warehouse_stock_per_day WHERE id_material = ? AND id_gudang = ? AND DATE(hist_date) = ? LIMIT 1 FOR UPDATE',
        [$stock['code_lv4'], $stock['id_gudang'], substr($now, 0, 10)]
    )->row_array();
    $data = ['id_material' => $stock['code_lv4'], 'nm_material' => $stock['nm_material'],
        'id_gudang' => $stock['id_gudang'], 'kd_gudang' => $stock['kd_gudang'],
        'qty_stock' => $stock['qty_stock'], 'qty_booking' => $stock['qty_booking'],
        'qty_free' => $stock['qty_free'], 'harga_beli' => $stock['harga_beli'],
        'total_nilai' => $stock['total_nilai'], 'hist_date' => $now, 'hist_by' => $user];
    material_stock_write($snap
        ? $db->where('id', $snap['id'])->update('warehouse_stock_per_day', $data)
        : $db->insert('warehouse_stock_per_day', $data));
}

function material_stock_coil_snapshot($db, array $coil, $direction, $user, $now)
{
    $snap = $db->query(
        'SELECT id FROM warehouse_coil_per_day WHERE id_material = ? AND id_gudang = ? AND no_coil = ? AND DATE(hist_date) = ? LIMIT 1 FOR UPDATE',
        [$coil['id_material'], $coil['id_gudang'], $coil['no_coil'], substr($now, 0, 10)]
    )->row_array();
    $data = array_intersect_key($coil, array_flip(['id_material', 'nm_material', 'id_gudang', 'kd_gudang',
        'no_coil', 'kode_internal', 'id_pack', 'parent_coil_id', 'is_baby_coil', 'gross_weight',
        'net_weight', 'length', 'qty_roll', 'harga_beli', 'total_nilai']));
    $data += ['status' => $direction, 'hist_date' => $now, 'hist_by' => $user];
    material_stock_write($snap
        ? $db->where('id', $snap['id'])->update('warehouse_coil_per_day', $data)
        : $db->insert('warehouse_coil_per_day', $data));
}

/** One material/coil movement, with traceable summary -> detail/history. */
function material_stock_post($db, array $coil, array $warehouse, $qty, $direction, array $context, array &$changes)
{
    material_stock_require_schema($db);
    $qty = round((float)$qty, 3);
    if ($qty <= 0 || !in_array($direction, ['IN', 'OUT'], true)) {
        throw new RuntimeException('The material movement quantity must be greater than zero.');
    }
    $stock = material_stock_row($db, $coil['id_material'], $warehouse);
    $old = $stock ?: [];
    if (!$stock && $direction === 'OUT') {
        throw new RuntimeException('Source balance ' . $coil['id_material'] . ' / ' . $warehouse['kd_gudang'] . ' is not available.');
    }
    if ($stock) {
        foreach (['qty_stock','total_nilai','harga_beli'] as $field) {
            if (!is_numeric($stock[$field])) {
                throw new RuntimeException('Material balance/costbook ' . $coil['id_material'] . ' / ' . $warehouse['kd_gudang'] . ' is incomplete. Stock reconciliation is required.');
            }
        }
        if ((float)$stock['qty_stock'] < 0 || (float)$stock['total_nilai'] < 0) {
            throw new RuntimeException('The opening material balance is negative. Reconcile stock before recording a movement.');
        }
    }
    $qty_before = $stock ? (float)$stock['qty_stock'] : 0.0;
    $value_before = $stock ? (float)$stock['total_nilai'] : 0.0;
    $rate_before = $stock ? (float)$stock['harga_beli'] : 0.0;
    $rate = $direction === 'OUT' ? $rate_before : (float)$context['rate'];
    $value = isset($context['value']) ? round((float)$context['value'], 2) : round($qty * $rate, 2);
    $sign = $direction === 'IN' ? 1 : -1;
    $qty_after = round($qty_before + $sign * $qty, 3);
    // Remove the entire remaining book value on a full withdrawal, including rounding cents.
    if ($direction === 'OUT' && $qty_after == 0 && !isset($context['value'])) {
        $value = $value_before;
    }
    $value_after = round($value_before + $sign * $value, 2);
    if ($qty_after < 0 || $value_after < 0 || $value < 0 || $rate < 0) {
        throw new RuntimeException('Material quantity/value balance ' . $coil['id_material'] . ' in ' . $warehouse['kd_gudang'] . ' is insufficient. Stock reconciliation is required.');
    }
    $now = $context['now'] ?? date('Y-m-d H:i:s');
    $user = $context['user'];
    $rate_after = $direction === 'IN' && $qty_after > 0 ? round($value_after / $qty_after, 2) : $rate_before;
    $data = ['qty_stock' => $qty_after, 'total_nilai' => $value_after, 'harga_beli' => $rate_after,
        'qty_free' => round(($stock ? (float)$stock['qty_free'] : 0) + $sign * $qty, 3),
        'incoming' => ($stock ? (float)$stock['incoming'] : 0) + ($direction === 'IN' ? $qty : 0),
        'outgoing' => ($stock ? (float)$stock['outgoing'] : 0) + ($direction === 'OUT' ? $qty : 0),
        'update_by' => $user, 'update_date' => $now];
    if ($stock) {
        material_stock_write($db->where('id', $stock['id'])->update('warehouse_stock', $data));
        $after = array_merge($stock, $data);
    } else {
        $data += array_intersect_key($context['material_meta'] ?? [], array_flip([
            'code_lv1','code_lv2','code_lv3','id_unit','id_unit_packing',
        ]));
        $data += ['code_lv4' => $coil['id_material'], 'nm_material' => $coil['nm_material'],
            'trade_name' => $coil['trade_name'] ?? null, 'id_gudang' => $warehouse['id'],
            'kd_gudang' => $warehouse['kd_gudang'], 'code_incoming' => $context['kode_trans'],
            'begining' => 0, 'qty_booking' => 0, 'use_qty_free' => 0];
        material_stock_write($db->insert('warehouse_stock', $data));
        $after = $data + ['id' => $db->insert_id()];
    }
    foreach (['qty_stock', 'total_nilai', 'harga_beli', 'incoming', 'outgoing', 'qty_free'] as $field) {
        $key = 'Stock ' . $coil['id_material'] . ' / ' . $warehouse['kd_gudang'] . ' - ' . $field;
        $changes[$key] = ['old' => $changes[$key]['old'] ?? ($old[$field] ?? 0), 'new' => $after[$field]];
    }
    material_stock_snapshot($db, $after, $user, $now);
    $summary = ['kode_trans' => $context['kode_trans'], 'id_material' => $coil['id_material'],
        'nm_material' => $coil['nm_material'], 'id_gudang' => $warehouse['id'], 'kd_gudang' => $warehouse['kd_gudang'],
        'tanggal' => $context['tanggal'] ?? substr($now, 0, 10), 'jumlah_coil' => count($context['details'] ?? [$coil]),
        'qty_awal' => $qty_before, 'qty_transaksi' => $qty, 'qty_akhir' => $qty_after,
        'costbook' => $rate_after, 'total_harga' => $value, 'saldo_awal' => $value_before,
        'saldo_akhir' => $value_after, 'harga_lama' => $rate_before, 'created_by' => $user, 'created_at' => $now,
        'id_pack' => $coil['id_pack'] ?? null, 'pack_code' => $context['pack_code'] ?? null,
        'production_report_id' => $context['production_report_id'] ?? null];
    material_stock_write($db->insert('warehouse_stock_transaction_summary', $summary));
    $summary_id = $db->insert_id();
    $details = $context['details'] ?? [array_merge($coil, ['net_weight_used' => $qty])];
    $remaining_value = $value;
    foreach (array_values($details) as $index => $detail) {
        $detail_qty = (float)$detail['net_weight_used'];
        $detail_value = $index === count($details) - 1 ? $remaining_value : round($value * $detail_qty / $qty, 2);
        $remaining_value = round($remaining_value - $detail_value, 2);
        material_stock_write($db->insert('warehouse_stock_transaction_detail', [
            'id_summary' => $summary_id, 'id_coil' => $detail['id'] ?? null, 'kode_trans' => $context['kode_trans'],
            'id_material' => $coil['id_material'], 'nm_material' => $coil['nm_material'],
            'id_gudang' => $warehouse['id'], 'kd_gudang' => $warehouse['kd_gudang'],
            'id_pack' => $coil['id_pack'] ?? null, 'pack_code' => $context['pack_code'] ?? null,
            'no_coil' => $detail['no_coil'], 'parent_coil_id' => $detail['parent_coil_id'] ?? null,
            'kode_internal' => $detail['kode_internal'] ?? '', 'gross_weight' => $detail['gross_weight'] ?? 0,
            'net_weight' => $detail_qty, 'length' => $detail['length'] ?? 0, 'price_per_coil' => $detail_value,
            'cost_book' => $rate, 'status_qc' => $direction, 'to_status' => $context['to_status'], 'created_at' => $now,
        ]));
    }
    material_stock_write($db->insert('warehouse_history', [
        'id_summary' => $summary_id, 'id_material' => $coil['id_material'], 'nm_material' => $coil['nm_material'],
        'no_coil' => $coil['no_coil'], 'id_gudang' => $warehouse['id'], 'kd_gudang' => $warehouse['kd_gudang'],
        'id_gudang_dari' => $context['source']['id'], 'kd_gudang_dari' => $context['source']['kd_gudang'],
        'id_gudang_ke' => $context['destination']['id'], 'kd_gudang_ke' => $context['destination']['kd_gudang'],
        'qty_stock_awal' => $qty_before, 'qty_stock_akhir' => $qty_after, 'no_ipp' => $context['kode_trans'],
        'jumlah_mat' => $qty, 'ket' => $context['description'], 'harga_beli' => $rate,
        'total_harga' => $value, 'saldo_awal' => $value_before, 'saldo_akhir' => $value_after,
        'harga_baru' => $rate_after, 'harga_lama' => $rate_before, 'update_by' => $user, 'update_date' => $now,
    ]));
    return ['old' => $old, 'new' => $after, 'value' => $value, 'rate' => $rate, 'id_summary' => $summary_id];
}
