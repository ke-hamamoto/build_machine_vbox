<?php
session_start();
require_once'./config.php';
require_once'./Encode.php';


function view(){
try{
$db=new PDO('mysql:host='.DBHOST.';dbname='.$_SESSION['room'].';charset=utf8','root',DBPASS);
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

try{
$db->beginTransaction();
if(!$db->inTransaction()){throw new Exception('トランザクションに入ってません');}

$stt=$db->prepare('SELECT session,uid FROM user WHERE myJoin!=:myjoin;');
$stt->bindValue(':myjoin','');
$stt->execute();

$memList=[];
while($res=$stt->fetch()){
$memObj=[];
if($res['session']==''){
$memObj['name']=e($res['uid']);
$memObj['sname']='雑談';
$memList[]=$memObj;


}//雑談の場合
else{
$stt2=$db->prepare('SELECT sname FROM session_index WHERE sid=:sid;');
$stt2->bindValue(':sid',$res['session']);
$stt2->execute();
if($res2=$stt2->fetch()){
$memObj['name']=e($res['uid']);
$memObj['sname']=e($res2['sname']);
$memList[]=$memObj;
}
}//雑談以外の場合
}//走査


$json=json_encode($memList);
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



}//end view



/////////////////////
function view_all(){
try{
$db=new PDO('mysql:host='.DBHOST.';dbname='.$_SESSION['room'].';charset=utf8','root',DBPASS);
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

try{
$db->beginTransaction();
if(!$db->inTransaction()){throw new Exception('トランザクションに入ってません');}

$stt=$db->prepare('SELECT uid,kengen,block,hairu FROM user;');
$stt->execute();

$memList=[];
while($res=$stt->fetch()){
$memObj=[];
$memObj['name']=e($res['uid']);
$memObj['kengen']=$res['kengen'];
$memObj['block']=$res['block'];
$memObj['date']=explode(' ',$res['hairu'])[0];
$memList[]=$memObj;
}//走査

$stt=$db->prepare('SELECT kengen FROM user WHERE uid=:uid;');
$stt->bindValue(':uid',$_SESSION['uid']);
$stt->execute();

$kengen=0;
while($res=$stt->fetch()){
$kengen=$res['kengen'];
}

$export['kengen']=$kengen;
$export['list']=$memList;

$json=json_encode($export);
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



}//end view_all


//////////////////
function block_enter(){
$resObj['mes']='';
$resObj['flg']=false;

$uid=$_POST['uid'];

try{
$db=new PDO('mysql:host='.DBHOST.';dbname='.$_SESSION['room'].';charset=utf8','root',DBPASS);
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

try{
$db->beginTransaction();
if(!$db->inTransaction()){throw new Exception('トランザクションに入ってません');}

$stt=$db->prepare('SELECT kengen FROM user WHERE uid=:uid;');
$stt->bindValue(':uid',$_SESSION['uid']);
$stt->execute();

if(!$row=$stt->fetch()){
$resObj['mes']='あなたの権限が特定できませんでした';
$json=json_encode($resObj);
$db->rollback();
$db=NULL;
echo $json;
return;
}//走査

if(!$row['kengen']){
$resObj['mes']='あなたにこの操作の権限はありません';
$json=json_encode($resObj);
$db->rollback();
$db=NULL;
echo $json;
return;
}

$stt=$db->prepare('SELECT kengen,block,myjoin FROM user WHERE uid=:uid;');
$stt->bindValue(':uid',$uid);
$stt->execute();

if(!$row=$stt->fetch()){
$resObj['mes']='対象が見つかりませんでした';
$json=json_encode($resObj);
$db->rollback();
$db=NULL;
echo $json;
return;
}

if($row['kengen']==2||$row['block']){
$resObj['mes']='この人はブロックできません';
$json=json_encode($resObj);
$db->rollback();
$db=NULL;
echo $json;
return;
}

$stt=$db->prepare('UPDATE user SET block=:block WHERE uid=:uid;');
$stt->bindValue(':block',1);
$stt->bindValue(':uid',$uid);
$stt->execute();

$resObj['flg']=true;
$resObj['sid']=$row['myjoin'];
$resObj['mes']=$uid.'をブロックしました';
$json=json_encode($resObj);
$db->commit();
$db=NULL;
echo $json;
}
catch(Exception $e){
$db->rollback();
$db=NULL;
throw $e;
}

}
catch(Exception $e){
die('MES__'.$e->getMessage().':::ROW__'.$e->getLine());
}

}//end block_enter

//////////////////
function block_cancel(){
$resObj['mes']='';
$resObj['flg']=false;

$uid=$_POST['uid'];

try{
$db=new PDO('mysql:host='.DBHOST.';dbname='.$_SESSION['room'].';charset=utf8','root',DBPASS);
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

try{
$db->beginTransaction();
if(!$db->inTransaction()){throw new Exception('トランザクションに入ってません');}

$stt=$db->prepare('SELECT kengen FROM user WHERE uid=:uid;');
$stt->bindValue(':uid',$_SESSION['uid']);
$stt->execute();

if(!$row=$stt->fetch()){
$resObj['mes']='あなたの権限が特定できませんでした';
$json=json_encode($resObj);
$db->rollback();
$db=NULL;
echo $json;
return;
}//走査

if(!$row['kengen']){
$resObj['mes']='あなたにこの操作の権限はありません';
$json=json_encode($resObj);
$db->rollback();
$db=NULL;
echo $json;
return;
}

$stt=$db->prepare('SELECT kengen,block FROM user WHERE uid=:uid;');
$stt->bindValue(':uid',$uid);
$stt->execute();

if(!$row=$stt->fetch()){
$resObj['mes']='対象が見つかりませんでした';
$json=json_encode($resObj);
$db->rollback();
$db=NULL;
echo $json;
return;
}

if(!$row['block']){
$resObj['mes']='この人はブロック解除できません';
$json=json_encode($resObj);
$db->rollback();
$db=NULL;
echo $json;
return;
}

$stt=$db->prepare('UPDATE user SET block=:block WHERE uid=:uid;');
$stt->bindValue(':block',0);
$stt->bindValue(':uid',$uid);
$stt->execute();

$resObj['flg']=true;
$resObj['mes']=$uid.'さんをブロック解除しました';
$json=json_encode($resObj);
$db->commit();
$db=NULL;
echo $json;
}
catch(Exception $e){
$db->rollback();
$db=NULL;
throw $e;
}

}
catch(Exception $e){
die('MES__'.$e->getMessage().':::ROW__'.$e->getLine());
}

}//end block_cancel

//////////////////
function kengen_get(){
$resObj['mes']='';
$resObj['flg']=false;

$uid=$_POST['uid'];

try{
$db=new PDO('mysql:host='.DBHOST.';dbname='.$_SESSION['room'].';charset=utf8','root',DBPASS);
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

try{
$db->beginTransaction();
if(!$db->inTransaction()){throw new Exception('トランザクションに入ってません');}

$stt=$db->prepare('SELECT kengen FROM user WHERE uid=:uid;');
$stt->bindValue(':uid',$_SESSION['uid']);
$stt->execute();

if(!$row=$stt->fetch()){
$resObj['mes']='あなたの権限が特定できませんでした';
$json=json_encode($resObj);
$db->rollback();
$db=NULL;
echo $json;
return;
}//走査

if(!$row['kengen']){
$resObj['mes']='あなたにこの操作の権限はありません';
$json=json_encode($resObj);
$db->rollback();
$db=NULL;
echo $json;
return;
}

$stt=$db->prepare('SELECT kengen,block,myjoin FROM user WHERE uid=:uid;');
$stt->bindValue(':uid',$uid);
$stt->execute();

if(!$row=$stt->fetch()){
$resObj['mes']='対象が見つかりませんでした';
$json=json_encode($resObj);
$db->rollback();
$db=NULL;
echo $json;
return;
}

if($row['kengen']){
$resObj['mes']='この人に権限付与できません';
$json=json_encode($resObj);
$db->rollback();
$db=NULL;
echo $json;
return;
}

$stt=$db->prepare('UPDATE user SET kengen=:kengen WHERE uid=:uid;');
$stt->bindValue(':kengen',1);
$stt->bindValue(':uid',$uid);
$stt->execute();

$resObj['flg']=true;
$resObj['sid']=$row['myjoin'];
$resObj['mes']=$uid.'さんに管理権限を付与しました';
$json=json_encode($resObj);
$db->commit();
$db=NULL;
echo $json;
}
catch(Exception $e){
$db->rollback();
$db=NULL;
throw $e;
}

}
catch(Exception $e){
die('MES__'.$e->getMessage().':::ROW__'.$e->getLine());
}

}//end kengen_get


//////////////////
function kengen_lost(){
$resObj['mes']='';
$resObj['flg']=false;

$uid=$_POST['uid'];

try{
$db=new PDO('mysql:host='.DBHOST.';dbname='.$_SESSION['room'].';charset=utf8','root',DBPASS);
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

try{
$db->beginTransaction();
if(!$db->inTransaction()){throw new Exception('トランザクションに入ってません');}

$stt=$db->prepare('SELECT kengen FROM user WHERE uid=:uid;');
$stt->bindValue(':uid',$_SESSION['uid']);
$stt->execute();

if(!$row=$stt->fetch()){
$resObj['mes']='あなたの権限が特定できませんでした';
$json=json_encode($resObj);
$db->rollback();
$db=NULL;
echo $json;
return;
}//走査

if(!$row['kengen']){
$resObj['mes']='あなたにこの操作の権限はありません';
$json=json_encode($resObj);
$db->rollback();
$db=NULL;
echo $json;
return;
}

$stt=$db->prepare('SELECT kengen,block,myjoin FROM user WHERE uid=:uid;');
$stt->bindValue(':uid',$uid);
$stt->execute();

if(!$row=$stt->fetch()){
$resObj['mes']='対象が見つかりませんでした';
$json=json_encode($resObj);
$db->rollback();
$db=NULL;
echo $json;
return;
}

if($row['kengen']==2||!$row['kengen']){
$resObj['mes']='この人の権限は剥奪できません';
$json=json_encode($resObj);
$db->rollback();
$db=NULL;
echo $json;
return;
}

$stt=$db->prepare('UPDATE user SET kengen=:kengen WHERE uid=:uid;');
$stt->bindValue(':kengen',0);
$stt->bindValue(':uid',$uid);
$stt->execute();

$resObj['flg']=true;
$resObj['sid']=$row['myjoin'];
$resObj['mes']=$uid.'の管理権を剥奪しました';
$json=json_encode($resObj);
$db->commit();
$db=NULL;
echo $json;
}
catch(Exception $e){
$db->rollback();
$db=NULL;
throw $e;
}

}
catch(Exception $e){
die('MES__'.$e->getMessage().':::ROW__'.$e->getLine());
}

}//end kengen_lost


///////////////
if(isset($_GET['mode'])){
// モードの振り分け
switch ($_GET['mode']){

case 'view':
view();
break;

case 'view_all':
view_all();
break;

case 'block_enter':
block_enter();
break;

case 'block_cancel':
block_cancel();
break;

case 'kengen_get':
kengen_get();
break;

case 'kengen_lost':
kengen_lost();
break;


}
}
