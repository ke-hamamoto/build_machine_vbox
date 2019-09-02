<?php
session_start();
require_once'./config.php';
require_once'./Encode.php';

function resError($msg){
$arry=array('flg'=>$msg);
$json=json_encode($arry);
echo $json;
}

function curSessionDataGet(&$db){

$stt=$db->prepare('SELECT * FROM user WHERE uid=:uid;');
$stt->bindValue(':uid',$_SESSION['uid']);
$stt->execute();
if($row=$stt->fetch()){
$sid=$row['session'];

$stt=$db->prepare('SELECT * FROM session_index WHERE sid=:sid;');
$stt->bindValue(':sid',$sid);
$stt->execute();
if($row=$stt->fetch()){
$_SESSION['session_sname']=$row['sname'];
$_SESSION['session_name']=$row['tname'];
}
else{
$_SESSION['session_sname']='雑談';
$_SESSION['session_name']='talk';
}
}
else{
$sid="";
$_SESSION['session_sname']='雑談';
$_SESSION['session_name']='talk';
}

$res=[];
$res['sid']=$sid;
$res['sname']=$_SESSION['session_sname'];
$res['tname']=$_SESSION['session_name'];

return $res;

}//end func


function start(){
//データを取得
try{
$db=new PDO('mysql:host='.DBHOST.';dbname='.$_SESSION['room'].';charset=utf8','root',DBPASS);
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

try{
$db->beginTransaction();
if(!$db->inTransaction()){throw new Exception('トランザクションに入ってません');}

$stt=$db->prepare('SELECT * FROM user WHERE uid=:uid;');
$stt->bindValue(':uid',$_SESSION['uid']);
$stt->execute();
if($row=$stt->fetch()){
$sid=$row['session'];
$datetime=$row['date'].' 00:00:00';

$stt=$db->prepare('SELECT * FROM session_index WHERE sid=:sid;');
$stt->bindValue(':sid',$sid);
$stt->execute();
if($row=$stt->fetch()){
$_SESSION['session_sname']=$row['sname'];
$_SESSION['session_name']=$row['tname'];
}//セッションが存在する
else{

$stt=$db->prepare('UPDATE user SET session=:session WHERE uid=:uid;');
$stt->bindValue(':session','');
$stt->bindValue(':uid',$_SESSION['uid']);
$stt->execute();

$sid="";
$_SESSION['session_sname']='雑談';
$_SESSION['session_name']='talk';
}//セッションが存在しない
}
else{
$sid="";
$_SESSION['session_sname']='雑談';
$_SESSION['session_name']='talk';
$datetime="0000-00-00 00:00:00";
}


$stt=$db->prepare('SELECT COUNT(id) AS cnt FROM '.$_SESSION['session_name'].';');
$stt->execute();
if($row=$stt->fetch()){
$sessionCnt=$row['cnt'];
}
else $sessionCnt=0;


if($datetime!="0000-00-00 00:00:00"){
$order="old";


$stt=$db->prepare('SELECT * FROM '.
$_SESSION['session_name'].
' t1 INNER JOIN(SELECT t4.id FROM '.
$_SESSION['session_name'].
' t2 INNER JOIN(SELECT t3.id FROM '.
$_SESSION['session_name'].
' t3 WHERE datetime>=:datetime) t4 ON t2.id=t4.id ORDER BY t4.id ASC LIMIT 100) t5 ON t1.id=t5.id ORDER BY t5.id ASC;');


$stt->bindValue(':datetime',$datetime);
}
else{
$order="new";
$stt=$db->prepare('SELECT * FROM '.$_SESSION['session_name'].' ORDER BY id DESC LIMIT 100;');
}
$stt->execute();

//ここにオープン処理入力
//取得したデータを配列に格納
$cnt=0;
while($row=$stt->fetch()){
$talk[]=array(
'id'=>e($row['id']),
'uid'=>e($row['uid']),
'type'=>e($row['type']),
'persn'=>e($row['persn']),
'avt'=>e($row['avt']),
'talk'=>$row['talk'],
'datetime'=>e($row['datetime']));
$cnt++;
}
if($cnt==0){
$talk=NULL;
if($order=="new")$posId=null;
if($order=="old")$posId=null;
}
else{
if($order=="new")$posId=$posId=$talk[0]['id'];
else if($order=="old")$posId=$posId=$talk[($cnt-1)]['id'];
}

if($posId!=null){
$stt=$db->prepare('SELECT COUNT(id) AS cnt FROM '.$_SESSION['session_name'].' WHERE id<:id');
$stt->bindValue(':id',$posId);
$stt->execute();
if($row=$stt->fetch()){
$sessionPos=$row['cnt'];
}
else if($order=="new")$sessionPos=0;
else if($order=="old")$sessionPos=0;
}
else{
if($order=="new")$sessionPos=0;
if($order=="old")$sessionPos=$sessionCnt;
}


$talkData['talk']=$talk;
$talkData['uid']=$_SESSION['uid'];
$talkData['sid']=$sid;
$talkData['sname']=e($_SESSION['session_sname']);
$talkData['order']=$order;
$talkData['cnt']=$sessionCnt;
$talkData['pos']=$sessionPos;
$talkData['posId']=$posId;

$json=json_encode($talkData);
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

}//start()

/////////////////////////
function addTalk(){

//check data
$checkLen=mb_strlen($_POST['data']);
if($checkLen<=0||$checkLen>500){
$msg="発言の文字数が正しくありません(talk001)";
resError($msg);
return;
}

try{
  $db=new PDO('mysql:host='.DBHOST.';dbname='.$_SESSION['room'].';charset=utf8','root',DBPASS);
  $db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
  $db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

try{
$db->beginTransaction();
if(!$db->inTransaction()){throw new Exception('トランザクションに入ってません');}

  $stt=$db->prepare('SELECT * FROM user WHERE uid=:uid;');
  $stt->bindValue(':uid',$_SESSION['uid']);
  $stt->execute();

if($res=$stt->fetch()){
if($res['avt']!=="")$avt=$res['avt'];
else $avt='../avatar/none.png';
if($res['avtname']!=="")$avtname=e($res['avtname']);
else $avtname='NO_NAME';
}
else{
$db=NULL;
return;
}

$curSessionData=curSessionDataGet($db);
$date=date("Y/m/d H:i:s");

  $stt=$db->prepare('INSERT INTO '.$curSessionData["tname"].'(uid,type,persn,avt,talk,datetime) VALUES(:uid,:type,:persn,:avt,:talk,:datetime)');
  $stt->bindValue(':uid',$_SESSION['uid']);
  $stt->bindValue(':type',$_POST['type']);
  $stt->bindValue(':persn',$avtname);
  $stt->bindValue(':avt',$avt);
  $stt->bindValue(':talk',$_POST['data']);
  $stt->bindValue(':datetime',$date);
  $stt->execute();

$add=array(
'sid'=>$curSessionData['sid'],
'id'=>$db->lastInsertId(),
'uid'=>e($_SESSION['uid']),
'type'=>e($_POST['type']),
'persn'=>e($avtname),
'avt'=>e($avt),
'talk'=>$_POST['data'],
'datetime'=>$date,
'flg'=>true);


if($_POST['type']=='bgm'){
$dbsys=new PDO('mysql:host='.DBHOST.';dbname=chat_sys;charset=utf8','root',DBPASS);
$dbsys->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
$dbsys->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
$dbsys->beginTransaction();
if(!$dbsys->inTransaction()){throw new Exception('トランザクションに入ってません');}

$stt=$dbsys->prepare('UPDATE bgm SET used=used+1 WHERE bgmid=:bgmid;');
$stt->bindValue(':bgmid',explode('?',$_POST['data'])[0]);
$stt->execute();

$dbsys->commit();
$dbsys=NULL;
}

$json=json_encode($add);
$db->commit();
$db=NULL;
echo $json;
}
catch(Exception $e){
$db->rollback();
if(isset($dbsys))$dbsys->rollback();
throw $e;
}

}
catch(Exception $e){
die('エラー１：'.$e->getMessage().'／エラー２：'.$e->getLine());
}

}//addTalk()

function remv(){
try{
  $db=new PDO('mysql:host='.DBHOST.';dbname='.$_SESSION['room'].';charset=utf8','root',DBPASS);
  $db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
  $db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

try{
$db->beginTransaction();
if(!$db->inTransaction()){throw new Exception('トランザクションに入ってません');}

$curSessionData=curSessionDataGet($db);

  $stt=$db->prepare('DELETE FROM '.$curSessionData["tname"].' WHERE id=:id');
  $stt->bindValue(':id',$_POST['id']);
  $stt->execute();

  $db->commit();
  $db=NULL;
  echo $curSessionData['sid'];
}
catch(Exception $e){
$db->rollback();
throw $e;
}

}
catch(Exception $e){
die('エラー１：'.$e->getMessage().'／エラー２：'.$e->getLine());
}

}//remv()

function remvAddOne(){
$id=$_POST['id'];

try{
  $db=new PDO('mysql:host='.DBHOST.';dbname='.$_SESSION['room'].';charset=utf8','root',DBPASS);
  $db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
  $db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

try{
$db->beginTransaction();
if(!$db->inTransaction()){throw new Exception('トランザクションに入ってません');}

$curSessionData=curSessionDataGet($db);

  $stt=$db->prepare('SELECT * FROM '.$curSessionData["tname"].' WHERE id<:id ORDER BY id DESC LIMIT 1');
  $stt->bindValue(':id',$id);
  $stt->execute();
  
if($arry=$stt->fetch()){
$addOne=array(
	'sid'=> e($curSessionData['sid']),
	'id'=> e($arry['id']),
	'type'=> e($arry['type']),
	'uid'=> e($arry['uid']),
	'persn' => e($arry['persn']),
	'avt' => e($arry['avt']),
	'talk' => $arry['talk'],
	'datetime' => e($arry['datetime']));
}
else $addOne=NULL;

$json=json_encode($addOne);
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

}//remvAddOne()




function edit(){

try{
  $db=new PDO('mysql:host='.DBHOST.';dbname='.$_SESSION['room'].';charset=utf8','root',DBPASS);
  $db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
  $db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

try{
$db->beginTransaction();
if(!$db->inTransaction()){throw new Exception('トランザクションに入ってません');}

$curSessionData=curSessionDataGet($db);

  $stt = $db->prepare('SELECT talk FROM '.$curSessionData["tname"].' WHERE id=:id AND type=:type;');
  $stt->bindValue(':id',$_POST['id']);
  $stt->bindValue(':type','normal');
  $stt->execute();
  
if($edit=$stt->fetch())$talk=eBr($edit['talk']);
else $talk=false;

$db->commit();
$db=NULL;
echo $talk;
}
catch(Exception $e){
$db->rollback();
throw $e;
}

}
catch(Exception $e){
die('エラー１：'.$e->getMessage().'／エラー２：'.$e->getLine());
}

}//edit()

function update(){

//check edit
$checkLen=mb_strlen($_POST['edit_data']);
if($checkLen<=0||$checkLen>500){
$msg="編集後の文字数が正しくありません(talk002)";
resError($msg);
return;
}

try{
  $db=new PDO('mysql:host='.DBHOST.';dbname='.$_SESSION['room'].';charset=utf8','root',DBPASS);
  $db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
  $db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

try{
$db->beginTransaction();
if(!$db->inTransaction()){throw new Exception('トランザクションに入ってません');}

$curSessionData=curSessionDataGet($db);

  $stt=$db->prepare('UPDATE '.$curSessionData["tname"].' SET talk=:talk WHERE id=:id;');
  $stt->bindValue(':id',$_POST['id']);
  $stt->bindValue(':talk',$_POST['edit_data']);
  $stt->execute();
  
  	$edit_arry=array(
	'sid'=> e($curSessionData['sid']),
	'id'=> e($_POST['id']),
	'talk' => $_POST['edit_data'],
    'flg' => true);
    $json=json_encode($edit_arry);

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

}//update()

///////////////////
function quote(){

try{
  $db=new PDO('mysql:host='.DBHOST.';dbname='.$_SESSION['room'].';charset=utf8','root',DBPASS);
  $db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
  $db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

try{
$db->beginTransaction();
if(!$db->inTransaction()){throw new Exception('トランザクションに入ってません');}

$curSessionData=curSessionDataGet($db);

$stt=$db->prepare('SELECT * FROM '.$curSessionData["tname"].' WHERE id=:id');
$stt->bindValue(':id',$_POST['id']);
$stt->execute();
  
if($arry=$stt->fetch()){
$talk=array(
	'sid'=> e($curSessionData['sid']),
	'id'=> e($arry['id']),
	'type'=> e($arry['type']),
	'uid'=> e($arry['uid']),
	'persn' => e($arry['persn']),
	'avt' => e($arry['avt']),
	'talk' => $arry['talk'],
	'datetime' => e($arry['datetime']));
$resObj['flg']=true;
$resObj['talk']=$talk;
}
else{
$resObj['flg']=false;
$resObj['talk']=NULL;
}
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
die('エラー１：'.$e->getMessage().'／エラー２：'.$e->getLine());
}

}//quote()

	
///////////////
if(isset($_GET['mode'])){
// モードの振り分け
switch($_GET['mode']){

case 'start':
start();
break;

case 'addTalk':
addTalk();
break;

case 'remv':
remv();
break;

case 'remvAddOne':
remvAddOne();
break;

case 'edit':
edit();
break;

case 'update':
update();
break;

case 'quote':
quote();
break;

}
}