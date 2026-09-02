//World Clock (No DST - standard time only!) 
//By http://www.btinternet.com/~kurt.grigg/javascript



if ((document.getElementById && 
document.firstChild &&
typeof document.getElementsByTagName != "undefined") &&
window.addEventListener || window.attachEvent){
(function(){

//Alter clock colours here:

var fCol = "#3264C8"; //face/number colour.
var dCol = "#3264C8"; //dot colour.
var hCol = "#3264C8"; //hours colour.
var mCol = "#666666"; //minutes colour.
var sCol = "#666666"; //seconds colour.
var cCol = "#3264C8"; //date colour.
var aCol = "#3264C8"; //am-pm colour.
var bCol = "#CCCCCC"; //select/form background colour.
var tCol = "#3264C8"; //select/form text colour.

//Add places here - make sure format is correct!

var locations = [
["Your Time","0"],
["GMT","0"],  
["Rome","1"], 
["Cairo","2"], 
["Moscow","3"], 
["Bangkok","7"], 
["Hong Kong","8"], 
["Paris","7"], 
["Tokyo","9"], 
["Sydney","10"], 
["New York","-5"]
]


//Alter nothing below! Alignments will be lost!

var d = document;
var idx = d.getElementsByTagName('div').length;
var ids = d.getElementsByTagName('select').length;

var y = 87;
var x = 60;
var h = 4;
var m = 5;
var s = 5;
var cf = [];
var cd = [];
var ch = [];
var cm = [];
var cs = [];
var face = "3 4 5 6 7 8 9 10 11 12 1 2";
face = face.split(" ");
var n = face.length;
var e = 360/n;
var hDims = 30/4;
var zone = 0;
var isItLocal = true;
var ampm = "";
var daysInMonth = 31;
var todaysDate = "";
var oddMinutes;
var getOddMinutes;
var addOddMinutes;
var plusMinus = false;

d.write("<div class = 'theContainer'>"                                    
+"<div id = 'theCities"+idx+"' class = 'citystyle'>"
+"<p style = 'margin:0px;'>" 
+"<select id = 'city"+ids+"' class = 'selectstyle'>");

for (var i = 0; i < locations.length; i++){
 d.write("<option value = "+locations[i][1]+">"+locations[i][0]+"<\/option>");
}

d.write("<\/select><\/p><\/div>");

d.write('<div id = "theDate'+(idx)+'" class = "datestyle" style = "color:'+cCol+'">\!<\/div>');
d.write('<div id = "amOrPm'+(idx)+'" class = "ampmstyle" style = "color:'+aCol+'">\!<\/div>');

for (var i = 0; i < n; i++){
 d.write('<div id = "theFace'+(idx+i)+'" class="facestyle" style="color:'+fCol+'">'+face[i]+'<\/div>'); 
}

for (var i = 0; i < n; i++){
 d.write('<div id = "theDots'+(idx+i)+'" class = "handsanddotsstyle" style = "background-color:'+dCol+'"><\/div>');
}

for (var i = 0; i < h; i++){
 d.write('<div id = "H'+(idx+i)+'" class = "handsanddotsstyle" style = "background-color:'+hCol+'"><\/div>');
}

for (var i = 0; i < m; i++){
 d.write('<div id = "M'+(idx+i)+'" class = "handsanddotsstyle" style = "background-color:'+mCol+'"><\/div>');
}

for (var i = 0; i < s; i++){
 d.write('<div id = "S'+(idx+i)+'" class = "handsanddotsstyle" style = "background-color:'+sCol+'"><\/div>');
}

d.write("<\/div>");



function init(){
for (var i = 0; i < n; i++){
 cf[i] = d.getElementById("theFace"+(idx+i)).style;
  cf[i].top = y-6+30*1.4*Math.sin(i*e*Math.PI/180)+"px";
  cf[i].left = x-6+30*1.4*Math.cos(i*e*Math.PI/180)+"px";
 }

for (var i = 0; i < n; i++){
 cd[i] = d.getElementById("theDots"+(idx+i)).style;
  cd[i].top = y+30*Math.sin(i*e*Math.PI/180)+"px";
  cd[i].left = x+30*Math.cos(i*e*Math.PI/180)+"px";
 }

for (var i = 0; i < h; i++){
 ch[i] = d.getElementById("H"+(idx+i)).style;
 }

for (var i = 0; i < m; i++){
 cm[i] = d.getElementById("M"+(idx+i)).style;
 }

for (var i = 0; i < s; i++){
 cs[i] = d.getElementById("S"+(idx+i)).style;
 }


d.getElementById("amOrPm"+idx).style.top = y-8+"px";
d.getElementById("amOrPm"+idx).style.left = x+6+"px";

d.getElementById("theCities"+idx).style.top = y-80+"px";
d.getElementById("theCities"+idx).style.left = x-55+"px";

d.getElementById("theDate"+idx).style.top = y+55+"px";
d.getElementById("theDate"+idx).style.left = x-49+"px";

d.getElementById("city"+ids).style.backgroundColor = bCol;
d.getElementById("city"+ids).style.color = tCol;

ClockAndAssign();
}


function lcl(){
var c = document.getElementById("city"+ids);
zone = c.options[c.selectedIndex].value;
isItLocal = (c.options[0].selected);
plusMinus = (zone.charAt(0) == "-");
oddMinutes = (zone.indexOf(".") != -1);
if (oddMinutes){
 getOddMinutes = zone.substring(zone.indexOf(".")+1,zone.length);
 }
if (plusMinus)
 addOddMinutes = (oddMinutes)?parseInt(-getOddMinutes):0;
else
 addOddMinutes = (oddMinutes)?parseInt(getOddMinutes):0;
}


function ClockAndAssign(){
var hourAdjust = 0;
var dayAdjust = 0;
var monthAdjust = 0;
var now = new Date();
var ofst = now.getTimezoneOffset()/60;

var secs = now.getSeconds();
var secOffSet = secs - 15;
if (secs < 15){ 
 secOffSet = secs + 45;
 }
var sec = Math.PI * (secOffSet/30);

var mins = now.getMinutes();
if (oddMinutes){ 
 mins = mins+addOddMinutes;
 }

var minOffSet = mins - 15;
if (mins < 15){ 
 minOffSet = mins + 45;
 }
var min = Math.PI * (minOffSet/30);

if (mins < 0){
 mins += 60;
 hourAdjust = -1;
 }

if (mins > 59){
 mins -= 60;
 hourAdjust = 1;
 }

hr = (isItLocal)?now.getHours()+hourAdjust:(now.getHours()+parseInt(ofst))+parseInt(zone)+hourAdjust;
hrs = Math.PI * (hr-3)/6 + Math.PI * (parseInt(mins)) / 360;

if (hr < 0){
 hr += 24;
 dayAdjust = -1;
 }

if (hr > 23){
 hr -= 24;
 dayAdjust = 1;
 }

day = now.getDate() + dayAdjust;
if (day < 1){
 day += daysInMonth; 
 monthAdjust = -1;
 }

if (day > daysInMonth){
 day -= daysInMonth; 
 monthAdjust = 1;
 }

month = parseInt(now.getMonth() + 1 + monthAdjust);
if (month == 2){
 daysInMonth = 28;
 }

year = now.getYear();
if (year < 2000){
 year = year + 1900;
 }

leapYear = (year%4 == 0);
if (leapYear&&month == 2){
 daysInMonth = 29;
 }

if (month < 1){
 month += 12;
 year--;
 }

if (month > 12){
 month -= 12;
 year++;
 }

todaysDate = day+" "+month+" "+year;
ampm = (hr > 11)?"pm":"am";

for (var i = 0; i < s; i++){
 cs[i].top = y+(i*hDims)*Math.sin(sec) + "px";
 cs[i].left = x+(i*hDims)*Math.cos(sec) + "px";
 }

for (var i = 0; i < m; i++){
 cm[i].top = y+(i*hDims)*Math.sin(min) + "px";
 cm[i].left = x+(i*hDims)*Math.cos(min) + "px";
 }

for (var i = 0; i < h; i++){
 ch[i].top = y+(i*hDims)*Math.sin(hrs) + "px";
 ch[i].left = x+(i*hDims)*Math.cos(hrs) + "px";
 }

d.getElementById("amOrPm"+idx).firstChild.data = ampm;
d.getElementById("theDate"+idx).firstChild.data = todaysDate;
setTimeout(ClockAndAssign,100);
}


if (window.addEventListener){
 window.addEventListener("load",init,false);
 d.getElementById("city"+ids).addEventListener("change",lcl,false);
 }  
else if (window.attachEvent){
 window.attachEvent("onload",init);
 d.getElementById("city"+ids).attachEvent("onchange",lcl);
 } 

})();
}