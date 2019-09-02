<?php
session_start();
require_once'./config.php';
require_once'./Encode.php';

function Check(){

try{
$db=new PDO('mysql:host='.DBHOST.';dbname='.$_SESSION['room'].';charset=utf8','root',DBPASS);
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

try{
$db->beginTransaction();
if(!$db->inTransaction()){throw new Exception('トランザクションに入ってません');}

$stt=$db->prepare('SELECT * FROM room_stts ORDER BY stts_id DESC;');
$stt->execute();
$i=0;
while($row=$stt->fetch()){
$data_stts[]=array(
'stts_id'=> e($row['stts_id']),
'stts_type'=> e($row['stts_type']),
'stts_master'=> e($row['stts_master']),
'stts_session'=> e($row['stts_session']),
'stts_session_name'=> e($row['stts_session_name']));
$i++;
}
if($i==0)$data_stts=[];

$resObj['flg']=true;
$resObj['data']=$data_stts;
$json=json_encode($resObj);
$db->commit();
$db=NULL;
echo $json;

}
catch(Exception $e){
$db->rollback();
throw $e;
}

}
catch(Exception $e){
$resObj['flg']=false;
$resObj['mes']='エラーです';
$resObj['log']='エラー１：'.$e->getMessage().'／エラー２：'.$e->getLine();
$json=json_encode($resObj);
echo $json;
}

}//Check


function Write(){

switch ($_GET['type']){

case 'battle_ready':

try{

$db=new PDO('mysql:host='.DBHOST.';dbname='.$_SESSION['room'].';charset=utf8','root',DBPASS);
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

try{
$db->beginTransaction();
if(!$db->inTransaction()){throw new Exception('トランザクションに入ってません');}

//バトルレディー状態でないか調べる処理
$stt=$db->prepare('SELECT * FROM room_stts WHERE stts_type=:stts_type_1 OR stts_type=:stts_type_2;');
$stt->bindValue(':stts_type_1','battle_ready');
$stt->bindValue(':stts_type_2','battle_now');
$stt->execute();
$i=0;
while($row=$stt->fetch()){$i++;}
if($i==0){
//ルーム状態を追加
$stt=$db->prepare('INSERT INTO room_stts(stts_type,stts_master,stts_session,stts_session_name) VALUES(:stts_type,:stts_master,:stts_session,:stts_session_name)');
$stt->bindValue(':stts_type','battle_ready');
$stt->bindValue(':stts_master',$_SESSION['uid']);
$stt->bindValue(':stts_session',$_SESSION['session_name']);
$stt->bindValue(':stts_session_name',$_SESSION['session_sname']);
$stt->execute();

$stt=$db->prepare('SELECT * FROM room_stts ORDER BY stts_id DESC LIMIT 1;');
$stt->execute();

$row=$stt->fetch();
$battle_mes=array(
'stts_id'=> e($row['stts_id']),
'stts_type'=> e($row['stts_type']),
'stts_master'=> e($row['stts_master']),
'stts_session'=> e($row['stts_session']),
'stts_session_name'=> e($row['stts_session_name']));
}
else{
$battle_mes=array(
'stts_id'=>null,
'stts_type'=>null,
'stts_master'=>null,
'stts_session'=>null,
'stts_session_name'=>null);
}

$json=json_encode($battle_mes);
$db->commit();
$db=NULL;
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

break;//battle_ready

}//switch

}//Write

function Del(){

$stts_id=e($_POST['stts_id']);

try{
$db=new PDO('mysql:host='.DBHOST.';dbname='.$_SESSION['room'].';charset=utf8','root',DBPASS);
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

try{
$db->beginTransaction();
if(!$db->inTransaction()){throw new Exception('トランザクションに入ってません');}

$stt = $db->prepare('DELETE FROM room_stts WHERE stts_id=:stts_id;');
$stt->bindValue(':stts_id',$stts_id);
$stt->execute();

$stt = $db->prepare('DELETE FROM ready_panel;');
$stt->execute();

$resObj['flg']=true;
$json=json_encode($resObj);
$db->commit();
$db=NULL;
echo $json;
}
catch(Exception $e){
$db->rollback();
throw $e;
}

}
catch(Exception $e){
$resObj['flg']=false;
$resObj['log']='エラー１：'.$e->getMessage().'／エラー２：'.$e->getLine();
$json=json_encode($resObj);
echo $json;
}

}//Del


function DelBattleEnd(){

try{
$db=new PDO('mysql:host='.DBHOST.';dbname='.$_SESSION['room'].';charset=utf8','root',DBPASS);
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

try{
$db->beginTransaction();
if(!$db->inTransaction()){throw new Exception('トランザクションに入ってません');}

$stt = $db->prepare('DELETE FROM room_stts WHERE stts_type=:stts_type;');
$stt->bindValue(':stts_type',"battle_now");
$stt->execute();

$db->commit();
$db=NULL;
}
catch(Exception $e){
$db->rollback();
throw $e;
}

}
catch(Exception $e){
die('エラー１：'.$e->getMessage().'／エラー２：'.$e->getLine());
}

}//Del

//////////////////

if(isset($_GET['mode'])) {
// モードの振り分け
switch ($_GET['mode']) {

case 'Check':
Check();
break;

case 'Write':
Write();
break;

case 'Del':
Del();
break;

case 'DelBattleEnd':
DelBattleEnd();
break;

}
}
