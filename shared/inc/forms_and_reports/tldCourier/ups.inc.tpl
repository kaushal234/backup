<FORM ACTION="http://wwwapps.ups.com/etracking/tracking.cgi" METHOD="GET" target="_blank">
	<INPUT TYPE="HIDDEN" NAME="tracknums_displayed" VALUE="5">
	<INPUT TYPE="HIDDEN" NAME="TypeOfInquiryNumber" VALUE="T">
	<INPUT TYPE="HIDDEN" NAME="HTMLVersion" VALUE="4.0">
	<INPUT TYPE="hidden" NAME="InquiryNumber1" value="{$trackNum}">{$trackNum}
	<input class="xsmalltext" type="submit" name="track" value="UPS">
</FORM>
