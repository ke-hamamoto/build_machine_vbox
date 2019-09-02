var LOAD_DIV='<div id="REPLACE" class="load_div"><ul><li class="loader"></li><li class="load_cap">Connecting…</li></ul></div>';


var LOAD_DIV_START='<div id="REPLACE" class="load_div_start"><ul><li class="loader"></li><li class="load_cap">Connecting…</li></ul></div>';


function catch_parse(e,load_rm){
//console.log(e.message+':::'+e.stack);
jQuery("body").prepend('<div id="previewAlert" class="cvPopup"><div><li class="previewAlertList clear">エラーが発生しました</li></div></div>');
setTimeout(function(){jQuery('#'+load_rm).remove();},200);
}


var getDevice=function(){
var ua=navigator.userAgent;
if(ua.indexOf('iPhone')>0||ua.indexOf('iPad')>0||ua.indexOf('iPod')>0||ua.indexOf('Android')>0||ua.indexOf('Mobile')>0){
return 'sp';
}
else{
return 'pc';
}
};
var DEVICE=getDevice();


var SOCKET_LOAD=function(){

var dff=new $.Deferred();

var NODE_SERVER = document.createElement('script');
NODE_SERVER.src = FQDN+':8080/socket.io/socket.io.js'; //ノードサーバー読み込み用
document.body.appendChild(NODE_SERVER);
NODE_SERVER.onload=function(){
dff.resolve();
};

return dff.promise();

};