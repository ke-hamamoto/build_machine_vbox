<?php
session_start();
require_once'/var/www/html/test_app/config.php';

$stdin=file_get_contents('php://stdin');
$obj=json_decode($stdin,true);

$_SESSION['enter']=true;


try{
$db=new PDO('mysql:host='.DBHOST.';dbname='.$obj['roomId'].';charset=utf8','root',DBPASS);
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

$dbsys=new PDO('mysql:host='.DBHOST.';dbname=chat_sys;charset=utf8','root',DBPASS);
$dbsys->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
$dbsys->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
//////////////////////

try{
$db->beginTransaction();
if(!$db->inTransaction()){throw new Exception('トランザクションに入ってません');}
$dbsys->beginTransaction();
if(!$dbsys->inTransaction()){throw new Exception('トランザクションに入ってません');}


$resObj['cnt']=0;
$resObj['preSktId']='';

$stt=$db->prepare('SELECT myjoin FROM user WHERE uid=:uid;');
$stt->bindValue(':uid',$obj['nameId']);
$stt->execute();
if($row=$stt->fetch()){
$resObj['preSktId']=$row['myjoin'];
}

$stt = $db->prepare('UPDATE user SET hairu=:hairu,myjoin=:myjoin WHERE uid=:uid;');
$stt->bindValue(':hairu',date("Y/m/d H:i:s"));
$stt->bindValue(':myjoin',$obj['id']);
$stt->bindValue(':uid',$obj['nameId']);
$stt->execute();

$stt=$db->prepare('SELECT COUNT(uid) AS cnt FROM user WHERE myjoin!=:myjoin;');
$stt->bindValue(':myjoin',"");
$stt->execute();
if($row=$stt->fetch()){
$resObj['cnt']=$row['cnt'];
}

$stt=$dbsys->prepare('UPDATE room_list SET cnt=:cnt WHERE dbname=:dbname;');
$stt->bindValue(':cnt',$resObj['cnt']);
$stt->bindValue(':dbname',$obj['roomId']);
$stt->execute();

$json=json_encode($resObj);
$db->commit();
$dbsys->commit();
$db=NULL;
$dbsys=NULL;
echo $json;
}
catch(Exception $e){
$db->rollback();
$dbsys->rollback();
throw $e;
}


}
catch(Exception $e){
$json=json_encode($resObj);
echo $json;
//die('エラー１：'.$e->getMessage().'／エラー２：'.$e->getLine());
}

session_unset();
session_destroy();
