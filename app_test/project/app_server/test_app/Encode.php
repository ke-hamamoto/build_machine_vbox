<?php

function e($str,$charset='UTF-8'){
$order=["\r\n","\n","\r"];
return str_replace($order,'<br>',htmlspecialchars($str,ENT_QUOTES,$charset));
}

function eBr($str,$charset='UTF-8'){
return htmlspecialchars($str,ENT_QUOTES,$charset);
}

function d($str,$charset='UTF-8'){
return htmlspecialchars_decode($str, ENT_QUOTES, $charset);
}

function format($datetime,$format='yyyy/MM/dd'){
$ts = strtotime($datetime);
print(date($format, $ts));
}