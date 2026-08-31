<?php
if(!is_admin() && !can('can_manage_settings')){echo '<div class="card">Settings permission denied.</div>';return;}

if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf();
    $a=$_POST['action']??'';
    if($a==='profile'){
        $name=trim((string)($_POST['restaurant_name']??''));
        if($name==='') $name='My Restaurant';
        set_setting('restaurant_name',substr($name,0,120));
        set_setting('restaurant_tagline',substr(trim((string)($_POST['restaurant_tagline']??'')),0,180));
        set_setting('currency_symbol',substr(trim((string)($_POST['currency_symbol']??'')),0,8) ?: '€');
        set_setting('restaurant_phone',substr(trim((string)($_POST['restaurant_phone']??'')),0,80));
        set_setting('restaurant_address',substr(trim((string)($_POST['restaurant_address']??'')),0,200));
        set_setting('restaurant_hours',substr(trim((string)($_POST['restaurant_hours']??'')),0,200));
        flash('Restaurant profile updated. Changes will appear across the system.');
    } elseif($a==='logo_upload'){
        if(empty($_FILES['logo']['tmp_name']) || $_FILES['logo']['error']!==UPLOAD_ERR_OK){
            flash('Please select a valid logo image.');
        } else {
            $f=$_FILES['logo'];
            if($f['size']>4*1024*1024){ flash('Logo must be 4 MB or smaller.'); }
            else {
                $mime=(new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
                $allowed=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
                if(!isset($allowed[$mime])) flash('Logo must be JPG, PNG or WebP.');
                else {
                    $dir=dirname(__DIR__).'/uploads'; if(!is_dir($dir)) mkdir($dir,0755,true);
                    $name='restaurant-logo-'.bin2hex(random_bytes(8)).'.'.$allowed[$mime];
                    if(move_uploaded_file($f['tmp_name'],$dir.'/'.$name)){
                        $old=setting('restaurant_logo','assets/logo-reference.jpg');
                        set_setting('restaurant_logo','uploads/'.$name);
                        if($old && str_starts_with($old,'uploads/')) @unlink(dirname(__DIR__).'/'.$old);
                        flash('Restaurant logo updated.');
                    } else flash('Could not save the logo. Check folder permissions.');
                }
            }
        }
    } elseif($a==='logo_reset') {
        $old=setting('restaurant_logo','assets/logo-reference.jpg');
        set_setting('restaurant_logo','assets/logo-reference.jpg');
        if($old && str_starts_with($old,'uploads/')) @unlink(dirname(__DIR__).'/'.$old);
        flash('Default logo restored.');
    }
    redirect('admin.php?page=settings');
}
$p=restaurant_profile();
?>
<h1 class="h1">Restaurant Profile</h1>
<p class="muted">This makes the ordering system reusable for any restaurant. Change the profile once and the customer menu, admin, login, order tracking, QR screens and reports use it automatically.</p>
<div class="row">
  <div class="card">
    <h2>Restaurant Details</h2>
    <form method="post">
      <input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="action" value="profile">
      <label>Restaurant Name</label><input name="restaurant_name" value="<?=e($p['name'])?>" maxlength="120" required>
      <label>Tagline / Cuisine</label><input name="restaurant_tagline" value="<?=e($p['tagline'])?>" maxlength="180" placeholder="e.g. Authentic Pakistani Cuisine">
      <div class="row"><div><label>Currency Symbol</label><input name="currency_symbol" value="<?=e($p['currency'])?>" maxlength="8" placeholder="€ / Rs / $"></div><div><label>Phone</label><input name="restaurant_phone" value="<?=e($p['phone'])?>"></div></div>
      <label>Address</label><input name="restaurant_address" value="<?=e($p['address'])?>">
      <label>Opening Hours</label><input name="restaurant_hours" value="<?=e($p['hours'])?>" placeholder="Mon-Sun 10:00 AM - 10:00 PM">
      <button class="btn gold" style="margin-top:10px">SAVE RESTAURANT PROFILE</button>
    </form>
  </div>
  <div class="card">
    <h2>Restaurant Logo</h2>
    <p class="muted">Upload one logo here. It is shared throughout the system.</p>
    <div class="logo-preview"><img src="<?=e(logo_url())?>?v=<?=time()?>" alt="<?=e($p['name'])?>"></div>
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="action" value="logo_upload">
      <label>Choose Logo (JPG, PNG or WebP · max 4 MB)</label><input type="file" name="logo" accept="image/jpeg,image/png,image/webp" required>
      <button class="btn gold" style="margin-top:10px">UPLOAD / CHANGE LOGO</button>
    </form>
    <form method="post" style="margin-top:8px"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="action" value="logo_reset"><button class="btn small light">Restore Default Logo</button></form>
  </div>
</div>
<div class="card"><h2>How this works</h2><p>For a new restaurant, the admin only needs to change the restaurant name, logo, tagline, currency and optional contact details here. Menu items, categories, tables and QR codes are then managed separately.</p></div>
