ALTER TABLE users ADD COLUMN can_take_orders TINYINT(1) NOT NULL DEFAULT 0 AFTER can_complete;
