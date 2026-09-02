{literal}
<script type="text/javascript">

function is_email(email){
	var reg='^[a-z0-9]+([_|\.|-]{1}[a-z0-9]+)*@[a-z0-9]+([_|\.|\-]{1}[a-z0-9]+)*[.]{1}[a-z]{2,6}$';
	var exp = new RegExp(reg);
	return exp.test(email.toLowerCase());
}

function addRecipient(){
	var out=document.getElementById('to[2]');
	var inName=document.getElementById('ExtName').value;
	var inEmail=document.getElementById('ExtEmail').value;
	if(inName!='' && inEmail!=''){
		if( is_email(inEmail) ){
		var dataOut=out.value;
		out.value=dataOut+'<'+inName+';'+inEmail+'>';
		document.getElementById('ExtName').value='';
		document.getElementById('ExtEmail').value='';
		}
		else alert('ERROR: '+inEmail+' is not a valid email, please try again.');
	}
	else alert('ERROR: Recipient Name and Recipient Email are required, please try again.');
}

function delRecipient(){
	document.getElementById('to[2]').value="";
}

function unsetDisable(id){
	var el=document.getElementById(id);
	el.removeAttribute('disabled');
}

function getFileSize(element){
    if(window.ActiveXObject){
        var fso = new ActiveXObject("Scripting.FileSystemObject");
        var filepath = element.value;
        var thefile = fso.getFile(filepath);
        var sizeinbytes = thefile.size;
    }else{
        var sizeinbytes = element.files[0].size;
    }
	return sizeinbytes;
}

function getFileSizeDisplay(sizeInBytes){
	var unitList = new Array('B', 'KB', 'MB', 'GB');
    fileSize = sizeInBytes;
    i = 0;
    while(fileSize>900){
    	fileSize/=1024;
        i++;
    }
    return (Math.round(fileSize*100)/100)+' '+unitList[i];
}

function checkFileSize(element){
	var fileSizeLimit = {/literal}{$FILE_SIZE_LIMIT}{literal};
	var fileSize = getFileSize(element);
	if(fileSizeLimit < fileSize){
		var fileSizeLimitDisplay = getFileSizeDisplay(fileSizeLimit);
		var fileSizeDisplay = getFileSizeDisplay(fileSize);
		alert('WARNING: Your file reached the limit of '+fileSizeLimitDisplay+' (Actual size: '+fileSizeDisplay+')');
		element.style.backgroundColor = "red";
	}else{
		element.style.backgroundColor = "transparent";
	}
}

</script>
{/literal}
