<?php
require_once __DIR__.'/../config/config.php'; require_login();
$u=user(); $page=$_GET['page']??'dashboard'; $msg=flash();
if($page==='logout'){session_destroy();redirect('login.php');}
if(!in_array($page,['dashboard','orders','kitchen','take_order','tables','menu','staff','reports','settings'],true))$page='dashboard';
?>
<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="../assets/css/style.css"><title><?=e(restaurant_name())?> Admin</title></head><body>
<header class="top"><div class="brand brand-with-logo"><?=logo_html("brand-logo")?><span><?=e(restaurant_name())?></span></div><div class="user"><?=e($u['name'])?> · <?=e(ucfirst($u['role']))?> &nbsp; <a href="admin.php?page=logout">Logout</a></div></header>
<div class="layout"><aside class="side">
<?php if(is_admin() || can('can_view_dashboard')): ?><a class="nav <?=$page==='dashboard'?'active':''?>" href="admin.php">Dashboard</a><?php endif; ?>
<?php if(is_admin() || can('can_view_orders')): ?><a class="nav <?=$page==='orders'?'active':''?>" href="?page=orders">Orders</a><?php endif; ?>
<?php if(is_admin() || can('can_use_kitchen')): ?><a class="nav <?=$page==='kitchen'?'active':''?>" href="?page=kitchen">Kitchen</a><?php endif; ?>
<?php if(is_admin() || can('can_take_orders')): ?><a class="nav <?=$page==='take_order'?'active':''?>" href="?page=take_order">Take Order</a><?php endif; ?>
<?php if(is_admin() || can('can_manage_tables')): ?><a class="nav <?=$page==='tables'?'active':''?>" href="?page=tables">Tables & QR</a><?php endif; ?>
<?php if(is_admin() || can('can_manage_menu')): ?><a class="nav <?=$page==='menu'?'active':''?>" href="?page=menu">Menu</a><?php endif; ?>
<?php if(is_admin() || can('can_manage_staff')): ?><a class="nav <?=$page==='staff'?'active':''?>" href="?page=staff">Staff & Permissions</a><?php endif; ?>
<?php if(is_admin() || can('can_view_reports')): ?><a class="nav <?=$page==='reports'?'active':''?>" href="?page=reports">Reports</a><?php endif; ?>
<?php if(is_admin() || can('can_manage_settings')): ?><a class="nav <?=$page==='settings'?'active':''?>" href="?page=settings">Settings</a><?php endif; ?>
</aside><main class="main"><?php if($msg): ?><div class="pill green" style="margin-bottom:15px"><?=e($msg)?></div><?php endif; ?>
<?php
$guards=['dashboard'=>'can_view_dashboard','orders'=>'can_view_orders','kitchen'=>'can_use_kitchen','take_order'=>'can_take_orders','tables'=>'can_manage_tables','menu'=>'can_manage_menu','staff'=>'can_manage_staff','reports'=>'can_view_reports','settings'=>'can_manage_settings'];
if(!is_admin() && isset($guards[$page]) && !can($guards[$page])){http_response_code(403);echo '<div class="card"><h2>Access denied</h2><p>You do not have permission to open this section.</p></div>';} else { include __DIR__.'/'.$page.'.php'; }
?>
</main></div></body></html>