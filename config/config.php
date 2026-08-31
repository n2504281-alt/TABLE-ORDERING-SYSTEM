<?php
declare(strict_types=1);
require_once __DIR__.'/database.php';
session_start();
const APP_NAME='Bismillah Pak Darbar';
const APP_URL='https://CHANGE-YOUR-DOMAIN.com';
function e(?string $v):string{return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
function csrf():string{if(empty($_SESSION['csrf']))$_SESSION['csrf']=bin2hex(random_bytes(32));return $_SESSION['csrf'];}
function verify_csrf():void{if(!hash_equals($_SESSION['csrf']??'',$_POST['csrf']??'')){http_response_code(419);exit('Security check failed. Please go back and try again.');}}
function user():?array{return $_SESSION['user']??null;}
function require_login():void{if(!user()){header('Location: login.php');exit;}}
function is_admin():bool{return(user()['role']??'')==='admin';}
function can(string $permission):bool{$u=user();if(!$u)return false;if(($u['role']??'')==='admin')return true;return array_key_exists($permission,$u)&&!empty($u[$permission]);}
function require_permission(string $permission):void{require_login();if(!can($permission)){http_response_code(403);exit('Permission denied.');}}
function redirect(string $url):never{header('Location: '.$url);exit;}
function setting(string $key,?string $default=null):?string{static $cache=[];if(array_key_exists($key,$cache))return $cache[$key];try{$s=db()->prepare('SELECT setting_value FROM app_settings WHERE setting_key=? LIMIT 1');$s->execute([$key]);$v=$s->fetchColumn();return $cache[$key]=($v===false?$default:(string)$v);}catch(Throwable $e){return $cache[$key]=$default;}}
function set_setting(string $key,string $value):void{$s=db()->prepare('INSERT INTO app_settings(setting_key,setting_value) VALUES(?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)');$s->execute([$key,$value]);}
function logo_url():string{$path=setting('restaurant_logo','assets/logo-reference.jpg');return rtrim(APP_URL,'/').'/'.ltrim($path,'/');}
function logo_path():string{$path=setting('restaurant_logo','assets/logo-reference.jpg');return dirname(__DIR__).'/'.ltrim($path,'/');}
function logo_html(string $class='brand-logo',string $alt=APP_NAME):string{return '<img class="'.e($class).'" src="'.e(logo_url()).'" alt="'.e($alt).'">';}
function restaurant_name():string{return setting('restaurant_name',APP_NAME)?:APP_NAME;}
function restaurant_tagline():string{return setting('restaurant_tagline','Authentic Pakistani & Indian Cuisine');}
function currency_symbol():string{return setting('currency_symbol','€');}
function restaurant_profile():array{return['name'=>restaurant_name(),'tagline'=>restaurant_tagline(),'currency'=>currency_symbol(),'phone'=>setting('restaurant_phone',''),'address'=>setting('restaurant_address',''),'hours'=>setting('restaurant_hours','')];}
function flash(?string $msg=null):?string{if($msg!==null){$_SESSION['flash']=$msg;return null;}$x=$_SESSION['flash']??null;unset($_SESSION['flash']);return $x;}
