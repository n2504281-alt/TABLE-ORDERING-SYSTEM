<?php
require_once __DIR__.'/../config/config.php';
header('Content-Type: application/json; charset=utf-8');
$action=$_GET['action']??'';
try{
 if($action==='menu'){
   $token=trim($_GET['table']??'');
   $s=db()->prepare("SELECT * FROM restaurant_tables WHERE qr_token=? AND active=1");$s->execute([$token]);$t=$s->fetch();
   if(!$t) throw new Exception('Invalid table QR.');
   $cats=db()->query("SELECT * FROM categories WHERE active=1 ORDER BY sort_order,name")->fetchAll();
   $items=db()->query("SELECT id,category_id,name,description,price,image FROM menu_items WHERE available=1 ORDER BY category_id,sort_order,name")->fetchAll();
   echo json_encode(['ok'=>true,'table'=>$t['table_no'],'categories'=>$cats,'items'=>$items,'currency'=>currency_symbol(),'restaurant'=>['name'=>restaurant_name(),'tagline'=>restaurant_tagline(),'logo'=>logo_url()]]); exit;
 }
 if($action==='order' && $_SERVER['REQUEST_METHOD']==='POST'){
   $d=json_decode(file_get_contents('php://input'),true)??[];
   $s=db()->prepare("SELECT * FROM restaurant_tables WHERE qr_token=? AND active=1");$s->execute([$d['table']??'']);$t=$s->fetch();
   if(!$t) throw new Exception('Invalid table.');
   if(empty($d['items']) || !is_array($d['items'])) throw new Exception('Cart is empty.');
   db()->beginTransaction(); $total=0; $valid=0;
   $q=db()->prepare("SELECT id,name,price FROM menu_items WHERE id=? AND available=1");
   $ins=db()->prepare("INSERT INTO order_items(order_id,menu_item_id,item_name,unit_price,qty,notes) VALUES(?,?,?,?,?,?)");
   $o=db()->prepare("INSERT INTO orders(table_id,status,notes) VALUES(?, 'new',?)");
   $o->execute([$t['id'],substr((string)($d['notes']??''),0,500)]);$oid=(int)db()->lastInsertId();
   foreach($d['items'] as $x){
      $q->execute([(int)($x['id']??0)]);$it=$q->fetch();if(!$it)continue;
      $qty=max(1,min(99,(int)($x['qty']??0)));if(!$qty)continue;
      $note=substr((string)($x['note']??''),0,300);
      $ins->execute([$oid,$it['id'],$it['name'],$it['price'],$qty,$note]);$total += $it['price']*$qty;$valid++;
   }
   if(!$valid) throw new Exception('No valid menu items.');
   $u=db()->prepare("UPDATE orders SET total=? WHERE id=?");$u->execute([$total,$oid]);db()->commit();
   echo json_encode(['ok'=>true,'order_id'=>$oid,'total'=>(float)$total]);exit;
 }
 if($action==='status'){
   $id=(int)($_GET['order']??0);
   $s=db()->prepare("SELECT o.id,o.status,o.payment_status,o.payment_method,o.total,t.table_no FROM orders o JOIN restaurant_tables t ON t.id=o.table_id WHERE o.id=?");$s->execute([$id]);$o=$s->fetch();
   if(!$o) throw new Exception('Order not found.');
   echo json_encode(['ok'=>true,'order'=>$o]);exit;
 }
 throw new Exception('Unknown action.');
}catch(Throwable $e){http_response_code(400);echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);}
