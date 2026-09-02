{literal}
	<style>
		span.short-description {
			padding: 1em 0;
			display: block;
			font-size: 80%;
			opacity: 80%;
		}
	</style>
<script type="text/javascript">
$(document).ready(function(){
	$(".datepicker").each(function(){
	    var format = $(this).data('dateformat');
	    if(format == null) format = 'yy-mm-dd';
	    $(this).datepicker({
	       dateFormat: format
	    });
	});

	let fetchShortDescription = function ($link, $moduleRef) {
		$moduleRef.siblings('.js-description-target').text()

		let module = $link.val()
		let id = $moduleRef.val()

		if (!module || !id) {
			return false
		}

		$.get('/en/private/manufacturing/eng/dev.php?m[0]=timekeeping&m[1]=form&m[2]=getShortDesc&module=' + module + '&id=' + id).done(function (data) {
			$moduleRef.siblings('.js-description-target').text(data)
		})
	}

	$('input[name$="[module_id]"]').on('change', function () {
		let $link = $('select[name="'+this.name.replace('module_id', 'link')+'"]')
		let $moduleRef = $(this)

		fetchShortDescription($link, $moduleRef)
	})


	let handleLinkUpdate = function () {
		let $link = $(this)
		let $moduleRef = $('input[name="'+this.name.replace('link', 'module_id')+'"]')

		fetchShortDescription($link, $moduleRef)
	}

	let $link = $('select[name$="[link]"]')
	$link.on('change', handleLinkUpdate)
	$link.each(handleLinkUpdate)

	let buildOptions = function($element, optionsList, addNaOption = false) {
		let currentValue = $element.val();
		$element.empty();
		optionsList.sort();
		if (addNaOption) {
			optionsList.unshift('N/A');
		}
		$.each(optionsList, function(index, value) {
			$element.append($("<option></option>").attr("value", value).text(value));
		});
		if (optionsList.includes(currentValue)) {
			$element.val(currentValue);
		}
	}

	let changeLinkList = function () {
		let $relatedLinkSelect = $('select[name="'+this.name.replace('category', 'link')+'"]');
		switch (this.value) {
			case 'Design Task':
				buildOptions($relatedLinkSelect, ['MEAP', 'EAP', 'FAQ', 'SOL', 'GWF', 'CPA', 'PIP'])
				break;
			case 'Training':
				buildOptions($relatedLinkSelect, ['AGILE'])
				break;
			case 'Supplier / Customer Visit':
				buildOptions($relatedLinkSelect, ['MEAP', 'EAP', 'PIP', 'PDC', 'SOL'])
				break;
			case 'Production Support':
				buildOptions($relatedLinkSelect, ['NCR', 'FAQ', 'GWF', 'CPA', 'AGILE', 'CRAB'], true)
				break;
			case 'Field / SPR Support':
				buildOptions($relatedLinkSelect, ['PDC', 'SB3', 'TOC', 'CPA', 'AGILE'])
				break;
			case 'Team Meeting':
			case 'Non Productive Hours':
			case 'Vacation':
			case 'Sick Leave':
				buildOptions($relatedLinkSelect, [], true)
				break;
			default:
				break;
		}
	}


	let $category = $('select[name$="[category]"]')
	$category.on('change', changeLinkList)
	$category.each(changeLinkList)
});
</script>
{/literal}

<h3>Quick Submit Timesheet</h3>

<form name="TimekeepingQuickSubmit" method="post" action="{$nextURL}" id="timekeepingform">
<div id="content">
	<table cellpadding="3">
		<thead>
			<tr style="background:#94B8D0;color:white;">
			<th colspan="17" style="text-align:center;">{$factory} <br>{$username}</th>
			</tr>
			<tr style="background:#2971A8;color:white;">
				<th>Category</th>
				<th>Module</th>
				<th>ID#</th>
				<th>Time (in {if $usingActualHours}hours{else}% of day{/if})</th>
				<th>Date</th>
				<th>Comments</th>
			</tr>
		</thead>
		<tbody>
			{foreach from=$defaulTimesheetNumber key=autokey item=key name=timesheet}
			<tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}">
				<td>
					<select name="timesheet[{$key}][category]">
						{foreach from=$categoryList key=categorykey item=name}
							<option label="{$name}" value="{$name}">{$name}</option>
						{/foreach}
					</select>
				</td>
				<td>
					<select name="timesheet[{$key}][link]" width="80" style="width:80px">
					{foreach from=$linkList key=linkkey item=name}
						<option label="{$name}" value="{$name}">{$name}</option>
					{/foreach}
					</select>
				</td>
				<td style="width:80px"><input style="width:80px" type="text" name="timesheet[{$key}][module_id]" value="" /><p class="js-description-target"></p></td>
				<td style="width:80px"><input style="width:80px" type="text" name="timesheet[{$key}][{if $usingActualHours}hours{else}time{/if}_actual]" value="" /></td>
				<td><input type="date" name="timesheet[{$key}][dt_open]" value="{$today}" class="datepicker" /></td>
				<td><textarea name="timesheet[{$key}][comment]" form="timekeepingform" rows="3" cols="20"></textarea></td>
			</tr>
    		{/foreach}
		</tbody>
	</table>
</div></br></br>
<input type="submit" value="Submit Timesheets" /> <input type="reset" value="Reset" />
</form>
