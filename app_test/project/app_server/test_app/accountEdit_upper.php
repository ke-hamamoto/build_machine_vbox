<?php
session_start();
require_once'./config.php';
require_once'./Encode.php';
require_once'./createUniqueId.php';



////////////////
function dataGet(){
$uid=$_POST['uid'];
$resObj['flg']=false;
$resObj['mes']='';

try{
$dbsys=new PDO('mysql:host='.DBHOST.';dbname=chat_sys;charset=utf8','root',DBPASS);
$dbsys->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
$dbsys->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
//////////////////////

$stt = $dbsys->prepare('SELECT img_name,img FROM user WHERE uid=:uid');
$stt->bindValue(':uid',$uid);
$stt->execute();
if($row=$stt->fetch()){
$resObj['img']=$row['img'];
$resObj['img_name']=e($row['img_name']);

$dbsys=NULL;
$resObj['flg']=true;
$json=json_encode($resObj);
echo $json;
}
else{
$dbsys=NULL;
$resObj['mes']='あなたが見つかりません';
$json=json_encode($resObj);
echo $json;
}
}
catch(Exception $e){
$resObj['mes']='エラーが発生しました';
$resObj['log']='/MES：'.$e->getMessage().'/ROW：'.$e->getLine();
$json=json_encode($resObj);
echo $json;
return;
}

}//dataGet



////////////////
function edit_upper(){

$resObj['flg']=false;
$resObj['mes']='';

//check name
if(preg_match('/[ \/\\\\{}<>"\':?&|]/',$_POST['name'])){
$resObj['mes']='ネームに禁止文字が含まれています / \{}<>"\':?&|';
$json=json_encode($resObj);
echo $json;
return;
}
$checkLen=mb_strlen($_POST['name']);
if($checkLen<=0||$checkLen>32){
$resObj['mes']="ネームの文字数が正しくありません(0002)";
$json=json_encode($resObj);
echo $json;
return;
}

dataUpper();

}//end edit_upper


////////////////////
function dataUpper(){
$resObj['flg']=false;
$resObj['mes']='';

$uid=$_POST['uid'];

try{
$dbsys=new PDO('mysql:host='.DBHOST.';dbname=chat_sys;charset=utf8','root',DBPASS);
$dbsys->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
$dbsys->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

try{
$dbsys->beginTransaction();
if(!$dbsys->inTransaction()){throw new Exception('トランザクションに入ってません');}


$stt=$dbsys->prepare('SELECT * FROM user WHERE uid=:uid');
$stt->bindValue(':uid',$uid);
$stt->execute();
if($row=$stt->fetch()){

$old_palam=array(
'uid'=> e($row['uid']),
'img_name'=> e($row['img_name']),
'img'=> e($row['img'])
);

}
else{
$dbsys->rollback();
$dbsys=NULL;
$resObj['mes']="キャラがみつかりません";
$json=json_encode($resObj);
echo $json;
return;
}

if($old_palam['img_name']!=$_POST['name']){
$stt=$dbsys->prepare('UPDATE user SET img_name=:img_name WHERE uid=:uid;');
$stt->bindValue(':img_name',$_POST['name']);
$stt->bindValue(':uid',$uid);
$stt->execute();
}//名前の変更

if($_POST['g_cutter_flg']==1){

$imageData=$_POST['image'];
if(strlen($imageData)<40000&&$imgType=exif_imagetype($imageData)){

$imageData=str_replace('data:image/jpeg;base64,','',$imageData);
$dec64=base64_decode($imageData);

if($imgType==2){

if($old_palam['img']!=''){

$filename=$old_palam['img'];
$filenameArray=explode('?',$filename);
$filename=$filenameArray[0];

$filename2=$filename.'?'.createUniqueId(0);

$stt=$dbsys->prepare('UPDATE user SET img=:img WHERE uid=:uid;');
$stt->bindValue(':img',$filename2);
$stt->bindValue(':uid',$uid);
$stt->execute();

chmod($filename,0777);
$fp=fopen($filename,'w');
fwrite($fp,$dec64);
fclose($fp);

}//メインアバタが空ではない=>上書き
else{

$filename='avatar/main_avt/'.createUniqueId(12).'.jpg';

$filename2=$filename.'?'.createUniqueId(0);

$stt=$dbsys->prepare('UPDATE user SET img=:img WHERE uid=:uid;');
$stt->bindValue(':img',$filename2);
$stt->bindValue(':uid',$uid);
$stt->execute();

$fp=fopen($filename,'w');
fwrite($fp,$dec64);
fclose($fp);

}//差し替え新規
}
else{
$resObj['mes'].='「画像エラーのため画像の差し替えは行われませんでした」';
}//jpgでない
}
else{
$resObj['mes'].='「画像エラーのため画像の差し替えは行われませんでした」';
}//ファイルがでかすぎる

}//アバターの変更
else{
$filename=$old_palam['avt'];
}//アバターの変更がない場合

$dbsys->commit();
}
catch(Exception $e){
$dbsys->rollback();
throw $e;
}


$dbsys=NULL;
$resObj['flg']=true;
$resObj['mes'].='アカウント編集を完了しました！';
$json=json_encode($resObj);
echo $json;
return;
}
catch(Exception $e){
$resObj['flg']=false;
$resObj['mes']='エラーが発生しました';
$resObj['log']='/MES：'.$e->getMessage().'/ROW：'.$e->getLine();
$json=json_encode($resObj);
echo $json;
return;
}

}//end upper

///////////////
if(isset($_GET['mode'])){
// モードの振り分け
switch($_GET['mode']){

case 'dataGet':
dataGet();
break;

case 'edit_upper':
edit_upper();
break;

}
}
