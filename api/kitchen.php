<?php require_once __DIR__.'/../config/config.php'; require_login(); header('Content-Type: application/json');
try{
 if(!is_admin() && !can('can_use_kitchen')) throw new Exception('Access denied');
 if($_SERVER['REQUEST_METHOD']==='POST'){
   $d=json_decode(file_get_contents('php://input'),true)??[];$allowed=['accepted','preparing','ready','served'];
   if(!in_array($d['status']??'', $allowed,true)) throw new Exception('Invalid status');
   $s=db()->prepare("UPDATE orders SET status=? WHERE id=?");$s->execute([$d['status'],(int)$d['id']]);
   echo json_encode(['ok'=>true]);exit;
 }
$orders=db()->query("SELECT o.id,o.table_id,o.status,o.total,t.table_no FROM orders o JOIN restaurant_tables t ON t.id=o.table_id WHERE o.status IN ('new','accepted','preparing','ready') ORDER BY o.id ASC")->fetchAll();
$q=db()->prepare("SELECT item_name,qty,notes FROM order_items WHERE order_id=?");
foreach($orders as &$o){$q->execute([$o['id']]);$o['items']=$q->fetchAll();}
echo json_encode(['ok'=>true,'orders'=>$orders]);
}catch(Throwable $e){http_response_code(403);echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);}