
process.env.TZ = "Asia/Tokyo";

var fs=require("fs");
require('date-utils');

/////////////mysql
const mysql=require('mysql');
const dbhost='mysql';
const dbpass='00000';

/////////////server

/*SSH化
var option={
key:fs.readFileSync(
'/var/ssl_certs'+
'/FQDN/production/domain.key'
),
cert:fs.readFileSync(
'/var/ssl_certs'+
'/FQDN/production/signed.crt'
)
};
var server=require("https").createServer(option,function(req, res){}).listen(8080);
*/

var server=require("http").createServer(function(req, res){}).listen(8080);

var io=require("socket.io").listen(server);

console.log('サーバーが起動しました');

var util=require('util');
var spawn=require('child_process').spawn;



//イベントの定義
io.sockets.on("connection",function(socket){//ユーザーが入ってきたか？

try{

socket.on("enter",function(data){
socket.nameId=data.uid;
socket.roomId=data.room;
socket.roomToken='roomid_'+data.rmid;
socket.rmid=data.rmid;
socket.join(socket.roomId);

var php=spawn('php',['/var/www/html/test_app/enter.php']);
var obj={};
obj.id=(""+socket.id);
obj.nameId=(""+data.uid);
obj.roomId=(""+data.room);
obj.rmid=(""+data.rmid);

if(data.uid!==undefined){
php.stdin.write(JSON.stringify(obj));
php.stdin.end();
php.stdout.on('data',function(res){

try{
res=res.toString('utf-8');
res=JSON.parse(res);
obj.cnt=res.cnt;
obj.myjoin=res.preSktId;

io.sockets.in(socket.roomId).emit("enter",obj);
io.sockets.in(socket.roomToken).emit("roomList_mem_refresh",obj);
}catch(e){
console.log("JSON.parse error catched!");
obj.cnt=0;
obj.myjoin=res.preSktId;

io.sockets.in(socket.roomId).emit("enter",obj);
io.sockets.in(socket.roomToken).emit("roomList_mem_refresh",obj);
}

});
}
});

socket.on("disconnect",function(){
socket.leave(socket.roomId);

var php=spawn('php',['/var/www/html/test_app/leave.php']);
var obj={};
obj.id=(""+socket.id);
obj.nameId=(""+socket.nameId);
obj.roomId=(""+socket.roomId);
obj.rmid=(""+socket.rmid);

if(socket.nameId!==undefined){
php.stdin.write(JSON.stringify(obj));
php.stdin.end();
php.stdout.on('data',function(res){
res=res.toString('utf-8');
obj.cnt=res;
socket.broadcast.to(socket.roomId).emit("leave",obj);
io.sockets.in(socket.roomToken).emit("roomList_mem_refresh",obj);
delete socket;
});
}
});



//追加メッセージ受信
socket.on("add", function(data){
io.sockets.in(socket.roomId).emit("add",data);
});

socket.on("remv", function(num){
io.sockets.in(socket.roomId).emit("remv",num);
});

socket.on("edit", function(obj){
io.sockets.in(socket.roomId).emit("edit",obj);
});

socket.on("battle_request", function(obj){
socket.broadcast.to(socket.roomId).emit("battle_request",obj);
});

socket.on("battle_req_cancel", function(obj){
socket.broadcast.to(socket.roomId).emit("battle_req_cancel",obj);
});

socket.on("selectPanelRewrite", function(setPanelObj){
io.sockets.in(socket.roomId).emit("selectPanelRewrite",setPanelObj);
});

socket.on("selectPanelDel", function(obj){
socket.broadcast.to(socket.roomId).emit("selectPanelDel",obj);
});

socket.on("startBattleSet", function(battleCharObj){
io.sockets.in(socket.roomId).emit("startBattleSet",battleCharObj);
});

socket.on("BattleNowCancel", function(obj){
socket.broadcast.to(socket.roomId).emit("BattleNowCancel",obj);
});

socket.on("actedTurn", function(data){
io.sockets.in(socket.roomId).emit("actedTurn",data);
});

socket.on("reloadBattle", function(data){
io.sockets.connected[socket.id].emit("reloadBattle",data);
});

socket.on("cmentAvtCheck", function(data){
io.sockets.in(socket.roomId).emit("cmentAvtCheck",data);
});

socket.on("refreshTool", function(postData){
io.sockets.in(socket.roomId).emit("refreshTool",postData);
});

socket.on("kengen", function(data){
io.sockets.in(socket.roomId).emit("kengen",data);
});

socket.on("leave_room", function(room){
io.sockets.in(room).emit("leave_room");
});

socket.on("reload_sess", function(data){
if(data.room!==undefined){
io.sockets.in(data.room).emit("reload_sess",data.sid);
}
});


///from roomList.php/////
socket.on("enter_roomList",function(data){
socket.uidToken=data;
socket.join(socket.uidToken);

try{

////////
var db=mysql.createConnection({
host:dbhost,
user:'root',
password:dbpass,
database:'chat_sys',
timezone:'jst'
});

db.on('error',(err)=>{
console.log("error connection DB");
db=null;
return;
});//error検知

db.connect(function(err){

if(err){
console.log("error connection DB");
return;
}

db.beginTransaction((err)=>{
if(err){db.destroy();return;}

var roomid_arr=[];
var uid=null;
var query_mem1=new Promise((resolve,reject)=>{

db.query('SELECT uid,join_list FROM user WHERE token=?;',[socket.uidToken])
.on('error',function(err){
console.log('query failed!');
db.rollback(function(){db.destroy();return;});
})//error
.on('result',function(row){

uid=row.uid;
var join_arr=row.join_list.split(',');

join_arr.forEach(function(val){
roomid_arr.push(val.split(' ')[0]);
});

})//result
.on('end',function(){
return resolve();
});//end

});//promise


var query_mem2=new Promise((resolve,reject)=>{

Promise.all([query_mem1]).then(()=>{

db.query('SELECT id FROM room_list WHERE rm_uid=?;',[uid])
.on('error',function(err){
console.log('query failed!');
db.rollback(function(){db.destroy();return;});
})//error
.on('result',function(row){
roomid_arr.push(row.id);
})//result
.on('end',function(){
return resolve();
});//end

});//promise.all
});//query_mem2


var query_mem3=new Promise((resolve,reject)=>{

Promise.all([query_mem2]).then(()=>{

roomid_arr.forEach(function(val){
db.query('SELECT id FROM room_list WHERE id=?;',[val])
.on('error',function(err){
console.log('query failed!');
db.rollback(function(){db.destroy();return;});
})//error
.on('result',function(row){
socket.join('roomid_'+row.id);
})//result
.on('end',function(){
return resolve();
});//end

});//each

});//promise.all
});//query_mem3


Promise.all([query_mem3]).then(()=>{

db.commit(function(err){
if(err){
console.log('commit failed!');
db.rollback(function(){db.destroy();return;});
}
db.destroy();
});

});


});//transaction

});//connect

}catch(e){db.destroy();console.log("mem_ref_sql error catched!");}

});//enter_roomList

socket.on("disconnect",function(){
socket.leave(socket.uidToken);
socket.leave(socket.roomToken);
delete socket;
});

socket.on("mailBoxRes", function(data){
io.sockets.in(data.uidToken).emit("mailBoxRes",data);
});

}catch(e){delete socket;console.log("socket error catched!");}


});//socket.io

/////////////cron
const {CronJob}=require('cron');
new CronJob('0 0 3 * * *',()=>{

try{

////////
var db=mysql.createConnection({
host:dbhost,
user:'root',
password:dbpass,
database:'chat_sys',
timezone:'jst'
});

db.on('error',(err)=>{
console.log("error connection DB");
db=null;
return;
});//error検知

db.connect(function(err){

if(err){
console.log("error connection DB");
return;
}

db.beginTransaction((err)=>{
if(err){db.destroy();return;}

var query0=new Promise((resolve,reject)=>{

var dt=new Date();
var date=dt.setTime(dt.getTime())+32400000-24*1000*60*60;
date=new Date(date);

db.query('DELETE FROM b_list WHERE datetime<=?;',[date])
.on('error',function(err){
console.log('query failed!');
db.rollback(function(){db.destroy();return;});
})//error
.on('end',function(){
return resolve();
});//end

});//promise

var query1=new Promise((resolve,reject)=>{

var dt=new Date();
var date=dt.setTime(dt.getTime())+32400000-24*1000*60*60;
date=new Date(date);

db.query('DELETE FROM app_list WHERE datetime<=?;',[date])
.on('error',function(err){
console.log('query failed!');
db.rollback(function(){db.destroy();return;});
})//error
.on('end',function(){
return resolve();
});//end

});//promise


var ability_arr=[];
var query2=new Promise((resolve,reject)=>{

db.query('SELECT id FROM ability WHERE rare>? ORDER BY RAND() LIMIT 2',[3])
.on('error',function(err){
console.log('query failed!');
db.rollback(function(){db.destroy();return;});
})//error
.on('result',function(row){

ability_arr.push(row.id);

})//result
.on('end',function(){
return resolve();
});//end

});//promise


var skill_arr=[];
var query3=new Promise((resolve,reject)=>{

db.query('SELECT id FROM skill WHERE rare>?  ORDER BY RAND() LIMIT 3',[3])
.on('error',function(err){
console.log('query failed!');
db.rollback(function(){db.destroy();return;});
})//error
.on('result',function(row){

skill_arr.push(row.id);

})//result
.on('end',function(){
return resolve();
});//end

});//promise


var query4=new Promise((resolve,reject)=>{

db.query('DELETE FROM daily_shop')
.on('error',function(err){
console.log('query failed!');
db.rollback(function(){db.destroy();return;});
})//error
.on('end',function(){
return resolve();
});//end

});//promise


var query5=new Promise((resolve,reject)=>{

Promise.all([query2,query3,query4]).then(()=>{

db.query('INSERT INTO daily_shop(skill,ability) VALUES(?,?)',[skill_arr.join(','),ability_arr.join(',')])
.on('error',function(err){
console.log('query failed!');
db.rollback(function(){db.destroy();return;});
})//error
.on('end',function(){
return resolve();
});//end

});

});


Promise.all([query0,query1,query5]).then(()=>{

db.commit(function(err){
if(err){
console.log('commit failed!');
db.rollback(function(){db.destroy();return;});
}
db.destroy();
});

});

});//transaction

});//connect

}catch(e){db.destroy();console.log("cron error catched!");}

},null,true);

