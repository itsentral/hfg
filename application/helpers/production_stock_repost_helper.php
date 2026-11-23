<?php
defined('BASEPATH') || exit('No direct script access allowed');

/** Rebuild only this report's postings, inside its save transaction. Never infer legacy links. */
function production_stock_unpost($db, $report_id, $user, array &$changes)
{
    material_stock_require_schema($db);
    $summaries = $db->query(
        'SELECT * FROM warehouse_stock_transaction_summary WHERE production_report_id = ? ORDER BY id DESC FOR UPDATE',
        [$report_id]
    )->result_array();
    if (!$summaries) {
        throw new RuntimeException('This legacy final report has no stock ledger links. Reconcile the report before editing it.');
    }
    $now = date('Y-m-d H:i:s');
    $source_coils = [];
    $output_coils = [];
    $summary_ids = array_column($summaries, 'id');
    foreach ($summaries as $summary) {
        $later = $db->query(
            'SELECT id FROM warehouse_stock_transaction_summary WHERE id_material = ? AND id_gudang = ?
             AND id > ? AND (production_report_id IS NULL OR production_report_id <> ?) LIMIT 1 FOR UPDATE',
            [$summary['id_material'], $summary['id_gudang'], max($summary_ids), $report_id]
        )->row_array();
        if ($later) {
            throw new RuntimeException('This report\'s stock has subsequent movements. A correction cannot overwrite balances from subsequent transactions.');
        }
        $details = $db->query('SELECT * FROM warehouse_stock_transaction_detail WHERE id_summary = ? ORDER BY id FOR UPDATE', [$summary['id']])->result_array();
        if (!$details) {
            throw new RuntimeException('Report posting details are incomplete. Stock reconciliation is required.');
        }
        foreach ($details as $detail) {
            if (empty($detail['id_coil'])) throw new RuntimeException('The report ledger is missing a coil ID.');
            $coil = $db->query('SELECT * FROM warehouse_stock_coil WHERE id = ? FOR UPDATE', [$detail['id_coil']])->row_array();
            if (!$coil || (string)$coil['id_gudang'] !== (string)$summary['id_gudang'] || $coil['kd_gudang'] !== $summary['kd_gudang']) {
                throw new RuntimeException('The report coil\'s location has changed. Reconcile stock before correcting the report.');
            }
            if ($detail['to_status'] === 'consumed') {
                if ((int)$coil['status'] !== 0 || $coil['status_proses'] !== 'consumed') {
                    throw new RuntimeException('The consumed coil status does not match the report posting.');
                }
                $source_coils[$coil['id']] = $coil;
            } elseif (in_array($detail['to_status'], ['wip', 'on_hold'], true)) {
                if ((int)$coil['status'] !== 1 || $coil['status_proses'] !== $detail['to_status']) {
                    throw new RuntimeException('The report\'s Remaining/Hold Coil has been used or moved. The correction has been cancelled.');
                }
                $child = $db->query('SELECT id FROM warehouse_stock_coil WHERE parent_coil_id = ? LIMIT 1 FOR UPDATE', [$coil['id']])->row_array();
                $claim = $db->query("SELECT id FROM claim_material WHERE id_baby_coil = ? AND status_claim NOT IN ('Claim Baru','Baru') LIMIT 1 FOR UPDATE", [$coil['id']])->row_array();
                if ($child || $claim) throw new RuntimeException('The report output has subsequent consumption or claim decisions.');
                $output_coils[$coil['id']] = $coil;
            } else {
                throw new RuntimeException('Unrecognized report posting type. No balance has been inferred.');
            }
        }
        $warehouse = material_stock_warehouse($db, $summary['kd_gudang']);
        $stock = material_stock_row($db, $summary['id_material'], $warehouse);
        if (!$stock) throw new RuntimeException('The report posting balance was not found.');
        if (round((float)$stock['qty_stock'], 3) !== round((float)$summary['qty_akhir'], 3)
            || round((float)$stock['total_nilai'], 2) !== round((float)$summary['saldo_akhir'], 2)) {
            throw new RuntimeException('The book balance differs from the report\'s latest posting. Reconcile other movements or adjustments first.');
        }
        $is_in = (float)$summary['qty_akhir'] > (float)$summary['qty_awal'];
        $sign = $is_in ? -1 : 1;
        $qty = round((float)$stock['qty_stock'] + $sign * (float)$summary['qty_transaksi'], 3);
        $value = round((float)$stock['total_nilai'] + $sign * (float)$summary['total_harga'], 2);
        if ($qty < 0 || $value < 0) throw new RuntimeException('The report output balance is insufficient for a correction.');
        $data = ['qty_stock' => $qty, 'total_nilai' => $value, 'harga_beli' => $summary['harga_lama'],
            'qty_free' => round((float)$stock['qty_free'] + $sign * (float)$summary['qty_transaksi'], 3),
            'incoming' => (float)$stock['incoming'] - ($is_in ? (float)$summary['qty_transaksi'] : 0),
            'outgoing' => (float)$stock['outgoing'] - (!$is_in ? (float)$summary['qty_transaksi'] : 0),
            'update_by' => $user, 'update_date' => $now];
        foreach ($data as $field => $v) {
            if (in_array($field, ['update_by', 'update_date'], true)) continue;
            $key = 'Stock ' . $summary['id_material'] . ' / ' . $warehouse['kd_gudang'] . ' - ' . $field;
            $changes[$key] = ['old' => $changes[$key]['old'] ?? $stock[$field], 'new' => $v];
        }
        material_stock_write($db->where('id', $stock['id'])->update('warehouse_stock', $data));
        material_stock_snapshot($db, array_merge($stock, $data), $user, $now);
        $changes['Posting lama ' . $summary['id']] = ['old' => ['summary' => $summary, 'details' => $details], 'new' => null];
    }

    $fg = $db->query('SELECT * FROM fg_stock_ledger WHERE production_report_id = ? ORDER BY id DESC FOR UPDATE', [$report_id])->result_array();
    foreach ($fg as $row) {
        $later = $db->query('SELECT id FROM fg_stock_ledger WHERE produk_fg = ? AND id > ? AND (production_report_id IS NULL OR production_report_id <> ?) LIMIT 1 FOR UPDATE',
            [$row['produk_fg'], max(array_column($fg, 'id')), $report_id])->row_array();
        if ($later) throw new RuntimeException('The report\'s FG/Scrap output has subsequent movements.');
        $stock = $db->query('SELECT * FROM fg_stock WHERE produk_fg = ? FOR UPDATE', [$row['produk_fg']])->row_array();
        if (!$stock) throw new RuntimeException('The report\'s FG/Scrap balance was not found.');
        if (round((float)$stock['qty_stok'], 3) !== round((float)$row['qty_saldo'], 3)
            || round((float)$stock['total_berat'], 3) !== round((float)$row['berat_saldo'], 3)
            || round((float)$stock['total_nilai'], 2) !== round((float)$row['saldo_nilai'], 2)) {
            throw new RuntimeException('The FG/Scrap balance differs from the report ledger. Reconcile stock before correcting the report.');
        }
        $qty = round((float)$stock['qty_stok'] - (float)$row['qty_in'], 3);
        $weight = round((float)$stock['total_berat'] - (float)$row['berat_in'], 3);
        $value = round((float)$stock['total_nilai'] - (float)$row['total_nilai'], 2);
        if ($qty < 0 || $weight < 0 || $value < 0) throw new RuntimeException('The FG/Scrap balance is insufficient to correct the report.');
        $denominator = in_array($row['kategori_stok'], ['SCRAP','TONG_COIL'], true) ? $weight : $qty;
        $data = ['qty_stok' => $qty, 'total_berat' => $weight, 'total_nilai' => $value,
            'costbook' => $denominator > 0 ? round($value / $denominator, 2) : 0,
            'berat_referensi' => $qty > 0 ? round($weight / $qty, 4) : 0, 'last_update' => $now];
        material_stock_write($db->where('produk_fg', $row['produk_fg'])->update('fg_stock', $data));
        $changes['Koreksi FG ' . $row['id']] = ['old' => ['ledger' => $row, 'stock' => $stock], 'new' => array_merge($stock, $data)];
    }
    $cards = $db->query('SELECT * FROM warehouse_coil_kartu_stok WHERE production_report_id = ? ORDER BY id FOR UPDATE', [$report_id])->result_array();
    foreach ($cards as $card) {
        $later = $db->query('SELECT id FROM warehouse_coil_kartu_stok WHERE id_coil = ? AND id > ? AND (production_report_id IS NULL OR production_report_id <> ?) LIMIT 1 FOR UPDATE',
            [$card['id_coil'], max(array_column($cards, 'id')), $report_id])->row_array();
        if ($later) throw new RuntimeException('The coil usage card has subsequent transactions.');
    }
    $changes['Kartu pemakaian laporan ' . $report_id] = ['old' => $cards, 'new' => []];
    foreach ($source_coils as $coil) {
        $stage = ['WIP' => 'wip', 'HLD' => 'in_warehouse'][$coil['kd_gudang']] ?? 'unpacked';
        material_stock_write($db->where('id', $coil['id'])->update('warehouse_stock_coil', ['status' => 1, 'status_proses' => $stage]));
        $changes['Pulihkan coil ' . $coil['id']] = ['old' => $coil, 'new' => array_merge($coil, ['status' => 1, 'status_proses' => $stage])];
        material_stock_coil_snapshot($db, array_merge($coil, ['status' => 1, 'status_proses' => $stage]), 'IN', $user, $now);
    }
    foreach ($output_coils as $coil) {
        $changes['Hasil coil lama ' . $coil['id']] = ['old' => $coil, 'new' => null];
        material_stock_coil_snapshot($db, $coil, 'OUT', $user, $now);
        material_stock_write($db->where('id', $coil['id'])->delete('warehouse_stock_coil'));
    }
    material_stock_write($db->where_in('id_summary', $summary_ids)->delete('warehouse_stock_transaction_detail'));
    material_stock_write($db->where_in('id_summary', $summary_ids)->delete('warehouse_history'));
    material_stock_write($db->where_in('id', $summary_ids)->delete('warehouse_stock_transaction_summary'));
    material_stock_write($db->where('production_report_id', $report_id)->delete('fg_stock_ledger'));
    material_stock_write($db->where('production_report_id', $report_id)->delete('warehouse_coil_kartu_stok'));
}
