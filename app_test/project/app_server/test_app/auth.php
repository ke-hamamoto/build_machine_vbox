<?php
session_start();
require_once'./config.php';
require_once'./createUniqueId.php';


function login(){

session_unset();

try{
  $db=new PDO('mysql:host='.DBHOST.';dbname=chat_sys;charset=utf8','root',DBPASS);
  $db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
  $db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

try{
$db->beginTransaction();
if(!$db->inTransaction()){throw new Exception('トランザクションに入ってません');}

$result['ret']=0;

$stt=$db->prepare('SELECT mail,miss_cnt,b_list.token,b_list.datetime FROM b_list INNER JOIN user ON b_list.uid=user.uid WHERE user.uid=:uid AND b_list.ip=:ip;');
$stt->bindValue(':uid',$_POST['uid']);
$stt->bindValue(':ip',$_SERVER['HTTP_X_FORWARDED_FOR']);
$stt->execute();
if($row=$stt->fetch()){
if($row['miss_cnt']>=5){
$result['ret']=99;
$result['err']="このアクセスは一定回数のエラーを計測したためロックされました<br>※ロック解除には１日～２日お待ちください";

//////////

$json=json_encode($result);
$db->commit();
$db=NULL;
echo $json;
return;
}
}//ブラックリストがある場合

$stt=$db->prepare('SELECT * FROM user WHERE uid=:uid;');
$stt->bindValue(':uid',$_POST['uid']);
$stt->execute();
if($row=$stt->fetch()){
if($row['pass']===md5($_POST['pass'])){

$stt=$db->prepare('DELETE FROM b_list WHERE uid=:uid AND ip=:ip;');
$stt->bindValue(':uid',$_POST['uid']);
$stt->bindValue(':ip',$_SERVER['HTTP_X_FORWARDED_FOR']);
$stt->execute();

$_SESSION['uid']=$_POST['uid'];
$_SESSION['uidToken']=$row['token'];
$result['ret']++;
}//ID一致＋パスワード一致
}
else{
$result['err']="ユーザ名かパスワードが間違っています";
}

$json=json_encode($result);
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
$result['err']="ログインに失敗しました";
$result['log']='MES__'.$e->getMessage().':::ROW__'.$e->getLine();
$json=json_encode($result);
echo $json;
}
}//func login


//////////////////////
function logout(){
session_unset();
session_destroy();
}//logput


/////////////
if(isset($_GET['mode'])){
	// モードの振り分け
	switch ($_GET['mode']){
		// データを取得
		case 'login':
			login();
			break;

		case 'logout':
			logout();
			break;

	}
}