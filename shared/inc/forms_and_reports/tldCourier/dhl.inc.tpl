<form method="get" action="http://www.dhl.com/cgi-bin/tracking.pl" target="_blank">
	<input type="hidden" name="TID" value="CP_ENG">
	<input type="hidden" name="FIRST_DB">
	<input type="hidden" name="AWB" value="{$trackNum}">{$trackNum}
	<input class="xsmalltext" type="submit" value="DHL">
</form>