-- Run once on an existing installation created before granular staff permissions.
ALTER TABLE users
  ADD COLUMN can_manage_tables TINYINT(1) NOT NULL DEFAULT 0 AFTER can_take_orders,
  ADD COLUMN can_manage_menu TINYINT(1) NOT NULL DEFAULT 0 AFTER can_manage_tables,
  ADD COLUMN can_view_reports TINYINT(1) NOT NULL DEFAULT 0 AFTER can_manage_menu,
  ADD COLUMN can_manage_settings TINYINT(1) NOT NULL DEFAULT 0 AFTER can_view_reports,
  ADD COLUMN can_manage_staff TINYINT(1) NOT NULL DEFAULT 0 AFTER can_manage_settings,
  ADD COLUMN can_view_dashboard TINYINT(1) NOT NULL DEFAULT 0 AFTER can_manage_staff,
  ADD COLUMN can_use_kitchen TINYINT(1) NOT NULL DEFAULT 0 AFTER can_view_dashboard;
