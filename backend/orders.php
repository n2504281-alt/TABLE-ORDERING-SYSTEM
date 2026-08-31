<?php
if(!can('can_view_orders') && !is_admin()) echo '<div class="card">You do not have permission to view orders.</div>';
else {
$orders=db()->query("SELECT o.*,t.table_no,u.name paid_by FROM orders o JOIN restaurant_tables t ON t.id=o.table_id LEFT JOIN users u ON u.id=o.paid_by_user_id ORDER BY o.id DESC LIMIT 100")->fetchAll();
?>
<h1 class="h1">Orders</h1><p class="muted">All QR orders. Payment is completed by authorized staff at the customer's table.</p>
<div class="card"><table class="table"><tr><th>Order</th><th>Table</th><th>Total</th><th>Status</th><th>Payment</th><th>Action</th></tr>
<?php foreach($orders as $o): ?><tr><td>#<?=$o['id']?></td><td><?=e($o['table_no'])?></td><td><?=e(currency_symbol())?><?=number_format($o['total'],2)?></td><td><span class="pill"><?=$o['status']?></span></td><td><?=e($o['payment_status'])?><?= $o['paid_by']?' · '.e($o['paid_by']):''?></td><td><a class="btn small" href="?page=kitchen&order=<?=$o['id']?>">Open</a><?php if($o['payment_status']==='unpaid' && can('can_mark_paid')): ?> <a class="btn small gold" href="pay.php?id=<?=$o['id']?>">Payment</a><?php endif; ?></td></tr><?php endforeach;?></table></div>
<?php } ?>