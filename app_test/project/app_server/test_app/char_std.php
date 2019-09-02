<?php
session_start();
require_once'./config.php';
require_once'./Encode.php';


function start(){
try{

$db=new PDO('mysql:host='.DBHOST.';dbname='.$_SESSION['room'].';charset=utf8','root',DBPASS);
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

$dbsys=new PDO('mysql:host='.DBHOST.';dbname=chat_sys;charset=utf8','root',DBPASS);
$dbsys->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
$dbsys->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

try{
$db->beginTransaction();
if(!$db->inTransaction()){throw new Exception('トランザクションに入ってません');}

$dbsys->beginTransaction();
if(!$dbsys->inTransaction()){throw new Exception('トランザクションに入ってません');}


$stt=$db->prepare('SELECT kengen,avt,avtname,avtid FROM user WHERE uid=:uid;');
$stt->bindValue(':uid',$_SESSION['uid']);
$stt->execute();
if($res=$stt->fetch()){

if($res['avtid']){$result['avtId']=$res['avtid'];}
else{$result['avtId']=-1;}

if($result['avtId']==-1){

$stt=$dbsys->prepare('SELECT img,img_name FROM user WHERE uid=:uid;');
$stt->bindValue(':uid',$_SESSION['uid']);
$stt->execute();
if($res2=$stt->fetch()){
if($res2['img_name']==''){$result['avtName']='NO_NAME';}
else{$result['avtName']=e($res2['img_name']);}
}

if($res['avtname']!=$res2['img_name']){
$stt=$db->prepare('UPDATE user SET avtname=:avtname WHERE uid=:uid;');
$stt->bindValue(':avtname',$res2['img_name']);
$stt->bindValue(':uid',$_SESSION['uid']);
$stt->execute();
}//名前の更新が見られた場合

if($res2['img']!==""){$result['avtUrl']='../'.$res2['img'];}
else{$result['avtUrl']='../avatar/none.png';}

if($res['avt']!='../'.$res2['img']){
$stt=$db->prepare('UPDATE user SET avt=:avt WHERE uid=:uid;');
$stt->bindValue(':avt','../'.$res2['img']);
$stt->bindValue(':uid',$_SESSION['uid']);
$stt->execute();
}//アバターの更新が見られた場合

}
else{
if($res['avtname']!==""){$result['avtName']=e($res['avtname']);}
else{$result['avtName']='NO_NAME';}

if($res['avt']!==""){$result['avtUrl']=$res['avt'];}
else{$result['avtUrl']='../avatar/none.png';}
}
$result['kengen']=$res['kengen'];

$result['flg']=true;

$json=json_encode($result);
echo $json;

}//ルームにUIDが登録されている場合
else{

$result['flg']=false;

$json=json_encode($result);
echo $json;

}//ルームにキャラが登録されていない場合

$db->commit();
$dbsys->commit();
$db=NULL;
}
catch(Exception $e){
$db->rollback();
$dbsys->rollback();
throw $e;
}


}
catch(Exception $e){
die('エラー１：'.$e->getMessage().'／エラー２：'.$e->getLine());
}

}//end func


///////////////
if(isset($_GET['mode'])){
// モードの振り分け
switch ($_GET['mode']){

case 'start':
start();
break;

}
}
