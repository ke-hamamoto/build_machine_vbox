<?php
session_start();
require_once'../config.php';
// ログイン状態のチェック
if(!isset($_SESSION['uid'])||!isset($_SESSION['room'])){
header("Location:".FQDN."/test_app/publics/roomList.html");
exit;
}