
<table border=0>
	<tr>
		<td>
			STEP 1: Search if PN already exists
		</td>
		<td>
			&nbsp;&nbsp;&nbsp;---------->&nbsp;&nbsp;&nbsp;
		</td>
		<td>
			<b>STEP 2: Reserve new PN</b>
		</td>
		<td>
			&nbsp;&nbsp;&nbsp;---------->&nbsp;&nbsp;&nbsp;
		</td>
		<td>
			STEP 3: List reserved PN
		</td>		
	</tr>
</table>
<br>


<form name="frmreserveItem" action="./dev.php?m[0]=item_reservation&m[1]=listing&m[2]=doReservation" method="post">
	<table border=0>
		<tr>
			<td colspan=2 bgcolor=grey><b>Fill new item description:</b></td>
		</tr>
		<tr>
			<td><font color=red>*</font><b>Description</b></td>
			<td><input type=text name='DSCA' class='DSCA' maxlength=30 size=40></td>
		</tr>
		<tr>
			<td></td>
			<td><INPUT type=submit Value=submit></td>
		</tr>
		<tr>
			<td></td>
			<td><font color=red>*</font>&nbsp;denotes required field&nbsp;<span id="errmsg"></span> </td>
		</tr>
		
	</table>		
</form>


