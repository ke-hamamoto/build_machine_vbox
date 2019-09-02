<?php
session_start();
require_once'./config.php';
require_once'./Encode.php';
require_once'./createUniqueId.php';

//////////////////////////
function resError($msg,&$db){
$db=NULL;
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

//////////////
function getTalkData($sessionDate,$sid,$datetime,$db){

$stt=$db->prepare('UPDATE user SET session=:session,date=:date WHERE uid=:uid;');
$stt->bindValue(':session',$sid);
$stt->bindValue(':date',$sessionDate);
$stt->bindValue(':uid',$_SESSION['uid']);
$stt->execute();

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
if($order=="new")$posId=$talk[0]['id'];
if($order=="old")$posId=$talk[($cnt-1)]['id'];
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

return $talkData;
}//トークデータの獲得

//////////////////////////
function writeSession(){

$json=file_get_contents('php://input');
$obj=json_decode($json,true);

$resObj=[];
$resObj['flg']=false;
$resObj['mes']='';

try{
$db=new PDO('mysql:host='.DBHOST.';dbname='.$_SESSION['room'].';charset=utf8','root',DBPASS);
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

try{
$db->beginTransaction();
if(!$db->inTransaction()){throw new Exception('トランザクションに入ってません');}

$sid=$obj['sessionId'];
$datetime=$obj['sessionDate'].' 00:00:00';

$stt=$db->prepare('SELECT * FROM session_index WHERE sid=:sid;');
$stt->bindValue(':sid',$sid);
$stt->execute();
if($row=$stt->fetch()){

$_SESSION['session_sname']=$row['sname'];
$_SESSION['session_name']=$row['tname'];

$resObj['flg']=true;
$resObj['mes']='セッションを変更しました';
$resObj['data']=getTalkData($obj['sessionDate'],$sid,$datetime,$db);
$json=json_encode($resObj);
$db->commit();
$db=NULL;
echo $json;
return;
}//セッションが存在する場合
else{
if($sid==''){

$_SESSION['session_sname']='雑談';
$_SESSION['session_name']='talk';

$resObj['flg']=true;
$resObj['mes']='セッションを変更しました';
$resObj['data']=getTalkData($obj['sessionDate'],$sid,$datetime,$db);
$json=json_encode($resObj);
$db->commit();
$db=NULL;
echo $json;
return;
}//雑談として扱う
else{
$resObj=[];
$resObj['mes']='選択されたセッションは見つかりませんでした…';
$json=json_encode($resObj);
$db->rollback();
$db=NULL;
echo $json;
return;
}
}//セッションが存在しない場合

}
catch(Exception $e){
$db->rollback();
throw $e;
}

}
catch(Exception $e){
die('エラー１：'.$e->getMessage().'／エラー２：'.$e->getLine());
}

}//end func


//////////////////////////
function addPage(){

$json=file_get_contents('php://input');
$obj=json_decode($json,true);

try{
$db=new PDO('mysql:host='.DBHOST.';dbname='.$_SESSION['room'].';charset=utf8','root',DBPASS);
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

$curSessionData=curSessionDataGet($db);//get

$stt=$db->prepare('SELECT COUNT(id) AS cnt FROM '.$curSessionData['tname'].';');
$stt->execute();
if($row=$stt->fetch()){
$sessionCnt=$row['cnt'];
}
else $sessionCnt=0;

if($obj['order']=="old"&&$obj['posIdBack']!=""){
$stt=$db->prepare('SELECT * FROM '.
$curSessionData['tname'].
' t1 INNER JOIN(SELECT t4.id FROM '.
$curSessionData['tname'].
' t2 INNER JOIN(SELECT t3.id FROM '.
$curSessionData['tname'].
' t3 WHERE id>:id) t4 ON t2.id=t4.id ORDER BY t4.id ASC LIMIT 100) t5 ON t1.id=t5.id ORDER BY t5.id ASC;');
$stt->bindValue(':id',$obj['posIdBack']);
}
else if($obj['order']=="new"){
$stt=$db->prepare('SELECT * FROM '.
$curSessionData['tname'].
' t1 INNER JOIN(SELECT t4.id FROM '.
$curSessionData['tname'].
' t2 INNER JOIN(SELECT t3.id FROM '.
$curSessionData['tname'].
' t3 WHERE id<:id) t4 ON t2.id=t4.id ORDER BY t4.id DESC LIMIT 100) t5 ON t1.id=t5.id ORDER BY t5.id DESC;');
$stt->bindValue(':id',$obj['posIdBack']);
}
else{
$msg="時系列の指定が不正です(page_0001)";
resError($msg,$db);
return;
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
if($cnt==0)$talk=NULL;

$talkData['talk']=$talk;
$talkData['uid']=$_SESSION['uid'];
$talkData['sid']=$curSessionData['sid'];
$talkData['sname']=e($curSessionData['sname']);

$db=NULL;
$json=json_encode($talkData);
echo $json;

}
catch(Exception $e){
die('エラー１：'.$e->getMessage().'／エラー２：'.$e->getLine());
}

}//end func


/////////////////////////
function newPage(){

try{
$db=new PDO('mysql:host='.DBHOST.';dbname='.$_SESSION['room'].';charset=utf8','root',DBPASS);
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

try{
$db->beginTransaction();
if(!$db->inTransaction()){throw new Exception('トランザクションに入ってません');}

$stt=$db->prepare('UPDATE user SET date=:date WHERE uid=:uid;');
$stt->bindValue(':date','0000-00-00');
$stt->bindValue(':uid',$_SESSION['uid']);
$stt->execute();

$curSessionData=curSessionDataGet($db);//get

$stt=$db->prepare('SELECT * FROM '.$curSessionData['tname'].' ORDER BY id DESC LIMIT 100;');
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
if($cnt==0)$talk=NULL;

$talkData['talk']=$talk;
$talkData['uid']=$_SESSION['uid'];
$talkData['sid']=$curSessionData['sid'];
$talkData['sname']=e($curSessionData['sname']);

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

}//end func


//////////////////////////
function jumpPage(){

$json=file_get_contents('php://input');
$obj=json_decode($json,true);

try{
$db=new PDO('mysql:host='.DBHOST.';dbname='.$_SESSION['room'].';charset=utf8','root',DBPASS);
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

$curSessionData=curSessionDataGet($db);//get

if($obj['order']=='new')$stt=$db->prepare('SELECT * FROM '.$curSessionData['tname'].' ORDER BY id DESC LIMIT 100 OFFSET :length');
else if($obj['order']=='old')$stt=$db->prepare('SELECT * FROM '.$curSessionData['tname'].' ORDER BY id ASC LIMIT 100 OFFSET :length');
$stt->bindValue(':length',$obj['offset']);
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
if($cnt==0)$talk=NULL;

$talkData['talk']=$talk;
$talkData['uid']=$_SESSION['uid'];
$talkData['sid']=$curSessionData['sid'];
$talkData['sname']=e($curSessionData['sname']);

$db=NULL;
$json=json_encode($talkData);
echo $json;

}
catch(Exception $e){
die('エラー１：'.$e->getMessage().'／エラー２：'.$e->getLine());
}

}//end func


//////////////////////////
function searchSession(){

try{
$db=new PDO('mysql:host='.DBHOST.';dbname='.$_SESSION['room'].';charset=utf8','root',DBPASS);
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

$stt=$db->prepare('SELECT * FROM session_index;');
$stt->execute();

$cnt=0;
while($row=$stt->fetch()){
$session[]=array(
'sid'=>e($row['sid']),
'sname'=>e($row['sname']));
$cnt++;
}
if($cnt==0)$session=[];

$db=NULL;
$json=json_encode($session);
echo $json;

}
catch(Exception $e){
die('エラー１：'.$e->getMessage().'／エラー２：'.$e->getLine());
}

}//end func


//////////////////////////
function createSession(){


if(preg_match('/[ \/\\\\{}<>"\':?&|]/',$_POST['name'])){
$res['result']=false;
$res['mes']='セッション名に禁止文字が含まれています / \{}<>"\':?&|';
$json=json_encode($res);
echo $json;
return;
}

$checkLen=mb_strlen($_POST['name']);
if($checkLen<=0||$checkLen>64){
$res['result']=false;
$res['mes']='セッション名が不正です';
$json=json_encode($res);
echo $json;
return;
}

try{
$db=new PDO('mysql:host='.DBHOST.';dbname='.$_SESSION['room'].';charset=utf8','root',DBPASS);
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

$db2=new PDO('mysql:host='.DBHOST.';dbname='.$_SESSION['room'].';charset=utf8','root',DBPASS);
$db2->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
$db2->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);


/**********/
$stt=$db->prepare('SELECT kengen FROM user WHERE uid=:uid;');
$stt->bindValue(':uid',$_SESSION['uid']);
$stt->execute();
if(!$row=$stt->fetch()){
$db=NULL;
$res['result']=false;
$res['mes']='この部屋にあなたは参加していません';
$json=json_encode($res);
echo $json;
return;
}
$uidKengen=$row['kengen'];
/**********/

if($uidKengen<=0){
$db=NULL;
$res['result']=false;
$res['mes']='あなたには作成の権限がありません';
$json=json_encode($res);
echo $json;
return;
}


$stt=$db->prepare('SELECT COUNT(sid) AS cnt FROM session_index WHERE uid=:uid');
$stt->bindValue(':uid',$_SESSION['uid']);
$stt->execute();
if($row=$stt->fetch()){
if($row['cnt']>=4){
$db=NULL;
$res['result']=false;
$res['mes']='ひとりが作成できるセッションは４つまでです';
$json=json_encode($res);
echo $json;
return;
}
}

$stt=$db->prepare('SELECT COUNT(sid) AS cnt FROM session_index;');
$stt->execute();
if($row=$stt->fetch()){
if($row['cnt']>=20){
$db=NULL;
$res['result']=false;
$res['mes']='ルーム内に作成できるセッションの上限は２０です';
$json=json_encode($res);
echo $json;
return;
}
}

$talkName='talk'.createUniqueId(7);

try{
$db->beginTransaction();
if(!$db->inTransaction()){throw new Exception('トランザクションに入ってません');}


$stt=$db->prepare('INSERT INTO session_index(uid,tname,sname) VALUES(:uid,:tname,:sname)');
$stt->bindValue(':uid',$_SESSION['uid']);
$stt->bindValue(':tname',$talkName);
$stt->bindValue(':sname',$_POST['name']);
$stt->execute();

$stt=$db2->prepare('CREATE TABLE IF NOT EXISTS '.$talkName.'(id int(11) NOT NULL AUTO_INCREMENT,
uid varchar(100) NOT NULL,
type varchar(16) NOT NULL,
persn varchar(100) NOT NULL,
avt text NOT NULL,
talk text NOT NULL,
datetime datetime NOT NULL,
PRIMARY KEY(id),
INDEX(datetime))COLLATE utf8_general_ci');
$stt->execute();


$db->commit();
}
catch(Exception $e){
$db->rollback();
$stt=$db2->prepare('DROP TABLE IF EXISTS '.$talkName.';');
$stt->execute();
throw $e;
}

$db=NULL;
$db2=NULL;
$res['result']=true;
$res['mes']='セッションの作成に成功しました';
$json=json_encode($res);
echo $json;
}
catch(Exception $e){
$resObj['result']=false;
$resObj['mes']='エラーが発生しました';
$resObj['log']='/MES：'.$e->getMessage().'/ROW：'.$e->getLine();
$json=json_encode($resObj);
echo $json;
}

}//end func

///////////////////////
function delSession(){

$sid=$_POST['sid'];

$resObj=[];
$resObj['flg']=false;
$resObj['mes']='';

try{
$db=new PDO('mysql:host='.DBHOST.';dbname='.$_SESSION['room'].';charset=utf8','root',DBPASS);
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

$db2=new PDO('mysql:host='.DBHOST.';dbname='.$_SESSION['room'].';charset=utf8','root',DBPASS);
$db2->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
$db2->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

$dbsys=new PDO('mysql:host='.DBHOST.';dbname=chat_sys;charset=utf8','root',DBPASS);
$dbsys->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
$dbsys->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);


///////passCheck//////
$stt=$dbsys->prepare('SELECT pass FROM user WHERE uid=:uid;');
$stt->bindValue(':uid',$_SESSION['uid']);
$stt->execute();
if($row=$stt->fetch()){
if(md5($_POST['pass'])!=$row['pass']){
$db=NULL;
$db2=NULL;
$dbsys=null;
$resObj['mes']='パスワードが一致していません';
$json=json_encode($resObj);
echo $json;
return;
}
}
else{
$db=NULL;
$db2=NULL;
$dbsys=null;
$resObj['mes']='アカウントが見つかりませんでした';
$json=json_encode($resObj);
echo $json;
return;
}


/**********/
$stt=$db->prepare('SELECT kengen FROM user WHERE uid=:uid;');
$stt->bindValue(':uid',$_SESSION['uid']);
$stt->execute();
if(!$row=$stt->fetch()){
$db=NULL;
$db2=NULL;
$dbsys=null;
$resObj['mes']='この部屋にあなたは参加していません';
$json=json_encode($resObj);
echo $json;
return;
}
$uidKengen=$row['kengen'];
/**********/

if($uidKengen<=0){
$db=NULL;
$db2=NULL;
$dbsys=null;
$resObj['mes']='あなたには削除の権限がありません';
$json=json_encode($resObj);
echo $json;
return;
}


if($sid!=''){

$stt=$db->prepare('SELECT tname FROM session_index WHERE sid=:sid;');
$stt->bindValue(':sid',$sid);
$stt->execute();
if($row=$stt->fetch()){
$tname=$row['tname'];

$stt=$db->prepare('SELECT stts_session FROM room_stts WHERE stts_session=:stts_session1 AND stts_type=:stts_type1 OR stts_session=:stts_session2 AND stts_type=:stts_type2;');
$stt->bindValue(':stts_session1',$tname);
$stt->bindValue(':stts_session2',$tname);
$stt->bindValue(':stts_type1','battle_ready');
$stt->bindValue(':stts_type2','battle_now');
$stt->execute();
if($stt->fetch()){
$db=NULL;
$db2=NULL;
$dbsys=null;
$resObj['mes']='このセッションはルーム内で使用されています';
$json=json_encode($resObj);
echo $json;
return;
}//部屋の状態に関与している場合
else{

try{
$db->beginTransaction();
if(!$db->inTransaction()){throw new Exception('トランザクションに入ってません');}

$stt=$db->prepare('DELETE FROM session_info WHERE tname=:tname;');
$stt->bindValue(':tname',$tname);
$stt->execute();

$stt=$db->prepare('DELETE FROM session_index WHERE sid=:sid;');
$stt->bindValue(':sid',$sid);
$stt->execute();

$stt=$db->prepare('DELETE FROM session_event WHERE tname=:tname;');
$stt->bindValue(':tname',$tname);
$stt->execute();

$stt=$db->prepare('DELETE FROM session_memo WHERE tname=:tname;');
$stt->bindValue(':tname',$tname);
$stt->execute();

$stt=$db2->prepare('DROP TABLE IF EXISTS '.$tname.';');
$stt->execute();

$db->commit();
}
catch(Exception $e){
$db->rollback();
throw $e;
}

$db=NULL;
$db2=NULL;
$dbsys=null;
$resObj['flg']=true;
$resObj['room']=$_SESSION['room'];
$resObj['sid']=$sid;
$resObj['mes']='セッションの削除に成功しました！';
$json=json_encode($resObj);
echo $json;
return;
}//部屋の状態に関与していない
}//テーブル名が取れた場合
else{
$db=NULL;
$db2=NULL;
$dbsys=null;
$resObj['mes']='セッションが存在しません';
$json=json_encode($resObj);
echo $json;
return;
}

}//雑談でない場合
else{
$db=NULL;
$db2=NULL;
$dbsys=null;
$resObj['mes']='雑談は削除できません';
$json=json_encode($resObj);
echo $json;
return;
}//雑談の場合

}
catch(Exception $e){
$resObj['flg']=false;
$resObj['mes']='エラーが発生しました';
$resObj['log']='/MES：'.$e->getMessage().'/ROW：'.$e->getLine();
$json=json_encode($resObj);
echo $json;
}

}//end delSession

////////////////
if(isset($_GET['mode'])){
// モードの振り分け
switch ($_GET['mode']){

case 'writeSession':
writeSession();
break;

case 'delSession':
delSession();
break;

case 'addPage':
addPage();
break;

case 'newPage':
newPage();
break;

case 'jumpPage':
jumpPage();
break;

case 'createSession':
createSession();
break;

case 'searchSession':
searchSession();
break;

}
}