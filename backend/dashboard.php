<?php
$today=db()->query("SELECT COUNT(*) c,COALESCE(SUM(total),0) total FROM orders WHERE DATE(created_at)=CURDATE() AND status<>'cancelled'")->fetch();
$pending=db()->query("SELECT COUNT(*) c FROM orders WHERE status IN ('new','accepted','preparing')")->fetchColumn();
$ready=db()->query("SELECT COUNT(*) c FROM orders WHERE status='ready'")->fetchColumn();
$tables=db()->query("SELECT COUNT(*) c FROM restaurant_tables WHERE active=1")->fetchColumn();
$recent=db()->query("SELECT o.*,t.table_no FROM orders o JOIN restaurant_tables t ON t.id=o.table_id ORDER BY o.id DESC LIMIT 8")->fetchAll();
?><h1 class="h1">Dashboard</h1><p class="muted">Restaurant overview</p>
<div class="cards"><div class="card">Today's Orders<div class="stat"><?=e((string)$today['c'])?></div></div><div class="card">Today's Sales<div class="stat"><?=e(currency_symbol())?><?=number_format((float)$today['total'],2)?></div></div><div class="card">Pending<div class="stat"><?=e((string)$pending)?></div></div><div class="card">Active Tables<div class="stat"><?=e((string)$tables)?></div></div></div>
<div class="card"><h2>Recent Orders</h2><table class="table"><tr><th>Order</th><th>Table</th><th>Total</th><th>Status</th><th>Payment</th></tr><?php foreach($recent as $o): ?><tr><td>#<?=$o['id']?></td><td><?=e($o['table_no'])?></td><td><?=e(currency_symbol())?><?=number_format($o['total'],2)?></td><td><span class="pill"><?=$o['status']?></span></td><td><?=$o['payment_status']?></td></tr><?php endforeach;?></table></div>