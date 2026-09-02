<?php

use ApiBundle\Client;
use Symfony\Component\HttpClient\Exception\ClientException;
require_once 'ION/ItemBySite.php';

$DEFAULT_TITLE .= "\Process & Inspection Online";
$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=pi">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=pi&m[1]=byNum">By Number</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=pi&m[1]=add">Add</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=pi&m[1]=reports">Reports</a>
&nbsp;|&nbsp;<a href="/en/private/mis/mis.php?m[0]=help&m[1]=dms&id=1799" target="_blank">Procedure</a>    
&nbsp;|&nbsp;<a href="$php_self?m[0]=pi&m[1]=userGuide">User guide</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=pi&m[1]=insertQuestions">Insert Question via XLSX</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=pi&m[1]=familyManager">Family Manager</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=pi&m[1]=bijection">Bijection</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=pi&m[1]=troubleshooting">Troubleshooting</a>
EOF;

if($user->isInGroup(array("superuser", "ROLE_PS", "ROLE_PM", "ROLE_MPE", "role_planner"))) {
	$DEFAULT_MENU .=<<<EOF
        &nbsp;|&nbsp;<a href="/en/private/manufacturing/index.php?m[0]=pi&m[1]=timekeeping" title="Timekeeping">Timekeeping</a>
EOF;
}

if($user->isInGroup(array("gg_MIS"))) {
	$DEFAULT_MENU .=<<<EOF
        &nbsp;|&nbsp;<a href="/en/private/manufacturing/pi/pi_admin.php" title="P&I Admin">Admin</a>
EOF;
}

switch($m[1]) {
    case 'userGuide':
        $body.="<h3>P&I User guide</h3>";
        $body.="<h4>Listing Reports</h4>";
        $body.="<ol>";
        $body.="<li><a href='/en/private/mis/mis.php?m[0]=help&m[1]=dms&id=1854' target='_blank'>DMS 1854: P&I user guide</a></li>";
        $body.="<li><a href='/en/private/mis/mis.php?m[0]=help&m[1]=dms&id=1942' target='_blank'>DMS 1942: ER & WO creation under PIO configuration</a></li>";
        $body.="<li><a href='/en/private/mis/mis.php?m[0]=help&m[1]=dms&id=1965' target='_blank'>DMS 1965: PIO: operations sequencing, accesses and notifications</a></li>";
        $body.="<li><a href='/en/private/mis/mis.php?m[0]=help&m[1]=dms&id=1966' target='_blank'>DMS 1966: PIO routing per family</a></li>";
        $body.="</ol>";
        break;
	case 'reports':
		include_once("pi/pi.reports.inc.php");
	break;
	case 'view':
		include_once("pi/view.inc.php");
	break;
	case 'byNum':
		$DEFAULT_TITLE .= "\By Number";
		$form = new HTML_QuickForm('frmByNum', 'post');
		$form->addElement(	'hidden', 'm[0]', 'pi');
		$form->addElement(	'hidden', 'm[1]', 'view');
		$form->addElement(	'header', 'title', "P&I Question - By Number");
		$form->addElement(	'text', 'id', 'ID#');
		$form->addElement(	'submit', 'btnSubmit', 'Submit');
		$body .= $form->toHTML();
	break;
	case 'delete':
	    if(!$user->isInGroup(array("pi_PCQ", "pi_QCQ", "pi_ECQ"))) {
	        $DEFAULT_ERROR[] = "ERROR: You do not have permissions for this page..";
	        return;
	    }
	    $DEFAULT_TITLE .= "\Delete";
        $copyid = (int)TldDatabase::escape($copyid);
	    $yesno = array("Yes", "No");
	    $form = new HTML_QuickForm('frmPiDelete', 'get', "", "", "", true);
	    $form->addElement(	'hidden', 'm[0]', 'pi');
	    $form->addElement(	'hidden', 'm[1]', 'delete');
	    $form->addElement(	'hidden', 'copyid', $copyid);
	    $form->addElement(	'header', 	'title','Delete question');
	    $form->addElement(	'select', 	's',	'Delete',	array_combine($yesno, $yesno));
	    $form->addElement(	'text', 	'r', 	'Reason (Mandatory)');
        $form->addElement('checkbox', 'impactProduction', 'Do you want to impact the production?');
        $form->addElement(	'submit', 'btnSubmit', 'Submit');
	    $form->addRule('r', 'Required', 'required');
	    $caption = "Shipping un-selected";

	    $title='Delete?';
	    $xItems = array_merge([
				"id"					=>"ID#",
				"t_opno"				=>"Operation",
				"t_item"				=>"P/N",
				"model"					=>"Model",
				"owner"					=>"Owner",
	        ],
	        tldPI::getFactoriesList(),
	        [
				"position"				=>"Position",
				"subject_en"			=>"Subject (EN)",
				"subject_fr"			=>"Subject (FR)",
				"subject_zh"			=>"Subject (ZH)",
				"desc_en"				=>"Description (EN)",
				"desc_fr"				=>"Description (FR)",
				"desc_zh"				=>"Description (ZH)",
				"active"				=>"Active",
			]
		);
	    $query="select * from pi_questions where id=".$copyid;
	    $rows=tldUtils::getSqlToAssocArray($query);
	    $body = $form->toHTML();

	    if($form->validate()){
	        $vars = tldUtils::cleanupFormInput($form->exportValues());
	        if($vars['s']=='Yes' && $vars['r']!='') {
	            $title='Deleted';
                //check if the questions is link to a pending Crab
                if($vars['impactProduction'] === '1' ) {
                    $query = <<<SQL
                    SELECT cr.id FROM pi_crab_eap AS pi
                    LEFT JOIN crabs AS cr ON pi.parent_id = cr.id
                    LEFT JOIN pi_questions_unit AS qst ON pi.question_id = qst.id
                    WHERE cr.status ='PENDING'
                    AND qst.parent_id =$copyid;
SQL;
                    $nbPendingCrab = tldUtils::getSqlToAssocArray($query);
                    if (!empty($nbPendingCrab)){
                        $DEFAULT_ERROR[] = "ERROR: PENDING CRABS";
                        foreach ($nbPendingCrab as $crab){
                            $DEFAULT_ERROR[] =<<<HTML
<a href="/en/private/manufacturing/qa/dev.php?m[0]=crab&m[1]=view&id={$crab['id']}" target="_blank">{$crab['id']}</a>
HTML;

                        }
                        $report = new tldReportColumnar($rows,
                            [
                                "xItems" => $xItems,
                                "title" => $title,
                                "links" => $links,
                            ]
                        );
                        $body .= $report->fetch();
                        return;
                    }
                    //delete question on unit in production.
                    $query = <<<SQL
                    DELETE FROM pi_questions_unit 
                    WHERE parent_id=$copyid
                    AND unit IN (SELECT sn FROM service WHERE dgt_act='0000-00-00');
SQL;
                    TldUtils::sqlExecute($query);
                }
                $e = tldPI::delete($copyid, $vars['r']);

	            $visu='<table border=0>';
	            $visu.='<tr bgcolor="#2971A8" style="color:white"><td>Field</td><td>Value</td></tr>';
	            $visu.='<tr bgcolor="#EEEEEE"><td>ID#             </td><td>'.$rows[0]['id']           .'</td></tr>';
	            $visu.='<tr bgcolor="#D0D0D0"><td>Operation       </td><td>'.$rows[0]['t_opno']       .'</td></tr>';
	            $visu.='<tr bgcolor="#EEEEEE"><td>P/N             </td><td>'.$rows[0]['t_item']       .'</td></tr>';
	            $visu.='<tr bgcolor="#D0D0D0"><td>Model           </td><td>'.$rows[0]['model']        .'</td></tr>';
	            $visu.='<tr bgcolor="#EEEEEE"><td>Owner           </td><td>'.$rows[0]['owner']        .'</td></tr>';
	            $i = 0;
	            foreach (tldPI::getFactoriesList() as $code => $name) {
					$color = (++$i % 2) ? '#D0D0D0' : '#EEEEEE';
					$visu.='<tr bgcolor="'.$color.'"><td>'.$name.'</td><td>'.$rows[0][$code] .'</td></tr>';
				}
	            $visu.='<tr bgcolor="#EEEEEE"><td>Subject (EN)    </td><td>'.$rows[0]['subject_en']   .'</td></tr>';
	            $visu.='<tr bgcolor="#D0D0D0"><td>Subject (FR)    </td><td>'.$rows[0]['subject_fr']   .'</td></tr>';
	            $visu.='<tr bgcolor="#EEEEEE"><td>Subject (ZH)    </td><td>'.$rows[0]['subject_zh']   .'</td></tr>';
	            $visu.='<tr bgcolor="#D0D0D0"><td>Description (EN)</td><td>'.$rows[0]['desc_en']      .'</td></tr>';
	            $visu.='<tr bgcolor="#EEEEEE"><td>Description (FR)</td><td>'.$rows[0]['desc_fr']      .'</td></tr>';
	            $visu.='<tr bgcolor="#D0D0D0"><td>Description (ZH)</td><td>'.$rows[0]['desc_zh']      .'</td></tr>';
	            $visu.='<tr bgcolor="#EEEEEE"><td>Position        </td><td>'.$rows[0]['position']     .'</td></tr>';
	            $visu.='<tr bgcolor="#D0D0D0"><td>Help (EN)       </td><td>'.$rows[0]['help_en']      .'</td></tr>';
	            $visu.='<tr bgcolor="#EEEEEE"><td>Help (FR)       </td><td>'.$rows[0]['help_fr']      .'</td></tr>';
	            $visu.='<tr bgcolor="#D0D0D0"><td>Help (ZH)       </td><td>'.$rows[0]['help_zh']      .'</td></tr>';
	            $visu.='<tr bgcolor="#EEEEEE"><td>Picture (EN)    </td><td>'.$rows[0]['attachment_en'].'</td></tr>';
	            $visu.='<tr bgcolor="#D0D0D0"><td>Picture (FR)    </td><td>'.$rows[0]['attachment_fr'].'</td></tr>';
	            $visu.='<tr bgcolor="#EEEEEE"><td>Picture (ZH)    </td><td>'.$rows[0]['attachment_zh'].'</td></tr>';
	            $visu.='<tr bgcolor="#D0D0D0"><td>Answer Type     </td><td>'.$rows[0]['answer_type']  .'</td></tr>';
	            $visu.='<tr bgcolor="#EEEEEE"><td>Answer Unit     </td><td>'.$rows[0]['answer_unit']  .'</td></tr>';
	            $visu.='<tr bgcolor="#D0D0D0"><td>Component S/N   </td><td>'.$rows[0]['component_sn'] .'</td></tr>';
	            $visu.='<tr bgcolor="#EEEEEE"><td>Answer Max      </td><td>'.$rows[0]['answer_max']   .'</td></tr>';
	            $visu.='<tr bgcolor="#D0D0D0"><td>Answer Min      </td><td>'.$rows[0]['answer_min']   .'</td></tr>';
	            $visu.='<tr bgcolor="#EEEEEE"><td>GT1             </td><td>'.$rows[0]['gt1']          .'</td></tr>';
	            $visu.='<tr bgcolor="#D0D0D0"><td>GT3             </td><td>'.$rows[0]['gt3']          .'</td></tr>';
	            $visu.='<tr bgcolor="#EEEEEE"><td>Active          </td><td>'.$rows[0]['active']       .'</td></tr>';
	            $visu.='<tr bgcolor="#EEEEEE"><td>impact prod          </td><td>'.$vars['impactProduction'] .'</td></tr>';
	            $visu.='</table>';


                // Notify involved people
                $query="select factory from pi_family_matrix where family='".$rows[0]['model']."'";
                $factories = tldUtils::getSqlToAssocArray($query);
                $factoryArray = array_merge(
                    ["ALVEST"],
                    array_column($factories, 'factory')
                );

                $factoryList = "'".implode("','", $factoryArray)."'";
                if (in_array($rows[0]['owner'], ['QCQ', 'PCQ', 'ECQ'])) {
                    $functionList = "'QAM', 'MPE', 'CMO'";
                }
                if ('ECQ' === $rows[0]['owner']) {
                    $functionList .= ", 'EM'";
                }

                $query = <<<SQL
select lastname, firstname, locations.location, email 
from people 
LEFT JOIN tld_functions AS fct ON fct.id=people.fct_id
LEFT JOIN locations ON locations.id=people.bu_id 
where people.hidden!=1 and fct.code in ($functionList) and locations.location in ($factoryList)
SQL;

                // notify involved pilots
                if ('ECQ' === $rows[0]['owner']) {
                    $query .= <<<SQL
 union select lastname, firstname, locations.location, email from people
 LEFT JOIN locations ON locations.id=people.bu_id 
where people.hidden!=1 and locations.location in ($factoryList)
and people.id in (select parent_id from people_groups where group_name='pi_PILOT_{$rows[0]['model']}')
SQL;
                }
                $assignees = tldUtils::getSqlToAssocArray($query);
                $assigneeList = array_column($assignees, 'email');
                $assignee = implode(',', array_unique($assigneeList));

	            $subject=$rows[0]['owner'].' #'.$rows[0]['id'].' - '.$rows[0]['model'].' / Op '.$rows[0]['t_opno'].' ('.$user->getFullname().') => Question deleted';
	            $email2="<html><body>";
	            $email2.=$subject.'<br>Reason:&nbsp;'.$vars['r'].'<br>Contact your counterparts to challenge eventually that decision and open a MIS ticket to reactivate it in case of mistake.<br><br>';
	            $email2.=$visu;
	            $email2.="</body></html>";
	            $e = tldUtils::emailAttachment($assignee,'noreply@tld-gse.com',$subject,$email2);
                $body = <<<HTML
                     <p> Question deleted <br/>
                     <a href="/en/private/manufacturing/index.php?m[0]=pi&m[1]=reports&m[2]=piByFilter">Report Questions by Filter</a></p>
HTML;
	        }
	    } else {
            $report = new tldReportColumnar($rows,
               [
                    "xItems" => $xItems,
                    "title" => $title,
                    "links" => $links,
                ]
            );
            $body .= $report->fetch();
        }

	break;
	case 'add':
	    if(!$user->isInGroup(array("pi_PCQ", "pi_QCQ", "pi_ECQ"))) {
	        $DEFAULT_ERROR[] = "ERROR: You do not have permissions for this page..";
	        return;
	    }

	    $families=tldPI::getPiFamilies(['status' => 'Final']);
	    foreach ($families as $key1 => $value1) {
	        $family[]=$families[$key1]['family'];
	    }
	    $authorizedOwner[]="QCQ";
	    $authorizedOwner[]="PCQ";

	    if ($user->isInGroup(array("pi_QCQ"))) {
	        $userOwner='QCQ';
	    } else {
	        if ($user->isInGroup(array("pi_ECQ"))) {
	            $userOwner='ECQ';
	        } else {
	            if ($user->isInGroup(array("pi_PCQ"))) {
	                $userOwner='PCQ';
	            } else {
	                $userOwner='';
	            }
	        }
	    }

		if ($user->isInGroup(array("pi_QCQ")) && $user->isInGroup(array("pi_ECQ")) && $user->isInGroup(array("pi_PCQ"))) {
			$userOwner='ALL';
		}

        if (!empty($copyid)) {
            $pi2copy = new tldPI($copyid);
            $header = $pi2copy->getHeader();
        }

		$DEFAULT_TITLE .= "\Add";
		$factory = tldPI::getFactoriesList();
		$form = new HTML_QuickForm('frmAdd', 'post');
		$form->addElement(	'hidden', 	'm[0]', 				'pi');
		$form->addElement(	'hidden', 	'm[1]', 				'add');
		$form->addElement(	'hidden', 	'entered_by', 			$user->getID());
		$form->addElement(	'header', 	'title', 				'P&I Questionnaire - Submit');
		$form->addElement(	'text', 	't_opno', 				'Operation');

        if (($form->isSubmitted() && isset($postItems)) || !empty($header['items'])) {
            //set pn fields default value
            $items = $form->isSubmitted() ? $postItems : $header['items'];
            foreach ($items as $index => $item) {
                $i = $index + 1;
                $form->addElement('text', 'items[]', "P/N #$i", ['class' => 'pn_input', 'value' => $item]);
            }
        } else {
            $form->addElement('text', 'items[]', 'P/N #1', ['class' => 'pn_input']);
        }

        $form->addElement(	'static', '', '<a id="addPN" href="#">Add another P/N</a>', null);

		$form->addElement(	'select', 	'model', 				'Family',					array_combine($family, $family));

		if ($userOwner === 'QCQ' || 'ALL' === $userOwner) {
		    $form->addElement(	'select', 	'owner',				'Owner', array_combine($authorizedOwner, $authorizedOwner));
		} else {
		    $form->addElement(	'text', 	'owner',				'Owner', array('disabled'=>'disabled'));
		}

		$fieldOptions = [];
		if ($userOwner !== 'QCQ' && $userOwner !== 'PCQ' && $userOwner !== 'ALL') {
			$fieldOptions = ['disabled' => 'disabled'];
		}
		foreach (tldPI::getFactoriesList() as $code => $name) {
			$form->addElement(	'text', $code, $name, $fieldOptions);
		}

		global $kernel;
		try {
			$container = $kernel->getContainer();
			$client = $container->get(Client::class);
		} catch (\Exception $e) {
			$DEFAULT_ERROR[] = 'Client could not be fetched.';
			break;
		}

		$formattedComponents = [];
		try {
			$components = $client->get('equipment_serial_components', ['query' => ['order' => ['name' => 'ASC']]]);
			foreach ($components['hydra:member'] as $component) {
				$formattedComponents[$component['name']] = $component['name'];
			}
		} catch (ClientException $exception) {
			$DEFAULT_ERROR[] = 'Components could not be fetched.';
			break;
		}

		$form->addElement(	'text', 	'subject_en', 			'Subject (EN)');
		$form->addElement(	'text', 	'subject_fr', 			'Subject (FR)');
		$form->addElement(	'text', 	'subject_zh', 			'Subject (ZH)');
		$form->addElement(	'textarea', 'desc_en', 				'Description (EN)',			array("rows"=>5, "cols"=>40));
		$form->addElement(	'textarea', 'desc_fr', 				'Description (FR)',			array("rows"=>5, "cols"=>40));
		$form->addElement(	'textarea', 'desc_zh', 				'Description (ZH)',			array("rows"=>5, "cols"=>40));
		$form->addElement(	'text', 	'position', 			'Position');
		$form->addElement(	'text', 	'help_en',		        'DMS# (EN)');
		$form->addElement(	'text', 	'help_fr',	        	'DMS# (FR)');
		$form->addElement(	'text', 	'help_zh',	           	'DMS# (ZH)');
		$form->addElement(	'file', 	'attachment_en',		'Picture (EN)');
		$form->addElement(	'file', 	'attachment_fr',		'Picture (FR)');
		$form->addElement(	'file', 	'attachment_zh',		'Picture (ZH)');
		$form->addElement(	'select', 	'answer_type', 			'Answer Type',				array(""=>"","YES/NO"=>"YES/NO","Decimal"=>"Decimal","Alphanumeric"=>"Alphanumeric","Match List"=>"Match List","S/N"=>"S/N"));
		$form->addElement(	'select', 	'answer_unit', 			'Answer Unit',				array(""=>"") + tldPI::getUnits());
		$form->addElement(	'select', 	'component_sn',			'Component (S/N question)',	array(""=>"") + $formattedComponents);
		$form->addElement(	'select', 	'match_list', 			'Match List',				array(""=>"","1"=>"mfg_test_list1","2"=>"mfg_test_list2"));
		$form->addElement(	'text', 	'answer_max', 			'Answer Max');
		$form->addElement(	'text', 	'answer_min', 			'Answer Min');
		$form->addElement(	'select', 	'gt1', 					'GT1',						array("N"=>"N","Y"=>"Y"));
		$form->addElement(	'select', 	'gt3', 					'GT3',						array("N"=>"N","Y"=>"Y"));
		$form->addElement(	'select', 	'active', 				'Active',					array(""=>"","N"=>"N","Y"=>"Y"));
		$form->addRule('answer_max', 'Field is numeric', 'numeric');
		$form->addRule('answer_min', 'Field is numeric', 'numeric');


        $required=["position","answer_type","active"];


        $form->addElement('checkbox', 'restricted_notification', 'Local CQ change with no impact <br>or no interest for the sister BUs, <br>so no need for notification');

        $form->addElement('checkbox', 'production_impact', 'Vehicles currently in production must be impacted.');

        foreach($required as $key=>$field) {
		    $form->addRule($field, 'Required', 'required');
        }
		$form->addElement('submit', 'btnSubmit', 'Submit');
		if(!empty($copyid)){
			$form->setDefaults($header);
		}else{
			$form->setDefaults(["model"=>$dmodels]);
		}

		if ($userOwner === 'PCQ' || $userOwner === 'ECQ') {
			$form->setDefaults(["owner" => $userOwner]);
		}

		if ($userOwner === 'ECQ') {
			foreach (tldPI::getFactoriesList() as $code => $name) {
				$form->setDefaults([$code => '1']);
			}
		}

		if(!$form->validate()){
			$body = $form->toHTML();
            $body.= <<<HTML
            <script type="application/javascript">
            function convertHtml(){
                var oldVal =  $(this).val()
                $(this).val($(this).html(oldVal).text());
            }
            
             var inputs =[]
             var desc = $(':input[name^=desc_]');
             var subject = $(':input[name^=subject_]');
             inputs = $.merge(subject,desc);
             inputs.each(convertHtml);

            </script>
            
HTML;

            $body.= <<<HTML
<script type="application/javascript">
$(document).ready(function() {
    $("#addPN").on('click', function() {
        var lastRow = $('.pn_input').last().closest('tr');
        var clone = lastRow.clone();
        clone.find('b').text('P/N #' + ($('.pn_input').length + 1));
        clone.find('input').val('');
        $(this).closest("tr").before(clone);
    });
});
</script> 
HTML;

			break;
		}
		$vars = tldUtils::cleanupFormInput($form->exportValues());

        $vars['items'] = array_unique(array_filter($_POST['items'], function ($item) {
            return $item !== '';
		}));

		if ('' !== $vars['answer_max'] && '' !== $vars['answer_min'] && (float)$vars['answer_max'] <= (float)$vars['answer_min']) {
			$DEFAULT_ERROR[] = 'ERROR: Answer Max should be greater than Answer Min';
		}

		if (('' !== $vars['answer_max'] && '' === $vars['answer_min']) || ('' !== $vars['answer_min'] && '' === $vars['answer_max'])) {
			$DEFAULT_ERROR[] = 'ERROR: Answer Max & Answer Min must be filled together';
		}

		if (false === validQuestion($vars)) {
			$body = $form->toHTML();
			break;
		}


		//File Management
		$file_en = $form->getElement('attachment_en');
		$file_array_en = $file_en->getValue();
		if($file_array_en['tmp_name']!=""){
			// Should be use if we want to duplicated file to
		    // $check = new tldFile($header['attachment_en']);
		    $timestamp = time();
		    $clean_name_en = basicFile::cleanupName($file_array_en['name']);
		    $filename_en = $timestamp.'en-'.$clean_name_en;
		    $e = tldFile::upload($file_array_en['tmp_name'],"pi",$filename_en);
		    if(is_string($e)){
		        $DEFAULT_ERROR[] = "ERROR: Problem uploading Help file (EN)...<br/> $e";
		    }else{
		        $vars ['attachment_en'] = $e;
		        $PIfile = new tldFile($e);
		        $PIFileName = $PIfile->getFilePath();
		        exec("convert ".$PIFileName." -resize 800x600 -gravity center -background white -extent 800x600 ".$PIFileName);
		    }
		}

		$file_fr = $form->getElement('attachment_fr');
		$file_array_fr = $file_fr->getValue();
		if($file_array_fr['tmp_name']!=""){
			// Should be use if we want to duplicated file to
		    // $check = new tldFile($header['attachment_fr']);
		    $timestamp=time();
		    $clean_name_fr = basicFile::cleanupName($file_array_fr['name']);
		    $filename_fr = $timestamp.'en-'.$clean_name_fr;
		    $f = tldFile::upload($file_array_fr['tmp_name'],"pi",$filename_fr);
		    if(is_string($f)){
		        $DEFAULT_ERROR[] = "ERROR: Problem uploading Help file (FR)...<br/> $f";
		    }else{
		        $vars ['attachment_fr'] = $f;
		        $PIfile = new tldFile($f);
		        $PIFileName = $PIfile->getFilePath();
		        exec("convert ".$PIFileName." -resize 800x600 -gravity center -background white -extent 800x600 ".$PIFileName);
		    }
		}

		$file_zh = $form->getElement('attachment_zh');
		$file_array_zh = $file_zh->getValue();
		if($file_array_zh['tmp_name']!="") {
			// Should be use if we want to duplicated file to
            // $check = new tldFile($header['attachment_zh']);
            $timestamp = time();
            $clean_name_zh = basicFile::cleanupName($file_array_zh['name']);
            $filename_zh = $timestamp . 'zh-' . $clean_name_zh;
            $z = tldFile::upload($file_array_zh['tmp_name'], "pi", $filename_zh);
            if (is_string($z)) {
                $DEFAULT_ERROR[] = "ERROR: Problem uploading Help file (ZH)...<br/> $z";
            } else {
                $vars ['attachment_zh'] = $z;
                $PIfile = new tldFile($z);
                $PIFileName = $PIfile->getFilePath();
                exec("convert " . $PIFileName . " -resize 800x600 -gravity center -background white -extent 800x600 " . $PIFileName);
            }
        }

        if ($vars['production_impact'] === "1") {
		    $vars['dt_validity'] = '0000-00-00';
        } else {
            $vars['dt_validity'] = date('Y-m-d');
        }

		if (($userOwner=='QCQ' && $vars['owner']=='QCQ') || ($userOwner=='ECQ' && $vars['owner']=='ECQ')) {
			foreach (tldPI::getFactoriesList() as $code => $name) {
				$vars[$code]='1';
			}
		}

		$e = tldPI::insert($vars);
		if(!is_numeric($e)){
			$DEFAULT_ERROR[] = "INTERNAL ERROR: Entry not created!<br/>Reason: $e";
			break;
		}else{
			$dmodel = $vars['model'];
			$dfactory = $vars['factory'];
			$body .= "P&I Question #$e created!<br>";
			$body .= "<a href='$php_self?m[0]=pi&m[1]=add&dmodel=$dmodel&dfactory=$dfactory'>Click here if you want to create another similar P&I Question</a>";

			// Get CQ data
			$id=$e;
			$query="select * from pi_questions_logs where parent_id=".$id." order by id desc limit 1";
			$CQ = tldUtils::getSqlRowToAssocArray($query);

			$visu='<table border=0>';
			$visu.='<tr bgcolor="#2971A8" style="color:white"><td>Field</td><td>Value</td></tr>';
			$visu.='<tr bgcolor="#EEEEEE"><td>ID#             </td><td>'.$CQ['parent_id']    .'</td></tr>';
			$visu.='<tr bgcolor="#D0D0D0"><td>Operation       </td><td>'.$CQ['t_opno']       .'</td></tr>';
			$visu.='<tr bgcolor="#EEEEEE"><td>P/N             </td><td>'.$CQ['t_item']       .'</td></tr>';
			$visu.='<tr bgcolor="#D0D0D0"><td>Model           </td><td>'.$CQ['model']        .'</td></tr>';
			$visu.='<tr bgcolor="#EEEEEE"><td>Owner           </td><td>'.$CQ['owner']        .'</td></tr>';
			$i = 0;
			foreach (tldPI::getFactoriesList() as $code => $name) {
				$color = (++$i % 2) ? '#D0D0D0' : '#EEEEEE';
				$visu.='<tr bgcolor="'.$code.'"><td>'.$name.'</td><td>'.$CQ[$code].'</td></tr>';
			}
			$visu.='<tr bgcolor="#EEEEEE"><td>Subject (EN)    </td><td>'.$CQ['subject_en']   .'</td></tr>';
			$visu.='<tr bgcolor="#D0D0D0"><td>Subject (FR)    </td><td>'.$CQ['subject_fr']   .'</td></tr>';
			$visu.='<tr bgcolor="#EEEEEE"><td>Subject (ZH)    </td><td>'.$CQ['subject_zh']   .'</td></tr>';
			$visu.='<tr bgcolor="#D0D0D0"><td>Description (EN)</td><td>'.$CQ['desc_en']      .'</td></tr>';
			$visu.='<tr bgcolor="#EEEEEE"><td>Description (FR)</td><td>'.$CQ['desc_fr']      .'</td></tr>';
			$visu.='<tr bgcolor="#D0D0D0"><td>Description (ZH)</td><td>'.$CQ['desc_zh']      .'</td></tr>';
			$visu.='<tr bgcolor="#EEEEEE"><td>Position        </td><td>'.$CQ['position']     .'</td></tr>';
			$visu.='<tr bgcolor="#D0D0D0"><td>Help (EN)       </td><td>'.$CQ['help_en']      .'</td></tr>';
			$visu.='<tr bgcolor="#EEEEEE"><td>Help (FR)       </td><td>'.$CQ['help_fr']      .'</td></tr>';
			$visu.='<tr bgcolor="#D0D0D0"><td>Help (ZH)       </td><td>'.$CQ['help_zh']      .'</td></tr>';
			$visu.='<tr bgcolor="#EEEEEE"><td>Picture (EN)    </td><td>'.$CQ['attachment_en'].'</td></tr>';
			$visu.='<tr bgcolor="#D0D0D0"><td>Picture (FR)    </td><td>'.$CQ['attachment_fr'].'</td></tr>';
			$visu.='<tr bgcolor="#EEEEEE"><td>Picture (ZH)    </td><td>'.$CQ['attachment_zh'].'</td></tr>';
			$visu.='<tr bgcolor="#D0D0D0"><td>Answer Type     </td><td>'.$CQ['answer_type']  .'</td></tr>';
			$visu.='<tr bgcolor="#EEEEEE"><td>Answer Unit     </td><td>'.$CQ['answer_unit']  .'</td></tr>';
			$visu.='<tr bgcolor="#D0D0D0"><td>Component S/N   </td><td>'.$CQ['component_sn'] .'</td></tr>';
			$visu.='<tr bgcolor="#EEEEEE"><td>Answer Max      </td><td>'.$CQ['answer_max']   .'</td></tr>';
			$visu.='<tr bgcolor="#D0D0D0"><td>Answer Min      </td><td>'.$CQ['answer_min']   .'</td></tr>';
			$visu.='<tr bgcolor="#EEEEEE"><td>GT1             </td><td>'.$CQ['gt1']          .'</td></tr>';
			$visu.='<tr bgcolor="#D0D0D0"><td>GT3             </td><td>'.$CQ['gt3']          .'</td></tr>';
			$visu.='<tr bgcolor="#EEEEEE"><td>Active          </td><td>'.$CQ['active']       .'</td></tr>';
			$visu.='</table>';

			$assigneeList = [];
			$assigneeList[] = [$user->getEmail()];
            if ($vars['restricted_notification'] === '1') {
                $cmoGrp = new tldGroup('role_CMO', 900);
				$assigneeList[] =  $cmoGrp->getEmailList();
            } else {
                // Notify involved people
                $query="select factory from pi_family_matrix where family='{$vars['model']}'";
                $factories = tldUtils::getSqlToAssocArray($query);
                //needed for the CMO
                $factoryArray = array_merge(
                    ["ALVEST"],
                    array_column($factories, 'factory')
                );

                $factoryList = "'".implode("','", $factoryArray)."'";
                if (in_array($vars['owner'], ['QCQ', 'PCQ', 'ECQ'])) {
                    $functionList="'QAM', 'MPE', 'CMO'";
                }
                if ($vars['owner'] === 'ECQ') {
                    $functionList.= ", 'EM'";
                }
                $query="select lastname, firstname, locations.location, email from people ";
                $query.="LEFT JOIN tld_functions AS fct ON fct.id=people.fct_id ";
                $query.="LEFT JOIN locations ON locations.id=people.bu_id ";
                $query.="where people.hidden!=1 and fct.code in (".$functionList.") and locations.location in (".$factoryList.")";

                // notify involved pilots
                if ($vars['owner']  === 'ECQ') {
                    $query.=" union select lastname, firstname, locations.location, email from people ";
                    $query.="LEFT JOIN locations ON locations.id=people.bu_id ";
                    $query.="where people.hidden!=1 and locations.location in (".$factoryList.") ";
                    $query.="and people.id in (select parent_id from people_groups where group_name='pi_PILOT_".$vars['model']."') ";
                }
                $assignees = tldUtils::getSqlToAssocArray($query);
                $assigneeList[] = array_column($assignees, 'email');
                foreach (array_column($factories, 'factory') as $factory) {
                    $location = tldLocation::byLocationName($factory);
                    $mpe = new tldGroup('ROLE_MPE', $location['erp']);
					$assigneeList[] = $mpe->getEmailList();
                }
            }

			$subject=$vars['owner'].' #'.$id.' - '.$vars['model'].' / Op '.$vars['t_opno'].' ('.$user->getFullname().')';
			$email2="<html><body>";
			$email2.=$subject.'<br><br>';
			$email2.='<a href="www.tld-gse.com/en/private/manufacturing/index.php?m[0]=pi&m[1]=view&m[2]=edit&id='.$id.'">Open question</a><br>';
			$email2.=$visu;
			$email2.="</body></html>";
			$assignee = implode(',', array_unique(array_merge(...$assigneeList)));
			$e = tldUtils::emailAttachment($assignee,'noreply@tld-gse.com',$subject,$email2);
		}
	break;

    case 'insertQuestions':
        include_once(__DIR__."/insertQuestions.inc.php");
        break;
    case 'familyManager':
        include_once(__DIR__."/family/familyManager.php");
        break;
	case 'bijection':
		$DEFAULT_MENU .= <<<EOF
<br>
<a href="$php_self?m[0]=pi&m[1]=bijection">Home</a>
EOF;

if( $user->isInGroup(['role_MPE', 'ROLE_PS']) ) {
		$DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=pi&m[1]=bijection&m[2]=add">Add</a>
EOF;
}
		$DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=pi&m[1]=bijection&m[2]=search">Search</a>
&nbsp;|&nbsp;<a href="/en/private/mis/mis.php?m[0]=help&m[1]=dms&id=3415" title="Bijection Help Page">Help</a>
EOF;

		$WHERE = '';
		$businessUnits = [];
		$piBijection   = null;

		// Get Bijection Id for DELETE or UPDATE
		if ($_GET['id']) {
			$sql          = 'SELECT * FROM pi_bijection WHERE id=' . $_GET['id'];
			$piBijection = tldUtils::getSqlRowToAssocArray($sql);
		}

		// Get Business Units
		if (!$user->isInGroup('superuser')) {
			foreach ($user->getGroups() as $group) {
				if ($group['group_name'] === 'role_MPE') {
					$businessUnits[$group['bu_erp']] = $group['bu_name'];
				}
			}
			$bu_string = implode('", "', $businessUnits);
			$WHERE     = <<<sql
WHERE pifm.factory IN("$bu_string")
sql;
		} else {
			$businessUnits = array_filter(array_column(tldLocation::getLocationList(), 'business_unit', 'erp'));
		}

		// Get PI Families
		$sql = <<<sql
SELECT pif.id, pifm.family FROM pi_family as pif
LEFT JOIN pi_family_matrix as pifm ON pif.family=pifm.family 
$WHERE
ORDER by pifm.family ASC
sql;
		$piFamilies = array_column(tldUtils::getSqlToAssocArray($sql), 'family', 'id');

		switch ($m[2]) {
			case 'add':
			case 'update':
				if( !$user->isInGroup(['role_MPE', 'ROLE_PS']) ){
					$DEFAULT_ERROR[]= 'ERROR: You do not have permissions to access this page';
					break;
				}
				$form = new HTML_QuickForm('formBijection', 'post', '', '', '', true);
				$form->addElement('hidden', 'm[0]', 'pi');
				$form->addElement('hidden', 'm[1]', 'bijection');
				$form->addElement('hidden', 'm[2]', $m[2]);
				$form->addElement('hidden', 'id', $_GET['id']);
				$form->addElement('header', 'title', ucfirst($m[2]) . ' Bijection');
				$form->addElement('select', 'erp', 'Business Unit', ['' => 'Select BU'] + $businessUnits);
				$form->addElement('select', 'family_id', 'PIO Family', ['' => 'Select PI Family'] + $piFamilies);
				$form->addElement('text', 'opno', 'Destination Operation No', ['maxLength' => 3]);
				$form->addElement('text', 'item', 'BOM', ['maxLength' => 16]);
				$form->addElement('submit', 'btnSubmit', 'Submit');
				$form->addRule('erp', 'Required', 'required');
				$form->addRule('family_id', 'Required', 'required');
				$form->addRule('opno', 'Required', 'required');
				$form->addRule('opno', 'Should be numeric', 'numeric');
				$form->addRule('item', 'Required', 'required');

				// Set record values for UPDATE
				if ($piBijection) {
					$form->setDefaults([
						'erp' => $piBijection['erp'],
						'family_id'     => $piBijection['family_id'],
						'opno'          => $piBijection['operation_number'],
						'item'          => $piBijection['item']
					]);
				}
				break;
			case 'delete':
				$query = <<<SQL
DELETE FROM pi_bijection 
WHERE id= {$_GET['id']}
SQL;
				$e = TldUtils::sqlQuery($query);
				if (is_string($e)) {
					$DEFAULT_ERROR[] = "ERROR: Problem deleting...<br/>Reason: $e";
					break;
				}
				$body .= '<p style="color: #2ca02c;">Bijection #' . $_GET['id'] . ' deleted successfully!</p>';
				break;
			case 'search':
				$searchForm = new HTML_QuickForm('formBijection', 'get', '', '', '', true);
				$searchForm->addElement('hidden', 'm[0]', 'pi');
				$searchForm->addElement('hidden', 'm[1]', 'bijection');
				$searchForm->addElement('hidden', 'm[2]', $m[2]);
				$searchForm->addElement('header', 'title', ucfirst($m[2]) . ' Bijection');
				$searchForm->addElement('select', 'erp', 'Business Unit', ['' => 'Select BU'] + $businessUnits);
				$searchForm->addElement('select', 'family_id', 'PIO Family', ['' => 'Select PI Family'] + $piFamilies);
				$searchForm->addElement('text', 'operation_number', 'Destination Operation No', ['maxLength' => 3]);
				$searchForm->addElement('submit', 'btnSubmit', 'Submit');
		}

		$WHERE = '';
		$ACTION_WHERE = '';
		// ADD/UPDATE FORM
		if ($form) {
			if ($form->isSubmitted() && $form->validate()) {
				$vars = tldUtils::cleanupFormInput($form->exportValues());
				$myerp = tldERP::getERPOb((int) $vars["erp"]);
				try {
					$itemBySite = new ItemBySite();
					$item = $itemBySite->getItem((int) $vars["erp"], (string) $vars["item"]);
				} catch (ClientException $e) {
					$DEFAULT_ERROR[] = 'ERROR: Could not get item. Reason: ' . $e->getMessage();
				}
				$description = $item['itemDescription'];
				$partNumber = $item['item'];
				$ACTION = 'INSERT INTO';
				if ($m[2] === 'update') {
					$ACTION = 'UPDATE';
					$ACTION_WHERE  = 'WHERE id='. $id;
				}
				$sql = <<<SQL
$ACTION pi_bijection
SET
erp	= $erp,
family_id = $family_id,
operation_number = $opno,
item = '$partNumber',
description = '$description'
$ACTION_WHERE
SQL;
				if ($m[2] === 'add'){
					$e = tldUtils::sqlInsert($sql);
					if(!is_numeric($e)){
						$DEFAULT_ERROR[] = "ERROR: A problem occurred while adding the new record...<br/>Reason: $e";
						break;
					}
					$body .= '<p style="color: #2ca02c;">Bijection #' . $e . ' added successfully!</p>';
				}

				if ($m[2] === 'update'){
					$e = tldUtils::sqlQuery($sql);
					if (is_string($e)) {
						$DEFAULT_ERROR[] = "ERROR: Problem deleting record #' . $id<br/>Reason: $e";
						break;
					}
					$body .= '<p style="color: #2ca02c;">Bijection #' . $id . ' updated successfully!</p>';
				}
			}
			$body .= $form->toHTML();
		}

		// SEARCH FORM
		if ($erp = array_search($user->itsDetails['location'], $businessUnits, true)) {
			$WHERE = 'WHERE ' . tldUtils::constructWhere(['pibi.erp' => $erp]);
		}
		if ($searchForm) {
			$WHERE = '';
			if ($searchForm->isSubmitted()) {
				$finalVars = [];
				$vars = tldUtils::cleanupFormInput($searchForm->exportValues());
				$searchFields = ['erp', 'family_id', 'operation_number'];
				foreach ($vars as $field => $value){
					if($value !== '' && in_array($field, $searchFields, true)) {
						$finalVars['pibi.' . $field] = $value;
					}
				}
				$WHERE = !empty($finalVars) ? 'WHERE ' . tldUtils::constructWhere($finalVars) : '';
			}
			$body .= $searchForm->toHTML();
		}

		// Bijection matrix report --------------------->
		$query = <<<SQL
SELECT pibi.id, loc.location, pif.family, pibi.operation_number, pibi.item, pibi.description FROM pi_bijection as pibi
LEFT JOIN pi_family as pif ON pif.id=pibi.family_id
LEFT JOIN locations as loc ON loc.erp=pibi.erp
$WHERE
SQL;
		$rows   = tldUtils::getSqlToAssocArray($query);
		foreach ($rows as $k => $row) {
			if ($user->isInGroup('superuser') || (in_array($row['location'], $businessUnits, false) && $user->isInGroup(['role_MPE','ROLE_PS']))) {
				$rows[$k]['update'] = 'Update';
				$rows[$k]['delete'] = 'Delete';
			}
		}
		$report = new tldReportColumnar(new tldPagination('PIO Bijection', $rows, 50),
			[
				'xItems' => [
					'id'               => 'PI Bijection#',
					'business_unit'    => 'Business Unit',
					'family'           => 'PIO Family',
					'operation_number' => 'Destination Operation no',
					'item'             => 'BOM',
					'description'      => 'Description',
					'update'           => 'Update',
					'delete'           => 'Delete'
				],
				'title'  => 'PIO Bijection',
				'links'  => [
					'update' => [
						'url'    => $php_self . '?m[0]=pi&m[1]=bijection&m[2]=update',
						'params' => ['id' => 'id'],
					],
					'delete' => [
						'url'    => $php_self . '?m[0]=pi&m[1]=bijection&m[2]=delete',
						'params' => ['id' => 'id'],
					]
				],
				'showzero'   => true

			]
		);

		$body   .= $report->fetch();
		break;

	case 'troubleshooting':
		include_once(__DIR__."/troubleshooting.php");
		break;
	default:
		$body .= $smarty->fetch("$PATH/pi/homepage.pi.tpl");
}

function getGeneralTab(){
	global $pi;
	$header = $pi->getHeader();
	$id=$header['id'];
    $header['t_item'] = implode(', ', $header['items']);
    $dmsUrl = "https://dms.tld-group.com/index.php?m[0]=view&id=";
    $attachmentURL="/en/private/manufacturing/index.php?m[0]=pi&m[1]=view&m[2]=files&m[3]=outfile&id=$id&file_id=";
	$report = new tldAssocTable(
			$header,
			array_merge([
					"id"					=>"ID#",
					"t_opno"				=>"Operation",
					"t_item"				=>"P/N",
					"model"					=>"Model",
			        "owner"					=>"Owner",
				],
				tldPI::getFactoriesList(),
				[
					"subject_en"			=>"Subject (EN)",
					"subject_fr"			=>"Subject (FR)",
					"subject_zh"			=>"Subject (ZH)",
					"desc_en"				=>"Description (EN)",
					"desc_fr"				=>"Description (FR)",
					"desc_zh"				=>"Description (ZH)",
					"position"				=>"Position",
					"help_en"				=>"DMS# (EN)",
					"help_fr"				=>"DMS# (FR)",
					"help_zh"				=>"DMS# (ZH)",
			        "attachment_en"		    =>"Picture (EN)",
			        "attachment_fr"	 	    =>"Picture (FR)",
			        "attachment_zh"		    =>"Picture (ZH)",
					"answer_type"			=>"Answer Type",
					"answer_unit"			=>"Answer Unit",
					"match_list"			=>"Match List",
					"answer_max"			=>"Answer Max",
					"answer_min"			=>"Answer Min",
					"gt1"					=>"GT1",
					"gt3"					=>"GT3",
					"active"				=>"Active",
				]
			),
        	["title"=>"Record details",
					"links"=>["id"=>"/en/private/manufacturing/index.php?m[0]=pi&m[1]=view&id=",
								   "help_en" => $dmsUrl,
								   "help_fr" => $dmsUrl,
								   "help_zh" => $dmsUrl,
					               "attachment_en" => $attachmentURL,
					               "attachment_fr" => $attachmentURL,
					               "attachment_zh" => $attachmentURL,
                    ]
        ]
	);
	$body = $report->fetch();
	return $body;
}

function validQuestion(array $vars): bool
{
	/** @var $DEFAULT_ERROR array */
	global $DEFAULT_ERROR;

	if (empty($vars['t_opno']) && empty($vars['items']) && empty($vars['model'])) {
		$DEFAULT_ERROR[] = 'ERROR: A P&I question needs to be linked to at least one of the following: Operation, P/N or Model';
		return false;
	}

	if (empty($vars['subject_en']) && empty($vars['subject_fr']) && empty($vars['subject_zh'])) {
		$DEFAULT_ERROR[] = 'ERROR: Subject Missing';
		return false;
	}

	if (empty($vars['desc_en']) && empty($vars['desc_fr']) && empty($vars['desc_zh'])) {
		$DEFAULT_ERROR[] = 'ERROR: Description Missing';
		return false;
	}

	if (!is_numeric($vars['position'])) {
		$DEFAULT_ERROR[] = 'ERROR: Invalid Position';
		return false;
	}

	if (!empty($vars['items'])) {
		try {
			global $kernel;
			$client = $kernel->getContainer()->get(Client::class);
			$items = $client->get(
				sprintf('/ion/engineering_item_descriptions'),
				['query' => ['itemsList' => implode('|', $vars['items'] ?? [])]]
			);

		} catch (ClientException $exception) {
			error_log(sprintf('PIO Question GET items error : [%s] %s', $exception->getCode(), $exception->getMessage()));
			$DEFAULT_ERROR[] = 'INTERNAL ERROR: Could not find parts on ERP.';
			return false;
		}

		if (count($vars['items']) !== $items['hydra:totalItems']) {
			$invalidItems = array_diff($vars['items'], array_column($items['hydra:member'], 'item'));
			$DEFAULT_ERROR[] = sprintf("INTERNAL ERROR: The PN %s you entered is not valid!", implode(',', $invalidItems));
			return false;
		}
	} elseif($vars['owner'] === 'ECQ') {
		$DEFAULT_ERROR[] = "ERROR: You need to enter a P/N for ECQ question!";
		return false;
	}

	if (!empty($vars['t_opno']) && (1 > (int) $vars['t_opno'] || 999 < (int) $vars['t_opno'])) {
		$DEFAULT_ERROR[] = "INTERNAL ERROR: The Operation Number you entered is not valid!";
		return false;
	}

	return true;
}
?>
