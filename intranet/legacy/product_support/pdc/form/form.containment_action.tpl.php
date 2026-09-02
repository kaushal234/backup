<?php
ob_start();
?>

<h4>Containment actions form</h4>

<form name="containment_form" method="post" target="_blank">
	<input type="hidden" name="m[0]" value="pdc" />
    <input type="hidden" name="m[1]" value="view" />
    <input type="hidden" name="m[2]" value="containmentAction" />
    <input type="hidden" name="m[3]" value="submit" />
    <input type="hidden" name="id" value="<?= $id ?>" />
	<table border="1" padding="5">
		<tr>
			<th>Subject</th>
			<th>Action</th>
			<th>Reference</th>
		</tr>
	<?php
	    foreach(ContainmentForm::getSubjectList() as $kSubject=>$vSubject):
	        $questions = ContainmentForm::getDataConfigBySubjectID($kSubject, 'action');
	?>
		<tr>
			<td style="vertical-align:middle; text-align:center;" rowspan="<?= count($questions)+1 ?>">
			    <?php
			    echo ContainmentForm::getSubjectHTMLInput(array(
				    'name'=>"actions[$kSubject][subject]",
			        'value'=>$vSubject,
			        'label'=>$vSubject
				));
				?>
		    </td>
		</tr>
		<?php  foreach($questions as $kAction=>$vAction): ?>
		<tr>
			<td align="right"><?= $vAction ?></td>
			<td>
				<?php
				echo ContainmentForm::getReferenceHTMLInput(array(
				    'name'=>"actions[$kSubject][$vAction]",
				    'default'=>$FORM_DEFAULT[$kSubject][$vAction]
				));
				?>
            <td> <input type="submit" name="<?= "btn[$kSubject][$vAction]" ?>" value="Create a task" />
			</td>
		</tr>
		<?php  endforeach; ?>
	<?php  endforeach; ?>
	</table>
	<p><input type="submit" name="btnSubmit" value="Submit" /></p>
</form>

<?php

// Reference functions ---------------------->

class ContainmentForm
{
    private static function getQuestionConfig()
    {
        return [
            0 => [
                'refType' => [0, 1],
                'action' => [7],
            ],
            1 => [
                'refType' => [0, 1],
                'action' => [0, 2, 4],
            ],
            2 => [
                'refType' => [1, 2],
                'action' => [0, 2, 4],
            ],
            3 => [
                'refType' => [3],
                'action' => [0, 1, 3, 5],
            ],
            4 => [
                'refType' => [3],
                'action' => [0, 1, 3, 5],
            ],
            5 => [
                'refType' => [3],
                'action' => [0, 1, 6, 5],
            ],
        ];
    }

    public static function getDataConfigBySubjectID($id, $configName)
    {
        $dataResult = [];
        // get ref id list from config
        $conf = self::getQuestionConfig();
        switch ($configName) {
            case 'refType':
                $dataList = self::getReferenceTypeList();
                break;
            case 'action':
                $dataList = self::getActionList();
                break;
        }
        $dataIDs = $conf[$id][$configName];
        foreach ($dataIDs as $dataID) {
            $dataResult[$dataID] = $dataList[$dataID];
        }
        return $dataResult;
    }

    /*****************************************
     *    REFERENCE LISTS METHODS
     ****************************************/

    public static function getSubjectList()
    {
        return [
            'Sister factories',
            'Units in service (Model or S/N)',
            'Units at factory (Model or S/N)',
            'Factory wip/stock',
            'Supplier stock',
            'SPH stock',
        ];
    }

    private static function getReferenceTypeList()
    {
        return [
            'Model',
            'SN#',
            'Project#',
            'PN#',
        ];
    }

    private static function getActionList()
    {
        return [
            0 => 'Use as is',
            1 => 'Scrap the part',
            2 => 'Stop the machine utilization',
            3 => 'Sort/purge',
            4 => 'Replace parts/immediate action',
            5 => 'Rework/immediate action',
            6 => 'Return parts to factory',
            7 => 'Do we need to add the sister factory as followers , are they concerned?',
        ];
    }

    /*****************************************
     *    VIEW METHODS
     ****************************************/

    public static function getSubjectHTMLInput($a)
    {
        return <<<EOF
<input type="hidden" name="{$a['name']}" value="{$a['value']}"> {$a['label']}
EOF;
    }

    public static function getReferenceHTMLInput($a)
    {
        $default_val = str_replace('\\', '', $a['default']);
        return <<<EOF
<textarea cols="40" rows="3" name="{$a['name']}">$default_val</textarea>
EOF;
    }
}

// Handle buffer
return ob_get_clean();
