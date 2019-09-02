<?php
require_once'./config.php';
require_once'./Encode.php';

try{
  $db=new PDO('mysql:host='.DBHOST.';dbname=chat_sys;charset=utf8','root',DBPASS);
  $db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
  $db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
//////////////////////

try{
$db->beginTransaction();
if(!$db->inTransaction()){throw new Exception('トランザクションに入ってません');}

  $stt = $db->prepare('SELECT * FROM bgm WHERE mode=:mode;');
  $stt->bindValue(':mode','battle');
  $stt->execute();
    while($bgm_arry=$stt->fetch()){
	$battle_arry[] = array(
	'bgmid'=> e($bgm_arry['bgmid']),
	'uid'=> e($bgm_arry['uid']),
	'mode'=> e($bgm_arry['mode']),
	'type'=> e($bgm_arry['type']),
	'url'=> e($bgm_arry['url']),
	'name'=> e($bgm_arry['name'])
	);
	}

  $stt = $db->prepare('SELECT * FROM bgm WHERE mode=:mode;');
  $stt->bindValue(':mode','ev');
  $stt->execute();
    while($bgm_arry=$stt->fetch()){
	$ev_arry[] = array(
	'bgmid'=> e($bgm_arry['bgmid']),
	'uid'=> e($bgm_arry['uid']),
	'mode'=> e($bgm_arry['mode']),
	'type'=> e($bgm_arry['type']),
	'url'=> e($bgm_arry['url']),
	'name'=> e($bgm_arry['name'])
	);
	}

  $stt = $db->prepare('SELECT * FROM bgm WHERE mode=:mode;');
  $stt->bindValue(':mode','field');
  $stt->execute();
    while($bgm_arry=$stt->fetch()){
	$field_arry[] = array(
	'bgmid'=> e($bgm_arry['bgmid']),
	'uid'=> e($bgm_arry['uid']),
	'mode'=> e($bgm_arry['mode']),
	'type'=> e($bgm_arry['type']),
	'url'=> e($bgm_arry['url']),
	'name'=> e($bgm_arry['name'])
	);
	}

  $stt = $db->prepare('SELECT * FROM bgm WHERE mode=:mode;');
  $stt->bindValue(':mode','dngn');
  $stt->execute();
    while($bgm_arry=$stt->fetch()){
	$dngn_arry[] = array(
	'bgmid'=> e($bgm_arry['bgmid']),
	'uid'=> e($bgm_arry['uid']),
	'mode'=> e($bgm_arry['mode']),
	'type'=> e($bgm_arry['type']),
	'url'=> e($bgm_arry['url']),
	'name'=> e($bgm_arry['name'])
	);
	}

$bgm_arry['battle']=$battle_arry;
$bgm_arry['field']=$field_arry;
$bgm_arry['dngn']=$dngn_arry;
$bgm_arry['ev']=$ev_arry;

$json=json_encode($bgm_arry);
$db->commit();
$db = NULL;
echo $json;
}
catch(Exception $e){
$db->rollback();
throw $e;
}

}
catch(Exception $e){
die('エラー１：'.$e->getMessage().'／エラー２：'.$e->getLine());
}