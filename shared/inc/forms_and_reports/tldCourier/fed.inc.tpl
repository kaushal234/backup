<form name="tracking" action="http://www.fedex.com/Tracking" method="get" target="_blank">
    <input type="hidden" name="ascend_header" value="1">
    <input type="hidden" name="clienttype" value="dotcom">
    <input type="hidden" name="cntry_code" value="us">
    <input type="hidden" name="language" value="english">
    <input type="hidden" name="tracknumbers" value="{$trackNum}">{$trackNum}
    <input class="xsmalltext" type=SUBMIT value="FED">
</form>
