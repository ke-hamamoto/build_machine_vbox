jQuery(function($){

/////////////////
$(document).on('click','#goods_preview,.cvPopup,.modalList',function(event){
$(this).remove();
});
////////////////

window.addEventListener('popstate',function(e){

var historycntNow=e.state?e.state.cnt:0;

if($('.popDel,.cvPopup').length){

$('.popDel,.cvPopup').remove();
$('body').css({'overflow-y':'auto'});

if(HISTORYCNT>historycntNow){window.history.go( 1 );}//戻るボタンの場合
else{window.history.go( -1 );}//進むボタンの場合
}//一時画面が存在している場合
else{

if(historycntNow){

//console.log(HISTORYCNT+"/"+historycntNow);
HISTORYCNT=historycntNow;

if(location.hash!=''){OLDHASHARR=location.hash.replace('#','').split('&');}
else{OLDHASHARR=[];}

}//初期画面でない場合(popstateカウント!=0)
else{
if(HISTORYCNT>historycntNow){window.history.go( -1 );}//戻るボタンの場合
}//初期画面である場合(popstateカウント=0)


}//一時画面が存在していない場合


});

});//EOF