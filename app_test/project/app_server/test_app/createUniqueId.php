<?php

/////////////////////
function createUniqueId($length){

$miri=sprintf('%-04s',explode('.',microtime(true))[1]);
$datetime=date("Ymd").date("His").$miri;

$token="";
$codeAlphabet="ABCDEFGHIJKLMNOPQRSTUVWXYZ";
$codeAlphabet.="abcdefghijklmnopqrstuvwxyz";
$codeAlphabet.="0123456789_";

$max=strlen($codeAlphabet);
$cnt=0;
while($cnt<$length){
$token.=$codeAlphabet[random_int(0,$max-1)];
$cnt++;
}
$res=$datetime.$token;
return $res;

}//func