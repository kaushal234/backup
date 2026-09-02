
function printPage(){
    var html = '<html>' + $('html').html() + '</html>';
	var newWindow = window.open('','Preview','fullscreen=1,scrollbars=1');
    newWindow.document.write(html);
    newWindow.document.close();
    newWindow.focus();
    newWindow.print();
    newWindow.close();
};