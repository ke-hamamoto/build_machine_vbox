<?php
session_start();
require_once'./config.php';
require_once'./Encode.php';

$data=[];

if(isset($_SESSION['uid']))$data['uid']=e($_SESSION['uid']);
else $data['uid']=NULL;

if(isset($_SESSION['room']))$data['room']=$_SESSION['room'];
else $data['room']=NULL;

if(isset($_SESSION['roomName']))$data['roomName']=e($_SESSION['roomName']);
else $data['roomName']=NULL;

if(isset($_SESSION['uidToken']))$data['uidToken']=$_SESSION['uidToken'];
else $data['uidToken']=NULL;

if(isset($_SESSION['rmid']))$data['rmid']=$_SESSION['rmid'];
else $data['rmid']=NULL;

$json=json_encode($data);

echo $json;
