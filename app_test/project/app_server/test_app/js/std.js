var STD_FUNC=function(){

jQuery(function($){

var page=new APP.page();

//開始用の関数
function Start(){

var load_rm="load_roomPageStart";
$('body').prepend(LOAD_DIV_START.replace('REPLACE',load_rm));

$.ajax({//アバターの処理
type:'POST',
url:'../char_std.php?mode=start',
timeout:TIMEOUT
})
.then(
function(data){

try{

//console.log(data);
data=JSON.parse(data);

if(data.flg){
$("#selectAvt").attr("src",data.avtUrl);
$("#selectAvt").attr("data-avtId",data.avtId);
$("#selectAvt").attr("data-avtName",data.avtName);
KENGEN=data.kengen;

if(TOOL.selectedGoods=='memo'){
$('#tool_list').empty();
TOOL.memoWrite();
}

}
else{
alert('あなたはこの部屋のメンバーに含まれていません');
location.href=FQDN+'/test_app/publics/roomList.html';
}

setTimeout(function(){$('#'+load_rm).remove();},200);
}catch(e){catch_parse(e,load_rm);}

}//true
,
function(jqXHR, textStatus, errorThrown){
alert(textStatus+"_"+errorThrown);
setTimeout(function(){$('#'+load_rm).remove();},200);
}
)
.then(function(){

////////////
var load_rm="load_talkStart";
$('body').prepend(LOAD_DIV_START.replace('REPLACE',load_rm));

$.ajax({
type:'POST',
url:'../talk.php?mode=start',
data:{},
timeout:TIMEOUT
})
.fail(function(jqXHR, textStatus, errorThrown){
alert(textStatus+"_"+errorThrown);
setTimeout(function(){$('#'+load_rm).remove();},200);
})
.done(function(cment){//コメントの描画

try{

cment=JSON.parse(cment);

PAGE.curSessionId=cment.sid;

if(cment.order=="new"){
$("#sessionTag span").empty();
$("#sessionTag span").append(cment.sname);
$("#sessionTag").addClass("sessionOrderNew");
}
else if(cment.order=="old"){
$("#sessionTag span").empty();
$("#sessionTag span").append(cment.sname);
$("#sessionTag").addClass("sessionOrderOld");
}

if(cment['talk']!==null){
var cmentLength=cment['talk'].length;
if(cmentLength>100)cmentLength=100;
for(var i=0;i<cmentLength;i++){
page.writeComent(cment['talk'][i],'append',false,false,false);
}
}

//console.log(cment['cnt']);
//console.log(cment['pos']);

var totalPage=Math.floor(((cment.cnt-1)/100)+1);
var curPage=cment.cnt==0?0:Math.ceil((cment.pos+1)/100);

var optionPageList="";
for(var i=1;i<=totalPage;i++){
if(i==curPage)optionPageList+='<option value="'+i+'" selected>'+i+'</option>';
else optionPageList+='<option value="'+i+'">'+i+'</option>';
}


var pageList='<li id="addPage">もっと読む</li><li id="newPage"><a>新着へ</a></li><li id="selectPage"><select name="">'+optionPageList+'</select>ページへ 現在<span id="curPageSpan">'+curPage+'</span>/<span id="totalPageSpan">'+totalPage+'</span></li>';

$('#pageControll').append(pageList);

PAGE.order=cment.order;
PAGE.totalTalk=cment['cnt'];
PAGE.backTalk=cment['pos'];
PAGE.totalPage=totalPage;
PAGE.curPage=curPage;
PAGE.posId=cment['posId'];

setTimeout(function(){$('#'+load_rm).remove();},200);
}catch(e){catch_parse(e,load_rm);}

$('#load_app_start').remove();//アプリロードのふた削除

});

}//true
,
function(jqXHR, textStatus, errorThrown){
alert(textStatus+"_"+errorThrown);
}//false
);

}//Start()


///////////////
$("#backTopBtn").hide();
$(window).on('scroll',function(){
if($(this).scrollTop()>1500){$("#backTopBtn").show();}
else{$("#backTopBtn").hide();}
});//スクロール

///////////////
$(window).on('resize',function(){
$('li.battle_cell').height($('li.battle_cell').width());
});//リサイズ


/////////////////
socketio.on('connect',function(socket){

$.ajax({
type:'POST',
url:'../who.php',
data:{},
timeout:TIMEOUT
})
.fail(function(jqXHR, textStatus, errorThrown){
alert(textStatus+"_"+errorThrown);
})
.done(function(data){
DATA=JSON.parse(data);
socketio.emit("enter",DATA);
USERNAME=DATA.uid;
$("#welcome").empty();
$("#welcome").prepend(" <span>"+USERNAME+"</span>"+
":<span>"+DATA.roomName+"</span>");


$(window).on('focus',function(){
if(STATECONNECT==false)location.reload(false);//ソケットが切断されてたらリロード
$.post('../who.php',{},function(data){
data=JSON.parse(data);
if(DATA.uid!=data.uid||DATA.room!=data.room)location.reload(false);
});//部屋名とユーザ名が一致しなかったらリロード
});


//開始処理
Start();

location.hash.replace('#','').split('&').forEach(function(val){
if(val==VH.selectAvt){
selectAvt.call($('#selectAvt'),null);
}
});

});

});


//入室用
socketio.on("enter",function(socket){

if(socket.myjoin==''){
SE.enter.currentTime=0;
SE.enter.play();
}

$("#currentMember span").empty();
$("#currentMember span").append(socket.cnt);
});//on(enter)
//退室用
socketio.on("leave",function(socket){
$("#currentMember span").empty();
$("#currentMember span").append(socket.cnt);
});//on(leave)


/////////////////
var g_obj_char="";
var g_ulId="";



/////////////////////
$(document).on('click','#changeTextLine',function(event){

var temp_coment=$('#hatsugen').val();

$('#hatsugen').remove();
$('#hatugenType').remove();
$('#changeTextLine').remove();
var tempSelectAvt=$('#add').html();
$('#selectAvt').remove();
$('#add').append(tempSelectAvt+
'<textarea id="hatsugen" class="customSubmit" name="typeTextArea" cols=40 rows=4></textarea>'+
'<input id="hatugenType" type="submit" value="発言" />'+
'<span id="changeTextArea">改行なし</span>');
$('#hatsugen').val(temp_coment);
$data=$('[name=typeTextArea]');
});//フォーム種類変更

$(document).on('click','#changeTextArea',function(event){

var temp_coment=$('#hatsugen').val();

$('#hatsugen').remove();
$('#hatugenType').remove();
$('#changeTextArea').remove();
var tempSelectAvt=$('#add').html();
$('#selectAvt').remove();
$('#add').append(tempSelectAvt+
'<input id="hatsugen" type="text" name="data" value="" />'+
'<input id="hatugenType" type="submit" value="発言" />'+
'<span id="changeTextLine">改行あり</span>');
$('#hatsugen').val(temp_coment);
$data=$('[name=data]');
});//フォーム種類変更

////////////////////
$(document).on('keydown','.customSubmit',function(event){
if(event.keyCode===13&&event.shiftKey){
event.preventDefault();
var resLen=$data.val().split("").length;
if(resLen<=0){alert("発言内容がないので投稿できません");}
else if(resLen<=500){

var postTalkData=$data.val();
$data.val('');//フォームの初期化

var load_rm="load_addTalk";
$('body').prepend(LOAD_DIV.replace('REPLACE',load_rm));

$.ajax({
type:'POST',
url:'../talk.php?mode=addTalk',
data:{data:postTalkData,type:'normal'},
timeout:TIMEOUT
})
.fail(function(jqXHR, textStatus, errorThrown){
alert(textStatus+"_"+errorThrown);
setTimeout(function(){$('#'+load_rm).remove();},200);
})
.done(function(data){

try{

data=JSON.parse(data);
if(data.flg===true){
$data.val('');
socketio.emit("add",data);
}
else alert(data.flg);

setTimeout(function(){$('#'+load_rm).remove();},200);
}catch(e){catch_parse(e,load_rm);}

});
}
else alert("500文字以内でお願いします（現在"+resLen+"文字）");
}
});

////////////////
var $data=$('input[name="data"]');
$(document).on('click','#hatugenType',function(event){
event.preventDefault();
var resLen=$data.val().split("").length;
if(resLen<=0){alert("発言内容がないので投稿できません");}
else if(resLen<=500){

var postTalkData=$data.val();
$data.val('');//フォームの初期化

var load_rm="load_addTalk";
$('body').prepend(LOAD_DIV.replace('REPLACE',load_rm));

$.ajax({
type:'POST',
url:'../talk.php?mode=addTalk',
data:{data:postTalkData,type:'normal'},
timeout:TIMEOUT
})
.fail(function(jqXHR, textStatus, errorThrown){
alert(textStatus+"_"+errorThrown);
setTimeout(function(){$('#'+load_rm).remove();},200);
})
.done(function(data){

try{

//console.log(data);
data=JSON.parse(data);
if(data.flg===true){
socketio.emit("add",data);
}
else{
alert(data.flg);
}

setTimeout(function(){$('#'+load_rm).remove();},200);
}catch(e){catch_parse(e,load_rm);}

});
}
else alert("500文字以内でお願いします（現在"+resLen+"文字）");
});//event(form)

socketio.on("add",function(add){
if(add.sid==PAGE.curSessionId){

if(PAGE.order=="new"){
PAGE.totalTalk++;

page.writeComent(add,'prepend',true,false,true);
PAGE.backTalk++;
PAGE.posId=add.id;
}
else if(PAGE.order=="old"){
PAGE.totalTalk++;

if(PAGE.totalPage==PAGE.curPage){
page.writeComent(add,'append',true,false,true);
PAGE.backTalk++;
PAGE.posId=add.id;
}
}


var cmentLength=$('.talk').length;
if(cmentLength>100){

if(PAGE.order=="new"){
$("#view .persn:nth-last-of-type(1)").remove();
$("#view .talk:nth-last-of-type(1)").remove();
$("#view .info:nth-last-of-type(1)").remove();
}

else if(PAGE.order=="old"&&PAGE.totalPage==PAGE.curPage){
$("#view .persn:nth-of-type(1)").remove();
$("#view .talk:nth-of-type(1)").remove();
$("#view .info:nth-of-type(1)").remove();
}

}

var totalPage=Math.floor(((PAGE.totalTalk-1)/100)+1);
var curPage=PAGE.totalTalk==0?0:Math.ceil((PAGE.backTalk+1)/100);

if(totalPage>PAGE.totalPage){
$('#selectPage select').append('<option value="'+totalPage+'">'+totalPage+'</option>');
$('#totalPageSpan').empty();
$('#totalPageSpan').append(totalPage);

if(PAGE.order=='new'){
PAGE.curPage++;
$('#curPageSpan').text(PAGE.curPage);
}
else if(PAGE.order=='old'&&PAGE.totalPage==PAGE.curPage){
PAGE.curPage++;
$('#curPageSpan').text(PAGE.curPage);
}
PAGE.totalPage++;
}

}
});//on(add)


/////////////////////////ポップアップトークメニュー
var g_talkSession="";
var g_talkNum="";
$(document).on('click','.talkMenu',function(event){

g_talkSession=$('#sessionTag span').text();
g_talkNum=$(this).parent().find('.num').text();

if($(event.target).closest('.myInfo').length==1){
if($(event.target).closest('[data-talktype="normal"]').length==1){
$("body").prepend('<div id="talkOption" class="cvPopup"><div>'+
'<p class="talkOptionList clear" id="">'+g_talkSession+':'+
g_talkNum+'番に対して</p>'+
'<p class="talkOptionList clear clickar" id="talkQuote">引用</p>'+
'<p class="talkOptionList clear clickar" id="talkEdit">編集</p>'+
'<p class="talkOptionList clear clickar" id="talkRemv">削除</p>'+
'</div></div>');
}
else if($(event.target).closest('[data-talktype="bgm"]').length==1){
$("body").prepend('<div id="talkOption" class="cvPopup"><div>'+
'<p class="talkOptionList clear" id="">'+g_talkSession+':'+
g_talkNum+'番に対して</p>'+
'<p class="talkOptionList clear clickar" id="talkQuote">引用</p>'+
'<p class="talkOptionList clear clickar" id="talkRemv">削除</p>'+
'</div></div>');
}
else{
$("body").prepend('<div id="talkOption" class="cvPopup"><div>'+
'<p class="talkOptionList clear" id="">'+g_talkSession+':'+
g_talkNum+'番に対して</p>'+
'<p class="talkOptionList clear clickar" id="talkQuote">引用</p>'+'</div></div>');
}
}
else if($(event.target).closest('.otherInfo').length==1){
$("body").prepend('<div id="talkOption" class="cvPopup"><div>'+
'<p class="talkOptionList clear" id="">'+g_talkSession+':'+
g_talkNum+'番に対して</p>'+
'<p class="talkOptionList clear clickar" id="talkQuote">引用</p>'+
'</div></div>');
}

});

////////////////
$(document).on('click','#talkQuote',function(event){
$('#hatsugen').val('[QT!'+g_talkNum+']'+$('#hatsugen').val());
});//event(click)

////////////////
$(document).on('click','#talkRemv',function(event){

var load_rm="load_talkRemv";
$('body').prepend(LOAD_DIV.replace('REPLACE',load_rm));

$.ajax({
type:'POST',
url:'../talk.php?mode=remv',
data:{id:g_talkNum},
timeout:TIMEOUT
})
.fail(function(jqXHR, textStatus, errorThrown){
alert(textStatus+"_"+errorThrown);
setTimeout(function(){$('#'+load_rm).remove();},200);
})
.done(function(sid){

var data={};
data.sid=sid;
data.num=g_talkNum;

socketio.emit("remv",data);

setTimeout(function(){$('#'+load_rm).remove();},200);

});
});//event(click)

socketio.on("remv",function(data){
if(data.sid==PAGE.curSessionId){


if(PAGE.order=="new"){
var posid=$('#view .info:nth-of-type(1) .num').text();
}
else if(PAGE.order=="old"){
var posid=$('#view .info:nth-last-of-type(1) .num').text();
}


var pageLength=$(".info").length;

if(pageLength<=500){
$.each($(".info"),function(){
if($(this).find("span.num").text()==data.num){

if(PAGE.order=="new"){
var numid=$('#view .info:nth-last-of-type(1) .num').text();
}
else if(PAGE.order=="old"){
var numid=$('#view .info:nth-of-type(1) .num').text();
}

var load_rm="load_remvAddone";
$('body').prepend(LOAD_DIV.replace('REPLACE',load_rm));

$.ajax({
type:'POST',
url:'../talk.php?mode=remvAddOne',
data:{id:numid},
timeout:TIMEOUT
})
.then(
function(data){

try{

data=JSON.parse(data);
if(data!==null&&PAGE.order=="new"){
page.writeComent(data,'append',false,false,false);
}
else if(data!==null&&PAGE.order=="old"){
page.writeComent(data,'prepend',false,false,false);
}

setTimeout(function(){$('#'+load_rm).remove();},200);
}catch(e){catch_parse(e,load_rm);}

}//true
,
function(jqXHR, textStatus, errorThrown){
alert(textStatus+"_"+errorThrown);
setTimeout(function(){$('#'+load_rm).remove();},200);
}//false
);

$(this).prev().prev('dt.persn').remove();
$(this).prev('dd.talk').remove();
$(this).remove();

return false;

}
});//each
}

PAGE.totalTalk--;
if(PAGE.totalPage==PAGE.curPage){
PAGE.backTalk--;
PAGE.posId=data.num;
}

var totalPage=Math.floor(((PAGE.totalTalk-1)/100)+1);
var curPage=PAGE.totalTalk==0?0:Math.ceil((PAGE.backTalk+1)/100);


if(totalPage<PAGE.totalPage){
$('#selectPage option:nth-last-of-type(1)').remove();
$('#totalPageSpan').empty();
$('#totalPageSpan').append(totalPage);


if(PAGE.order=='new'&&+posid>=+data.num){
if(PAGE.curPage>0){
PAGE.curPage--;
$('#curPageSpan').text(PAGE.curPage);
}
}
else if(PAGE.order=='old'&&+posid>=+data.num){
if(PAGE.curPage>0){
PAGE.curPage--;
$('#curPageSpan').text(PAGE.curPage);
}
}
PAGE.totalPage--;

}

}
});//on(remv)


//////////////////
var talkEdit=function(event){
$('body').css({'overflow-y':'hidden'});

var load_rm="load_talkEdit";
$('body').prepend(LOAD_DIV.replace('REPLACE',load_rm));

$.ajax({
type:'POST',
url:'../talk.php?mode=edit',
data:{id:getParam(VP.talkEdit)},
timeout:TIMEOUT
})
.fail(function(jqXHR, textStatus, errorThrown){
alert(textStatus+"_"+errorThrown);
setTimeout(function(){$('#'+load_rm).remove();},200);
})
.done(function(data){
if(data){
$("body").prepend('<div id="edit_space"><form action="../talk_update.php" method="post"><h3>コメント編集</h3><textarea id="input_edit" name="edit" cols=40 rows=12>'+data+
'</textarea>'+
'<div><input style="cursor:pointer;" id="update" type="submit" name="update" value="更新"/><input style="cursor:pointer;" id="edit_cancel" type="submit" name="cancel" value="キャンセル"/></div></form></div>');
}
else{
$("body").prepend('<div id="previewAlert" class="cvPopup"><div>'+
'<p class="previewAlertList clear">この投稿は編集できません</p>'+
'</div></div>');
}

setTimeout(function(){$('#'+load_rm).remove();},200);

});
};
$(document).on('click','#talkEdit',function(event){

if(HISTORYPUSH(VP.talkEdit+'='+g_talkNum,VH.talkEdit,DEL_G.talkEdit.param,[])){//newParam(NO?),newHash(NO#),delParam[ARR],delHash[ARR]
talkEdit.call($('#talkEdit'),event);
}

});//event(click)

$(document).on('click','#update',function(event){

event.preventDefault();
var resLen=$("#input_edit").val().length;

if(resLen<=0){alert("発言内容がないので更新できません");}
else if(resLen<=500){

var load_rm="load_talkUpdate";
$('body').prepend(LOAD_DIV.replace('REPLACE',load_rm));

$.ajax({
type:'POST',
url:'../talk.php?mode=update',
data:{id:getParam(VP.talkEdit),edit_data:$("#input_edit").val()},
timeout:TIMEOUT
})
.fail(function(jqXHR, textStatus, errorThrown){
alert(textStatus+"_"+errorThrown);
setTimeout(function(){$('#'+load_rm).remove();},200);
})
.done(function(data){

try{

var obj=JSON.parse(data);
if(obj.flg===true){
$("#edit_space").remove();
socketio.emit("edit",obj);
}
else alert(obj.flg);

setTimeout(function(){$('#'+load_rm).remove();},200);
}catch(e){catch_parse(e,load_rm);}

});
$('body').css({'overflow-y':'auto'});
}
else alert("500文字以内でお願いします（現在"+resLen+"文字）");

HISTORYPUSH('','',DEL_G.talkEdit.param,DEL_G.talkEdit.hash);//newParam(NO?),newHash(NO#),delParam[ARR],delHash[ARR]

});//event(click)

socketio.on("edit",function(obj){
if(obj.sid==PAGE.curSessionId){

var quote='';
var matchArry=obj.talk.match(/\[QT!\d+\]/g);
obj.talk=obj.talk.replace(/\[QT!\d+\]/g,'');
if(matchArry!==null){
$.each(matchArry,function(index){
matchArry[index]=this.replace('[QT!','').replace(']','');
quote+='<p id="quote_content" class="float_btn" data-quote-id='+matchArry[index]+'>引用：'+matchArry[index]+'</p>';
});
}
obj.talk=SANITAIZE.encode(obj.talk);
obj.talk=obj.talk.replace(/^(<br>)+/,'');

$.each($(".info"),function(){
if($(this).find('.num').text()==obj.id){
var tempClass=$(this).prev('.talk').find('.phrse span').attr("class");
$(this).prev('.talk').find(".phrse > div").empty();
$(this).prev('.talk').find(".phrse > div").append(
quote+
'<p style="clear:both;">'+obj.talk+'</p>');
return false;
}
});

}
});//on(edit)


////////////////
var edit_cancel=function(event){
$("#edit_space").remove();
$('body').css({'overflow-y':'auto'});
};
$(document).on('click','#edit_cancel',function(event){

HISTORYPUSH('','',DEL_G.talkEdit.param,DEL_G.talkEdit.hash);//newParam(NO?),newHash(NO#),delParam[ARR],delHash[ARR]

edit_cancel.call($('#edit_cancel'),event);

});//event(click)


/****************************************/
var g_curCLicked='';
////////////////////////////

var g_cid="";

///////////////////
$(document).on('click',function(event){
if($(event.target).closest(".mdList").length==0){
$(".std_char_view li div,#Char_view li div,#evTarget_view li div,#ev_target li div,#memoTarget_view li div,#memo_target li div").remove();
}
});//指定した要素以外のクリックで指定した要素を削除する場合
///////////////////

$(document).on('click','#menu_child_Option',function(event){//オプションクリック

var optionList='<ul id="optionUl">'+
'<li id="optionListRoomMem" class="optionList">メンバー</li>'+
'<li id="optionListRoom" class="optionList">ルーム選択</li>'+
'<li id="optionListLogout" class="optionList">ログアウト</li>'+
'<li id="optionListCancel" class="optionList">閉じる</li>'+
'</ul>';
$('#optionUl').remove();
$(this).append(optionList);
});

$(document).on('click','#optionListRoom',function(event){
location.href=FQDN+'/test_app/publics/roomList.html';
});

$(document).on('click','#optionListLogout',function(event){//ログアウトクリック
$("body").prepend('<div id="previewAlert" class="cvPopup"><div><p class="previewAlertList clear">本当にログアウトしますか？</p>'+
'<p class="previewAlertList clear clickar" id="logoutBtn">はい</p>'+
'<p class="previewAlertList clear clickar">いいえ</p></div></div>');
});

/////////////////////
$(document).on('click',function(event){//対象を決めないクリック処理
if($(event.target).closest('#optionUl,#menu_child_Option,.cvPopup').length==0||
$(event.target).closest('#optionListCancel').length==1)
$('#optionUl').remove();//オプションリストを閉じる
});


/////////////////////
$(document).on('mouseenter','li.mdList',function(event){
var domLi=$(this);
var domName=$(this).find("span.name");
var scrollWidth=domName.width();
if(scrollWidth>90){
var animeObj={};
animeObj.dom=domName;
var anime=new APP.anime(animeObj);
anime.nameScroll(scrollWidth);
anime=null;
}
});
////////////////////////
$(document).on('mouseenter','#selectSessionList li,#sessionTag,#sttsChar_space #charName',function(event){
var domName=$(this).find("span");
var scrollWidth=domName.width();
if(scrollWidth>$(this).width()){
var animeObj={};
animeObj.dom=domName;
var anime=new APP.anime(animeObj);
anime.nameScroll_2(scrollWidth);
anime=null;
}
});

/////引用を閉じる/////
$(document).on('click','#quoteContent_close',function(){
$(this).closest('#quote_space').remove();
if($('.qt_counter').length<=0){
$('body').css({'overflow-y':'auto'});
}
});//引用を閉じる

/////引用の表示/////
var g_quoteLen=0;
$(document).on('click','#quote_content',function(){

g_quoteLen=$('.qt_counter').length;

if(g_quoteLen>=5){
$("body").prepend('<div id="previewAlert" class="cvPopup"><div>'+
'<p class="previewAlertList clear">これ以上引用を遡れません</p>'+
'</div></div>');
return;
}

var quoteid=$(this).attr('data-quote-id');
$('body').css({'overflow-y':'hidden'});
var quote_space='<div id="quote_space" class="qt_counter popDel"><span id="quoteContent_close">×</span><div id="quoteContent"></div></div>';
$('body').prepend(quote_space);

g_quoteLen++;
$('#quote_space').css({'z-index':6 + g_quoteLen});

var load_rm="load_quote_write";
$('body').prepend(LOAD_DIV.replace('REPLACE',load_rm));

$.ajax({
type:'POST',
url:'../talk.php?mode=quote',
data:{id:quoteid},
timeout:TIMEOUT
})
.then(
function(data){
try{

data=JSON.parse(data);
if(data.flg){
page.writeComent(data.talk,'append',false,true,false);
}
else{
$("body").prepend('<div id="previewAlert" class="cvPopup"><div>'+
'<p class="previewAlertList clear">引用データが見つかりませんでした</p>'+
'</div></div>');
$('#quoteContent').hide();
}

setTimeout(function(){$('#'+load_rm).remove();},200);
}catch(e){catch_parse(e,load_rm);}
}//true
,
function(jqXHR, textStatus, errorThrown){
alert(textStatus+"_"+errorThrown);
setTimeout(function(){$('#'+load_rm).remove();},200);
}//false
);

});//引用の表示



/////////////////////
$(document).on('click','#currentMember',function(event){
$("body").prepend('<div id="previewAlert" class="cvPopup" style="position:fixed;"><div><p class="previewAlertList clear" style="line-height:1.5;">ルーム内メンバーの居場所</p></div></div>');

var load_rm="load_viewMem";
$('body').prepend(LOAD_DIV.replace('REPLACE',load_rm));

$.ajax({
type:'POST',
url:'../view_mem.php?mode=view',
timeout:TIMEOUT
})
.then(
function(data){

try{

//console.log(data);
data=JSON.parse(data);
//console.log(data);

$.each(data,function(){

$('#previewAlert div').append('<p class="previewAlertList clear" style="line-height:1.5;">'+
this.name+'：'+this.sname+
'</p>');

});

setTimeout(function(){$('#'+load_rm).remove();},200);
}catch(e){catch_parse(e,load_rm);}

}//true
,
function(jqXHR, textStatus, errorThrown){
alert(textStatus+"_"+errorThrown);
setTimeout(function(){$('#'+load_rm).remove();},200);
}//false
);

});//カレントメンバー


/////////////////////
$(document).on('click','#previewAlert_close',function(event){
$('#memList_space').remove();
$('body').css({'overflow-y':'auto'});
});//全メンバー閲覧を閉じる


/////////////////////
$(document).on('click','#optionListRoomMem',function(event){
$('body').css({'overflow-y':'hidden'});

$("body").prepend('<div id="memList_space" class="popDel" style="position:fixed;"><span id="previewAlert_close">×</span><p class="previewAlertList clear" style="line-height:1.5;">このルームの全メンバー</p><ul></ul></div>');

var load_rm="load_viewMem";
$('body').prepend(LOAD_DIV.replace('REPLACE',load_rm));

$.ajax({
type:'POST',
url:'../view_mem.php?mode=view_all',
timeout:TIMEOUT
})
.then(
function(data){

try{

//console.log(data);
data=JSON.parse(data);
//console.log(data);

data.list.sort(function(a,b){
if(a.date<b.date)return 1;
if(a.date>b.date)return -1;
});

$.each(data.list,function(index){
var mem_block="";
var mem_kengen="";

if(data.kengen){
if(!this.block&&this.kengen!=2&&USERNAME!=this.name){mem_block='<span id="mem_block_enter" class="memList_btn">ブロック</span>';}
else if(this.block&&USERNAME!=this.name){mem_block='<span id="mem_block_cancel" class="memList_btn">ブロック解除</span>';}
if(!this.kengen){mem_kengen='<span id="mem_kengen_get" class="memList_btn">管理権付与</span>';}
else if(this.kengen==1){mem_kengen='<span id="mem_kengen_lost" class="memList_btn">管理権剥奪</span>';}
}
else{$('#memList_space').css({'text-align':'center'});}

$('#memList_space ul').append('<li class="previewAlertList clear" style="line-height:1.5;" data-name='+this.name+' data-index='+index+'>'+
this.name+'さん '+mem_block+mem_kengen+
'</li>');

});

setTimeout(function(){$('#'+load_rm).remove();},200);
}catch(e){catch_parse(e,load_rm);}

}//true
,
function(jqXHR, textStatus, errorThrown){
alert(textStatus+"_"+errorThrown);
setTimeout(function(){$('#'+load_rm).remove();},200);
}//false
);

});//メンバーの確認


/////////////
var g_mem_index=null;
var g_post_target=null;
///////権限関連の通知を受けた場合
socketio.on("kengen",function(data){
if(data.target==USERNAME){
if(data.type=='block'){
location.href=FQDN+'/test_app/publics/roomList.html';
}
else if(data.type=='kengen_add'){
KENGEN=1;
if(TOOL.selectedGoods=='memo'){
$('#tool_list').empty();
TOOL.memoWrite();
}
}
else if(data.type=='kengen_rm'){
KENGEN=0;
if(TOOL.selectedGoods=='memo'){
$('#tool_list').empty();
TOOL.memoWrite();
}
}
}
});

socketio.on("leave_room",function(){
location.href=FQDN+'/test_app/publics/roomList.html';
});

socketio.on('reload_sess',function(data){
if(data==PAGE.curSessionId){
location.reload();
}
});

//////////////
$(document).on('click','#mem_block_enter',function(event){
g_mem_index=$(this).closest('li').attr('data-index');
g_post_target=$(this).closest('li').attr('data-name');

$("body").prepend('<div id="previewAlert" class="cvPopup"><div><p class="previewAlertList clear">本当に'+g_post_target+'さんをブロックしますか？<br><span style="color:#f00;">※ブロックされた人はこの部屋に入れなくなります</span></p>'+
'<p class="previewAlertList clear clickar" id="mem_block_enter_enter">はい</p>'+
'<p class="previewAlertList clear clickar">いいえ</p></div></div>');
});//ブロック１
///////////
$(document).on('click','#mem_block_enter_enter',function(event){

var load_rm="load_block_enter";
$('body').prepend(LOAD_DIV.replace('REPLACE',load_rm));

$.ajax({
type:'POST',
url:'../view_mem.php?mode=block_enter',
data:{'uid':g_post_target},
timeout:TIMEOUT
})
.then(
function(data){

try{

//console.log(data);
data=JSON.parse(data);

$("body").prepend('<div id="previewAlert" class="cvPopup"><div><p class="previewAlertList clear">'+data.mes+'</p></div></div>');


if(data.flg){
var dom=$('#memList_space ul li:nth-of-type('+(+g_mem_index+1)+') #mem_block_enter');
dom.text('ブロック解除');
dom.attr('id','mem_block_cancel');

//if(data.sid!=''){
var postData={};
postData.type='block';
postData.target=g_post_target;
socketio.emit("kengen",postData);
//}//入室中かどうか

}//成功

setTimeout(function(){$('#'+load_rm).remove();},200);
}catch(e){catch_parse(e,load_rm);}

}//true
,
function(jqXHR, textStatus, errorThrown){
alert(textStatus+"_"+errorThrown);
setTimeout(function(){$('#'+load_rm).remove();},200);
}//false
);

});//ブロック2


//////////////
$(document).on('click','#mem_block_cancel',function(event){
g_mem_index=$(this).closest('li').attr('data-index');
g_post_target=$(this).closest('li').attr('data-name');

$("body").prepend('<div id="previewAlert" class="cvPopup"><div>'+
'<p class="previewAlertList clear">本当にブロック解除しますか？</p>'+
'<p class="previewAlertList clear clickar" id="mem_block_cancel_enter">はい</p>'+
'<p class="previewAlertList clear clickar">いいえ</p>'+
'</div></div>');
});//ブロック解除１
///////////
$(document).on('click','#mem_block_cancel_enter',function(event){

var load_rm="load_block_cancel";
$('body').prepend(LOAD_DIV.replace('REPLACE',load_rm));

$.ajax({
type:'POST',
url:'../view_mem.php?mode=block_cancel',
data:{'uid':g_post_target},
timeout:TIMEOUT
})
.then(
function(data){

try{

//console.log(data);
data=JSON.parse(data);

$("body").prepend('<div id="previewAlert" class="cvPopup"><div>'+
'<p class="previewAlertList clear">'+data.mes+'</p>'+
'</div></div>');

if(data.flg){
var dom=$('#memList_space ul li:nth-of-type('+(+g_mem_index+1)+') #mem_block_cancel');
dom.text('ブロック');
dom.attr('id','mem_block_enter');
}//成功

setTimeout(function(){$('#'+load_rm).remove();},200);
}catch(e){catch_parse(e,load_rm);}

}//true
,
function(jqXHR, textStatus, errorThrown){
alert(textStatus+"_"+errorThrown);
setTimeout(function(){$('#'+load_rm).remove();},200);
}//false
);

});//ブロック解除2

//////////////
$(document).on('click','#mem_kengen_get',function(event){
g_mem_index=$(this).closest('li').attr('data-index');
g_post_target=$(this).closest('li').attr('data-name');

$("body").prepend('<div id="previewAlert" class="cvPopup"><div>'+
'<p class="previewAlertList clear">'+g_post_target+'さんを信頼して管理権を付与しますか？<br><span style="color:#f00;">※権限付与するとその人もブロックや管理権限付与ができるようになります</span></p>'+
'<p class="previewAlertList clear clickar" id="mem_kengen_get_enter">はい</p>'+
'<p class="previewAlertList clear clickar">いいえ</p>'+
'</div></div>');
});//権限付与１
///////////
$(document).on('click','#mem_kengen_get_enter',function(event){

var load_rm="load_kengen_get";
$('body').prepend(LOAD_DIV.replace('REPLACE',load_rm));

$.ajax({
type:'POST',
url:'../view_mem.php?mode=kengen_get',
data:{'uid':g_post_target},
timeout:TIMEOUT
})
.then(
function(data){

try{

//console.log(data);
data=JSON.parse(data);

$("body").prepend('<div id="previewAlert" class="cvPopup"><div>'+
'<p class="previewAlertList clear">'+data.mes+'</p>'+
'</div></div>');


if(data.flg){
var dom=$('#memList_space ul li:nth-of-type('+(+g_mem_index+1)+') #mem_kengen_get');
dom.text('管理権剥奪');
dom.attr('id','mem_kengen_lost');

//if(data.sid!=''){
var postData={};
postData.type='kengen_add';
postData.target=g_post_target;
socketio.emit("kengen",postData);
//}//入室中かどうか

}//成功

setTimeout(function(){$('#'+load_rm).remove();},200);
}catch(e){catch_parse(e,load_rm);}

}//true
,
function(jqXHR, textStatus, errorThrown){
alert(textStatus+"_"+errorThrown);
setTimeout(function(){$('#'+load_rm).remove();},200);
}//false
);

});//権限付与2

//////////////
$(document).on('click','#mem_kengen_lost',function(event){
g_mem_index=$(this).closest('li').attr('data-index');
g_post_target=$(this).closest('li').attr('data-name');

$("body").prepend('<div id="previewAlert" class="cvPopup"><div>'+
'<p class="previewAlertList clear">本当に管理権を剥奪しますか？</p>'+
'<p class="previewAlertList clear clickar" id="mem_kengen_lost_enter">はい</p>'+
'<p class="previewAlertList clear clickar">いいえ</p>'+
'</div></div>');
});//権限剥奪１
///////////
$(document).on('click','#mem_kengen_lost_enter',function(event){

var load_rm="load_kengen_lost";
$('body').prepend(LOAD_DIV.replace('REPLACE',load_rm));

$.ajax({
type:'POST',
url:'../view_mem.php?mode=kengen_lost',
data:{'uid':g_post_target},
timeout:TIMEOUT
})
.then(
function(data){

try{

//console.log(data);
data=JSON.parse(data);

$("body").prepend('<div id="previewAlert" class="cvPopup"><div>'+
'<p class="previewAlertList clear">'+data.mes+'</p>'+
'</div></div>');


if(data.flg){
var dom=$('#memList_space ul li:nth-of-type('+(+g_mem_index+1)+') #mem_kengen_lost');
dom.text('管理権付与');
dom.attr('id','mem_kengen_get');

//if(data.sid!=''){
var postData={};
postData.type='kengen_rm';
postData.target=g_post_target;
socketio.emit("kengen",postData);
//}//入室中かどうか

}//成功

setTimeout(function(){$('#'+load_rm).remove();},200);
}catch(e){catch_parse(e,load_rm);}

}//true
,
function(jqXHR, textStatus, errorThrown){
alert(textStatus+"_"+errorThrown);
setTimeout(function(){$('#'+load_rm).remove();},200);
}//false
);

});//権限剥奪2









////////////////////////
location.hash.replace('#','').split('&').forEach(function(val){

if(val==VH.talkEdit){
talkEdit.call($('#talkEdit'),null);
}
if(val==VH.menu_child_view){
menu_child_view.call($('#menu_child_view'),null);
}

});


///////////////////////
window.addEventListener('popstate',function(e){

if(!$('.popDel,.cvPopup').length){

var excArr=[];
var initArr=JSON.stringify(OLDHASHARR);
initArr=JSON.parse(initArr);
if(location.hash!=""){

var hashArr=location.hash.replace('#','').split('&');

$.each(hashArr,function(indexHash){
$.each(initArr,function(indexInit){
if(hashArr[indexHash]==initArr[indexInit]){
excArr.push(initArr[indexInit]);
}
});
});

}
initArr=initArr.filter(function(inites){return excArr.indexOf(inites)==-1});

var excArr=[];
var writeArr=[];
if(location.hash!=""){
writeArr=location.hash.replace('#','').split('&');
$.each(OLDHASHARR,function(indexOld){
$.each(writeArr,function(indexWrite){
if(writeArr[indexWrite]==OLDHASHARR[indexOld]){
excArr.push(writeArr[indexWrite]);
}
});
});
}
writeArr=writeArr.filter(function(writes){return excArr.indexOf(writes)==-1});


$.each(initArr,function(){
if(this==VH.selectAvt||this==VH.menu_child_view){
view_cancel.call($('#view_cancel'),null);
}
else if(this==VH.talkEdit){
edit_cancel.call($('#edit_cancel'),null);
}
});

$.each(writeArr,function(){
if(this==VH.selectAvt){
selectAvt.call($('#selectAvt'),null);
}
else if(this==VH.talkEdit){
talkEdit.call($('#talkEdit'),null);
}
else if(this==VH.menu_child_view){
menu_child_view.call($('#menu_child_view'),null);
}
});

}

});



});

};