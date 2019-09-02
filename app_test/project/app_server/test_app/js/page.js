var PAGE_FUNC=function(){

jQuery(function($){

$(document).on('click','#backTopBtn',function(event){
var animeObj={};
var anime=new APP.anime(animeObj);
anime.topToScroll('.wrap');
anime=null;
});


$(document).on('click','#goBottomBtn',function(event){
var animeObj={};
var anime=new APP.anime(animeObj);
anime.bottomToScroll('.wrap');
anime=null;
});


/////////////////
var g_today=(function(){
var today=new Date();
today.setDate(today.getDate());
var yyyy=today.getFullYear();
var mm=("0"+(today.getMonth()+1)).slice(-2);
var dd=("0"+today.getDate()).slice(-2);
today=null;
return yyyy+"-"+mm+"-"+dd;
}());


/////////////////////
var g_calender='<p>開始日</p>'+
'<input type="date" name="selectStartDay" value="" min="2018-04-01" max="2050-03-31">';
var g_pagePost={};

$(document).on('click','#sessionTag',function(event){

$('body').css({'overflow-y':'hidden'});

g_pagePost.sessionId="";
g_pagePost.sessionOrder="new";
g_pagePost.sessionDate="0000-00-00";

var sessionList='<li class="selectedList" data-sessionId=""><div class="nameScrollWrap"><span>雑談</span></div></li>';

var orderList='<li id="orderSessionNew" class="selectedBtn">新着</li><li id="orderSessionOld">古い順</li>';

$('#selectSessionSpace').remove();

var selectSession_space='<div id="selectSessionSpace" class="popDel">';
if(KENGEN>0){
selectSession_space+='<div id="selectSessionSpace_create" class="c_c_btn">新規セッション</div>';
}
selectSession_space+='<h3>セッション選択</h3>'+
'<ul id="selectSessionList" class="clear">'+
sessionList+
'</ul>'+
'<ul id="selectSessionOrder" class="clear">'+
orderList+
'</ul>'+
'<form id="selectSessionDate">'+
'</form>'+
'<div id="selectSessionSpace_return" class="c_c_btn">決定</div>';
if(KENGEN>0){
selectSession_space+='<div id="selectSession_delete" class="c_c_btn">削除</div>';
}
selectSession_space+='<div id="selectSessionSpace_close" class="c_c_btn">閉じる</div>'+
'</div>';

$("body").prepend(selectSession_space);

var load_rm="load_sessionSearch";
$('body').prepend(LOAD_DIV.replace('REPLACE',load_rm));

$.ajax({
type:'POST',
url:'../page.php?mode=searchSession',
timeout:TIMEOUT
})
.then(
function(data){

try{

data=JSON.parse(data);

var list="";
$.each(data,function(){
list+='<li data-sessionId="'+
this.sid+'"><div class="nameScrollWrap"><span>'+
this.sname+'</span></div></li>';
});

$('#selectSessionList').append(list);

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

/////////////////
$(document).on('change','input[name="selectStartDay"]',function(event){
var date=$('input[name="selectStartDay"]');
if(date.val()==""){
date.val(g_today);
g_pagePost.sessionDate=g_today;
}
else g_pagePost.sessionDate=date.val();
});//change

$(document).on('click','#selectSessionList li',function(event){
g_pagePost.sessionId=$(this).attr("data-sessionId");
$("#selectSessionList li").removeClass("selectedList");
$(this).addClass("selectedList");
});//click

$(document).on('click','#orderSessionNew',function(event){
g_pagePost.sessionOrder="new";
g_pagePost.sessionDate="0000-00-00";
$("#selectSessionDate").empty();
$("#orderSessionOld").removeClass("selectedBtn");
$("#orderSessionNew").addClass("selectedBtn");
});//click
$(document).on('click','#orderSessionOld',function(event){
g_pagePost.sessionOrder="old";
g_pagePost.sessionDate=g_today;
$("#selectSessionDate").empty();
$("#orderSessionNew").removeClass("selectedBtn");
$("#orderSessionOld").addClass("selectedBtn");
$("#selectSessionDate").append(g_calender);
var date=$('input[name="selectStartDay"]');
date.val(g_today);
});//click

/////////////
$(document).on('click','#selectSessionSpace_return',function(event){

var load_rm="load_writeSession";
$('body').prepend(LOAD_DIV.replace('REPLACE',load_rm));

$.ajax({
type:'POST',
url:'../page.php?mode=writeSession',
data:JSON.stringify(g_pagePost),
timeout:TIMEOUT
})
.then(
function(cment){

try{

//console.log(cment);
cment=JSON.parse(cment);
if(cment.flg){

cment=cment.data;

PAGE.curSessionId=cment.sid;

$('body').css({'overflow-y':'auto'});
$("#selectSessionSpace").remove();

$("#sessionTag span").empty();
$("#sessionTag").removeClass();
if(cment.order=="new"){
$("#sessionTag span").append(cment.sname);
$("#sessionTag").addClass("sessionOrderNew");
}
else if(cment.order=="old"){
$("#sessionTag span").append(cment.sname);
$("#sessionTag").addClass("sessionOrderOld");
}

$('#view').empty();

if(cment['talk']!==null){
var cmentLength=cment['talk'].length;
if(cmentLength>100)cmentLength=100;

////////////////////////////
var page=new APP.page();
for(var i=0;i<cmentLength;i++){
page.writeComent(cment['talk'][i],'append',false,false,false);
}
page=null;
////////////////////////////
}


var totalPage=Math.floor(((cment.cnt-1)/100)+1);
var curPage=cment.cnt==0?0:Math.ceil((cment.pos+1)/100);


var optionPageList="";
for(var i=1;i<=totalPage;i++){
if(i==curPage)optionPageList+='<option value="'+i+'" selected>'+i+'</option>';
else optionPageList+='<option value="'+i+'">'+i+'</option>';
}


var pageList='<li id="addPage">もっと読む</li><li id="newPage"><a>新着へ</a></li><li id="selectPage"><select name="">'+optionPageList+'</select>ページへ 現在<span id="curPageSpan">'+curPage+'</span>/<span id="totalPageSpan">'+totalPage+'</span></li>';

$('#pageControll').empty();
$('#pageControll').append(pageList);

PAGE.order=cment.order;
PAGE.totalTalk=cment['cnt'];
PAGE.backTalk=cment['pos'];
PAGE.totalPage=totalPage;
PAGE.curPage=curPage;
PAGE.posId=cment['posId'];

}//正常系
else{
$("body").prepend('<div id="previewAlert" class="cvPopup"><div><p class="previewAlertList clear">'+cment.mes+'</p></div></div>');
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

//////////////
$(document).on('click','#selectSessionSpace_close',function(event){
$('body').css({'overflow-y':'auto'});
$("#selectSessionSpace").remove();
});




/////////////////////////
$(document).on('click','#addPage',function(event){

if(+$("#curPageSpan").text()==0||
$('#sessionTag').attr('class')=='sessionOrderNew'&&$("#curPageSpan").text()==1||
$('#sessionTag').attr('class')=='sessionOrderOld'&&$("#curPageSpan").text()==$("#totalPageSpan").text()){
$("body").prepend('<div id="previewAlert" class="cvPopup"><div><p class="previewAlertList clear">現在この操作はできません</p></div></div>');
return;
}

if(PAGE.order=="new"&&PAGE.curPage!=1||PAGE.order=="old"&&PAGE.curPage!==PAGE.totalPage){

var postObj=PAGE;
postObj['posIdBack']=$("#view .info:nth-last-of-type(1) .num").text();
//console.log(postObj['posIdBack']);

var load_rm="load_addPage";
$('body').prepend(LOAD_DIV.replace('REPLACE',load_rm));

$.ajax({
type:'POST',
url:'../page.php?mode=addPage',
data:JSON.stringify(postObj),
timeout:TIMEOUT
})
.then(
function(data){

try{

//console.log(data);
data=JSON.parse(data);

if(data['talk']!==null&&data['talk']!==undefined){
var addLength=data['talk'].length;
if(addLength>100)addLength=100;
var cmentLength=$("#view .info").length;

var delCnt=(addLength+cmentLength)-500;

var page=new APP.page();
for(var i=0;i<addLength;i++){
page.writeComent(data['talk'][i],'append',false,false,false);

if(delCnt>0){
$("#view .persn:nth-of-type(1)").remove();
$("#view .talk:nth-of-type(1)").remove();
$("#view .info:nth-of-type(1)").remove();
}
delCnt--;
}
page=null;

if(PAGE.order=="new"){
var curPage=$('#curPageSpan').text();
curPage--;
PAGE.curPage=curPage;
$('#curPageSpan').text(curPage);
}
else if(PAGE.order=="old"){
var curPage=$('#curPageSpan').text();
curPage++;
PAGE.curPage=curPage;
$('#curPageSpan').text(curPage);
}

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

}

});


/////////////////////
$(document).on('click','#newPage',function(event){

var postObj=PAGE;

if(+$("#curPageSpan").text()==0||
$('#sessionTag').attr('class')=='sessionOrderNew'&&$("#curPageSpan").text()==$("#totalPageSpan").text()){
$("body").prepend('<div id="previewAlert" class="cvPopup"><div><p class="previewAlertList clear">現在この操作は有効ではありません</p></div></div>');
return;
}

var load_rm="load_newPage";
$('body').prepend(LOAD_DIV.replace('REPLACE',load_rm));

$.ajax({
type:'POST',
url:'../page.php?mode=newPage',
timeout:TIMEOUT
})
.then(
function(data){

try{

//console.log(data);
data=JSON.parse(data);

$("#sessionTag").removeClass();
$("#sessionTag").addClass("sessionOrderNew");

if(data['talk']!==null){

$('#view').empty();

var addLength=data['talk'].length;
if(addLength>100)addLength=100;

var page=new APP.page();
for(var i=0;i<addLength;i++){
page.writeComent(data['talk'][i],'append',false,false,false);
}
page=null;

PAGE.order='new';

var curPage=$('#totalPageSpan').text();
$('#curPageSpan').text(curPage);
PAGE.curPage=+curPage;

var animeObj={};
var anime=new APP.anime(animeObj);
anime.topToScroll('.wrap');
anime=null;

}

setTimeout(function(){$('#'+load_rm).remove();},200);
}catch(e){catch_parse(e,load_rm);}

}//true
,
function(jqXHR,textStatus,errorThrown){
alert(textStatus+"_"+errorThrown);
setTimeout(function(){$('#'+load_rm).remove();},200);
});

});



////////////////////
$(document).on('change','#selectPage select',function(event){

var postObj=PAGE;

if(PAGE.order=="new")postObj.offset=(+$('#totalPageSpan').text()-$('#selectPage select').val())*100;
else if(PAGE.order=="old")postObj.offset=(+$('#selectPage select').val()-1)*100;

var load_rm="load_jumpPage";
$('body').prepend(LOAD_DIV.replace('REPLACE',load_rm));

$.ajax({
type:'POST',
url:'../page.php?mode=jumpPage',
data:JSON.stringify(postObj),
timeout:TIMEOUT
})
.then(
function(data){

try{

data=JSON.parse(data);

if(data['talk']!==null){

$('#view').empty();

var addLength=data['talk'].length;
if(addLength>100)addLength=100;

var page=new APP.page();
for(var i=0;i<addLength;i++){
page.writeComent(data['talk'][i],'append',false,false,false);
}
page=null;

var animeObj={};
var anime=new APP.anime(animeObj);
anime.topToScroll('.wrap');
anime=null;

var curPage=$('#selectPage select').val();
$('#curPageSpan').text(curPage);

PAGE.curPage=(+curPage);

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

});//change


/////////////セッション新規作成////////////
$(document).on('click','#selectSessionSpace_create',function(event){
$('#selectSessionSpace').remove();
$("body").prepend('<div id="createSessionSpace" class="popDel">'+
'<h4>新規セッション名</h4>'+
'<input type="text" name="createSessionName" value="" />'+
'<div id="createSessionSpace_return" class="c_c_btn">決定</div>'+
'<div id="createSessionSpace_close" class="c_c_btn">閉じる</div>'+
'</div>');
});

/////////////////////////
$(document).on('click','#createSessionSpace_return',function(event){
var sname=$('[name="createSessionName"]').val();
$("#createSessionSpace").remove();
$('body').css({'overflow-y':'auto'});

var load_rm="load_createSession";
$('body').prepend(LOAD_DIV.replace('REPLACE',load_rm));

$.ajax({
type:'POST',
url:'../page.php?mode=createSession',
data:{name:sname},
timeout:TIMEOUT
})
.then(
function(data){

try{

//console.log(data);
data=JSON.parse(data);
if(data.result==true){
$("body").prepend('<div id="previewAlert" class="cvPopup"><div><p class="previewAlertList clear">セッションの作成に成功しました</p></div></div>');
}
if(data.result==false){
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

});

/////////////////////////
$(document).on('click','#createSessionSpace_close',function(event){
$('body').css({'overflow-y':'auto'});
$("#createSessionSpace").remove();
});//作成を閉じる

$(document).on('click','#selectSession_delete',function(event){
$("body").prepend('<div id="previewAlert" class="cvPopup"><div><p class="previewAlertList clear" style="line-height:1.5;">本当に「'+$("#selectSessionList .selectedList span").text()+'」を削除しますか？<br><span style="color:#f00;">※セッション内のトーク内容や各種設定はすべて削除され元に戻りません</span></p>'+
'<p id="selectSession_delete_enter" class="previewAlertList clear clickar">はい</p>'+
'<p class="previewAlertList clear clickar">いいえ</p></div></div>');
});//削除ボタン



///////////////
$(document).on('click','#selectSession_delete_enter',function(event){

var sessionDel_space=
'<div id="sessionDel_space"><form><h3>セッション削除：登録パスワードを入力してください</h3><div class="container error"></div><div class="container"><label>パスワード</label><br/><input type="password" id="sessionDel_pass" name="sessionDel_pass" size="20" maxlength="80"/></div><div class="container"><label>パスワード：確認</label><br/><input type="password" id="sessionDel_pass_re" name="sessionDel_pass_re" size="20" maxlength="80"/></div><input id="sessionDel_submit" type="submit" name="submit" value="セッションの削除"/><div id="sessionDel_cancel">削除をやめる</div></form></div>';

$('body').prepend(sessionDel_space);

});//セッションの削除2



//////////////////
$(document).on('click','#sessionDel_submit',function(event){

event.preventDefault();

if(g_pagePost.sessionId!=''){


$('.error').empty();

var pass=$('[name="sessionDel_pass"]').val();
var repass=$('[name="sessionDel_pass_re"]').val();

if(pass==''||repass==''){
$('.error').append('パスワードが空です');
return;
}
if(pass!=repass){
$('.error').append('パスワードと確認パスワードが一致していません');
return;
}


var load_rm="load_delSession";
$('body').prepend(LOAD_DIV.replace('REPLACE',load_rm));

$.ajax({
type:'POST',
url:'../page.php?mode=delSession',
data:{"sid":g_pagePost.sessionId,"pass":pass},
timeout:TIMEOUT
})
.then(
function(data){

try{

$('#sessionDel_space').remove();

//console.log(data);
data=JSON.parse(data);
if(data.flg==true){

var postData={};
postData.sid=data.sid;
postData.room=data.room;

//console.log(postData);

socketio.emit("reload_sess",postData);

$("body").prepend('<div id="previewAlert" class="cvPopup"><div><p class="previewAlertList clear">セッションの削除に成功しました</p></div></div>');

$('#selectSessionList li').each(function(){
if($(this).attr('data-sessionid')==g_pagePost.sessionId){
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
}
else{
$("body").prepend('<div id="previewAlert" class="cvPopup"><div><p class="previewAlertList clear">雑談は削除できません</p></div></div>');
}


});//click セッションの削除


///////////////
$(document).on('click','#sessionDel_cancel',function(event){
//g_post_editAccount={};
$('#sessionDel_space').remove();
});//セッションの削除キャンセル





});//EOF

};