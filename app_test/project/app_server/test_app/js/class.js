$=jQuery;

var APP={};
APP.pcData=(function(){

var X_charObj="";

var constructor=function(battleCharObj){
this.battleCharObj=battleCharObj;
};//コンストラクター

constructor.prototype.pcSet=function(scrollLeft){

var battle_list="";
var trap_view=false;
var charCnt=0;
var trapCnt=0;
for(var panelCnt=0; panelCnt<MAX_WIDTH*MAX_HEIGHT; panelCnt++){

if(this.battleCharObj.char.length>charCnt && this.battleCharObj.char[charCnt].panelid==panelCnt){

var temp1="";
if(this.battleCharObj.oder[0].bcid==this.battleCharObj.char[charCnt].bcid){
if(this.battleCharObj.char[charCnt].uid==USERNAME){
temp1='<li class="battle_cell currentPc" draggable=true';
trap_view=true;
}
else if(this.battleCharObj.oder[0].side==this.battleCharObj.char[charCnt].side)
temp1='<li class="battle_cell no_turn party currentPc"';
else if(this.battleCharObj.oder[0].side!=this.battleCharObj.char[charCnt].side)
temp1='<li class="battle_cell no_turn enemy currentPc"';
}

else if(this.battleCharObj.oder[0].side==this.battleCharObj.char[charCnt].side)
temp1='<li class="battle_cell no_turn party"';

else if(this.battleCharObj.oder[0].side!=this.battleCharObj.char[charCnt].side)
temp1='<li class="battle_cell no_turn enemy"';

var temp2="";
temp2=
' data-bcid="'+
this.battleCharObj.char[charCnt].bcid+
'" data-pcElem="'+
this.battleCharObj.char[charCnt].elem+
'" data-pcPose="'+
this.battleCharObj.char[charCnt].pose+
'" data-pcState="'+
this.battleCharObj.char[charCnt].state+
'" data-pcSide="'+
this.battleCharObj.char[charCnt].side+
'" data-pcEndState="'+
(+this.battleCharObj.char[charCnt].endstate-this.battleCharObj.char[charCnt].turn)+
'">';

var temp3="";
if(this.battleCharObj.trap){
if(this.battleCharObj.trap.length>0 && this.battleCharObj.trap.length>trapCnt && this.battleCharObj.trap[trapCnt].panelid==panelCnt){
if(this.battleCharObj.oder[0].uid==USERNAME&&this.battleCharObj.oder[0].side==this.battleCharObj.trap[trapCnt].side)
temp3='<span class="trap_area">×</span>';
trapCnt++;
}
}

if(this.battleCharObj.char[charCnt].v_sun>=0){
var v_sun1=+this.battleCharObj.char[charCnt].v_sun;
var v_sun2=0;
}
else if(this.battleCharObj.char[charCnt].v_sun<0){
var v_sun1=0;
var v_sun2=+(-1*this.battleCharObj.char[charCnt].v_sun);
}

var temp4="";
temp4=
'<ul id="BIBar">'+
'<li class="hpBar" style="width:'+
(this.battleCharObj.char[charCnt].life-this.battleCharObj.char[charCnt].v_life)/this.battleCharObj.char[charCnt].life*100+
'%;"></li>'+
'<li class="sunBar" style="width:'+
(this.battleCharObj.char[charCnt].sun-v_sun1)/this.battleCharObj.char[charCnt].sun*100+
'%;"></li>'+
'<li class="sunBar2" style="width:'+
v_sun2/this.battleCharObj.char[charCnt].sun*100+
'%;"></li>'+
'</ul>'+
'<img src="'+
this.battleCharObj.char[charCnt].avt+
'">'+
'</li>';

battle_list+=temp1+temp2+temp3+temp4;

charCnt++;
}
else{

var temp1='<li class="battle_cell">';

var temp2="";
if(this.battleCharObj.trap){
if(this.battleCharObj.trap.length>trapCnt && this.battleCharObj.trap[trapCnt].panelid==panelCnt){
if(this.battleCharObj.oder[0].uid==USERNAME&&this.battleCharObj.oder[0].side==this.battleCharObj.trap[trapCnt].side)
temp2='<span class="trap_area">×</span>';
trapCnt++;
}
}

var temp3="</li>";

battle_list+=temp1+temp2+temp3;

}
}//for

var oder_list="";
var temp_delay="";

$.each(this.battleCharObj.oder,function(){
oder_list+='<li class="oder_cell" data-oderbcid="'+
this.bcid+
'" data-time="'+
this.time+
'" data-spd="'+
this.oder+
'">'+
'<span class="oderDelayView">'+
(temp_delay!=""?'＋'+((+this.time)-(+temp_delay)):'＋0')+
'</span>'+
'<img src="'+
this.avt+
'"></li>';
temp_delay==""?temp_delay=this.time:false;
});

$("#b_wrap").empty();
$("#b_wrap").append(
'<div id="b_area"><ul id="battle_area" class="clear">'+
battle_list+
'</ul></div>'+
'<ul id="oder_area" class="clear">'+
oder_list+
'</ul>');

$('li.battle_cell').height($('li.battle_cell').width());//高さの描画
$('#b_area').scrollLeft(scrollLeft);//スクロール位置合わせ

};//バトルエリアにデータセット

return constructor;}());//pcset系統

/////////////////////////
APP.anime=(function(){

var constructor=function(animeObj){
this.animeObj=animeObj;
};//コンストラクター

///////////
constructor.prototype.animeWait=function(waitTime){

var ddd=$.Deferred();

this.animeObj.dom.animate({opacity:1},{duration:(waitTime*1000)}).promise().then(function(){
ddd.resolve();
}
,
function(jqXHR, textStatus, errorThrown){
alert(textStatus+"_"+errorThrown);
});
return ddd.promise();
};//ただのアニメ待機

////////////
constructor.prototype.animeTrapEL=function(resTrpObj){

var ddd=$.Deferred();

if(!resTrpObj)ddd.resolve();
else{

var prependTxt1='<div id="trapEL"><h4>TRAP!!</h4><ul>';
var prependTxt2='';
var prependTxt3='</ul></div>';
var flg=0;

$.each(resTrpObj,function(){

//console.log("SXSDERF__"+this);

if(this!=false){
prependTxt2+='<li class="clear" style="margin-bottom:10px;"><img src="'+
this.avt+
'"><p>'+this.name+'</p></li>';
flg++;
}

});
if(flg==0)ddd.resolve();
else{

SE.lose.currentTime=0;
SE.lose.play();

$('#b_wrap').prepend(prependTxt1+prependTxt2+prependTxt3);
$('#b_wrap').addClass('trapEL');

$("#trapEL").animate({opacity:1},{duration:2000}).promise().then(function(){
$('#b_wrap').removeClass('trapEL');
$("#trapEL").remove();
ddd.resolve();
}
,
function(jqXHR, textStatus, errorThrown){
alert(textStatus+"_"+errorThrown);
});
}
}

return ddd.promise();
};//トラップ発火画面アニメ

////////////////
constructor.prototype.animeBar=function(){

var ddd=$.Deferred();

this.animeObj.dom.animate({width:this.animeObj.resultWidth+"%"},{duration:2000}).promise().then(function(){
ddd.resolve();
//if(dfd!=null)return dfd.resolve();
}
,
function(jqXHR, textStatus, errorThrown){
alert(textStatus+"_"+errorThrown);
});
return ddd.promise();
};//棒の変化アニメ

////////////////
constructor.prototype.animeCutin=function(cutinList){

var ddd=$.Deferred();

$("#info_dl .cutin").remove();

$('#info_dl').append('<div class="cutin"><img src="'+
cutinList.avt+
'"><p>'+
cutinList.cment+
'</p></div>');

$("#info_dl .cutin").animate({left:0+"%"},{duration:250})
.promise().then(function(){},
function(jqXHR,textStatus,errorThrown){alert(textStatus+"_"+errorThrown);})
.then(function(){
var dfd=$.Deferred();
$("#info_dl .cutin").animate({opacity:1},{duration:2000})
.promise().then(function(){
dfd.resolve();
},
function(jqXHR,textStatus,errorThrown){alert(textStatus+"_"+errorThrown);});
return dfd.promise();
}
,
function(jqXHR,textStatus,errorThrown){alert(textStatus+"_"+errorThrown);})
.then(function(){
$("#info_dl .cutin").animate({left:-100+"%"},{duration:250})
.promise().then(function(){
$("#info_dl .cutin").remove();
ddd.resolve();
},
function(jqXHR,textStatus,errorThrown){alert(textStatus+"_"+errorThrown);});
}
,
function(jqXHR,textStatus,errorThrown){alert(textStatus+"_"+errorThrown);});

return ddd.promise();
};//カットインアニメ

///////////////
constructor.prototype.animeResultCutin=function(cutinResultObj){


var ddd=$.Deferred();

$("#info_dl .cutin").remove();

if(cutinResultObj.hantei=="成功")
$('#info_dl').append('<div class="cutin"><img src="'+
cutinResultObj.avt+
'"><p><span data-skillColor="'+cutinResultObj.skillelem+'">'+cutinResultObj.skill+'</span>！<br>'+
'こうかは <span class="skillEffectTxt">'+cutinResultObj.weak+'</span> だ'+
'</p></div>');
else
$('#info_dl').append('<div class="cutin"><img src="'+
cutinResultObj.avt+
'"><p><span data-skillColor="'+cutinResultObj.skillelem+'">'+cutinResultObj.skill+'</span>！<br>'+
'…は はずれた'+
'</p></div>');

$("#info_dl .cutin").animate({left:0+"%"},{duration:250}).promise()
.then(function(){
var dfd=$.Deferred();
$("#info_dl .cutin").animate({opacity:1},{duration:1500}).promise()
.then(function(){
dfd.resolve();
},
function(jqXHR,textStatus,errorThrown){alert(textStatus+"_"+errorThrown);});
dfd.promise();
}
,
function(jqXHR,textStatus,errorThrown){alert(textStatus+"_"+errorThrown);}).promise()
.then(function(){
$("#info_dl .cutin").animate({left:-100+"%"},{duration:250}).promise()
.then(function(){
$("#info_dl .cutin").remove();
ddd.resolve();
},
function(jqXHR,textStatus,errorThrown){alert(textStatus+"_"+errorThrown);});
}
,
function(jqXHR,textStatus,errorThrown){alert(textStatus+"_"+errorThrown);});

return ddd.promise();

};//リザルトカットインアニメ

/////////////////
constructor.prototype.sideDelete=function(){

var ddd=$.Deferred();

this.animeObj.dom.animate({opacity:0},{duration:100}).promise().then(function(){
ddd.resolve();
}
,
function(jqXHR,textStatus,errorThrown){alert(textStatus+"_"+errorThrown);});
return ddd.promise();
};//勢力の消滅アニメ

///////////////
constructor.prototype.nameScroll=function(scrollWidth){
var dom=this.animeObj.dom;
dom.stop();
dom.css('left',10+"px");
dom.animate({"left":"-"+scrollWidth+"px"},{duration:scrollWidth*20})
.promise().then(function(){
dom.css('left',10+"px");
}
,
function(jqXHR,textStatus,errorThrown){alert(textStatus+"_"+errorThrown);});
};//名前のスクロールアニメ

/////////////
constructor.prototype.nameScroll_2=function(scrollWidth){
var dom=this.animeObj.dom;
dom.stop();
dom.css('margin-left',0+"px");
dom.animate({"margin-left":"-"+scrollWidth+"px"},{duration:scrollWidth*10})
.promise().then(function(){
dom.css('margin-left',0+"px");
}
,
function(jqXHR,textStatus,errorThrown){alert(textStatus+"_"+errorThrown);});
};//名前のスクロールアニメ2

////////////
constructor.prototype.topToScroll=function(dom){
var ddd=$.Deferred();
$('html,'+dom).stop();
$('html,'+dom).animate({"scrollTop":0},500)
.promise()
.then(function(){
ddd.resolve();
}
,
function(jqXHR,textStatus,errorThrown){alert(textStatus+"_"+errorThrown);});
return ddd.promise();
};//トップへのスクロールアニメ

////////////
constructor.prototype.bottomToScroll=function(dom){
var ddd=$.Deferred();
$('html,'+dom).stop();
$('html,'+dom).animate({"scrollTop":$(document).height()},500)
.promise()
.then(function(){
ddd.resolve();
}
,
function(jqXHR,textStatus,errorThrown){alert(textStatus+"_"+errorThrown);});
return ddd.promise();
};//トップへのスクロールアニメ

return constructor;}());//アニメ系統


///////////////////////////
APP.page=(function(){

var constructor=function(){};//コンストラクター

constructor.prototype.writeComent=function(cmtObj,key,type,qtFlg,se){

var quote='';
var matchArry=cmtObj.talk.match(/\[QT!\d+\]/g);
cmtObj.talk=cmtObj.talk.replace(/\[QT!\d+\]/g,'');
if(matchArry!==null){
$.each(matchArry,function(index){
matchArry[index]=this.replace('[QT!','').replace(']','');
quote+='<p id="quote_content" class="float_btn" data-quote-id='+matchArry[index]+'>引用：'+matchArry[index]+'</p>';
});
}

if(type&&cmtObj.type!='bgm'){
if(cmtObj.talk.length<=50){
cmtObj.talk=cmtObj.talk.replace(/\n/g,'<br>').replace(/\r/g,'<br>').replace(/\r\n/g,'<br>');
cmtObj.talk=cmtObj.talk.replace(/^(<br>)+/,'');
var talkArray=[];
talkArray=cmtObj.talk.split('');
var talkArrLen=talkArray.length;
}
else{
cmtObj.talk=SANITAIZE.encode(cmtObj.talk);
type=false;
}
}
else{
cmtObj.talk=SANITAIZE.encode(cmtObj.talk);
type=false;
}

var talk='';
if(type&&cmtObj.type!='bgm'){
var brCnt=0;
$.each(talkArray,function(i){
if(talkArray[4*i]=='<'&&talkArray[4*i+1]=='b'&&talkArray[4*i+2]=='r'&&talkArray[4*i+3]=='>')brCnt++;
else return false;
});
if(brCnt){
talk=talkArray.splice(0,4*brCnt+1);
talk=talk.join('');
}
else talk=talkArray.splice(0,1);

if(se){
SE.comentL.currentTime=0;
SE.comentL.play();
}

}//タイピング描写ができる場合
else if(cmtObj.type=='bgm'){
var bgmTalkArr=cmtObj.talk.split('?');

if(AUDIO.playFlg=='bgm'&&AUDIO.playId=='bgm'+bgmTalkArr[0]){
talk='<p class="clear">'+
'<span class="bgm_name">'+bgmTalkArr[2]+'</span>'+
'<div data-bgmId="'+bgmTalkArr[0]+'" data-bgmurl="'+bgmTalkArr[1]+'"><span id="playBgm_btn" class="bgm_cntBtn selectedBorder">停止</span></div></p>';
}//コメントのBGMと同じものがすでに再生されている場合
else{
talk='<p class="clear">'+
'<span class="bgm_name">'+bgmTalkArr[2]+'</span>'+
'<div data-bgmId="'+bgmTalkArr[0]+'" data-bgmurl="'+bgmTalkArr[1]+'"><span id="playBgm_btn" class="bgm_cntBtn">再生</span></div></p>';
}//コメントのBGMと同じものがすでに再生されてない場合

if(se){
SE.comentL.currentTime=0;
SE.comentL.play();
}

}//BGMコメントを描写する場合
else{
talk=cmtObj.talk.replace(/^(<br>)+/,'');

if(se){
SE.comentL.currentTime=0;
SE.comentL.play();
}

}//コメントをそのまま描写する場合

var talk_html='';
if(USERNAME==cmtObj.uid){

talk_html='<dt class="persn myPersn">'
+cmtObj.persn+
'</dt><dd class="talk myTalk"><img src="'
+cmtObj.avt+
'" onerror="this.onerror=null;this.src=\'../avatar/none.png\'"><div class="phrse"><div data-viewtalkid="'+cmtObj.id+'">'+
quote+
'<p style="clear:both;">'+talk+'</p>'+
'</div><span class="fukiL"></span>'+
'</div></dd><div class="info myInfo">';
if(qtFlg==false){
talk_html+='<span class="talkMenu" data-talktype="'+cmtObj.type+'">MENU</span>';
}
talk_html+='<span class="date">'
+cmtObj.datetime+
'</span><span class="num">'
+cmtObj.id+
'</span><span class="comentUid">'
+cmtObj.uid+
'</span></div>';

}
else{

talk_html='<dt class="persn otherPersn">'
+cmtObj.persn+
'</dt><dd class="talk otherTalk"><img src="'
+cmtObj.avt+
'" onerror="this.onerror=null;this.src=\'../avatar/none.png\'"><div class="phrse"><div data-viewtalkid="'+cmtObj.id+'">'+
quote+
'<p style="clear:both;">'+talk+'</p>'+
'</div><span class="fukiR"></span>'+
'</div></dd><div class="info otherInfo"><span class="date">'
+cmtObj.datetime+
'</span><span class="num">'
+cmtObj.id+
'</span><span class="comentUid">'
+cmtObj.uid+
'</span>';
if(qtFlg==false){
talk_html+='<span class="talkMenu" data-talktype="'+cmtObj.type+'">MENU</span>';
}
talk_html+='</div>';

}

if(key=='append'&&qtFlg==false){
$("#view").append(talk_html);
}
else if(key=='prepend'&&qtFlg==false){
$("#view").prepend(talk_html);
}
else if(qtFlg==true){
$("#quoteContent").append(talk_html);
}//引用表示

var timerId=null;
var typingWord=function(talkArr){

if(talkArr.length){
var waitTime=50;

var brCnt=0;
$.each(talkArr,function(i){
if(talkArr[4*i]=='<'&&talkArr[4*i+1]=='b'&&talkArr[4*i+2]=='r'&&talkArr[4*i+3]=='>')brCnt++;
else return false;
});

if(brCnt){
if(talkArr[4*brCnt]=='、'||talkArr[4*brCnt]==','||talkArr[4*brCnt]=='…'){
waitTime=500;
}
var tempTalk=talkArr.splice(0,4*brCnt+1);
talk+=tempTalk.join('');
}
else{
if(talkArr[0]=='、'||talkArr[0]==','||talkArr[0]=='…'){
waitTime=500;
}
talk+=talkArr.splice(0,1);
}
$('[data-viewtalkid='+cmtObj.id+'] p:nth-last-of-type(1)').html(talk);

timerId=setTimeout(typingWord,waitTime,talkArr);
}
else{
clearTimeout(timerId);
}
}//打鍵感をもたらす

if(type){
var waitTime=50;
if(talkArray[0]=='、'||talkArray[0]==','||talkArray[0]=='…'){
waitTime=500;
}

setTimeout(typingWord,waitTime,talkArray);
//console.log(SE.arr);
}

}//コメント描画

return constructor;}());//ページ処理系統
