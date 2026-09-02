<?php
ob_start();
?>

<h4>Corrective actions form</h4>

<form name="containment_form" method="post" target="_blank">
	<input type="hidden" name="m[0]" value="pdc" />
    <input type="hidden" name="m[1]" value="view" />
    <input type="hidden" name="m[2]" value="correctiveAction" />
    <input type="hidden" name="m[3]" value="submit" />
    <input type="hidden" name="id" value="<?= $id ?>" />
	<table border="1" padding="5" width="90%">
		<?php  foreach(CorrectiveActionForm::getQuestions() as $who=>$actions): ?>
		<tr>
			<th width="20%" style="vertical-align:middle; text-align:center;"><?= $who ?></th>
			<td width="80%">
				<table width="100%">
				<?php  foreach($actions as $k=>$action): ?>
    				<tr>
    					<td width="30%">
    					<?=
                            CorrectiveActionForm::getHiddenInput(array(
    					        'name'=>"actions[$who][$k][action]",
    					        'value'=>$action,
    					    ));
    					?>
    					</td>
    					<td width="70%">
    					<?=
                            CorrectiveActionForm::getTextarea(array(
    					        'name'=>"actions[$who][$k][ref]",
    					        'default'=>$FORM_DEFAULT[$who][$k]['ref']
    					    ));
    					?>
                        <td> <input type="submit" name="<?= "btn[$who][$k]" ?>" value="Create a task" />
    					</td>
    				</tr>
				<?php  endforeach; ?>
				</table>
			</td>
		</tr>
		<?php  endforeach; ?>
	</table>
	<p><input type="submit" name="btnSubmit" value="Submit" /> <input type="reset" name="btnReset" value="Reset" />  </p>
</form>

<?php

// Reference functions ---------------------->

class CorrectiveActionForm
{
    public static function getQuestions()
    {
        return [
            'Customer' => [
                'Manual',
                'Service bulletin',
                'SB to release',
                'Type of SB',
            ],
            'Factory' => [
                'BOM',
                'Drawing',
                'DMS documents',
                'Production Quality alert',
                'PIO CQ and frequency - Other tests documents',
                'FAI planned in Baan system',
                'If BOM change, estimated additional material cost (local currency)',
            ],
            'Supplier' => [
                'NCR',
                'SCAR',
                'Other',
            ],
            'Spare part dept.' => [
                'Information',
                'Other',
            ],
        ];
    }

    /*****************************************
     *    VIEW METHODS
     ****************************************/

    public static function getHiddenInput($a)
    {
        return <<<EOF
<input type="hidden" name="{$a['name']}" value="{$a['value']}"> {$a['value']}
EOF;
    }

    public static function getTextarea($a)
    {
        $default_val = str_replace('\\', '', $a['default']);
        return <<<EOF
<textarea cols="60" rows="2" name="{$a['name']}">$default_val</textarea>
EOF;
    }

}

// Handle buffer
return ob_get_clean();
