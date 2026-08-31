<?php
require_once __DIR__.'/../config/config.php'; require_login();
if(!can('can_take_orders')){http_response_code(403);exit('Order-taking permission denied.');}
if($_SERVER['REQUEST_METHOD']==='POST'){
  verify_csrf();
  $tableId=(int)($_POST['table_id']??0);
  $items=json_decode((string)($_POST['items_json']??'[]'),true);
  $notes=substr(trim((string)($_POST['notes']??'')),0,500);
  $ts=db()->prepare("SELECT * FROM restaurant_tables WHERE id=? AND active=1 LIMIT 1");$ts->execute([$tableId]);$table=$ts->fetch();
  if(!$table || !is_array($items) || !$items){flash('Please select a table and at least one item.');redirect('admin.php?page=take_order');}
  try{
    db()->beginTransaction();
    $o=db()->prepare("INSERT INTO orders(table_id,status,notes) VALUES(?, 'new', ?)");$o->execute([$tableId,$notes]);$oid=(int)db()->lastInsertId();
    $q=db()->prepare("SELECT id,name,price FROM menu_items WHERE id=? AND available=1 LIMIT 1");
    $ins=db()->prepare("INSERT INTO order_items(order_id,menu_item_id,item_name,unit_price,qty,notes) VALUES(?,?,?,?,?,?)");
    $total=0;$valid=0;
    foreach($items as $x){
      $q->execute([(int)($x['id']??0)]);$it=$q->fetch(); if(!$it) continue;
      $qty=max(1,min(99,(int)($x['qty']??0))); if(!$qty) continue;
      $note=substr((string)($x['note']??''),0,300);
      $ins->execute([$oid,$it['id'],$it['name'],$it['price'],$qty,$note]);
      $total += (float)$it['price']*$qty; $valid++;
    }
    if(!$valid) throw new RuntimeException('No valid menu items selected.');
    db()->prepare("UPDATE orders SET total=? WHERE id=?")->execute([$total,$oid]);
    db()->prepare("INSERT INTO audit_logs(user_id,action,entity_type,entity_id,details) VALUES(?,?,?,?,?)")->execute([user()['id'],'staff_create_order','order',(string)$oid,'Table '.$table['table_no']]);
    db()->commit();
    flash('Order #'.$oid.' sent to kitchen for Table '.$table['table_no'].'.');
  }catch(Throwable $e){if(db()->inTransaction())db()->rollBack();flash('Order could not be created. Please try again.');}
  redirect('admin.php?page=take_order');
}
$tables=db()->query("SELECT id,table_no FROM restaurant_tables WHERE active=1 ORDER BY table_no+0,table_no")->fetchAll();
$cats=db()->query("SELECT * FROM categories WHERE active=1 ORDER BY sort_order,name")->fetchAll();
$items=db()->query("SELECT id,category_id,name,description,price FROM menu_items WHERE available=1 ORDER BY category_id,sort_order,name")->fetchAll();
?><h1 class="h1">Take Order</h1><p class="muted">For waiter / order staff on a mobile or tablet. Select the customer's table, add items and send the order directly to the kitchen.</p>
<div class="card"><label>Table</label><select id="tableSelect"><option value="">Select table</option><?php foreach($tables as $t): ?><option value="<?=$t['id']?>">Table <?=e($t['table_no'])?></option><?php endforeach;?></select></div>
<div class="catbar staff-cats"><button class="on" onclick="filterCat(0,this)">All</button><?php foreach($cats as $c): ?><button onclick="filterCat(<?=$c['id']?>,this)"><?=e($c['name'])?></button><?php endforeach;?></div>
<div id="items" class="staff-items"><?php foreach($items as $i): ?><div class="mi staff-mi" data-cat="<?=$i['category_id']?>"><div class="mig"><b><?=e($i['name'])?></b><div class="muted" style="font-size:13px"><?=e($i['description']??'')?></div><strong><?=e(currency_symbol())?><?=number_format((float)$i['price'],2)?></strong></div><div class="qty"><button type="button" onclick="chg(<?=$i['id']?>,-1)">−</button><b id="q<?=$i['id']?>">0</b><button type="button" onclick="chg(<?=$i['id']?>,1)">+</button></div></div><?php endforeach;?></div>
<div class="card staff-cart"><h2>Current Order</h2><div id="cartList"><div class="muted">No items selected.</div></div><label>Special instructions (optional)</label><textarea id="notes" rows="2" placeholder="e.g. No onion, less spicy"></textarea><div style="display:flex;justify-content:space-between;margin:14px 0;font-size:20px"><b>Total</b><b><?=e(currency_symbol())?><span id="total">0.00</span></b></div><button class="btn gold" style="width:100%;font-size:16px" onclick="sendOrder()">SEND ORDER TO KITCHEN</button></div>
<form id="orderForm" method="post" style="display:none"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="table_id" id="formTable"><input type="hidden" name="items_json" id="formItems"><input type="hidden" name="notes" id="formNotes"></form>
<script>const currency=<?=json_encode(currency_symbol())?>;const items=<?=json_encode($items)?>;const cart={};function chg(id,d){cart[id]=(cart[id]||0)+d;if(cart[id]<0)cart[id]=0;const q=document.getElementById('q'+id);if(q)q.textContent=cart[id];renderCart();}function renderCart(){let h='',t=0,n=0;for(const i of items){const q=cart[i.id]||0;if(q){h+=`<div class="card" style="margin:7px 0;padding:10px;display:flex;justify-content:space-between"><span>${esc(i.name)} × ${q}</span><b>${esc(currency)}${(q*i.price).toFixed(2)}</b></div>`;t+=q*i.price;n+=q;}}document.getElementById('cartList').innerHTML=h||'<div class="muted">No items selected.</div>';document.getElementById('total').textContent=t.toFixed(2);}function filterCat(id,btn){document.querySelectorAll('.staff-cats button').forEach(x=>x.classList.remove('on'));btn.classList.add('on');document.querySelectorAll('.staff-mi').forEach(x=>x.style.display=(!id||x.dataset.cat==id)?'flex':'none');}function sendOrder(){const table=document.getElementById('tableSelect').value;if(!table)return alert('Please select a table.');const selected=Object.entries(cart).filter(([id,q])=>q>0).map(([id,q])=>({id:+id,qty:q}));if(!selected.length)return alert('Please select at least one item.');document.getElementById('formTable').value=table;document.getElementById('formItems').value=JSON.stringify(selected);document.getElementById('formNotes').value=document.getElementById('notes').value;document.getElementById('orderForm').submit();}function esc(x){return String(x??'').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;"}[m]))}</script>
