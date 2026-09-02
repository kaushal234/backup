function _escape(str) {
    str = str.replace(/ /g, "+");
    str = str.replace(/%/g, '%25');
    str = str.replace(/\?/, '%3F');
    str = str.replace(/&amp;/, '%26');
    return str;
}

function showClock(obj) {
    var str = 'bed src="http://www.worldtimeserver.com/clocks/' + obj.wtsclock + "?";
    var prop;
    for (prop in obj) {
        if ('wtsclock' == prop || 'width' == prop || 'height' == prop || 'wmode' == prop || 'type' == prop) {
            continue;
        }
        str += (prop + "=" + _escape(obj[prop]) + "&");
    }
    str += '" ';
    str += ' width="' + obj.width + '"';
    str += ' height="' + obj.height + '"';
    var isOpera = (navigator.userAgent.indexOf("Opera") != -1) ? true : false;
    if (isOpera != true) {
        str += ' wmode="' + obj.wmode + '"';
    }
    str += ' type="application/x-shockwave-flash" allowscriptaccess="always" />';
    document.write("<em" + str);
}