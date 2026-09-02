{literal}
<script src="/shared/jqzoom/js/jqzoom.pack.1.0.1.js" type="text/javascript"></script>

<style type"text/css">
div.jqZoomTitle{
	z-index:5000;
	text-align:center;
	font-size:11px;
	font-family:Tahoma;
	height:16px;
	padding-top:2px;
	position:absolute;
	top: 0px;
	left: 0px;
	width: 100%;
	color: #FFF;
	background: #999;
}

.jqZoomPup{
	overflow:hidden;
	background-color: #FFF;
	-moz-opacity:0.6;
	opacity: 0.6;
	filter: alpha(opacity = 60);
	z-index:10;
	border-color:#c4c4c4;
	border-style: solid;
	cursor:crosshair;
}

.jqZoomPup img{
	border: 0px;
}

.preload{
	-moz-opacity:0.8;
	opacity: 0.8;
	filter: alpha(opacity = 80);
	color: #333;
	font-size: 12px;
	font-family: Tahoma;
	text-decoration: none;
	border: 1px solid #CCC;
	background-color: white;
	padding: 8px;
	text-align:center;
	background-image: url(/shared/jqzoom/images/zoomloader.gif);
	background-repeat: no-repeat;
	background-position: 43px 30px;
	width:500px;
	* width:100px;
	height:500px;
	*height:55px;
	z-index:10;
	position:absolute;
	top:3px;
	left:3px;
}

.jqZoomWindow{
	border: 2px solid gray;
	background-color: #FFF;
}

.jqzoom{
	float: left;
	padding: 2px;
}
</style>

<script type="text/javascript">
$(function() {
    $(".jqzoom").each(function(){
        $(this).jqzoom({
            zoomWidth : 500,
            zoomHeight : 350,
            title : false
        });
    });
});
</script>
{/literal}