<?php
if(!is_admin() && !can('can_manage_staff')){echo '<div class="card">Staff management permission denied.</div>';return;}
if($_SERVER['REQUEST_METHOD']==='POST'){
  verify_csrf(); $a=$_POST['action']??'';
  $permKeys=['can_view_dashboard','can_view_orders','can_take_orders','can_mark_paid','can_complete','can_use_kitchen','can_manage_tables','can_manage_menu','can_view_reports','can_manage_settings','can_manage_staff'];
  if($a==='save'){
    $id=(int)($_POST['id']??0); $name=trim((string)($_POST['name']??'')); $username=trim((string)($_POST['username']??''));
    if($name===''||$username===''){flash('Name and username are required.');redirect('admin.php?page=staff');}
    $role=in_array($_POST['role']??'', ['staff','kitchen'], true) ? $_POST['role'] : 'staff';
    $vals=[]; foreach($permKeys as $k)$vals[$k]=isset($_POST[$k])?1:0;
    try{
      if($id>0){
        $sql="UPDATE users SET name=?, username=?, role=?, can_view_dashboard=?, can_view_orders=?, can_take_orders=?, can_mark_paid=?, can_complete=?, can_use_kitchen=?, can_manage_tables=?, can_manage_menu=?, can_view_reports=?, can_manage_settings=?, can_manage_staff=?";
        $args=[$name,$username,$role,$vals['can_view_dashboard'],$vals['can_view_orders'],$vals['can_take_orders'],$vals['can_mark_paid'],$vals['can_complete'],$vals['can_use_kitchen'],$vals['can_manage_tables'],$vals['can_manage_menu'],$vals['can_view_reports'],$vals['can_manage_settings'],$vals['can_manage_staff']];
        $pass=trim((string)($_POST['password']??'')); if($pass!==''){if(strlen($pass)<8)throw new RuntimeException('Password must be at least 8 characters.');$sql.=', password_hash=?';$args[]=password_hash($pass,PASSWORD_DEFAULT);}
        $sql.=' WHERE id=?';$args[]=$id; db()->prepare($sql)->execute($args); flash('Staff member updated.');
      }else{
        $pass=(string)($_POST['password']??''); if(strlen($pass)<8)throw new RuntimeException('Password must be at least 8 characters.');
        db()->prepare("INSERT INTO users(name,username,password_hash,role,can_view_dashboard,can_view_orders,can_take_orders,can_mark_paid,can_complete,can_use_kitchen,can_manage_tables,can_manage_menu,can_view_reports,can_manage_settings,can_manage_staff) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)")
          ->execute([$name,$username,password_hash($pass,PASSWORD_DEFAULT),$role,$vals['can_view_dashboard'],$vals['can_view_orders'],$vals['can_take_orders'],$vals['can_mark_paid'],$vals['can_complete'],$vals['can_use_kitchen'],$vals['can_manage_tables'],$vals['can_manage_menu'],$vals['can_view_reports'],$vals['can_manage_settings'],$vals['can_manage_staff']]);
        flash('Staff member created.');
      }
    }catch(Throwable $e){flash('Could not save staff member: '.($e instanceof RuntimeException?$e->getMessage():'username may already exist.'));}
  }elseif($a==='toggle'){ $id=(int)$_POST['id']; if($id!== (int)user()['id']){db()->prepare("UPDATE users SET active=1-active WHERE id=?")->execute([$id]);flash('User status updated.');}}
  redirect('admin.php?page=staff');
}
$editId=(int)($_GET['edit']??0); $edit=null; if($editId){$q=db()->prepare('SELECT * FROM users WHERE id=?');$q->execute([$editId]);$edit=$q->fetch();}
$users=db()->query("SELECT * FROM users ORDER BY id")->fetchAll();
$permissions=[
 'can_view_dashboard'=>'Dashboard', 'can_view_orders'=>'View Orders', 'can_take_orders'=>'Take Orders (Mobile/Tablet)',
 'can_mark_paid'=>'Mark as Paid', 'can_complete'=>'Complete Orders', 'can_use_kitchen'=>'Kitchen Display',
 'can_manage_tables'=>'Tables & QR', 'can_manage_menu'=>'Menu Management', 'can_view_reports'=>'Reports',
 'can_manage_settings'=>'Restaurant Settings', 'can_manage_staff'=>'Staff Management'
];
?>
<h1 class="h1">Staff & Permissions</h1>
<p class="muted">Admin can give every staff member a different combination of permissions. Role is only a label; permissions control the actual access.</p>
<div class="card"><h2><?=$edit?'Edit Staff Member':'Add Staff Member'?></h2>
<form method="post"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?=$edit?(int)$edit['id']:0?>">
<div class="row"><div><label>Name</label><input name="name" value="<?=e($edit['name']??'')?>" required></div><div><label>Username</label><input name="username" value="<?=e($edit['username']??'')?>" required></div><div><label>Password <?=$edit?'(leave blank to keep current)':'(8+ characters)'?></label><input type="password" name="password" <?=$edit?'':'required'?>></div><div><label>Role Label</label><select name="role"><option value="staff" <?=($edit['role']??'')==='staff'?'selected':''?>>Staff</option><option value="kitchen" <?=($edit['role']??'')==='kitchen'?'selected':''?>>Kitchen</option></select></div></div>
<h3>Permissions</h3><div class="perm-grid"><?php foreach($permissions as $key=>$label): ?><label class="perm"><input type="checkbox" name="<?=$key?>" <?=!empty($edit[$key])?'checked':''?>> <span><?=e($label)?></span></label><?php endforeach;?></div>
<div style="display:flex;gap:8px;margin-top:14px"><button class="btn gold"><?=$edit?'UPDATE STAFF':'CREATE STAFF'?></button><?php if($edit): ?><a class="btn light" href="admin.php?page=staff">Cancel</a><?php endif;?></div>
</form></div>
<div class="card"><h2>Staff Members</h2><div style="overflow:auto"><table class="table"><tr><th>Name</th><th>Role</th><th>Permissions</th><th>Status</th><th>Actions</th></tr><?php foreach($users as $x): ?><tr><td><?=e($x['name'])?><br><span class="muted">@<?=e($x['username'])?></span></td><td><?=e($x['role'])?></td><td><?php $labels=[];foreach($permissions as $k=>$v)if(!empty($x[$k]))$labels[]=$v;echo e(implode(' · ',$labels)?:'No permissions'); ?></td><td><?=$x['active']?'Active':'Disabled'?></td><td><a class="btn small" href="admin.php?page=staff&edit=<?=$x['id']?>">Edit</a><?php if($x['id']!==(int)user()['id']): ?> <form method="post" style="display:inline"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?=$x['id']?>"><button class="btn small light"><?=$x['active']?'Disable':'Enable'?></button></form><?php endif;?></td></tr><?php endforeach;?></table></div></div>
