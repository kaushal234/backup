<?php
ob_start();
?>

<script type="text/javascript" src="/shared/javascript/jquery/jquery-1.9.1.min.js"></script>
<script type="text/javascript" src="/shared/javascript/jquery/jquery-ui-1.10.1.custom.min.js"></script>
<link rel="stylesheet" href="/shared/javascript/jquery/css/smoothness/jquery-ui-1.10.1.custom.min.css">

<script type='text/javascript'>
window.onload = function(){
	// Init calculated locale date-time
	localeDateStr=$( ".dateLocale" ).text()+' '+$( ".timeLocale" ).text();
	aa=localeDateStr.substring(0, 4);
	mm=localeDateStr.substring(5, 7)-1;
	jj=localeDateStr.substring(8, 10);
	hh=localeDateStr.substring(11, 13);
	mn=localeDateStr.substring(14, 16);
	ss=localeDateStr.substring(17, 19);
	date = new Date(aa,mm,jj,hh,mn,ss,00);
	a = date.getFullYear();
	m = date.getMonth()+1; if(m<10) { m = "0"+m; }
	j = date.getDate();    if(j<10) { j = "0"+j; }
	h = date.getHours();   if(h<10) { h = "0"+h; }
	n = date.getMinutes(); if(n<10) { n = "0"+n; }
	s = date.getSeconds(); if(s<10) { s = "0"+s; }
	document.getElementById('local_clock').innerHTML = h+':'+n+':'+s;
	document.getElementById('local_clock_hidden').value = a+'-'+m+'-'+j+' '+h+':'+n+':'+s;

	dateBrowser=new Date();
	dateOffset=date.getTime()-dateBrowser.getTime();
	document.getElementById('local_clock_offset').value = dateOffset;

	<?php if($LOCAT['erp']!='400' && $LOCAT['erp']!='410' && $LOCAT['erp']!='420') { ?>
	  document.getElementById('tk_task_input').focus();
	<?php } ?>
	get_local_time.call();
}

function get_local_time()
{
    dateTmp=new Date().getTime()+parseInt(document.getElementById('local_clock_offset').value);
    date=new Date(dateTmp);

	a = date.getFullYear();
	m = date.getMonth()+1; if(m<10) { m = "0"+m; }
	j = date.getDate();    if(j<10) { j = "0"+j; }
	h = date.getHours();   if(h<10) { h = "0"+h; }
	n = date.getMinutes(); if(n<10) { n = "0"+n; }
	s = date.getSeconds(); if(s<10) { s = "0"+s; }
	document.getElementById('local_clock').innerHTML = h+':'+n+':'+s;
	document.getElementById('local_clock_hidden').value = a+'-'+m+'-'+j+' '+h+':'+n+':'+s;
	setTimeout('get_local_time();','1000');
	<?php if($LOCAT['erp']!='400' && $LOCAT['erp']!='410' && $LOCAT['erp']!='420') { ?>
	  document.getElementById('tk_task_input').focus();
	<?php } ?>
	return true;
}
</script>
<div class='dateLocale' hidden><?php echo($_SESSION['dateLocale']); ?></div>
<div class='timeLocale' hidden><?php echo($_SESSION['timeLocale']); ?></div>

<form action="/shop/autoselect.php" method="post" name="frmTask" id="frmTask">
<div>
<input name="m[0]" type="hidden" value="time_keeping" />
<input name="m[1]" type="hidden" value="<?= $_SESSION['task_form']?>" />
<input name="m[2]" type="hidden" value="<?= $LOCAT['erp']?>" />
<input name="m[3]" type="hidden" value="<?= $_GET['m'][3]?>" />
<input name="local_clock_hidden" type="hidden" id="local_clock_hidden" />
<input name="local_clock_offset" type="hidden" id="local_clock_offset" value=0 />

<table width=100% height=50% border=0>
	<tr width=100% height=100%>
		<td width=25% height=100%>
			&nbsp;
		</td>
		<td width=70% height=100%>
			<table border=0>
				<tr>
					<td bgcolor=lightgrey colspan=3 style='font-size: <?= $_SESSION['pi_font_size'] ?>;' height=25px><b><?= _('Time keeping for company') ?>&nbsp;<?= $LOCAT['erp']?></b></td>
				</tr>
				<tr><td><br><br></td><td></td></tr>
				<tr>
					<td style='font-size: <?= $_SESSION['pi_font_size'] ?>;' height=25px>
						<b><?= _('User ID') ?>:</b>
					</td>
					<td style='font-size: <?= $_SESSION['pi_font_size'] ?>;' height=25px>
						<input type=text size=25 disabled value='<?= $_SESSION['tk_user_input'] ?>'>
					</td>
				</tr>
				<tr><td><br><br></td><td></td></tr>
				<tr>
					<td style='font-size: <?= $_SESSION['pi_font_size'] ?>;' height=25px>
						<b><?= _('Name') ?>:</b>
					</td>
					<td style='font-size: <?= $_SESSION['pi_font_size'] ?>;' height=25px>
						<input type=text size=25 disabled value='<?= $_SESSION['tk_user_name'] ?>'>
					</td>
					<?php if ($LOCAT['erp']!='400' && $LOCAT['erp']!='410' && $LOCAT['erp']!='420') { ?>
                    <td rowspan=3 style="vertical-align:bottom;">
						&nbsp;&nbsp;&nbsp;&nbsp;
						<INPUT border=0 src="//www.tld-gse.com/shared/icons/application/Clock_out.png" type=image name=clockOut Value=submit width=80 >
					</td>
					<?php } ?>
				</tr>
				<tr>
					<td><br><br></td>
					<td></td>
				</tr>
				<tr>
					<td style='font-size: <?= $_SESSION['pi_font_size'] ?>;' height=25px>
						<b><?= _('Task') ?>:</b>
					</td>
					<td>
						<input name="tk_task_input" id="tk_task_input" type=text size=25>
					</td>
				</tr>
				<?php if (($LOCAT['erp']=='400' || $LOCAT['erp']=='410' || $LOCAT['erp']=='420') && $_GET['m'][3]=='indirect400') { ?>
				<tr>
					<td><br><br></td>
					<td></td>
				</tr>
				<tr>
					<td style='font-size: <?= $_SESSION['pi_font_size'] ?>;' height=25px>
						<b><?= _('Comment') ?>:</b>
					</td>
					<td>
						<input name="tk_text_input" id="tk_text_input" type=text size=25>
					</td>
				</tr>
				<?php } ?>

				<tr><td><br><br></td><td></td></tr>
				<tr>
					<td><input name="btnSubmit" value="<?= _('Submit') ?>" type="submit" /></td>
					<td style="text-align: right;"><a href="<?= $php_self ?>"><?= _('Go to Shopfloor') ?></a></td>
				</tr>
				<tr><td><br><br></td><td></td></tr>
				<tr>
				    <td align=center colspan=2 style='font-size: 36; font-weight: bold;' height=25px>
					   <span id="local_clock" ></span>
                    </td>
				</tr>
			</table>
		</td>
	</tr>
</table>
</div>
</form>

<table width=100%>
	<tr width=100%>
	    <td width=20%>&nbsp;</td>
		<td align=left width=80%>
			<span style='font-size: <?= $_SESSION['pi_font_size'] ?>;'><b><?= $_SESSION['message'] ?></b></span>
		</td>
	</tr>
</table>

<?php
return ob_get_clean();
?>
