-- HFG 20-11-2026. Review and execute manually; never run on a test's behalf.
-- Preserves legacy rows. New nullable links are not guessed/backfilled.
-- MySQL DDL auto-commits: take a backup and stop writers before applying.
DROP PROCEDURE IF EXISTS hfg_upgrade_stock_flow_017;
DELIMITER $$
CREATE PROCEDURE hfg_upgrade_stock_flow_017()
BEGIN
    IF EXISTS (SELECT 1 FROM warehouse WHERE
        (id = '3' AND COALESCE(kd_gudang,'') <> 'PRT') OR (id = '4' AND COALESCE(kd_gudang,'') <> 'WIP') OR
        (id = '5' AND COALESCE(kd_gudang,'') <> 'HLD') OR (kd_gudang = 'PRT' AND id <> '3') OR
        (kd_gudang = 'WIP' AND id <> '4') OR (kd_gudang = 'HLD' AND id <> '5')) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Warehouse IDs conflict: reconcile master before migration 017';
    END IF;
    IF EXISTS (SELECT 1 FROM warehouse WHERE id IN ('3','4','5') AND COALESCE(status,'N') <> 'Y') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Warehouse inactive: review master before migration 017';
    END IF;
    IF EXISTS (SELECT 1 FROM warehouse_stock WHERE GREATEST(ABS(COALESCE(begining,0)),ABS(COALESCE(incoming,0)),
        ABS(COALESCE(outgoing,0)),ABS(COALESCE(qty_stock,0)),ABS(COALESCE(qty_booking,0)),ABS(COALESCE(qty_free,0)),ABS(COALESCE(use_qty_free,0))) >= 100000000000000000)
    OR EXISTS (SELECT 1 FROM warehouse_stock_per_day WHERE GREATEST(ABS(COALESCE(qty_stock,0)),ABS(COALESCE(qty_booking,0)),
        ABS(COALESCE(qty_rusak,0)),ABS(COALESCE(qty_free,0))) >= 1000000000000000) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Quantity exceeds target decimal range; review precision before migration 017';
    END IF;
    IF EXISTS (SELECT kd_gudang FROM warehouse WHERE kd_gudang IN ('PRO','PRT','WIP','HLD') GROUP BY kd_gudang HAVING COUNT(*) > 1) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Duplicate warehouse codes: reconcile before migration 017';
    END IF;
    INSERT INTO warehouse (id,kd_gudang,nm_gudang,status,urut)
        SELECT '3','PRT','WRH Production 2','Y',3 WHERE NOT EXISTS (SELECT 1 FROM warehouse WHERE id='3');
    INSERT INTO warehouse (id,kd_gudang,nm_gudang,status,urut)
        SELECT '4','WIP','WIP','Y',4 WHERE NOT EXISTS (SELECT 1 FROM warehouse WHERE id='4');
    INSERT INTO warehouse (id,kd_gudang,nm_gudang,status,urut)
        SELECT '5','HLD','Material Hold','Y',5 WHERE NOT EXISTS (SELECT 1 FROM warehouse WHERE id='5');

    ALTER TABLE warehouse_stock
        MODIFY begining DECIMAL(20,3) DEFAULT 0,
        MODIFY incoming DECIMAL(20,3) DEFAULT 0,
        MODIFY outgoing DECIMAL(20,3) DEFAULT 0,
        MODIFY qty_stock DECIMAL(20,3) DEFAULT 0,
        MODIFY qty_booking DECIMAL(20,3) DEFAULT 0,
        MODIFY qty_free DECIMAL(20,3) DEFAULT 0,
        MODIFY use_qty_free DECIMAL(20,3) DEFAULT NULL;
    ALTER TABLE warehouse_stock_per_day
        MODIFY qty_stock DECIMAL(18,3) DEFAULT 0,
        MODIFY qty_booking DECIMAL(18,3) DEFAULT 0,
        MODIFY qty_rusak DECIMAL(18,3) DEFAULT 0,
        MODIFY qty_free DECIMAL(18,3) DEFAULT NULL;

    IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='warehouse_stock_transaction_summary' AND COLUMN_NAME='production_report_id') THEN
        ALTER TABLE warehouse_stock_transaction_summary ADD production_report_id INT NULL,
            ADD INDEX idx_production_report (production_report_id);
    END IF;
    IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='warehouse_stock_transaction_detail' AND COLUMN_NAME='id_summary') THEN
        ALTER TABLE warehouse_stock_transaction_detail ADD id_summary INT UNSIGNED NULL,
            ADD INDEX idx_summary (id_summary);
    END IF;
    IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='warehouse_history' AND COLUMN_NAME='id_summary') THEN
        ALTER TABLE warehouse_history ADD id_summary INT UNSIGNED NULL, ADD INDEX idx_summary (id_summary);
    END IF;
    IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='warehouse_stock_transaction_detail' AND COLUMN_NAME='id_coil') THEN
        ALTER TABLE warehouse_stock_transaction_detail ADD id_coil INT NULL, ADD INDEX idx_coil (id_coil);
    END IF;
    IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='fg_stock_ledger' AND COLUMN_NAME='production_report_id') THEN
        ALTER TABLE fg_stock_ledger ADD production_report_id INT NULL, ADD INDEX idx_production_report (production_report_id);
    END IF;
END$$
DELIMITER ;
CALL hfg_upgrade_stock_flow_017();
DROP PROCEDURE hfg_upgrade_stock_flow_017;
