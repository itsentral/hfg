-- READ ONLY. Run before/after migration 017; no balance repair, DELETE, or UPDATE here.
-- Results are candidates for review, not instructions to overwrite historical balances.
START TRANSACTION READ ONLY;

-- 1. Master identities that do not agree. Includes missing PRT/WIP/HLD masters.
SELECT 'warehouse_stock' AS source_table, s.id, s.code_lv4 AS id_material,
       s.id_gudang, s.kd_gudang, w.kd_gudang AS master_kd_gudang
FROM warehouse_stock s LEFT JOIN warehouse w ON w.id=s.id_gudang
WHERE w.id IS NULL OR w.kd_gudang<>s.kd_gudang;
SELECT 'warehouse_stock_coil' AS source_table, c.id, c.id_material,
       c.id_gudang, c.kd_gudang, w.kd_gudang AS master_kd_gudang
FROM warehouse_stock_coil c LEFT JOIN warehouse w ON w.id=c.id_gudang
WHERE w.id IS NULL OR w.kd_gudang<>c.kd_gudang;

-- 2. Duplicate balance identities and negative balances.
SELECT code_lv4,id_gudang,kd_gudang,COUNT(*) AS row_count
FROM warehouse_stock GROUP BY code_lv4,id_gudang,kd_gudang HAVING COUNT(*)>1;
SELECT id,code_lv4,id_gudang,kd_gudang,qty_stock,total_nilai,qty_free
FROM warehouse_stock WHERE qty_stock<0 OR total_nilai<0 OR qty_free<0;

-- 3. Scan detail exists without a matching stock summary on that side.
-- This is intentionally a candidate query for legacy SPKs: historical links were absent.
SELECT d.kode_trans,d.id_material,d.id_gudang,d.kd_gudang,d.status_qc,
       COUNT(*) AS coil_count,SUM(d.net_weight) AS qty_scan
FROM warehouse_stock_transaction_detail d
WHERE d.to_status='in_transit' AND d.status_qc IN ('IN','OUT')
AND NOT EXISTS (
    SELECT 1 FROM warehouse_stock_transaction_summary s
    WHERE s.kode_trans=d.kode_trans AND s.id_material=d.id_material AND s.id_gudang=d.id_gudang
      AND ((d.status_qc='OUT' AND s.qty_akhir<s.qty_awal) OR (d.status_qc='IN' AND s.qty_akhir>s.qty_awal))
)
GROUP BY d.kode_trans,d.id_material,d.id_gudang,d.kd_gudang,d.status_qc;

-- 4. Historical production charged to PRO while the referenced coil is at PRT/WIP/Hold.
-- no_coil alone can be ambiguous: inspect the report and its actual physical ID before repair.
SELECT d.id AS detail_id,d.kode_trans,d.id_material,d.no_coil,d.kd_gudang AS charged_warehouse,
       c.id AS candidate_coil_id,c.kd_gudang AS coil_warehouse,d.net_weight,d.price_per_coil
FROM warehouse_stock_transaction_detail d
JOIN warehouse_stock_coil c ON c.no_coil=d.no_coil AND c.id_material=d.id_material
WHERE d.to_status='consumed' AND d.kd_gudang='PRO' AND c.kd_gudang IN ('PRT','WIP','HLD');

-- 5. Active physical packing quantity vs book quantity. Actual weight is a separate measure.
SELECT p.id_material,p.id_gudang,p.kd_gudang,p.qty_packing,p.qty_actual,
       s.qty_stock,s.total_nilai,ROUND(p.qty_packing-COALESCE(s.qty_stock,0),3) AS qty_book_difference
FROM (
    SELECT c.id_material,c.id_gudang,c.kd_gudang,
      SUM(CASE WHEN c.is_baby_coil=1 AND c.kd_gudang='PRT'
          THEN COALESCE(b.net_weight_pl,c.net_weight) ELSE c.net_weight END) AS qty_packing,
      SUM(c.net_weight) AS qty_actual
    FROM warehouse_stock_coil c
    LEFT JOIN (SELECT id_coil_baru,MAX(net_weight_pl) AS net_weight_pl
               FROM tr_unpack_coil_baby WHERE is_delete=0 GROUP BY id_coil_baru) b ON b.id_coil_baru=c.id
    WHERE c.status=1 GROUP BY c.id_material,c.id_gudang,c.kd_gudang
) p LEFT JOIN warehouse_stock s ON s.code_lv4=p.id_material AND s.id_gudang=p.id_gudang AND s.kd_gudang=p.kd_gudang
WHERE s.id IS NULL OR ABS(p.qty_packing-s.qty_stock)>0.001;
SELECT id_coil_baru,COUNT(*) AS active_unpack_links
FROM tr_unpack_coil_baby WHERE is_delete=0 GROUP BY id_coil_baru HAVING COUNT(*)>1;

-- 6. Orphan book quantity (no active physical coil), and costbook outside expected cent rounding.
SELECT s.id,s.code_lv4,s.id_gudang,s.kd_gudang,s.qty_stock,s.total_nilai
FROM warehouse_stock s WHERE s.qty_stock<>0 AND NOT EXISTS (
 SELECT 1 FROM warehouse_stock_coil c WHERE c.status=1 AND c.id_material=s.code_lv4
 AND c.id_gudang=s.id_gudang AND c.kd_gudang=s.kd_gudang
);
SELECT id,code_lv4,id_gudang,kd_gudang,qty_stock,harga_beli,total_nilai
FROM warehouse_stock WHERE qty_stock>0
AND ABS(total_nilai-qty_stock*harga_beli)>ABS(qty_stock)*0.005+0.01;

-- 7. Snapshot older than the last book update, or duplicate same-day snapshots.
SELECT s.id,s.code_lv4,s.id_gudang,s.update_date,MAX(p.hist_date) AS latest_snapshot
FROM warehouse_stock s LEFT JOIN warehouse_stock_per_day p
ON p.id_material=s.code_lv4 AND p.id_gudang=s.id_gudang
GROUP BY s.id,s.code_lv4,s.id_gudang,s.update_date
HAVING latest_snapshot IS NULL OR latest_snapshot<s.update_date;
SELECT id_material,id_gudang,DATE(hist_date) AS snapshot_day,COUNT(*) AS row_count
FROM warehouse_stock_per_day GROUP BY id_material,id_gudang,DATE(hist_date) HAVING COUNT(*)>1;

ROLLBACK;
