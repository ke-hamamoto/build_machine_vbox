<?php
session_start();
require_once'/var/www/html/test_app/config.php';

$stdin=file_get_contents('php://stdin');
$obj=json_decode($stdin,true);
$_SESSION['enter']=false;

sleep(10);

if(isset($_SESSION['enter'])){

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

$stt = $db->prepare('UPDATE user SET deru=:deru,myjoin=:myjoin1 WHERE myjoin=:myjoin2;');
$stt->bindValue(':deru',date("Y/m/d H:i:s"));
$stt->bindValue(':myjoin1',"");
$stt->bindValue(':myjoin2',$obj['id']);
$stt->execute();

$stt=$db->prepare('SELECT COUNT(uid) AS cnt FROM user WHERE myjoin!=:myjoin;');
$stt->bindValue(':myjoin',"");
$stt->execute();
if($row=$stt->fetch()){
$res=$row['cnt'];
}
else{
$res=0;
}

$stt=$dbsys->prepare('UPDATE room_list SET cnt=:cnt WHERE dbname=:dbname;');
$stt->bindValue(':cnt',$res);
$stt->bindValue(':dbname',$obj['roomId']);
$stt->execute();

$db->commit();
$dbsys->commit();
$db = NULL;
$dbsys = NULL;
echo $res;
}
catch(Exception $e){
$db->rollback();
$dbsys->rollback();
throw $e;
}

}
catch(Exception $e){
echo 0;
//die('エラー１：'.$e->getMessage().'／エラー２：'.$e->getLine());
}


}//部屋に入っているかどうか

session_unset();
session_destroy();
