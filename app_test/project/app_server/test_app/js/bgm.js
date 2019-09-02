var BGM_VOL=0.25;

var BGM_FUNC=function(){

jQuery(function($){

$(document).on('click','#bgm_close',function(){
if(AUDIO.playFlg=='sample'){
AUDIO.bgm.pause();
AUDIO.bgm=null;
AUDIO.playId=null;
AUDIO.playFlg=false;
$('#bgm_player').empty();
}
$('body').css({'overflow-y':'auto'});
$('#bgm_space').remove();
});


//////////////
$(document).on('click','#infoBGM',function(event){
$("body").prepend('<div id="previewAlert" class="cvPopup"><div></div></div>');
if(AUDIO.playFlg){
$('#previewAlert > div').append(
'<p id="viewAllBGM" class="previewAlertList clear clickar">BGM一覧へ</p>'+
'<p id="stopNowBGM"class="previewAlertList clear clickar">BGMを停止</p>'+
'<p id="bgmVol_open" class="previewAlertList clear clickar">音量調整</p>');
}
else{
$('#previewAlert > div').append(
'<p id="viewAllBGM" class="previewAlertList clear clickar">BGM一覧へ</p>'+
'<p id="bgmVol_open" class="previewAlertList clear clickar">音量調整</p>');
}
});
//////////////

$(document).on('click','#viewAllBGM',function(event){
selectBgm();
});//BGMの一覧描画


//////////////////
$(document).on('click','#stopNowBGM',function(event){

$('.bgm_cntBtn').removeClass('selectedBorder');
$('.bgm_cntBtn').text("再生");

AUDIO.bgm.pause();
AUDIO.bgm=null;
AUDIO.playId=null;
AUDIO.playFlg=false;
$('#bgm_player').empty();
});//現在のBGMの停止


/////////////////BGM
var g_obj_bgm=null;
function selectBgm(){

var load_rm="load_bgm_view";
$('body').prepend(LOAD_DIV.replace('REPLACE',load_rm));

var bgm_space=
'<div id="bgm_space" class="clear popDel">'+
'<h3 id="bgm_title">BGM一覧</h3>'+
'<p id="bgm_close">CLOSE</p>'+
'<ul id="bgm_about" class="clear"><li class="selected">フィールド</li><li>イベント</li><li>ダンジョン</li><li>戦闘</li></ul>'+
'<ul id="bgm_item" class="clear"></ul>'+
'</div>';
$('body').css({'overflow-y':'hidden'});
$('body').prepend(bgm_space);

$.ajax({
type:'POST',
url:'../view_bgm_select.php',
data:{uid:USERNAME},
timeout:TIMEOUT
})
.fail(function(jqXHR, textStatus, errorThrown){
alert(textStatus+"_"+errorThrown);
setTimeout(function(){$('#'+load_rm).remove();},200);
})
.done(function(data){

try{

g_obj_bgm=JSON.parse(data);
console.log(g_obj_bgm);
if(g_obj_bgm.field!==null){

$.each(g_obj_bgm.field,function(){
$("#bgm_item").append('<li class="clear" data-id="'+this.bgmid+'" data-bgmurl="'+this.url+'" data-name="'+this.name+'">'+
'<span data-bgmType="'+this.type+'">'+this.type+'</span>'+
'<span class="bgm_name">'+this.bgmid+'.'+this.name+'</span>'+
'<span class="bgm_uid">by:'+this.uid+'</span>'+
'<div><span id="playSampleBgm_btn" class="bgm_cntBtn">再生</span><span id="selectSampleBgm_btn" class="bgm_retBtn">選択</span></div></li>');
});

}

setTimeout(function(){$('#'+load_rm).remove();},200);
}catch(e){catch_parse(e,load_rm);}

});

};//selectBgm()

/******/
$(document).on('click','#bgm_about li',function(event){

if(AUDIO.playFlg=='sample'){
AUDIO.bgm.pause();
AUDIO.bgm=null;
AUDIO.playId=null;
AUDIO.playFlg=false;
$('#bgm_player').empty();
}

$('#bgm_about li').removeClass('selected');
$(this).addClass('selected');

$('#bgm_item li').remove();

content=null;
if($(this).index()==0){content='field';}
if($(this).index()==1){content='ev';}
if($(this).index()==2){content='dngn';}
if($(this).index()==3){content='battle';}

if(g_obj_bgm[content]!==null){
$.each(g_obj_bgm[content],function(){
$("#bgm_item").append('<li class="clear" data-id="'+this.bgmid+'" data-bgmurl="'+this.url+'" data-name="'+this.name+'">'+
'<span data-bgmType="'+this.type+'">'+this.type+'</span>'+
'<span class="bgm_name">'+this.bgmid+'.'+this.name+'</span>'+
'<span class="bgm_uid">by:'+this.uid+'</span>'+
'<div><span id="playSampleBgm_btn" class="bgm_cntBtn">再生</span><span id="selectSampleBgm_btn" class="bgm_retBtn">選択</span></div></li>');
});
}

});
/*****/


/////////////
$(document).on('click','#playSampleBgm_btn',function(event){

var tempNum='sample'+$(this).closest('li').attr('data-id');

if(AUDIO.playId!=tempNum){
AUDIO.playId=tempNum;
var bgmUrl=$(this).closest("li").attr("data-bgmurl");
var bgmId=$(this).closest("li").attr("data-id");

$('.bgm_cntBtn').removeClass('selectedBorder');
$('.bgm_cntBtn').text("再生");
$(this).addClass('selectedBorder');
$(this).text("停止");
AUDIO.playFlg='sample';

$("#bgm_player").empty();
$("#bgm_player").append('<audio id="samplePlayBgm" data-bgmId="'+bgmId+'" preload="auto">'+
'<source src="../'+bgmUrl+'.mp3" type="audio/mp3">'+
'</audio>');
AUDIO.bgm=document.getElementById("samplePlayBgm");
AUDIO.bgm.load();
AUDIO.bgm.volume=BGM_VOL;
AUDIO.bgm.play();
AUDIO.bgm.loop=true;
}
else if(AUDIO.playFlg){
$(this).removeClass('selectedBorder');
$(this).text("再生");
AUDIO.playFlg=false;
AUDIO.bgm.pause();
}
else if(!AUDIO.playFlg){
$(this).addClass('selectedBorder');
$(this).text("停止");
AUDIO.playFlg='sample';
AUDIO.bgm.volume=BGM_VOL;
AUDIO.bgm.play();
}
});//bgmの試聴

/////////////
$(document).on('click','#playBgm_btn',function(event){

var tempNum='bgm'+$(this).closest('div').attr('data-bgmid');

var bgmUrl=$(this).closest("div").attr("data-bgmurl");
var bgmId=$(this).closest("div").attr("data-bgmid");

if(AUDIO.playId!=tempNum){
AUDIO.playId=tempNum;

$('.bgm_cntBtn').removeClass('selectedBorder');
$('.bgm_cntBtn').text("再生");
$('[data-bgmid='+bgmId+'] .bgm_cntBtn').addClass('selectedBorder');
$('[data-bgmid='+bgmId+'] .bgm_cntBtn').text("停止");
AUDIO.playFlg='bgm';

$("#bgm_player").empty();
$("#bgm_player").append('<audio id="playBgm" data-bgmId="'+bgmId+'" preload="auto">'+
'<source src="../'+bgmUrl+'.mp3" type="audio/mp3">'+
'</audio>');
AUDIO.bgm=document.getElementById("playBgm");
AUDIO.bgm.load();
AUDIO.bgm.volume=BGM_VOL;
AUDIO.bgm.play();
AUDIO.bgm.loop=true;
}
else if(AUDIO.playFlg){
$('.bgm_cntBtn').removeClass('selectedBorder');
$(this).text("再生");
AUDIO.playFlg=false;
AUDIO.bgm.pause();
}
else if(!AUDIO.playFlg){
$('[data-bgmid='+bgmId+'] .bgm_cntBtn').addClass('selectedBorder');
$('[data-bgmid='+bgmId+'] .bgm_cntBtn').text("停止");
AUDIO.playFlg='bgm';
AUDIO.bgm.volume=BGM_VOL;
AUDIO.bgm.play();
}

});//bgmの本番再生

//////////////////
$(document).on('click','#selectSampleBgm_btn',function(event){


var load_rm="load_addBGMComent";
$('body').prepend(LOAD_DIV.replace('REPLACE',load_rm));

var dom=$(this).closest('#bgm_item li');

$.ajax({
type:'POST',
url:'../talk.php?mode=addTalk',
data:{data:dom.attr('data-id')+'?'+dom.attr('data-bgmurl')+'?'+dom.attr('data-name'),type:'bgm'},
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

if(AUDIO.playFlg=='sample'){
AUDIO.bgm.pause();
AUDIO.bgm=null;
AUDIO.playId=null;
AUDIO.playFlg=false;
$('#bgm_player').empty();
}
$('body').css({'overflow-y':'auto'});
$('#bgm_space').remove();

socketio.emit("add",data);
}
else alert(data.flg);

setTimeout(function(){$('#'+load_rm).remove();},200);
}catch(e){catch_parse(e,load_rm);}

});


});//bgmコメントの投稿



////////////////////////
var g_bgmVolVal=BGM_VOL;
$(document).on('click','#bgmVol_close',function(){
if(AUDIO.bgm!=null)AUDIO.bgm.volume=g_bgmVolVal;
BGM_VOL=g_bgmVolVal;
if(AUDIO.playFlg=='sample'){
AUDIO.bgm.pause();
AUDIO.bgm=null;
AUDIO.playId=null;
AUDIO.playFlg=false;
$('#bgm_player').empty();
}
$('body').css({'overflow-y':'auto'});
$('#bgmVol_space').remove();
});
$(document).on('click','#bgmVol_open',function(){
$('body').css({'overflow-y':'hidden'});

g_bgmVolVal=BGM_VOL;

var bgmVol_view='<div id="bgmVol_space" class="clear popDel">'+
'<p id="bgmVol_enter">決定</p>'+
'<p id="bgmVol_close">CLOSE</p>'+
'<div><h3 id="bgm_title">音量調整</h3></div>'+
'<ul id="bgm_item" class="clear">'+
'<li class="clear" data-id="1" data-bgmurl="bgm/Ariadne-battle" data-name="テストBGM">'+
'<span data-bgmtype="クール">テスト</span>'+
'<span class="bgm_name">0.テストBGM</span>'+
'<span class="bgm_uid">by:ユーフルカ</span>'+
'<div><span id="playSampleBgm_btn" class="bgm_cntBtn">再生</span></div></li>'+
'</ul>'+
'<div id="bgmVol_length_div"><input id="bgmVol_length" type="range" value="'+(BGM_VOL*1000)+'" min="0" max="1000"></div>'+
'</div>';

$('body').prepend(bgmVol_view);//画面の構築

});//BGMボリューム調整
$(document).on('click','#bgmVol_enter',function(){
if(AUDIO.playFlg=='sample'){
AUDIO.bgm.pause();
AUDIO.bgm=null;
AUDIO.playId=null;
AUDIO.playFlg=false;
$('#bgm_player').empty();
}
$('body').css({'overflow-y':'auto'});
$('#bgmVol_space').remove();
});



$(document).on('input','#bgmVol_length',function(e){
BGM_VOL=$('#bgmVol_length').val()/1000;
if(AUDIO.bgm!=null)AUDIO.bgm.volume=BGM_VOL;
});




});//EOF

};