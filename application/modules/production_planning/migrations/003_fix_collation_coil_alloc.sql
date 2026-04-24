-- Fix collation mismatch: no_coil di tr_production_plan_coil_alloc
-- harus sama dengan warehouse_stock_coil (utf8mb4_0900_ai_ci)
ALTER TABLE `tr_production_plan_coil_alloc`
    MODIFY COLUMN `no_coil` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL;
