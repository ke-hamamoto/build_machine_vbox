<?php
session_start();
require_once'./config.php';
require_once'./Encode.php';
require_once'./createUniqueId.php';

//////////////
function roomSearch(){

$resObj['mes']='';
$resObj['flg']=false;
$roomList=[];

if($_POST['uid']==$_SESSION['uid']){
$resObj['mes']='自分自身に申請はできません';
$json=json_encode($resObj);
echo $json;
return;
}

try{
$db=new PDO('mysql:host='.DBHOST.';dbname=chat_sys;charset=utf8','root',DBPASS);
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

try{
$db->beginTransaction();
if(!$db->inTransaction()){throw new Exception('トランザクションに入ってません');}

$stt=$db->prepare('SELECT join_list FROM user WHERE uid=:uid;');
$stt->bindValue(':uid',$_SESSION['uid']);
$stt->execute();
if($row=$stt->fetch()){
$list_array=explode(',',$row['join_list']);
}
else{
$list_array=[];
}

if(count($list_array)>99){
$resObj['mes']='100個以上のルームには参加できません';
$json=json_encode($resObj);
$db->rollback();
$db=NULL;
echo $json;
return;
}//すでに参加上限にある場合


$stt=$db->prepare('SELECT id,rm_uid,rname FROM room_list WHERE rm_uid=:rm_uid;');
$stt->bindValue(':rm_uid',$_POST['uid']);
$stt->execute();
$cnt=0;
while($row=$stt->fetch()){
$flg=true;
foreach($list_array as $cur){
$joinId=explode(' ',$cur)[0];
if($joinId==$row['id'])$flg=false;
$resObj['mes']='すでに参加している部屋しか見つかりませんでした';
}
if($flg){
$resObj['mes']='';
$roomList[]=array(
'id'=>$row['id'],
'uid'=>$row['rm_uid'],
'rname'=>e($row['rname'])
);
$resObj['flg']=true;
}
$cnt++;
}
if(!$cnt){
$resObj['mes']='そのユーザIDでは見つかりません';
}

$resObj['roomList']=$roomList;
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

}//roomSearch


////////////////
function appJoinRoom(){

$resObj['mes']='';
$resObj['type']='app';
$resObj['flg']=false;

if($_POST['uid']==$_SESSION['uid']){
$resObj['mes']='自分自身に申請はできません';
$json=json_encode($resObj);
echo $json;
return;
}

try{
$db=new PDO('mysql:host='.DBHOST.';dbname=chat_sys;charset=utf8','root',DBPASS);
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

try{
$db->beginTransaction();
if(!$db->inTransaction()){throw new Exception('トランザクションに入ってません');}

$stt=$db->prepare('SELECT COUNT(id) AS cnt FROM mail_box WHERE type=:type AND from_uid=:from_uid;');
$stt->bindValue(':type','app');
$stt->bindValue(':from_uid',$_SESSION['uid']);
if($row=$stt->fetch()){
if($row['cnt']>9){
$db->rollback();
$db=NULL;
$resObj['mes']='未処理の申請が１０個以上あります';
$json=json_encode($resObj);
echo $json;
return;
}
}


$stt=$db->prepare('SELECT id FROM mail_box WHERE type=:type AND content=:content AND from_uid=:from_uid;');
$stt->bindValue(':type','app');
$stt->bindValue(':content',$_POST['id']);
$stt->bindValue(':from_uid',$_SESSION['uid']);
$stt->execute();
if($row=$stt->fetch()){
$db->rollback();
$db=NULL;
$resObj['mes']='このリクエストはすでに申請中です';
$json=json_encode($resObj);
echo $json;
return;
}

$stt=$db->prepare('SELECT join_list FROM user WHERE uid=:uid;');
$stt->bindValue(':uid',$_SESSION['uid']);
$stt->execute();
if($row=$stt->fetch()){
$list_array=explode(',',$row['join_list']);
}
else{
$list_array=[];
}

if(count($list_array)>29){
$db->rollback();
$db=NULL;
$resObj['mes']='30個以上のルームには参加できません';
$json=json_encode($resObj);
echo $json;
return;
}//すでに参加上限にある場合

$stt=$db->prepare('SELECT id,rm_uid,rname FROM room_list WHERE id=:id;');
$stt->bindValue(':id',$_POST['id']);
$stt->execute();
if($row=$stt->fetch()){

if($_POST['uid']!=$row['rm_uid']){
$db->rollback();
$db=NULL;
$resObj['mes']='申請先の部屋と部屋の主が一致しません';
$json=json_encode($resObj);
echo $json;
return;
}


$stt=$db->prepare('SELECT myjoin,token FROM user WHERE uid=:uid;');
$stt->bindValue(':uid',$row['rm_uid']);
$stt->execute();
if(!$row2=$stt->fetch()){
$db->rollback();
$db=NULL;
$resObj['mes']='申請先の部屋の主が登録されていませんでした';
$json=json_encode($resObj);
echo $json;
return;
}
$resObj['uidToken']=$row2['token'];


$flg=true;
foreach($list_array as $cur){
$joinId=explode(' ',$cur)[0];
if($joinId==$row['id'])$flg=false;
$resObj['mes']='すでに参加しています';
}
if($flg){
$resObj['flg']=true;

$stt=$db->prepare('INSERT INTO mail_box(type,content,from_uid,to_uid) VALUES(:type,:content,:from_uid,:to_uid)');
$stt->bindValue(':type','app');
$stt->bindValue(':content',$row['id']);
$stt->bindValue(':from_uid',$_SESSION['uid']);
$stt->bindValue(':to_uid',$row['rm_uid']);
$stt->execute();

$resObj['mes']=$_POST['uid'].'さんへ申請しました';

}
}
else{
$resObj['mes']='申請先が見つかりません';
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


}//end appJoinRoom

///////////
function checkMail(){

try{
$db=new PDO('mysql:host='.DBHOST.';dbname=chat_sys;charset=utf8','root',DBPASS);
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

try{
$db->beginTransaction();
if(!$db->inTransaction()){throw new Exception('トランザクションに入ってません');}

$mailList=[];
$stt=$db->prepare('SELECT * FROM mail_box WHERE to_uid=:to_uid;');
$stt->bindValue(':to_uid',$_SESSION['uid']);
$stt->execute();
while($row=$stt->fetch()){
$mailList[]=array(
'id'=>$row['id'],
'type'=>$row['type'],
'content'=>$row['content'],
'to_uid'=>$row['to_uid'],
'from_uid'=>$row['from_uid']
);
}

$db->commit();
$db=NULL;
$json=json_encode($mailList);
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


}//end checkMail


///////////
function checkApp(){

try{
$db=new PDO('mysql:host='.DBHOST.';dbname=chat_sys;charset=utf8','root',DBPASS);
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

try{
$db->beginTransaction();
if(!$db->inTransaction()){throw new Exception('トランザクションに入ってません');}

$mailList=[];
$stt=$db->prepare('SELECT * FROM mail_box WHERE from_uid=:from_uid;');
$stt->bindValue(':from_uid',$_SESSION['uid']);
$stt->execute();
while($row=$stt->fetch()){
$mailList[]=array(
'id'=>$row['id'],
'type'=>$row['type'],
'content'=>$row['content'],
'to_uid'=>$row['to_uid'],
'from_uid'=>$row['from_uid']
);
}

$db->commit();
$db=NULL;
$json=json_encode($mailList);
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

}//end checkApp


/////////////////
function app_kyoka(){

$resObj['flg']=false;
$resObj['type']='app_kyoka';
$resObj['mes']='';

try{
$db=new PDO('mysql:host='.DBHOST.';dbname=chat_sys;charset=utf8','root',DBPASS);
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

try{
$db->beginTransaction();
if(!$db->inTransaction()){throw new Exception('トランザクションに入ってません');}

if($_POST['type']=='app'){

$stt=$db->prepare('SELECT * FROM mail_box WHERE id=:id;');
$stt->bindValue(':id',$_POST['id']);
$stt->execute();
if($row=$stt->fetch()){
if($row['to_uid']!=$_SESSION['uid']){
$db->rollback();
$db=NULL;
$resObj['mes']='このリクエストはあなた宛てではありませんでした';
$json=json_encode($resObj);
echo $json;
return;
}
if($row['from_uid']!=$_POST['fromuid']){
$db->rollback();
$db=NULL;
$resObj['mes']='申請者の整合性が確認できませんでした';
$json=json_encode($resObj);
echo $json;
return;
}



$stt=$db->prepare('SELECT * FROM room_list WHERE id=:id;');
$stt->bindValue(':id',$row['content']);
$stt->execute();
if(!$row2=$stt->fetch()){
$db->rollback();
$db=NULL;
$resObj['mes']='許可対象のルームが見つかりませんでした';
$json=json_encode($resObj);
echo $json;
return;
}
$resObj['roomId']=$row2['id'];
$resObj['roomName']=$row2['rname'];

if($row2['rm_uid']!=$_SESSION['uid']){
$db->rollback();
$db=NULL;
$resObj['mes']='許可対象の部屋の主はあなたではありませんでした';
$json=json_encode($resObj);
echo $json;
return;
}

$stt=$db->prepare('SELECT join_list,myjoin,token FROM user WHERE uid=:uid;');
$stt->bindValue(':uid',$row['from_uid']);
$stt->execute();
if(!$row3=$stt->fetch()){
$db->rollback();
$db=NULL;
$resObj['mes']='申請者が登録されてませんでした';
$json=json_encode($resObj);
echo $json;
return;
}
//$resObj['sktid']=$row3['myjoin'];
$resObj['uidToken']=$row3['token'];


if($row3['join_list']==''){$join_list_add=$row2['id'].' 0000/00/00 00:00:00';}
else{$join_list_add=','.$row2['id'].' 0000/00/00 00:00:00';}
$stt=$db->prepare('UPDATE user SET join_list=CONCAT(join_list,:join_list) WHERE uid=:uid;');
$stt->bindValue(':join_list',$join_list_add);
$stt->bindValue(':uid',$row['from_uid']);
$stt->execute();


$stt=$db->prepare('DELETE FROM mail_box WHERE id=:id;');
$stt->bindValue(':id',$row['id']);
$stt->execute();


$resObj['flg']=true;
$db->commit();
$db=NULL;
$resObj['mes']='参加申請を許可しました！';
$json=json_encode($resObj);
echo $json;

}
else{
$db->rollback();
$db=NULL;
$resObj['mes']='リクエストが見つかりませんでした';
$json=json_encode($resObj);
echo $json;
return;
}


}//参加申請だった場合
else{
$db->rollback();
$db=NULL;
$resObj['mes']='リクエストが不正です';
$json=json_encode($resObj);
echo $json;
return;
}

}
catch(Exception $e){
$db->rollback();
throw $e;
}

}
catch(Exception $e){
die('エラー１：'.$e->getMessage().'／エラー２：'.$e->getLine());
}

}//app_kyoka


/////////////////
function app_kyohi(){

$resObj['flg']=false;
$resObj['type']='app_kyohi';
$resObj['kyohi_man']=$_SESSION['uid'];
$resObj['mes']='';

try{
$db=new PDO('mysql:host='.DBHOST.';dbname=chat_sys;charset=utf8','root',DBPASS);
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

try{
$db->beginTransaction();
if(!$db->inTransaction()){throw new Exception('トランザクションに入ってません');}

if($_POST['type']=='app'){

$stt=$db->prepare('SELECT * FROM mail_box WHERE id=:id;');
$stt->bindValue(':id',$_POST['id']);
$stt->execute();
if($row=$stt->fetch()){
if($row['to_uid']!=$_SESSION['uid']){
$db->rollback();
$db=NULL;
$resObj['mes']='このリクエストはあなた宛てではありませんでした';
$json=json_encode($resObj);
echo $json;
return;
}
if($row['from_uid']!=$_POST['fromuid']){
$db->rollback();
$db=NULL;
$resObj['mes']='申請者の整合性が確認できませんでした';
$json=json_encode($resObj);
echo $json;
return;
}



$stt=$db->prepare('SELECT myjoin,token FROM user WHERE uid=:uid;');
$stt->bindValue(':uid',$row['from_uid']);
$stt->execute();
if(!$row2=$stt->fetch()){
$db->rollback();
$db=NULL;
$resObj['mes']='申請者が登録されてませんでした';
$json=json_encode($resObj);
echo $json;
return;
}
//$resObj['sktid']=$row2['myjoin'];
$resObj['uidToken']=$row2['token'];


$stt=$db->prepare('DELETE FROM mail_box WHERE id=:id;');
$stt->bindValue(':id',$row['id']);
$stt->execute();

$resObj['flg']=true;
$db->commit();
$db=NULL;
$resObj['mes']='申請を拒否しました';
$json=json_encode($resObj);
echo $json;

}
else{
$db->rollback();
$db=NULL;
$resObj['mes']='リクエストが見つかりませんでした';
$json=json_encode($resObj);
echo $json;
return;
}


}//参加申請だった場合
else{
$db->rollback();
$db=NULL;
$resObj['mes']='リクエストが不正です';
$json=json_encode($resObj);
echo $json;
return;
}

}
catch(Exception $e){
$db->rollback();
throw $e;
}

}
catch(Exception $e){
die('エラー１：'.$e->getMessage().'／エラー２：'.$e->getLine());
}

}//app_kyohi


/////////////////
function app_del(){

$resObj['flg']=false;
$resObj['type']='app_del';
$resObj['mes']='';

try{
$db=new PDO('mysql:host='.DBHOST.';dbname=chat_sys;charset=utf8','root',DBPASS);
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

try{
$db->beginTransaction();
if(!$db->inTransaction()){throw new Exception('トランザクションに入ってません');}

if($_POST['type']=='app'){

$stt=$db->prepare('SELECT * FROM mail_box WHERE id=:id;');
$stt->bindValue(':id',$_POST['id']);
$stt->execute();
if($row=$stt->fetch()){
if($row['from_uid']!=$_SESSION['uid']){
$db->rollback();
$db=NULL;
$resObj['mes']='このリクエストはあなたのものではありませんでした';
$json=json_encode($resObj);
echo $json;
return;
}
if($row['to_uid']!=$_POST['touid']){
$db->rollback();
$db=NULL;
$resObj['mes']='申請先の整合性が確認できませんでした';
$json=json_encode($resObj);
echo $json;
return;
}



$stt=$db->prepare('SELECT myjoin,token FROM user WHERE uid=:uid;');
$stt->bindValue(':uid',$row['to_uid']);
$stt->execute();
if(!$row2=$stt->fetch()){
$db->rollback();
$db=NULL;
$resObj['mes']='申請先が登録されてませんでした';
$json=json_encode($resObj);
echo $json;
return;
}
$resObj['uidToken']=$row2['token'];


$stt=$db->prepare('DELETE FROM mail_box WHERE id=:id;');
$stt->bindValue(':id',$row['id']);
$stt->execute();

$resObj['flg']=true;
$db->commit();
$db=NULL;
$resObj['mes']='申請を削除しました！';
$json=json_encode($resObj);
echo $json;

}
else{
$db->rollback();
$db=NULL;
$resObj['mes']='リクエストが見つかりませんでした';
$json=json_encode($resObj);
echo $json;
return;
}


}//参加申請だった場合
else{
$db->rollback();
$db=NULL;
$resObj['mes']='リクエストが不正です';
$json=json_encode($resObj);
echo $json;
return;
}

}
catch(Exception $e){
$db->rollback();
throw $e;
}

}
catch(Exception $e){
die('エラー１：'.$e->getMessage().'／エラー２：'.$e->getLine());
}

}//app_del


////////////////////
function start(){

try{
$db=new PDO('mysql:host='.DBHOST.';dbname=chat_sys;charset=utf8','root',DBPASS);
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

try{
$db->beginTransaction();
if(!$db->inTransaction()){throw new Exception('トランザクションに入ってません');}

$stt=$db->prepare('SELECT * FROM room_list WHERE rm_uid=:rm_uid;');
$stt->bindValue(':rm_uid',$_SESSION['uid']);
$stt->execute();
$cnt=0;
if($row=$stt->fetch()){
$roomList[]=array(
'type'=>"my",
'id'=>$row['id'],
'rname'=>e($row['rname']),
'cnt'=>$row['cnt'],
'datetime'=>$row['datetime']);
$cnt++;
}

$stt=$db->prepare('SELECT join_list FROM user WHERE uid=:uid;');
$stt->bindValue(':uid',$_SESSION['uid']);
$stt->execute();
if($row=$stt->fetch()){

$delFlg=false;
$update_joinList=[];
$join_list_array=explode(',',$row['join_list']);
foreach($join_list_array as $content){
$content_array=explode(' ',$content);
//[0]=roomid,[1]=yyyymmdd [2]=hhmmss

$stt=$db->prepare('SELECT * FROM room_list WHERE id=:id;');
$stt->bindValue(':id',$content_array[0]);
$stt->execute();
if($row2=$stt->fetch()){

$update_joinList[]=$content;

$roomList[]=array(
'type'=>"join",
'id'=>$row2['id'],
'rname'=>$row2['rname'],
'cnt'=>$row2['cnt'],
'datetime'=>$content_array[1].' '.$content_array[2]);

$cnt++;
}//参加しているルームが存在している場合
else{
$delFlg=true;
}//参加しているルームが存在していない場合

}//参加しているルームの数だけループ

if($delFlg){
$stt=$db->prepare('UPDATE user SET join_list=:join_list WHERE uid=:uid;');
$stt->bindValue(':join_list',implode(',',$update_joinList));
$stt->bindValue(':uid',$_SESSION['uid']);
$stt->execute();
}


}//userテーブルからjoin_listが取得できたか

if($cnt==0)$roomList=[];


$stt=$db->prepare('SELECT COUNT(id) AS cnt FROM mail_box WHERE to_uid=:to_uid;');
$stt->bindValue(':to_uid',$_SESSION['uid']);
$stt->execute();
if($row=$stt->fetch()){
$mailCnt=$row['cnt'];
}
else{
$mailCnt=0;
}


$resObj['roomList']=$roomList;
$resObj['mailCnt']=$mailCnt;
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

}//end func

/////////////////////
function jump(){

$res=false;

try{
$dbsys=new PDO('mysql:host='.DBHOST.';dbname=chat_sys;charset=utf8','root',DBPASS);
$dbsys->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
$dbsys->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

try{
$dbsys->beginTransaction();
if(!$dbsys->inTransaction()){throw new Exception('トランザクションに入ってません');}

if($_POST['type']=="my"){
$stt=$dbsys->prepare('SELECT * FROM room_list t1 INNER JOIN
(SELECT t4.id FROM room_list t2 INNER JOIN
(SELECT t3.id FROM room_list t3 WHERE id=:id) t4 ON t2.id=t4.id WHERE rm_uid=:rm_uid) t5 ON t1.id=t5.id;');
$stt->bindValue(':id',$_POST['id']);
$stt->bindValue(':rm_uid',$_SESSION['uid']);
$stt->execute();
if($row=$stt->fetch()){
$_SESSION['room']=$row['dbname'];
$_SESSION['roomName']=$row['rname'];
$_SESSION['rmid']=$row['id'];
$id=$row['id'];
$stt=$dbsys->prepare('UPDATE room_list SET datetime=:datetime WHERE id=:id;');
$stt->bindValue(':datetime',date("Y-m-d H:i:s"));
$stt->bindValue(':id',$id);
$stt->execute();

$db=new PDO('mysql:host='.DBHOST.';dbname='.$_SESSION['room'].';charset=utf8','root',DBPASS);
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
$db->beginTransaction();
if(!$db->inTransaction()){throw new Exception('トランザクションに入ってません');}

$stt=$db->prepare('SELECT uid FROM user WHERE uid=:uid;');
$stt->bindValue(':uid',$_SESSION['uid']);
$stt->execute();
if(!$stt->fetch()){

$kengen=0;
if($_SESSION['uid']==$row['rm_uid']){
$kengen=2;
}

$stt=$db->prepare('INSERT INTO user(uid,kengen,avt,avtid,avtname) VALUES(:uid,:kengen,:avt,:avtid,:avtname)');
$stt->bindValue(':uid',$_SESSION['uid']);
$stt->bindValue(':kengen',$kengen);
$stt->bindValue(':avt','../avatar/none.png');
$stt->bindValue(':avtid',-1);
$stt->bindValue(':avtname',$_SESSION['uid']);
$stt->execute();
}
$res=true;
}
}
else if($_POST['type']=="join"){

$stt=$dbsys->prepare('SELECT join_list FROM user WHERE uid=:uid');
$stt->bindValue(':uid',$_SESSION['uid']);
$stt->execute();
if($row=$stt->fetch()){
$updateArray=[];
$list_array=explode(',',$row['join_list']);
foreach($list_array as $cur){
$content=explode(' ',$cur);
if($_POST['id']==$content[0]){
$stt2=$dbsys->prepare('SELECT * FROM room_list WHERE id=:id');
$stt2->bindValue(':id',$content[0]);
$stt2->execute();
if($row2=$stt2->fetch()){
$_SESSION['room']=$row2['dbname'];
$_SESSION['roomName']=$row2['rname'];
$_SESSION['rmid']=$row2['id'];
$content[1]=date("Y/m/d");
$content[2]=date("H:i:s");
$updateArray[]=implode(' ',$content);
$res=true;
}
}
else{
$updateArray[]=implode(' ',$content);
}
}

if($res){
$res=false;
$updateArray=implode(',',$updateArray);
$stt=$dbsys->prepare('UPDATE user SET join_list=:join_list WHERE uid=:uid;');
$stt->bindValue(':join_list',$updateArray);
$stt->bindValue(':uid',$_SESSION['uid']);
$stt->execute();

$db=new PDO('mysql:host='.DBHOST.';dbname='.$_SESSION['room'].';charset=utf8','root',DBPASS);
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
$db->beginTransaction();
if(!$db->inTransaction()){throw new Exception('トランザクションに入ってません');}

$stt=$db->prepare('SELECT uid FROM user WHERE uid=:uid;');
$stt->bindValue(':uid',$_SESSION['uid']);
$stt->execute();
if(!$stt->fetch()){

$kengen=0;
if($_SESSION['uid']==$row2['rm_uid']){
$kengen=2;
}

$stt=$db->prepare('INSERT INTO user(uid,kengen,avt,avtid,avtname) VALUES(:uid,:kengen,:avt,:avtid,:avtname)');
$stt->bindValue(':uid',$_SESSION['uid']);
$stt->bindValue(':kengen',$kengen);
$stt->bindValue(':avt','../avatar/none.png');
$stt->bindValue(':avtid',-1);
$stt->bindValue(':avtname',$_SESSION['uid']);
$stt->execute();
}
$res=true;
}//判定がtrueの場合

}
}


$stt=$db->prepare('SELECT block FROM user WHERE uid=:uid;');
$stt->bindValue(':uid',$_SESSION['uid']);
$stt->execute();
if($row=$stt->fetch()){
if($row['block']){
unset($_SESSION['room']);
$res=false;
}
}


$dbsys->commit();
$db->commit();
$dbsys=NULL;
$db=NULL;
echo $res;
}
catch(Exception $e){
$dbsys->rollback();
if(isset($db))$db->rollback();
throw $e;
}

}
catch(Exception $e){
die('エラー１：'.$e->getMessage().'／エラー２：'.$e->getLine());
}

}//end func

///////////////////
function menuCheck(){

try{
$db=new PDO('mysql:host='.DBHOST.';dbname=chat_sys;charset=utf8','root',DBPASS);
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

try{
$db->beginTransaction();
if(!$db->inTransaction()){throw new Exception('トランザクションに入ってません');}

$stt=$db->prepare('SELECT rm_uid FROM room_list WHERE rm_uid=:rm_uid;');
$stt->bindValue(':rm_uid',$_SESSION['uid']);
$stt->execute();
if($row=$stt->fetch())$res=true;
else $res=false;


$db->commit();
$db=NULL;
echo $res;

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

///////////////////////////
function createRoom(){

if(preg_match('/[ \/\\\\{}<>"\':?&|]/',$_POST['rname'])){
$res['result']=false;
$res['mes']='※禁止文字が含まれています / \{}<>"\':?&|';
$json=json_encode($res);
echo $json;
return;
}

$checkLen=mb_strlen($_POST['rname']);
if($checkLen<=0||$checkLen>64){
$res['result']=false;
$res['mes']='ルーム名が不正です';
$json=json_encode($res);
echo $json;
return;
}

$token=createUniqueId(12);
$dbName='chat_room_'.createUniqueId(7);
$dpath='./avatar/'.$dbName;

if(!isset($token)){
$res['result']=false;
$res['mes']='エラーが発生しています';
$json=json_encode($res);
echo $json;
return;
}

try{
$db=new PDO('mysql:host='.DBHOST.';dbname=chat_sys;charset=utf8','root',DBPASS);
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
$dbList['db']=$db;

$stt=$db->prepare('SELECT COUNT(id) AS cnt FROM room_list;');
$stt->execute();
if($row=$stt->fetch()){
if($row['cnt']>=2000){
$db=NULL;
$res['result']=false;
$res['mes']='申し訳ありません。システム資源の関係により現段階でのルーム作成の上限数は２０００です';
$json=json_encode($res);
echo $json;
return;
}
}

$stt=$db->prepare('SELECT rm_uid FROM room_list WHERE rm_uid=:rm_uid;');
$stt->bindValue(':rm_uid',$_SESSION['uid']);
$ret=$stt->execute();
if($row=$stt->fetch()){//作成しない
$db=NULL;
$res['result']=false;
$res['mes']='あなたはすでにルームを持っています';
$json=json_encode($res);
echo $json;
return;
}


if(file_exists($dpath)){
$db=NULL;
$res['result']=false;
$res['mes']='作成しようとしたディレクトリはすでに存在しています';
$json=json_encode($res);
echo $json;
return;
}

if(mkdir($dpath,0777)){
chmod($dpath,0777);
}
else{
$db=NULL;
$res['result']=false;
$res['mes']='ディレクトリの作成に失敗しました';
$json=json_encode($res);
echo $json;
return;
}


$dbc=new PDO('mysql:host='.DBHOST.';charset=utf8','root',DBPASS);
$dbc->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
$dbc->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
$dbList['dbc']=$dbc;

try{

$stt=$dbc->prepare('CREATE DATABASE IF NOT EXISTS '.$dbName.' DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;');
$ret=$stt->execute();

}
catch(Exception $e){
rollBack($dbList,$dpath,$dbName);
throw $e;
}


$dbt=new PDO('mysql:host='.DBHOST.';dbname='.$dbName.';charset=utf8','root',DBPASS);
$dbt->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
$dbt->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
$dbList['db']=$db;

try{

$stt=$dbt->prepare('CREATE TABLE IF NOT EXISTS `battle_info` (
  `bcid` int(5) NOT NULL,
  `t_turn` int(5) NOT NULL,
  `turn` int(5) NOT NULL,
  `endstate` int(5) NOT NULL,
  `state` varchar(16) NOT NULL,
  `v_life` float NOT NULL,
  `v_sun` float NOT NULL,
  `v_str` float NOT NULL,
  `v_siz` float NOT NULL,
  `v_spd` float NOT NULL,
  `v_tec` float NOT NULL,
  `v_sit` float NOT NULL,
  `v_cns` float NOT NULL,
  `v_itg` float NOT NULL,
  `v_brv` float NOT NULL,
  `v_luc` float NOT NULL,
  `v_atk` float NOT NULL,
  `v_cmd` float NOT NULL,
  `v_thr` float NOT NULL,
  `v_eng` float NOT NULL,
  `v_snk` float NOT NULL,
  `v_gun` float NOT NULL,
  `v_crt` float NOT NULL,
  `v_com` float NOT NULL,
  `v_med` float NOT NULL,
  `v_snp` float NOT NULL,
  `v_app` float NOT NULL,
  `v_sns` float NOT NULL,
  `v_chm` float NOT NULL,
  `v_eco` float NOT NULL,
  `none_elem_up` float NOT NULL,
  `fire_elem_up` float NOT NULL,
  `aqua_elem_up` float NOT NULL,
  `elec_elem_up` float NOT NULL,
  `wood_elem_up` float NOT NULL,
  `aim_up` float NOT NULL,
  `cri_up` float NOT NULL,
  `mov_up` int(3) NOT NULL,
  `com_aim_up` float NOT NULL,
  `com_pow_up` float NOT NULL,
  `sun_def` float NOT NULL,
  `sun_rise` float NOT NULL,
  `pnc_str` int(3) NOT NULL,
  `pnc_siz` int(3) NOT NULL,
  `pnc_spd` int(3) NOT NULL,
  `counter` int(3) NOT NULL,
  `fire_counter` int(3) NOT NULL,
  `aqua_counter` int(3) NOT NULL,
  `elec_counter` int(3) NOT NULL,
  `wood_counter` int(3) NOT NULL,
  `fire_def` int(3) NOT NULL,
  `aqua_def` int(3) NOT NULL,
  `elec_def` int(3) NOT NULL,
  `wood_def` int(3) NOT NULL,
  `fire_pow` float NOT NULL,
  `aqua_pow` float NOT NULL,
  `elec_pow` float NOT NULL,
  `state_cnt_up` int(3) NOT NULL,
  `state_def` int(3) NOT NULL,
  `mov_eff` int(3) NOT NULL,
  `act_eff` int(3) NOT NULL,
  `wait_eff` int(3) NOT NULL,
  `pose_eff` int(3) NOT NULL,
  `shirt` int(3) NOT NULL,
  `barrier` int(3) NOT NULL,
  `auto_rcv` float NOT NULL,
  `self_rcv` int(3) NOT NULL,
  `drain` float NOT NULL,
  `elem_atk` int(3) NOT NULL,
  `no_mov_atk` int(3) NOT NULL,
  `avd_bns` int(3) NOT NULL,
  `def_bns` int(3) NOT NULL,
  `salary` int(3) NOT NULL,
  `act_ptn_bns` int(3) NOT NULL,
  `in_atk` int(3) NOT NULL,
  `out_atk` int(3) NOT NULL,
  `berserk` int(3) NOT NULL,
  PRIMARY KEY (`bcid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;');
$ret=$stt->execute();


$stt=$dbt->prepare('CREATE TABLE IF NOT EXISTS `battle_order` (
  `bcid` int(5) NOT NULL,
  `uid` varchar(100) NOT NULL,
  `side` varchar(10) NOT NULL,
  `avt` text NOT NULL,
  `oder` int(5) NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`bcid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;');
$ret=$stt->execute();


$stt=$dbt->prepare('CREATE TABLE IF NOT EXISTS `battle_panel` (
  `bcid` int(5) NOT NULL,
  `panelid` int(5) NOT NULL,
  `side` varchar(10) NOT NULL,
  `uid` varchar(100) NOT NULL,
  `cid` int(11) NOT NULL,
  `kengen` varchar(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `avt` text NOT NULL,
  `pose` varchar(16) NOT NULL,
  `past` varchar(16) NOT NULL,
  `elem` varchar(16) NOT NULL,
  `prof` text NOT NULL,
  `life` int(5) NOT NULL,
  `sun` int(5) NOT NULL,
  `str` int(5) NOT NULL,
  `siz` int(5) NOT NULL,
  `spd` int(5) NOT NULL,
  `tec` int(5) NOT NULL,
  `sit` int(5) NOT NULL,
  `cns` int(5) NOT NULL,
  `itg` int(5) NOT NULL,
  `brv` int(5) NOT NULL,
  `luc` int(5) NOT NULL,
  `atk` int(5) NOT NULL,
  `cmd` int(5) NOT NULL,
  `thr` int(5) NOT NULL,
  `eng` int(5) NOT NULL,
  `snk` int(5) NOT NULL,
  `gun` int(5) NOT NULL,
  `crt` int(5) NOT NULL,
  `com` int(5) NOT NULL,
  `med` int(5) NOT NULL,
  `snp` int(5) NOT NULL,
  `app` int(5) NOT NULL,
  `sns` int(5) NOT NULL,
  `chm` int(5) NOT NULL,
  `eco` int(5) NOT NULL,
  PRIMARY KEY (`bcid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;');
$ret=$stt->execute();


$stt=$dbt->prepare('CREATE TABLE IF NOT EXISTS `battle_panelstate` (
  `pushid` int(10) NOT NULL AUTO_INCREMENT,
  `panelid` int(5) NOT NULL,
  `type` varchar(10) NOT NULL,
  `bcid` int(10) NOT NULL,
  `content_id` int(11) NOT NULL,
  `side` varchar(10) NOT NULL,
  `atk` int(10) NOT NULL,
  `setelem` varchar(10) NOT NULL,
  UNIQUE KEY `pushturn` (`pushid`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;');
$ret=$stt->execute();


$stt=$dbt->prepare('CREATE TABLE IF NOT EXISTS `battle_skill` (
  `bcid` int(5) NOT NULL,
  `cid` int(11) NOT NULL,
  `uid` varchar(100) NOT NULL,
  `content_id` int(11) NOT NULL,
  `dupl` int(5) NOT NULL,
  `name` varchar(100) NOT NULL,
  `text` varchar(256) NOT NULL,
  `type` varchar(16) NOT NULL,
  `elem` varchar(16) NOT NULL,
  `panel_x` varchar(128) NOT NULL,
  `panel_y` varchar(128) NOT NULL,
  `atk` varchar(16) NOT NULL,
  `aim` varchar(16) NOT NULL,
  `avd` varchar(16) NOT NULL,
  `p_pow` float NOT NULL,
  `p_hit` float NOT NULL,
  `p_cri` float NOT NULL,
  `delay` int(3) NOT NULL,
  `state_normal` float NOT NULL,
  `state_yakedo` float NOT NULL,
  `state_awa` float NOT NULL,
  `state_mahi` float NOT NULL,
  `state_doku` float NOT NULL,
  `state_ice` float NOT NULL,
  `state_plant` float NOT NULL,
  `state_sleep` float NOT NULL,
  `state_anger` float NOT NULL,
  `state_sex` float NOT NULL,
  `state_fear` float NOT NULL,
  `state_strong` float NOT NULL,
  `state_dear` float NOT NULL,
  `state_heart` float NOT NULL,
  `state_cool` float NOT NULL,
  `state_inferno` float NOT NULL,
  `change_none` float NOT NULL,
  `change_fire` float NOT NULL,
  `change_aqua` float NOT NULL,
  `change_elec` float NOT NULL,
  `change_wood` float NOT NULL,
  `fire_bad_state` float NOT NULL,
  `aqua_bad_state` float NOT NULL,
  `elec_bad_state` float NOT NULL,
  `break` int(3) NOT NULL,
  `reset_barrier` int(3) NOT NULL,
  `reset_shirt` int(3) NOT NULL,
  `atk_delay` int(3) NOT NULL,
  `break_pose` int(3) NOT NULL,
  `hit_away` int(3) NOT NULL,
  `atk_dmg` float NOT NULL,
  `push` int(3) NOT NULL,
  `pull` int(3) NOT NULL,
  PRIMARY KEY (`bcid`,`content_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;');
$ret=$stt->execute();


$stt=$dbt->prepare('CREATE TABLE IF NOT EXISTS `pc` (
  `cid` int(11) NOT NULL AUTO_INCREMENT,
  `uid` varchar(100) NOT NULL,
  `kengen` varchar(10) NOT NULL,
  `level` int(4) NOT NULL,
  `name` varchar(100) NOT NULL,
  `avt` text NOT NULL,
  `past` varchar(16) NOT NULL,
  `element` varchar(16) NOT NULL,
  `prof` text NOT NULL,
  `life` int(5) NOT NULL,
  `sun` int(5) NOT NULL,
  `str` int(5) NOT NULL,
  `siz` int(5) NOT NULL,
  `spd` int(5) NOT NULL,
  `tec` int(5) NOT NULL,
  `sit` int(5) NOT NULL,
  `cns` int(5) NOT NULL,
  `itg` int(5) NOT NULL,
  `brv` int(5) NOT NULL,
  `luc` int(5) NOT NULL,
  `atk` int(5) NOT NULL,
  `cmd` int(5) NOT NULL,
  `thr` int(5) NOT NULL,
  `eng` int(5) NOT NULL,
  `snk` int(5) NOT NULL,
  `gun` int(5) NOT NULL,
  `crt` int(5) NOT NULL,
  `com` int(5) NOT NULL,
  `med` int(5) NOT NULL,
  `snp` int(5) NOT NULL,
  `app` int(5) NOT NULL,
  `sns` int(5) NOT NULL,
  `chm` int(5) NOT NULL,
  `eco` int(5) NOT NULL,
  `hosei` varchar(70) NOT NULL,
  `datetime` datetime NOT NULL,
  PRIMARY KEY (`cid`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;');
$ret=$stt->execute();


$stt=$dbt->prepare('CREATE TABLE IF NOT EXISTS `ready_panel` (
  `panelid` int(5) NOT NULL,
  `uid` varchar(100) NOT NULL,
  `cid` int(11) NOT NULL,
  `kengen` varchar(10) NOT NULL,
  `avt` text NOT NULL,
  `pose` varchar(16) NOT NULL,
  PRIMARY KEY (`panelid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;');
$ret=$stt->execute();


$stt=$dbt->prepare('CREATE TABLE IF NOT EXISTS `room_stts` (
  `stts_id` int(11) NOT NULL AUTO_INCREMENT,
  `stts_type` varchar(64) NOT NULL,
  `stts_master` varchar(100) NOT NULL,
  `stts_session` varchar(100) NOT NULL,
  `stts_session_name` varchar(100) NOT NULL,
  PRIMARY KEY (`stts_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;');
$ret=$stt->execute();


$stt=$dbt->prepare('CREATE TABLE IF NOT EXISTS `session_event` (
  `eid` int(11) NOT NULL AUTO_INCREMENT,
  `tname` varchar(70) NOT NULL,
  `ename` varchar(128) NOT NULL,
  `dice` varchar(64) NOT NULL,
  `success` varchar(256) NOT NULL,
  `fail` varchar(256) NOT NULL,
  `pc` varchar(256) NOT NULL,
  PRIMARY KEY (`eid`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;');
$ret=$stt->execute();


$stt=$dbt->prepare('CREATE TABLE IF NOT EXISTS `session_index` (
  `sid` int(11) NOT NULL AUTO_INCREMENT,
  `uid` varchar(100) NOT NULL,
  `tname` varchar(100) NOT NULL,
  `sname` varchar(100) NOT NULL,
  PRIMARY KEY (`sid`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;');
$ret=$stt->execute();


$stt=$dbt->prepare('CREATE TABLE IF NOT EXISTS `session_info` (
  `tname` varchar(70) NOT NULL,
  `cid` int(11) NOT NULL,
  `money` int(11) NOT NULL,
  `get_skill` text NOT NULL,
  `equip_skill` text NOT NULL,
  `get_ability` text NOT NULL,
  `equip_ability` text NOT NULL,
  `get_consume` text NOT NULL,
  `get_item` text NOT NULL,
  `e_life` float NOT NULL,
  `e_sun` float NOT NULL,
  `e_str` float NOT NULL,
  `e_siz` float NOT NULL,
  `e_spd` float NOT NULL,
  `e_tec` float NOT NULL,
  `e_sit` float NOT NULL,
  `e_cns` float NOT NULL,
  `e_itg` float NOT NULL,
  `e_brv` float NOT NULL,
  `e_luc` float NOT NULL,
  `e_atk` float NOT NULL,
  `e_cmd` float NOT NULL,
  `e_thr` float NOT NULL,
  `e_eng` float NOT NULL,
  `e_snk` float NOT NULL,
  `e_gun` float NOT NULL,
  `e_crt` float NOT NULL,
  `e_com` float NOT NULL,
  `e_med` float NOT NULL,
  `e_snp` float NOT NULL,
  `e_app` float NOT NULL,
  `e_sns` float NOT NULL,
  `e_chm` float NOT NULL,
  `e_eco` float NOT NULL,
  PRIMARY KEY (`tname`,`cid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;');
$ret=$stt->execute();


$stt=$dbt->prepare('CREATE TABLE IF NOT EXISTS `session_memo` (
  `mid` int(10) NOT NULL AUTO_INCREMENT,
  `tname` varchar(70) NOT NULL,
  `mname` varchar(70) NOT NULL,
  `type` varchar(10) NOT NULL,
  `memo` text NOT NULL,
  `pc` text NOT NULL,
  PRIMARY KEY (`mid`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;');
$ret=$stt->execute();


$stt=$dbt->prepare('CREATE TABLE IF NOT EXISTS `shop` (
  `tname` varchar(70) NOT NULL,
  `kind` varchar(16) NOT NULL,
  `content_id` int(11) NOT NULL,
  `rare` int(1) NOT NULL,
  `price` int(8) NOT NULL,
  `name` varchar(100) NOT NULL,
  `text` varchar(256) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;');
$ret=$stt->execute();


$stt=$dbt->prepare('CREATE TABLE IF NOT EXISTS `talk` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` varchar(100) NOT NULL,
  `type` varchar(16) NOT NULL,
  `persn` varchar(100) NOT NULL,
  `avt` text NOT NULL,
  `talk` text NOT NULL,
  `datetime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `datetime` (`datetime`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;');
$ret=$stt->execute();


$stt=$dbt->prepare('CREATE TABLE IF NOT EXISTS `user` (
  `uid` varchar(100) NOT NULL,
  `kengen` int(1) NOT NULL,
  `block` int(1) NOT NULL,
  `avt` text NOT NULL,
  `avtid` int(10) NOT NULL,
  `avtname` varchar(100) NOT NULL,
  `session` varchar(11) NOT NULL,
  `date` date NOT NULL,
  `hairu` datetime NOT NULL,
  `deru` datetime NOT NULL,
  `myjoin` varchar(32) NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;');
$ret=$stt->execute();


}
catch(Exception $e){
rollBack($dbList,$dpath,$dbName);
throw $e;
}


try{
$db->beginTransaction();
if(!$db->inTransaction()){throw new Exception('トランザクションに入ってません');}

$date=date("Y-m-d H:i:s");

$stt=$db->prepare('INSERT INTO room_list(token,rm_uid,dbname,rname,datetime) VALUES(:token,:rm_uid,:dbname,:rname,:datetime)');
$stt->bindValue(':token',$token);
$stt->bindValue(':rm_uid',$_SESSION['uid']);
$stt->bindValue(':dbname',$dbName);
$stt->bindValue(':rname',$_POST['rname']);
$stt->bindValue(':datetime',$date);
$ret=$stt->execute();
$dbid=$db->lastInsertId('id');

$db->commit();
}
catch(Exception $e){
$db->rollback();
rollBack($dbList,$dpath,$dbName);
throw $e;
}


$db=NULL;
$dbc=NULL;
$dbt=NULL;

$res['result']=true;
$res['mes']='ルームの作成に成功しました';
$res['id']=$dbid;
$res['rname']=$_POST['rname'];
$res['date']=$date;
$json=json_encode($res);
echo $json;

}
catch(Exception $e){

$res['result']=false;
$res['mes']='エラーが発生しました';
$res['mes_']='/MES：'.$e->getMessage().'/ROW：'.$e->getLine();
$json=json_encode($res);
echo $json;
return;

}

}//end func



///////////////////////////
function renameRoom(){

if(preg_match('/[ \/\\\\{}<>"\':?&|]/',$_POST['rname'])){
$res['result']=false;
$res['mes']='※禁止文字が含まれています / \{}<>"\':?&|';
$json=json_encode($res);
echo $json;
return;
}

$checkLen=mb_strlen($_POST['rname']);
if($checkLen<=0||$checkLen>64){
$res['result']=false;
$res['mes']='ルーム名が不正です';
$json=json_encode($res);
echo $json;
return;
}


try{

$dbsys=new PDO('mysql:host='.DBHOST.';dbname=chat_sys;charset=utf8','root',DBPASS);
$dbsys->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
$dbsys->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

try{
$dbsys->beginTransaction();
if(!$dbsys->inTransaction()){throw new Exception('トランザクションに入ってません');}


$stt=$dbsys->prepare('SELECT id FROM room_list WHERE rm_uid=:rm_uid;');
$stt->bindValue(':rm_uid',$_SESSION['uid']);
$stt->execute();
if(!$row=$stt->fetch()){
$dbsys->commit();
$dbsys=NULL;
$res['result']=false;
$res['mes']='あなたは部屋を持っていません';
$json=json_encode($res);
echo $json;
return;
}//自部屋があるか


$stt=$dbsys->prepare('UPDATE room_list SET rname=:rname WHERE rm_uid=:rm_uid;');
$stt->bindValue(':rname',$_POST['rname']);
$stt->bindValue(':rm_uid',$_SESSION['uid']);
$stt->execute();

$dbsys->commit();
$res['result']=true;
$res['mes']='部屋の編集に成功しました';
$json=json_encode($res);
echo $json;
return;
}
catch(Exception $e){
$dbsys->rollback();
throw $e;
}
}
catch(Exception $e){

$res['result']=false;
$res['mes']='エラーが発生しました';
$res['mes_']='/MES：'.$e->getMessage().'/ROW：'.$e->getLine();
$json=json_encode($res);
echo $json;
return;

}

}//end func



///////////////////////////
function rollBack(&$dbList,$dpath,$dbName){

///////////////////
if(isset($dbList['dbc'])){
$stt=$dbList['dbc']->prepare('DROP DATABASE IF EXISTS '.$dbName.';');
$stt->execute();
}
///////////////////
if(file_exists($dpath)){
foreach(glob($dpath.'/*')as$file){
chmod($file,0777);
unlink($file);
}
rmdir($dpath);
}

}//end func



///////////////////////////
function roomDel(){

$resObj['flg']=false;
$resObj['mes']='';

if($_SESSION['uid']=='test1'||$_SESSION['uid']=='test2'){
$resObj['mes']='すみません。テストアカウントで部屋を削除することはできません';
$json=json_encode($resObj);
echo $json;
return;
}

try{

$dbsys=new PDO('mysql:host='.DBHOST.';dbname=chat_sys;charset=utf8','root',DBPASS);
$dbsys->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
$dbsys->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

$g_db=new PDO('mysql:host='.DBHOST.';charset=utf8','root',DBPASS);
$g_db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
$g_db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);


try{
$dbsys->beginTransaction();
if(!$dbsys->inTransaction()){throw new Exception('トランザクションに入ってません');}


$stt=$dbsys->prepare('SELECT pass FROM user WHERE uid=:uid;');
$stt->bindValue(':uid',$_SESSION['uid']);
$stt->execute();
if($row=$stt->fetch()){
if(md5($_POST['pass'])!=$row['pass']){
$dbsys->rollback();
$dbsys=null;
$g_db=null;
$resObj['mes']='パスワードが一致していません';
$json=json_encode($resObj);
echo $json;
return;
}
}
else{
$dbsys->rollback();
$dbsys=null;
$g_db=null;
$resObj['mes']='アカウントが見つかりませんでした';
$json=json_encode($resObj);
echo $json;
return;
}


$stt=$dbsys->prepare('SELECT id,dbname FROM room_list WHERE rm_uid=:rm_uid;');
$stt->bindValue(':rm_uid',$_SESSION['uid']);
$stt->execute();
if(!$row=$stt->fetch()){
$g_db=NULL;
$dbsys=NULL;
$resObj['mes']='あなたは部屋を持っていません';
$json=json_encode($resObj);
echo $json;
return;
}//自部屋があるか

$dbname=$row['dbname'];
$dbid=$row['id'];
$dpath='./avatar/'.$dbname;


$stt=$dbsys->prepare('DELETE FROM room_list WHERE id=:id;');
$stt->bindValue(':id',$dbid);
$stt->execute();

$stt=$g_db->prepare('DROP DATABASE IF EXISTS '.$dbname.';');
$stt->execute();

if(file_exists($dpath)){
foreach(glob($dpath.'/*')as$file){
chmod($file,0777);
unlink($file);
}
rmdir($dpath);
}

$dbsys->commit();
}
catch(Exception $e){
$dbsys->rollback();
throw $e;
}

$resObj['flg']=true;
$resObj['room']=$dbname;
$resObj['mes']='部屋の削除に成功しました！';
$json=json_encode($resObj);
echo $json;

}
catch(Exception $e){
$resObj['mes']='エラーが発生しました';
$resObj['log']='/MES：'.$e->getMessage().'/ROW：'.$e->getLine();
$json=json_encode($resObj);
echo $json;
return;
}

}//end func


///////////////////////////
function taikai(){
$resObj['flg']=false;
$resObj['mes']='';
$db=[];
$db2=[];

if($_SESSION['uid']=='test1'||$_SESSION['uid']=='test2'){
$resObj['mes']='すみません。このアカウントはテスト用で退会できません';
$json=json_encode($resObj);
echo $json;
return;
}

try{

$dbsys=new PDO('mysql:host='.DBHOST.';dbname=chat_sys;charset=utf8','root',DBPASS);
$dbsys->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
$dbsys->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

try{
$dbsys->beginTransaction();
if(!$dbsys->inTransaction()){throw new Exception('トランザクションに入ってません');}

$stt=$dbsys->prepare('SELECT pass,join_list FROM user WHERE uid=:uid;');
$stt->bindValue(':uid',$_SESSION['uid']);
$stt->execute();
if($row=$stt->fetch()){
if(md5($_POST['pass'])!=$row['pass']){
$dbsys->rollback();
$dbsys=null;
$g_db=null;
$resObj['mes']='パスワードが一致していません';
$json=json_encode($resObj);
echo $json;
return;
}
}
else{
$dbsys->rollback();
$dbsys=null;
$g_db=null;
$resObj['mes']='アカウントが見つかりませんでした';
$json=json_encode($resObj);
echo $json;
return;
}

$join_list=$row['join_list'];
$list_array=explode(',',$join_list);
foreach($list_array as $cur){
$joinId=explode(' ',$cur)[0];

$stt=$dbsys->prepare('SELECT dbname FROM room_list WHERE id=:id;');
$stt->bindValue(':id',$joinId);
$stt->execute();
if($row=$stt->fetch()){

$dbname=$row['dbname'];

$db2[$dbname]=new PDO('mysql:host='.DBHOST.';dbname='.$dbname.';charset=utf8','root',DBPASS);
$db2[$dbname]->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
$db2[$dbname]->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
$db2[$dbname]->beginTransaction();
if(!$db2[$dbname]->inTransaction()){throw new Exception('トランザクションに入ってません');}

$stt=$db2[$dbname]->prepare('UPDATE pc SET kengen=:kengen WHERE uid=:uid;');
$stt->bindValue(':kengen','npc');
$stt->bindValue(':uid',$_SESSION['uid']);
$stt->execute();

$stt=$db2[$dbname]->prepare('DELETE FROM user WHERE uid=:uid;');
$stt->bindValue(':uid',$_SESSION['uid']);
$stt->execute();

$db[]=&$db2[$dbname];

}
}


$img_flg=false;
$mainAvtPath=NULL;
$stt=$dbsys->prepare('SELECT img FROM user WHERE uid=:uid;');
$stt->bindValue(':uid',$_SESSION['uid']);
$stt->execute();
if($row=$stt->fetch()){
if($row['img']!=''){
$img_flg=true;
$mainAvtPath=$row['img'];
}
}//メインアバターがあるか

$stt=$dbsys->prepare('DELETE FROM user WHERE uid=:uid;');
$stt->bindValue(':uid',$_SESSION['uid']);
$stt->execute();//ユーザーの削除

if($img_flg){

$filenameArray=explode('?',$mainAvtPath);
if(!unlink($filenameArray[0])){throw new Exception('エラー！');}

}//メインアバターの削除


$stt=$dbsys->prepare('SELECT id,dbname FROM room_list WHERE rm_uid=:rm_uid;');
$stt->bindValue(':rm_uid',$_SESSION['uid']);
$stt->execute();
if($row=$stt->fetch()){

$dbname=$row['dbname'];
$resObj['room']=$dbname;

$dbid=$row['id'];
$dpath='./avatar/'.$dbname;

$stt=$dbsys->prepare('DELETE FROM room_list WHERE id=:id;');
$stt->bindValue(':id',$dbid);
$stt->execute();

$g_db=new PDO('mysql:host='.DBHOST.';charset=utf8','root',DBPASS);
$g_db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
$g_db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

$stt=$g_db->prepare('DROP DATABASE IF EXISTS '.$dbname.';');
$stt->execute();

if(file_exists($dpath)){
foreach(glob($dpath.'/*')as$file){
chmod($file,0777);
unlink($file);
}
rmdir($dpath);
}

}//自部屋があるか

$dbsys->commit();
foreach($db as $curdb){$curdb->commit();}
}
catch(Exception $e){
$dbsys->rollback();
foreach($db as $curdb){$curdb->rollBack();}
throw $e;
}

$resObj['flg']=true;
$resObj['mes']='アカウントの削除に成功しました<br><br>今までのご利用ありがとうございました！';
$json=json_encode($resObj);
echo $json;

session_unset();
session_destroy();

}
catch(Exception $e){
$resObj['mes']='エラーが発生しました';
$resObj['log']='/MES：'.$e->getMessage().'/ROW：'.$e->getLine();
$json=json_encode($resObj);
echo $json;
return;
}

}//end func


///////////////
if(isset($_GET['mode'])){
// モードの振り分け
switch($_GET['mode']){

case 'app_del':
app_del();
break;

case 'app_kyohi':
app_kyohi();
break;

case 'app_kyoka':
app_kyoka();
break;

case 'checkMail':
checkMail();
break;

case 'checkApp':
checkApp();
break;

case 'appJoinRoom':
appJoinRoom();
break;

case 'roomSearch':
roomSearch();
break;

case 'start':
if(isset($_SESSION['room'])){
unset($_SESSION['room']);
}
start();
break;

case 'menuCheck':
menuCheck();
break;

case 'jump':
jump();
break;

case 'createRoom':
createRoom();
break;

case 'renameRoom':
renameRoom();
break;


case 'roomDel':
roomDel();
break;

case 'taikai':
taikai();
break;

}
}