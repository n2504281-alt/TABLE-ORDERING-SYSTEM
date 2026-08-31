<?php require_once __DIR__.'/../config/config.php';
if (user()) redirect('admin.php');
$err=null;
if($_SERVER['REQUEST_METHOD']==='POST'){
  verify_csrf();
  $s=db()->prepare("SELECT * FROM users WHERE username=? AND active=1 LIMIT 1"); $s->execute([trim($_POST['username']??'')]); $u=$s->fetch();
  if($u && password_verify($_POST['password']??'',$u['password_hash'])){ session_regenerate_id(true); $_SESSION['user']=$u; redirect('admin.php'); }
  $err='Invalid username or password.';
}
?><!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="../assets/css/style.css"><title><?=e(restaurant_name())?> Login</title></head><body>
<div style="max-width:390px;margin:8vh auto;padding:20px"><div class="card">
<div style="text-align:center"><img src="<?=e(logo_url())?>" alt="<?=e(restaurant_name())?>" style="max-width:220px;max-height:150px;object-fit:contain;margin-bottom:10px"><div style="font-size:25px;font-weight:900;color:#c79a3a"><?=e(restaurant_name())?></div><p class="muted">Admin / Staff / Kitchen</p></div>
<?php if($err): ?><div class="pill red"><?=e($err)?></div><?php endif; ?>
<form method="post"><input type="hidden" name="csrf" value="<?=csrf()?>"><label>Username</label><input name="username" required autofocus><label>Password</label><input type="password" name="password" required><button class="btn" style="width:100%;margin-top:14px">Login</button></form>
</div></div></body></html>