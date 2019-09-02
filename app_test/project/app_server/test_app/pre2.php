<?php
session_start();
require_once'../config.php';
// ログイン状態のチェック
if(!isset($_SESSION['uid'])){
header("Location:".FQDN."/test_app/login.html");
exit;
}