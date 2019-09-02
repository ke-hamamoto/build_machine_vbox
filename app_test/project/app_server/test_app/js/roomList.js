var ROOMLIST_FUNC=function(){

jQuery(function($){


(function(){//スタート処理

var load_rm="load_roomListPage";
$('body').prepend(LOAD_DIV_START.replace('REPLACE',load_rm));

$.ajax({
type:'POST',
url:'../roomList.php?mode=start',
timeout:TIMEOUT
})
.then(function(data){

try{

//console.log(data);
data=JSON.parse(data);

$('#curJoinRoomNum').text(data.roomList.length);

data.roomList.sort(function(a,b){
if(+Date.parse(a.datetime)<+Date.parse(b.datetime))return 1;
if(+Date.parse(a.datetime)>+Date.parse(b.datetime))return -1;
return 0;
});
$.each(data.roomList,function(){
$('#joinRoom').append('<li data-roomListId="'+
this.id+
'" data-roomListType="'+
this.type+
'">'+
this.rname+
'<span class="roomlistcurmem">'+this.cnt+'</span>人'+
'<span>'+
this.datetime+
'</span></li>');
});

if(data.mailCnt){
$('#roomListNews').attr('data-mailCnt',data.mailCnt);
$('#roomListNews span.new').css({'opacity':1});
}
else{$('#roomListNews').attr('data-mailCnt',data.mailCnt);}

setTimeout(function(){$('#'+load_rm).remove();},200);
}catch(e){catch_parse(e,load_rm);}

}//true
,
function(jqXHR,textStatus,errorThrown){
alert(textStatus+"_"+errorThrown);
setTimeout(function(){$('#'+load_rm).remove();},200);
}//false
);

}());


////////////////
$(document).on('click','#app_search_close',function(event){
$('body').css({'overflow-y':'auto'});
$('.app_search_space').remove();
});//申請キャンセル

////////////////
$(document).on('click','#roomListApp',function(event){//申請をクリック

$('body').css({'overflow-y':'hidden'});

var app_search_space='<div class="app_search_space"><h3>他のルームへ参加申請をする</h3><h4>ルーム主のユーザIDで探す</h4>'+
'<input type="text" id="userid" name="userid" size="20" maxlength="80"/><span class="error"></span>'+
'<ul id="app_result_list" class="tanzakuList"></ul>'+
'<div><p class="btn" id="app_search_retutn">検索</p><p class="btn" id="app_search_close">閉じる</p></div>'+
'</div>';

$('body').prepend(app_search_space);

});//申請クリック


////////////////
$(document).on('click','#app_search_retutn',function(event){//申請をクリック

$('.error').empty();
var flg=false;
var posUid=uidLen=$('input#userid').val();
var uidLen=$('input#userid').val().length;

if(uidLen<1){
$('.app_search_space .error').text('※IDが空です');
}
else if(uidLen>64){
$('.app_search_space .error').text('※64文字以上です');
}
else{
flg=true;
}

if(flg){

var load_rm="load_appSearchRetutn";
$('body').prepend(LOAD_DIV.replace('REPLACE',load_rm));

$.ajax({
type:'POST',
url:'../roomList.php?mode=roomSearch',
data:{'uid':posUid},
timeout:TIMEOUT
})
.then(
function(data){

try{

//console.log(data);
data=JSON.parse(data);
//console.log(data);

if(data.flg){
$('#app_result_list').empty();
$.each(data.roomList,function(){

$('#app_result_list').append('<li data-id='+this.id+
' data-uid='+this.uid+' data-rname='+this.rname+'>'+
this.rname+
'<span>主:'+
this.uid+
'</span></li>');

});
}//エラーなし正常系の場合

else{
$("body").prepend('<div id="previewAlert" class="cvPopup"><div><p class="previewAlertList clear">'+data.mes+'</p></div></div>');
}//エラーなし異常系の場合

setTimeout(function(){$('#'+load_rm).remove();},200);
}catch(e){catch_parse(e,load_rm);}

}//true
,
function(jqXHR,textStatus,errorThrown){
alert(textStatus+"_"+errorThrown);
setTimeout(function(){$('#'+load_rm).remove();},200);
}//false
);//then roomList.php roomSearch

}

});//検索（申請）

////////////////
var g_posApp={};
$(document).on('click','#app_result_list li',function(event){

g_posApp.id=$(this).attr('data-id');
g_posApp.uid=$(this).attr('data-uid');

$("body").prepend('<div id="previewAlert" class="cvPopup"><div><p class="previewAlertList clear">このルームへの参加を申請しますか？<br><span style="color:#0ff;">※ルーム名：'+
$(this).attr('data-rname')+
'</span></p>'+
'<p class="previewAlertList clear clickar" id="app_return">はい</p>'+
'<p class="previewAlertList clear clickar">いいえ</p></div></div>');
});//リストを押下で申請を確認

/////////////////
$(document).on('click','#app_return',function(event){

var load_rm="load_appReturn";
$('body').prepend(LOAD_DIV.replace('REPLACE',load_rm));

$.ajax({
type:'POST',
url:'../roomList.php?mode=appJoinRoom',
data:{'id':g_posApp.id,'uid':g_posApp.uid},
timeout:TIMEOUT
})
.then(
function(data){

try{

//console.log(data);
data=JSON.parse(data);
//console.log(data);

if(data.flg){

socketio.emit("mailBoxRes",data);

$('body').css({'overflow-y':'auto'});
$('.app_search_space').remove();

$("body").prepend('<div id="previewAlert" class="cvPopup"><div><p class="previewAlertList clear">'+data.mes+'</p></div></div>');

}//エラーなし正常系の場合

else{
$("body").prepend('<div id="previewAlert" class="cvPopup"><div><p class="previewAlertList clear">'+data.mes+'</p></div></div>');
}//エラーなし異常系の場合

setTimeout(function(){$('#'+load_rm).remove();},200);
}catch(e){catch_parse(e,load_rm);}

}//true
,
function(jqXHR,textStatus,errorThrown){
alert(textStatus+"_"+errorThrown);
setTimeout(function(){$('#'+load_rm).remove();},200);
}//false
);//then roomList.php roomSearch

});//申請の実行



////////////////
$(document).on('click','#app_news_close',function(event){
$('body').css({'overflow-y':'auto'});
$('.app_news_space').remove();
});//申請キャンセル

//////////////////
$(document).on('click','#roomListNews',function(event){

$('body').css({'overflow-y':'hidden'});

var app_news_space='<div class="app_news_space"><h3>お知らせ一覧</h3>'+
'<ul id="app_news_list" class="tanzakuList"></ul>'+
'<div><p class="btn" id="app_news_close">閉じる</p></div>'+
'</div>';

$('body').prepend(app_news_space);

var load_rm="load_roomListNews";
$('body').prepend(LOAD_DIV.replace('REPLACE',load_rm));

$.ajax({
type:'POST',
url:'../roomList.php?mode=checkMail',
timeout:TIMEOUT
})
.then(function(data){

try{

//console.log(data);
data=JSON.parse(data);
//console.log(data);

if(!data.length){
$('#roomListNews span.new').css({'opacity':0});
$('#app_news_list').before('<p>お知らせはありません</p>');
}
else{
$('#roomListNews span.new').css({'opacity':1});
}

$.each(data,function(){

var mes='';
if(this.type=='app'){
mes=this.from_uid+'さんからあなたの部屋に参加申請が来ています';
}
else if(this.type=='inv'){
mes=this.from_uid+'さんの部屋にあなたへ参加招待が来ています';
}

$('#app_news_list').append('<li class="clear" data-mailListId="'+
this.id+
'" data-mailListType="'+
this.type+
'" data-mailListToUid="'+
this.to_uid+
'" data-mailListFromUid="'+
this.from_uid+
'">'+
mes+
'<div style="float:right;"><span class="kyohi_btn">拒否</span><span class="kyoka_btn">許可</span></div></li>');

});

setTimeout(function(){$('#'+load_rm).remove();},200);
}catch(e){catch_parse(e,load_rm);}

}//true
,
function(jqXHR,textStatus,errorThrown){
alert(textStatus+"_"+errorThrown);
setTimeout(function(){$('#'+load_rm).remove();},200);
}//false
);

});//お知らせを見る

//////////////////
var g_posMail={};
$(document).on('click','.kyoka_btn',function(event){

g_posMail.id=$(this).closest('li').attr('data-mailListId');
g_posMail.type=$(this).closest('li').attr('data-mailListType');
g_posMail.fromuid=$(this).closest('li').attr('data-mailListFromUid');

$("body").prepend('<div id="previewAlert" class="cvPopup"><div><p class="previewAlertList clear">あなたの部屋に'+g_posMail.fromuid+'さんが参加するのを許可しますか？<br><span style="color:#f00;">※許可するとあなたの部屋に'+
g_posMail.fromuid+
'さんが出入りできるようになります</span></p>'+
'<p class="previewAlertList clear clickar" id="app_kyoka_return">はい</p>'+
'<p class="previewAlertList clear clickar">いいえ</p></div></div>');

});//許可

//////////////////
$(document).on('click','.kyohi_btn',function(event){

g_posMail.id=$(this).closest('li').attr('data-mailListId');
g_posMail.type=$(this).closest('li').attr('data-mailListType');
g_posMail.fromuid=$(this).closest('li').attr('data-mailListFromUid');

$("body").prepend('<div id="previewAlert" class="cvPopup"><div><p class="previewAlertList clear">あなたの部屋に'+$(this).closest('li').attr('data-mailListFromUid')+'さんが参加するのを拒否しますか？<br><span style="color:#f00;">※拒否すると一覧からこの申請は消えます</span></p>'+
'<p class="previewAlertList clear clickar" id="app_kyohi_return">はい</p>'+
'<p class="previewAlertList clear clickar">いいえ</p></div></div>');

});//拒否


///////////////////
$(document).on('click','#app_kyohi_return',function(event){

var load_rm="load_kyohiReturn";
$('body').prepend(LOAD_DIV.replace('REPLACE',load_rm));

$.ajax({
type:'POST',
url:'../roomList.php?mode=app_kyohi',
data:{'id':g_posMail.id,'type':g_posMail.type,'fromuid':g_posMail.fromuid},
timeout:TIMEOUT
})
.then(function(data){

try{

//console.log(data);
data=JSON.parse(data);
//console.log(data);

if(data.flg){

socketio.emit("mailBoxRes",data);

$("body").prepend('<div id="previewAlert" class="cvPopup"><div><p class="previewAlertList clear">'+data.mes+'</p></div></div>');

$('#app_news_list li').each(function(){
if($(this).attr('data-maillistid')==g_posMail.id){
$(this).remove();

var mailCnt=+$('#roomListNews').attr('data-mailCnt')-1;
if(mailCnt<1){
mailCnt=0;
$('#roomListNews span.new').css({'opacity':0});
}
$('#roomListNews').attr('data-mailCnt',mailCnt);

}
});

}
else{
$("body").prepend('<div id="previewAlert" class="cvPopup"><div><p class="previewAlertList clear">'+data.mes+'</p></div></div>');
}

setTimeout(function(){$('#'+load_rm).remove();},200);
}catch(e){catch_parse(e,load_rm);}

}//true
,
function(jqXHR,textStatus,errorThrown){
alert(textStatus+"_"+errorThrown);
setTimeout(function(){$('#'+load_rm).remove();},200);
}//false
);



});//拒否ボタン押下時


///////////////////
$(document).on('click','#app_kyoka_return',function(event){

var load_rm="load_kyokaReturn";
$('body').prepend(LOAD_DIV.replace('REPLACE',load_rm));

$.ajax({
type:'POST',
url:'../roomList.php?mode=app_kyoka',
data:{'id':g_posMail.id,'type':g_posMail.type,'fromuid':g_posMail.fromuid},
timeout:TIMEOUT
})
.then(function(data){

try{

//console.log(data);
data=JSON.parse(data);
//console.log(data);
if(data.flg){

socketio.emit("mailBoxRes",data);

$("body").prepend('<div id="previewAlert" class="cvPopup"><div><p class="previewAlertList clear">'+data.mes+'</p></div></div>');

$('#app_news_list li').each(function(){
if($(this).attr('data-maillistid')==g_posMail.id){
$(this).remove();

var mailCnt=+$('#roomListNews').attr('data-mailCnt')-1;
if(mailCnt<1){
mailCnt=0;
$('#roomListNews span.new').css({'opacity':0});
}
$('#roomListNews').attr('data-mailCnt',mailCnt);

}
});

}
else{
$("body").prepend('<div id="previewAlert" class="cvPopup"><div><p class="previewAlertList clear">'+data.mes+'</p></div></div>');
}

setTimeout(function(){$('#'+load_rm).remove();},200);
}catch(e){catch_parse(e,load_rm);}

}//true
,
function(jqXHR,textStatus,errorThrown){
alert(textStatus+"_"+errorThrown);
setTimeout(function(){$('#'+load_rm).remove();},200);
}//false
);

});//許可ボタン押下時



///////////////////
$(document).on('click','#app_stts_close',function(event){
$('body').css({'overflow-y':'auto'});
$('.app_stts_space').remove();
});//申請状態の確認の削除

///////////////////
$(document).on('click','#roomListAppStts',function(event){

$('body').css({'overflow-y':'hidden'});

var app_stts_space='<div class="app_stts_space"><h3>申請状態一覧</h3>'+
'<ul id="app_stts_list" class="tanzakuList"></ul>'+
'<div><p class="btn" id="app_stts_close">閉じる</p></div>'+
'</div>';
$('body').prepend(app_stts_space);

var load_rm="load_appStts";
$('body').prepend(LOAD_DIV.replace('REPLACE',load_rm));

$.ajax({
type:'POST',
url:'../roomList.php?mode=checkApp',
timeout:TIMEOUT
})
.then(function(data){

try{

//console.log(data);
data=JSON.parse(data);
//console.log(data);

if(!data.length){
$('#app_stts_list').before('<p>申請中のものはありません</p>');
}

$.each(data,function(){

var mes='';
if(this.type=='app'){
mes='返事待ち：'+this.to_uid+'さんの部屋にあなたは申請中です';
}
else if(this.type=='inv'){
mes='返事待ち：'+this.to_uid+'さんにあなたの部屋へ招待中です';
}

$('#app_stts_list').append('<li class="clear" data-mailListId="'+
this.id+
'" data-mailListType="'+
this.type+
'" data-mailListToUid="'+
this.to_uid+
'" data-mailListFromUid="'+
this.from_uid+
'">'+
mes+
'<div style="float:right;"><span class="delApp_btn">削除</span></div></li>');

});

setTimeout(function(){$('#'+load_rm).remove();},200);
}catch(e){catch_parse(e,load_rm);}

}//true
,
function(jqXHR,textStatus,errorThrown){
alert(textStatus+"_"+errorThrown);
setTimeout(function(){$('#'+load_rm).remove();},200);
}//false
);

});//申請状況の確認クリック

///////////////
$(document).on('click','.delApp_btn',function(event){

g_posMail.id=$(this).closest('li').attr('data-mailListId');
g_posMail.type=$(this).closest('li').attr('data-mailListType');
g_posMail.touid=$(this).closest('li').attr('data-mailListToUid');

$("body").prepend('<div id="previewAlert" class="cvPopup"><div><p class="previewAlertList clear">'+$(this).closest('li').attr('data-mailListToUid')+'さんへの申請を削除しますか？<br><span style="color:#f00;">※削除すると相手側への申請が取り消されます</span></p>'+
'<p class="previewAlertList clear clickar" id="app_del_return">はい</p>'+
'<p class="previewAlertList clear clickar">いいえ</p></div></div>');

});//削除



///////////////////
$(document).on('click','#app_del_return',function(event){

var load_rm="load_appDel";
$('body').prepend(LOAD_DIV.replace('REPLACE',load_rm));

$.ajax({
type:'POST',
url:'../roomList.php?mode=app_del',
data:{'id':g_posMail.id,'type':g_posMail.type,'touid':g_posMail.touid},
timeout:TIMEOUT
})
.then(function(data){

try{

//console.log(data);
data=JSON.parse(data);
//console.log(data);
if(data.flg){

socketio.emit("mailBoxRes",data);

$("body").prepend('<div id="previewAlert" class="cvPopup"><div><p class="previewAlertList clear">'+data.mes+'</p></div></div>');

$('#app_stts_list li').each(function(){
if($(this).attr('data-maillistid')==g_posMail.id){
$(this).remove();
}
});

}
else{
$("body").prepend('<div id="previewAlert" class="cvPopup"><div><p class="previewAlertList clear">'+data.mes+'</p></div></div>');
}

setTimeout(function(){$('#'+load_rm).remove();},200);
}catch(e){catch_parse(e,load_rm);}

}//true
,
function(jqXHR,textStatus,errorThrown){
alert(textStatus+"_"+errorThrown);
setTimeout(function(){$('#'+load_rm).remove();},200);
}//false
);

});//削除ボタン押下時



///////////////////申請結果の通知
socketio.on("mailBoxRes",function(socket){
if(socket.type=='app'){
var mailCnt=+$('#roomListNews').attr('data-mailCnt')+1;
$('#roomListNews').attr('data-mailCnt',mailCnt);
$('#roomListNews span.new').css({'opacity':1});
}

else if(socket.type=='app_kyoka'){
$('#joinRoom').append('<li data-roomListId="'+
socket.roomId+
'" data-roomListType="join">'+
socket.roomName+
'<span>0000/00/00 00:00:00</span></li>');
$("body").prepend('<div id="previewAlert" class="cvPopup"><div><p class="previewAlertList clear">'+
socket.roomName+
'への参加申請が許可されました！'+
'</p></div></div>');
$('#curJoinRoomNum').text($('#joinRoom li').length);
}

else if(socket.type=='app_del'){
var mailCnt=+$('#roomListNews').attr('data-mailCnt')-1;
if(mailCnt<1){
mailCnt=0;
$('#roomListNews span.new').css({'opacity':0});
}
$('#roomListNews').attr('data-mailCnt',mailCnt);
}

else if(socket.type=='app_kyohi'){
$("body").prepend('<div id="previewAlert" class="cvPopup"><div><p class="previewAlertList clear">'+socket.kyohi_man+'さんから申請を拒否されました</p></div></div>');
}

});//申請結果の通知



////////////////////
$(document).on('click','#roomListOption',function(event){//オプションクリック

if($(event.target).closest('.optionList').length==0){

var optionList='<ul id="optionUl">'+
'<li id="roomListApp" class="optionList">他ルームに参加</li>'+
'<li id="roomListAppStts" class="optionList">申請状態の確認</li>'+
'<li id="roomListAccountDel" class="optionList">ゲシュテム退会</li>'+
'<li id="roomListLogOut" class="optionList">ログアウト</li>'+
'<li id="roomListClose" class="optionList">閉じる</li>'+
'</ul>';
$('#optionUl').remove();
$(this).append(optionList);

var load_rm="load_roomListOption";
$('body').prepend(LOAD_DIV.replace('REPLACE',load_rm));

$.ajax({
type:'POST',
url:'../roomList.php?mode=menuCheck',
timeout:TIMEOUT
})
.then(function(data){
if(data==true)$('#optionUl').prepend(
'<li id="roomListRename" class="optionList">マイルーム編集</li>'+
'<li id="roomListDelete" class="optionList">マイルーム削除</li>');
else if(data==false)$('#optionUl').prepend('<li id="roomListCreate" class="optionList">マイルーム作成</li>');
setTimeout(function(){$('#'+load_rm).remove();},200);
}//true
,
function(jqXHR,textStatus,errorThrown){
alert(textStatus+"_"+errorThrown);
setTimeout(function(){$('#'+load_rm).remove();},200);
}//false
);

}

});

$(document).on('click',function(event){//対象を決めないクリック処理
if($(event.target).closest('#optionUl,#roomListOption,.cvPopup').length==0){
$('#optionUl').remove();//オプションリストを閉じる
}
});

$(document).on('click','#roomListLogOut',function(event){//ログアウトクリック
$("body").prepend('<div id="previewAlert" class="cvPopup"><div><p class="previewAlertList clear">本当にログアウトしますか？</p>'+
'<p class="previewAlertList clear clickar" id="logoutBtn">はい</p>'+
'<p class="previewAlertList clear clickar">いいえ</p></div></div>');
});

$(document).on('click','#joinRoom li',function(event){//リストクリック

var roomId=$(this).attr('data-roomListId');
var roomType=$(this).attr('data-roomListType');

var load_rm="load_roomListJump";
$('body').prepend(LOAD_DIV.replace('REPLACE',load_rm));

$.ajax({
type:'POST',
url:'../roomList.php?mode=jump',
data:{id:roomId,type:roomType},
timeout:TIMEOUT
})
.then(function(data){

setTimeout(function(){$('#'+load_rm).remove();},200);

if(data)location.href=FQDN+'/test_app/public/index.html';
else{
$("body").prepend('<div id="previewAlert" class="cvPopup"><div><p class="previewAlertList clear">入室できません</p></div></div>');
}

}//true
,
function(jqXHR,textStatus,errorThrown){
alert(textStatus+"_"+errorThrown);
setTimeout(function(){$('#'+load_rm).remove();},200);
}//false
);

});

//////////////////////
$(document).on('click','#roomListCreate',function(event){//部屋作成
$('#createRoomSpace').remove();
$("body").prepend('<div id="createRoomSpace">'+
'<h4>新規ルーム名</h4>'+
'<input type="text" name="createRoomName" value="" maxlength="60" />'+
'<div id="createRoomSpace_return" class="c_c_btn">決定</div>'+
'<div id="createRoomSpace_close" class="c_c_btn">閉じる</div>'+
'</div>');
$('body').css({'overflow-y':'hidden'});
});

$(document).on('click','#createRoomSpace_return',function(event){

var rname=$('[name="createRoomName"]').val();
$("#createRoomSpace").remove();

var load_rm="load_createRoom";
$('body').prepend(LOAD_DIV.replace('REPLACE',load_rm));

$.ajax({
type:'POST',
url:'../roomList.php?mode=createRoom',
data:{rname:rname},
timeout:TIMEOUT
})
.then(
function(data){

try{

//console.log(data)
data=JSON.parse(data);
//console.log(data)

if(data.result==true){
$("body").prepend('<div id="previewAlert" class="cvPopup"><div><p class="previewAlertList clear">ルームの作成に成功しました</p></div></div>');

$('#joinRoom').prepend('<li data-roomListId="'+
data.id+
'" data-roomListType="my">'+
data.rname+
'<span class="roomlistcurmem">0</span>人'+
'<span>'+data.date+'</span></li>');

$('#curJoinRoomNum').text($('#joinRoom li').length);

}
if(data.result==false){
$("body").prepend('<div id="previewAlert" class="cvPopup"><div><p class="previewAlertList clear">ルームの作成に失敗しました</p>'+
'<p class="previewAlertList clear">'+data.mes+'</p></div></div>');
}

setTimeout(function(){$('#'+load_rm).remove();},200);
}catch(e){catch_parse(e,load_rm);}

}//true
,
function(jqXHR,textStatus,errorThrown){
alert(textStatus+"_"+errorThrown);
setTimeout(function(){$('#'+load_rm).remove();},200);
}//false
);
});

$(document).on('click','#createRoomSpace_close',function(event){
$('body').css({'overflow-y':'auto'});
$('#createRoomSpace').remove();
});



//////////////////////
$(document).on('click','#roomListRename',function(event){//部屋リネーム
$('#renameRoomSpace').remove();
$("body").prepend('<div id="renameRoomSpace">'+
'<h4>新たなルーム名</h4>'+
'<input type="text" name="renameRoomName" value="" maxlength="60" />'+
'<div id="renameRoomSpace_return" class="c_c_btn">決定</div>'+
'<div id="renameRoomSpace_close" class="c_c_btn">閉じる</div>'+
'</div>');
$('body').css({'overflow-y':'hidden'});
});//部屋リネーム開く

$(document).on('click','#renameRoomSpace_return',function(event){

var rname=$('[name="renameRoomName"]').val();
$("#renameRoomSpace").remove();

var load_rm="load_renameRoom";
$('body').prepend(LOAD_DIV.replace('REPLACE',load_rm));

$.ajax({
type:'POST',
url:'../roomList.php?mode=renameRoom',
data:{rname:rname},
timeout:TIMEOUT
})
.then(
function(data){

try{

//console.log(data)
data=JSON.parse(data);
//console.log(data)

if(data.result==true){
$("body").prepend('<div id="previewAlert" class="cvPopup"><div><p class="previewAlertList clear">ルームの編集に成功しました</p></div></div>');

$('#joinRoom li').each(function(){
if($(this).attr('data-roomListType')=='my'){
var rename_id=$(this).attr('data-roomListId');
var rename_num=$(this).find('span:nth-of-type(1)').text();
var rename_time=$(this).find('span:nth-of-type(2)').text();
$(this).remove();

$('#joinRoom').prepend('<li data-roomListId="'+
rename_id+
'" data-roomListType="my">'+
rname+
'<span class="roomlistcurmem">'+rename_num+'</span>人'+
'<span>'+
rename_time+
'</span></li>');

return false;
}
});

}
if(data.result==false){
$("body").prepend('<div id="previewAlert" class="cvPopup"><div><p class="previewAlertList clear">ルームの編集に失敗しました</p>'+
'<p class="previewAlertList clear">'+data.mes+'</p></div></div>');
}

setTimeout(function(){$('#'+load_rm).remove();},200);
}catch(e){catch_parse(e,load_rm);}

}//true
,
function(jqXHR,textStatus,errorThrown){
alert(textStatus+"_"+errorThrown);
setTimeout(function(){$('#'+load_rm).remove();},200);
}//false
);
});//部屋リネーム決定

$(document).on('click','#renameRoomSpace_close',function(event){
$('body').css({'overflow-y':'auto'});
$('#renameRoomSpace').remove();
});//部屋リネーム閉じる



///////////////
$(document).on('click','#roomListDelete',function(event){

$("body").prepend('<div id="previewAlert" class="cvPopup"><div><p class="previewAlertList clear">あなたの部屋を削除しますか？<br><span style="color:#0ff;">対象ルーム：'+$('#joinRoom li[data-roomListType="my"]').text()+'</span><br><span style="color:#f00;">※削除すると部屋のデータはすべて削除されて元には戻りません</span></p>'+
'<p class="previewAlertList clear clickar" id="room_del_return">はい</p>'+
'<p class="previewAlertList clear clickar">いいえ</p></div></div>');

});//部屋の削除


///////////////
$(document).on('click','#room_del_return',function(event){

var roomDel_space=
'<div id="roomDel_space"><form><h3>部屋削除：登録パスワードを入力してください</h3><div class="container error"></div><div class="container"><label>パスワード</label><br/><input type="password" id="roomDel_pass" name="roomDel_pass" size="20" maxlength="80"/></div><div class="container"><label>パスワード：確認</label><br/><input type="password" id="roomDel_pass_re" name="roomDel_pass_re" size="20" maxlength="80"/></div><input id="roomDel_submit" type="submit" name="submit" value="部屋の削除"/><div id="roomDel_cancel">削除をやめる</div></form></div>';

$('body').prepend(roomDel_space);

});//部屋の削除2


///////////////
$(document).on('click','#roomDel_submit',function(event){
event.preventDefault();


$('.error').empty();

var pass=$('[name="roomDel_pass"]').val();
var repass=$('[name="roomDel_pass_re"]').val();

if(pass==''||repass==''){
$('.error').append('パスワードが空です');
return;
}
if(pass!=repass){
$('.error').append('パスワードと確認パスワードが一致していません');
return;
}


var load_rm="load_delRoom";
$('body').prepend(LOAD_DIV.replace('REPLACE',load_rm));

$.ajax({
type:'POST',
url:'../roomList.php?mode=roomDel',
data:{"pass":pass},
timeout:TIMEOUT
})
.then(function(data){

try{

$('#roomDel_space').remove();

//console.log(data);
data=JSON.parse(data);

if(data.flg){

socketio.emit("leave_room",data.room);

$("body").prepend('<div id="previewAlert" class="cvPopup"><div><p class="previewAlertList clear">'+data.mes+'</p></div></div>');

$('#joinRoom li').each(function(){
if($(this).attr('data-roomlisttype')=='my'){
$(this).remove();
}
});

$('#curJoinRoomNum').text($('#joinRoom li').length);

}
else{
$("body").prepend('<div id="previewAlert" class="cvPopup"><div><p class="previewAlertList clear">部屋の削除はできませんでした<br><span style="color:#f00;">'+data.mes+'</span></p></div></div>');
}

setTimeout(function(){$('#'+load_rm).remove();},200);
}catch(e){catch_parse(e,load_rm);}

}//true
,
function(jqXHR,textStatus,errorThrown){
alert(textStatus+"_"+errorThrown);
setTimeout(function(){$('#'+load_rm).remove();},200);
}//false
);


});//部屋の削除の決定

///////////////
$(document).on('click','#roomDel_cancel',function(event){
//g_post_editAccount={};
$('#roomDel_space').remove();
});//部屋の削除キャンセル



///////////////
$(document).on('click','#roomListAccountDel',function(event){
$("body").prepend('<div id="previewAlert" class="cvPopup"><div><p class="previewAlertList clear">アカウントを削除してサービスから退会しますか？<br><span style="color:#f00;">※アカウントを削除すると以下のデータは完全に消滅して元に戻りません。</span>'+
'<br><span style="font-size:0.9em;">・あなたのアカウント情報全般'+
'<br>・あなたが所持している部屋（その部屋の他の参加者も入れなくなります）'+
'</span></p>'+
'<p class="previewAlertList clear clickar" id="accountDel_return">同意して削除する</p>'+
'<p class="previewAlertList clear clickar">キャンセル</p></div></div>');
});//アカウントの削除1


///////////////
$(document).on('click','#accountDel_return',function(event){

var taikai_space=
'<div id="taikai_space"><form><h3>退会：登録パスワードを入力してください</h3><div class="container error"></div><div class="container"><label>パスワード</label><br/><input type="password" id="taikai_pass" name="taikai_pass" size="20" maxlength="80"/></div><div class="container"><label>パスワード：確認</label><br/><input type="password" id="taikai_pass_re" name="taikai_pass_re" size="20" maxlength="80"/></div><input id="taikai_submit" type="submit" name="submit" value="サービス退会"/><div id="taikai_cancel">退会をやめる</div></form></div>';

$('body').prepend(taikai_space);

});//アカウントの削除2


///////////////
$(document).on('click','#taikai_submit',function(event){
event.preventDefault();

$('.error').empty();

var pass=$('[name="taikai_pass"]').val();
var repass=$('[name="taikai_pass_re"]').val();

if(pass==''||repass==''){
$('.error').append('パスワードが空です');
return;
}
if(pass!=repass){
$('.error').append('パスワードと確認パスワードが一致していません');
return;
}


var load_rm="load_taikai";
$('body').prepend(LOAD_DIV_START.replace('REPLACE',load_rm));

$.ajax({
type:'POST',
url:'../roomList.php?mode=taikai',
data:{"pass":pass},
timeout:TIMEOUT
})
.then(function(data){

try{

$('#taikai_space').remove();

//console.log(data);
data=JSON.parse(data);

if(data.flg){

socketio.emit("leave_room",data.room);

$('body').empty();
$("body").prepend('<div id="previewAlert" class="cvPopup"><div><p class="previewAlertList clear">'+data.mes+'</p></div></div>');

$('body').append('<div style="position:fixed;top:calc(50vh - 25px);text-align:center;right:0;left:0;"><h1 style="float:none;">ご利用ありがとうございました。</h1><p style="margin-top:20px;"><a href="../login.html">TOPへ戻る</a></p></div>');

}
else{
$("body").prepend('<div id="previewAlert" class="cvPopup"><div><p class="previewAlertList clear">'+data.mes+'</p></div></div>');
}

setTimeout(function(){$('#'+load_rm).remove();},200);
}catch(e){catch_parse(e,load_rm);}

}//true
,
function(jqXHR,textStatus,errorThrown){
alert(textStatus+"_"+errorThrown);
setTimeout(function(){$('#'+load_rm).remove();},200);
}//false
);



});//アカウントの削除3

///////////////
$(document).on('click','#taikai_cancel',function(event){
//g_post_editAccount={};
$('#taikai_space').remove();
});//アカウントの削除キャンセル

////////////////////
$(document).on('click','#editAccount_close_btn',function(event){
$('body').css({'overflow-y':'auto'});
$('#editAccount_space').remove();
});


/////////////////
var g_editAccount_type=null;
$(document).on('change','#editAccount_type',function(event){
g_editAccount_type=$('#editAccount_type').val();
if(g_editAccount_type=='none'){
$('.editAccount_mail').css({'display':'none'});
$('.editAccount_pass').css({'display':'none'});
$('#editAccount_submit').css({'display':'none'});
}
else if(g_editAccount_type=='mail'){
$('.editAccount_mail').css({'display':'block'});
$('.editAccount_pass').css({'display':'none'});
$('#editAccount_submit').css({'display':'block'});
}
else if(g_editAccount_type=='pass'){
$('.editAccount_mail').css({'display':'none'});
$('.editAccount_pass').css({'display':'block'});
$('#editAccount_submit').css({'display':'block'});
}
else if(g_editAccount_type=='passmail'){
$('#editAccount_space .container').css({'display':'block'});
$('#editAccount_submit').css({'display':'block'});
}

});//change

/////////////////
var g_post_editAccount={};
////////////////
$(document).on('click','#optionUl li',function(event){
$('#optionUl').remove();
});


///////////////////ルームメンバー更新の通知
socketio.on("roomList_mem_refresh",function(data){

$('#joinRoom li').each(function(index){
if($(this).attr('data-roomlistid')==data.rmid){
$('#joinRoom li:nth-of-type('+(index+1)+') .roomlistcurmem').text(data.cnt);
return false;
}
});

});//ルームメンバー更新の通知



});

};