<?php

use ApiBundle\Client;
use Symfony\Component\HttpClient\Exception\ClientException;

if(empty($id) OR !is_numeric($id)){
    $DEFAULT_ERROR[]=  "ERROR: no ID set...";
    return;
}
$pi = new tldPI($id);
if($pi->isEmpty()){
	$DEFAULT_ERROR[]=  "ERROR: No Record #$id found...";
	return;
}
$header = $pi->getHeader();
if(!$user->isInGroup(["gg_MIS", "role_QAM", "pi_PCQ", "pi_QCQ", "pi_ECQ","ROLE_RME"])){
	$DEFAULT_ERROR[] = "ERROR: You do not have permissions to view this record...";
	return;
}

$DEFAULT_TITLE .= "\Record #$id";
$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=pi&m[1]=view&id=$id">General</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=pi&m[1]=view&m[2]=files&id=$id" title="Link Files to #$id">Files</a>
EOF;
if($user->isInGroup(["gg_MIS", "role_QAM", "pi_PCQ", "pi_QCQ", "pi_ECQ"])) {
	$DEFAULT_MENU .=<<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=pi&m[1]=view&m[2]=edit&id=$id" title="Edit Record #$id">Edit</a>
EOF;
}
switch($m[2]){
	case 'files':
		$DEFAULT_TITLE .="\Files";
		switch($m[3]){
			case 'delete':
				if($file_id == ''){
					$DEFAULT_ERROR[] = "ERROR: File #ID is empty!";
					break;
				}
				$file = new tldFile($file_id);
				if($file->isEmpty()){
					$DEFAULT_ERROR[]=  "ERROR: No File #$file_id found...";
					break;
				}
				$file->delete();
			break;
			case 'outfile':
			if($file_id == ''){
				$DEFAULT_ERROR[] = "ERROR: File #ID is empty!";
			break;
			}
			$file = new tldFile($file_id);
			if($file->isEmpty()){
				$DEFAULT_ERROR[]=  "ERROR: No File #$file_id found...";
				break;
			}

			$file->download();
			break;
			default:
			$report = new tldReportColumnar(
					$pi->getFiles(),
					array("xItems"=>array(
							"id"=>"File ID",
							"dt"=>"Date",
							"poster"=>"Poster",
							"filename"=>"Filename"),
							"links"=>array("id"=>"/en/private/manufacturing/index.php?m[0]=pi&m[1]=view&m[2]=files&m[3]=outfile&id=$id&file_id="),
							"functions"=>array(
									"Delete"=>array(
											"url"=>"/en/private/manufacturing/index.php?m[0]=pi&m[1]=view&m[2]=files&m[3]=delete&id=$id",
											"param"=>array("file_id"=>"id"),
											"img"=>"/shared/icons/application/delete2.png"
											)
										)
					)
			);
			$body .= $report->fetch();
			break;
		}
	break;
	case 'edit':
		// Check permissions
		if(!$user->isInGroup(array("gg_ADMIN","gg_MIS", "role_QAM", "pi_PCQ", "pi_QCQ", "pi_ECQ"))){
			$DEFAULT_ERROR[] = "ERROR: You do not have permissions for this page..";
			return;
		}
		$families=tldPI::getPiFamilies(['status' => 'Final']);
		foreach ($families as $key1 => $value1) {
		    $family[]=$families[$key1]['family'];
		}
		//value by default for owner field for each case ( key= userRight.questionType)
		$authorizedOwner = [
			"QCQQCQ"=>["QCQ","PCQ"],
			"QCQPCQ"=>["QCQ","PCQ"],
			"QCQECQ"=>["ECQ"],
			"ECQQCQ"=>["QCQ"],
			"ECQPCQ"=>["PCQ"],
			"ECQECQ"=>["ECQ"],
			"PCQECQ"=>["ECQ"],
			"PCQQCQ"=>["QCQ"],
			"PCQPCQ"=>["PCQ"],
            "ALLPCQ"=>["QCQ","PCQ", "ECQ"],
            "ALLECQ"=>["QCQ","PCQ", "ECQ"],
            "ALLQCQ"=>["QCQ","PCQ", "ECQ"],
		];

        $userOwner = '';

		if($user->isInGroup(["pi_QCQ"])) {
		    $userOwner = 'QCQ';
		} elseif($user->isInGroup(["pi_ECQ"])) {
			$userOwner = 'ECQ';
		} elseif($user->isInGroup(["pi_PCQ"])) {
			$userOwner = 'PCQ';
		}

        if ($user->isInGroup(["pi_QCQ"]) && $user->isInGroup(["pi_ECQ"]) && $user->isInGroup(["pi_PCQ"])) {
            $userOwner = 'ALL';
        }

		// Form
        $required =[];
		$form = new HTML_QuickForm('frmEdit', 'post');
		$form->addElement(  'hidden', 	'm[0]', 'pi');
		$form->addElement(  'hidden', 	'm[1]', 'view');
		$form->addElement(  'hidden', 	'm[2]', 'edit');
		$form->addElement(  'hidden', 	'id',   $id);
		$form->addElement(  'hidden', 	'updated_by',   		$user->getID());
		$form->addElement(  'header', 	'title', 				"Edit Record #$id");
        $form->addElement('text', 't_opno', 'Operation');
        $items = array_key_exists('items', $_POST) ? $_POST['items'] : [];
        createPNFields($form,$items,$header['items']);
        $form->addElement(	'static', '', '<a id="addPN" href="#">Add another P/N</a>', null);
		$form->addElement(	'select', 	'model', 				'Family', array_combine($family, $family));
        $form->addElement(	'select', 	'owner',				'Owner', array_combine($authorizedOwner[$userOwner.$header['owner']], $authorizedOwner[$userOwner.$header['owner']]));
        foreach(tldPI::getFactoriesList() as $code => $name) {
            $form->addElement(	'text', $code, $name);
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
        $form->addElement(	'textarea', 'desc_en', 				'Description (EN)',			["rows"=>5, "cols"=>40]);
        $form->addElement(	'textarea', 'desc_fr', 				'Description (FR)',			["rows"=>5, "cols"=>40]);
        $form->addElement(	'textarea', 'desc_zh', 				'Description (ZH)',			["rows"=>5, "cols"=>40]);
		$form->addElement('text', 'position', 'Position');
        $form->addElement(	'text', 	'help_en',		        'DMS# (EN)');
        $form->addElement(	'text', 	'help_fr',		        'DMS# (FR)');
        $form->addElement(	'text', 	'help_zh',    		    'DMS# (ZH)');
        $form->addElement(	'text', 	'attachment_en',		'Actual picture (EN)');
        $form->addElement(	'file', 	'attach_en',		    'Change picture (EN)');
        $form->addElement(	'text', 	'attachment_fr',		'Actual picture (FR)');
        $form->addElement(	'file', 	'attach_fr',		    'Change picture (FR)');
        $form->addElement(	'text', 	'attachment_zh',		'Actual picture (ZH)');
        $form->addElement(	'file', 	'attach_zh',		    'Change picture (ZH)');
        $form->addElement(	'select', 	'answer_type', 			'Answer Type',				[""=>"","YES/NO"=>"YES/NO","Decimal"=>"Decimal","Alphanumeric"=>"Alphanumeric","Match List"=>"Match List","S/N"=>"S/N"]);
        $form->addElement(	'select', 	'answer_unit', 			'Answer Unit',				[""=>""] + tldPI::getUnits());
        $form->addElement(	'select', 	'component_sn',			'Component (S/N question)',	[""=>""] + $formattedComponents);
        $form->addElement(	'select', 	'match_list', 			'Match List',				[""=>"","1"=>"mfg_test_list1","2"=>"mfg_test_list2"]);
        $form->addElement(	'text', 	'answer_max', 			'Answer Max');
        $form->addElement(	'text', 	'answer_min', 			'Answer Min');
        $form->addRule('answer_max', 'Field is numeric', 'numeric');
        $form->addRule('answer_min', 'Field is numeric', 'numeric');
        $form->addElement(	'text', 	'gt1', 					'GT1');
        $form->addElement(	'text', 	'gt3', 					'GT3');
        $form->addElement(	'select', 	'active', 				'Active',[""=>"","N"=>"N","Y"=>"Y"]);
        $form->addElement('checkbox', 'restricted_notification', 'Local CQ change with no impact <br>or no interest for the sister BUs, <br>so no need for notification');

        // follow DMS 2109. disable field following role and type of question(ECQ,PCQ,QCQ)
		switch ($userOwner.$header['owner']){
			case 'QCQECQ':
				$enableField =[
					"gt1"=>"gt1",
					"gt3"=>"gt3",
				];
				$form = limitEnableFields($form,$enableField);
				break;
			case 'PCQQCQ':
			case 'PCQECQ':
				$enableField = ["position" => "position"];
				$required = array_merge($required,["position"]);
				$form = limitEnableFields($form,$enableField);
				break;
			case 'ECQQCQ':
			case 'ECQPCQ':
				$enableField = [];
				$form = limitEnableFields($form,$enableField);
				break;
			default:
				$required = array_merge($required,["position","answer_type","active"]);
				break;
		}
        $form->addElement(  'submit', 	'btnSubmit', 'Submit');
		$form->addElement(  'reset', 	'btnReset',  'Reset');


        $form->setDefaults($header);
        foreach($required as $key=>$field) {
			$form->addRule($field, 'Required', 'required');
		}

		if ('ECQ' === $userOwner) {
			foreach (tldPI::getFactoriesList() as $code => $name) {
				$form->setDefaults([$code => '1']);
			}
		}

		if(!$form->validate()){
			$body = $form->toHTML();
            $body.= <<<HTML
            <script type="application/javascript">
            function convertHtml(){
                var oldVal =  $(this).val();
                $(this).val($(this).html(oldVal).text());
            }
            
             var inputs =[];
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

		$fields = array_merge(
		    array_keys(tldPI::getFactoriesList()),
		    [
		    "t_opno",
            "t_item",
            "model",
            "owner",
            "subject_en",
            "subject_fr",
            "subject_zh",
            "desc_en",
            "desc_fr",
            "desc_zh",
            "position",
            "help_en",
            "help_fr",
            "help_zh",
            "attachment_en",
            "attachment_fr",
            "attachment_zh",
            "answer_type",
            "answer_unit",
            "component_sn",
            "match_list",
            "answer_max",
            "answer_min",
            "gt1",
            "gt3",
            "active"
        ])
        ;
		$vars = tldUtils::cleanupFormInput($form->exportValues());
		//get the PN in the fields item[]
		$varsItem = $form->exportValue('items[]');
		//if one PN => the result is a string

		if (is_array($varsItem)) {

            $vars['items'] = array_unique(array_filter($varsItem, function ($item) {
                return trim($item) !== "";
            }));

        } elseif (is_string($varsItem) && trim($varsItem)!=="") {
            $vars['items'] = [$varsItem];
        } else {
            //other case
            $vars['items'] = [];
        }


		if(!empty($vars['answer_min']) || !empty($vars['answer_max'])){
		    if(empty($vars['answer_min'])) { $vars['answer_min']='0'; }
		    if(empty($vars['answer_max'])) { $vars['answer_max']='0'; }
		    if($vars['answer_min']>$vars['answer_max']) {
		        $tmp=$vars['answer_min'];
		        $vars['answer_min']=$vars['answer_max'];
		        $vars['answer_max']=$tmp;
		    }
		    if($vars['answer_max'] === $vars['answer_min'] ){
				$DEFAULT_ERROR[] = "INTERNAL ERROR: Answer min mustn't be egal to the answer max";
				$body = $form->toHTML();
				break;
			}
		}

        if (false === validQuestion($vars)) {
            $body = $form->toHTML();
            break;
        }

		//File Management
		$file_en = $form->getElement('attach_en');
		$file_array_en = $file_en->getValue();
		if($file_array_en['tmp_name']!=""){
		    $check = new tldFile($header['attachment_en']);
	        $timestamp=time();
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

		$file_fr = $form->getElement('attach_fr');
		$file_array_fr = $file_fr->getValue();
		if($file_array_fr['tmp_name']!=""){
		    $check = new tldFile($header['attachment_fr']);
		    $timestamp=time();
		    $clean_name_fr = basicFile::cleanupName($file_array_fr['name']);
		    $filename_fr = $timestamp.'fr-'.$clean_name_fr;
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

		$file_zh = $form->getElement('attach_zh');
		$file_array_zh = $file_zh->getValue();
		if($file_array_zh['tmp_name']!=""){
		    $check = new tldFile($header['attachment_zh']);
		    $timestamp=time();
		    $clean_name_zh = basicFile::cleanupName($file_array_zh['name']);
		    $filename_zh = $timestamp.'zh-'.$clean_name_zh;
		    $z = tldFile::upload($file_array_zh['tmp_name'],"pi",$filename_zh);
		    if(is_string($z)){
		        $DEFAULT_ERROR[] = "ERROR: Problem uploading Help file (ZH)...<br/> $z";
		    }else{
		        $vars ['attachment_zh'] = $z;
		        $PIfile = new tldFile($z);
		        $PIFileName = $PIfile->getFilePath();
		        exec("convert ".$PIFileName." -resize 800x600 -gravity center -background white -extent 800x600 ".$PIFileName);
		    }
		}

		if (($userOwner=='QCQ' && $vars['owner']=='QCQ') || ($userOwner=='ECQ' && $vars['owner']=='ECQ')) {
            foreach (tldPI::getFactoriesList() as $code => $name) {
                $vars[$code]='1';
            }
		}

		//encoding value in html entities.
        $vars['subject_fr'] = htmlentities($vars['subject_fr'], ENT_COMPAT | ENT_HTML401, ini_get("default_charset"), false);
        $vars['subject_en'] = htmlentities($vars['subject_en'], ENT_COMPAT | ENT_HTML401, ini_get("default_charset"), false);

        $e = $pi->update($vars, $fields);
		if(is_string($e)){
			$DEFAULT_ERROR[] = "ERROR: Problem updating...<br/>Reason: $e";
			break;
		}
		$body .= "Record #$id updated successfully!";

		// Get CQ data
		$query="select * from pi_questions_logs where parent_id=".$id." order by id desc limit 2";
		$CQ = tldUtils::getSqlToAssocArray($query);

		if ($CQ[0]['t_opno']       !=$CQ[1]['t_opno'])       { $color_t_opno='red';        }
		if ($CQ[0]['t_item']       !=$CQ[1]['t_item'])       { $color_t_item='red';        }
		if ($CQ[0]['model']        !=$CQ[1]['model'])        { $color_model='red';         }
		if ($CQ[0]['owner']        !=$CQ[1]['owner'])        { $color_owner='red';         }
		foreach (tldPI::getFactoriesList() as $code => $name) {
            if ($CQ[0][$code] != $CQ[1][$code]) {
                ${'color_'.$code}='red';
            }
        }
		if ($CQ[0]['subject_en']   !=$CQ[1]['subject_en'])   { $color_subject_en='red';    }
		if ($CQ[0]['subject_fr']   !=$CQ[1]['subject_fr'])   { $color_subject_fr='red';    }
		if ($CQ[0]['subject_zh']   !=$CQ[1]['subject_zh'])   { $color_subject_zh='red';    }
		if ($CQ[0]['desc_en']      !=$CQ[1]['desc_en'])      { $color_desc_en='red';       }
		if ($CQ[0]['desc_fr']      !=$CQ[1]['desc_fr'])      { $color_desc_fr='red';       }
		if ($CQ[0]['desc_zh']      !=$CQ[1]['desc_zh'])      { $color_desc_zh='red';       }
		if ($CQ[0]['position']     !=$CQ[1]['position'])     { $color_position='red';      }
		if ($CQ[0]['help_en']      !=$CQ[1]['help_en'])      { $color_help_en='red';       }
		if ($CQ[0]['help_fr']      !=$CQ[1]['help_fr'])      { $color_help_fr='red';       }
		if ($CQ[0]['help_zh']      !=$CQ[1]['help_zh'])      { $color_help_zh='red';       }
		if ($CQ[0]['attachment_en']!=$CQ[1]['attachment_en']){ $color_attachment_en='red'; }
		if ($CQ[0]['attachment_fr']!=$CQ[1]['attachment_fr']){ $color_attachment_fr='red'; }
		if ($CQ[0]['attachment_zh']!=$CQ[1]['attachment_zh']){ $color_attachment_zh='red'; }
		if ($CQ[0]['answer_type']  !=$CQ[1]['answer_type'])  { $color_answer_type='red';   }
		if ($CQ[0]['answer_unit']  !=$CQ[1]['answer_unit'])  { $color_answer_unit='red';   }
		if ($CQ[0]['component_sn'] !=$CQ[1]['component_sn']) { $color_component_sn='red';  }
		if ($CQ[0]['answer_max']   !=$CQ[1]['answer_max'])   { $color_answer_max='red';    }
		if ($CQ[0]['answer_min']   !=$CQ[1]['answer_min'])   { $color_answer_min='red';    }
		if ($CQ[0]['gt1']          !=$CQ[1]['gt1'])          { $color_gt1='red';           }
		if ($CQ[0]['gt3']          !=$CQ[1]['gt3'])          { $color_gt3='red';           }
		if ($CQ[0]['active']       !=$CQ[1]['active'])       { $color_active='red';        }

        $visu='<table border=0>';
        $visu.='<tr bgcolor="#2971A8" style="color:white"><td>Field           </td><td>Actual                     </td><td>Previous                   </td></tr>';
	    $visu.='<tr bgcolor="#EEEEEE"><td>ID#             </td><td                                >'   .$CQ[0]['parent_id']    .'</td><td>'.$CQ[1]['parent_id']    .'</td></tr>';
	    $visu.='<tr bgcolor="#D0D0D0"><td>Operation       </td><td bgcolor="'.$color_t_opno.'">'       .$CQ[0]['t_opno']       .'</td><td>'.$CQ[1]['t_opno']       .'</td></tr>';
	    $visu.='<tr bgcolor="#EEEEEE"><td>Model           </td><td bgcolor="'.$color_model.'">'        .$CQ[0]['model']        .'</td><td>'.$CQ[1]['model']        .'</td></tr>';
	    $visu.='<tr bgcolor="#D0D0D0"><td>Owner           </td><td bgcolor="'.$color_owner.'">'        .$CQ[0]['owner']        .'</td><td>'.$CQ[1]['owner']        .'</td></tr>';
        $visu.='<tr bgcolor="#EEEEEE"><td>P/N             </td><td bgcolor="'.$color_t_item.'">'       .$CQ[0]['t_item']       .'</td><td>'.$CQ[1]['t_item']       .'</td></tr>';
        $i = 0;
        foreach (tldPI::getFactoriesList() as $code => $name) {
            $color = (++$i % 2) ? '#D0D0D0' : '#EEEEEE';
            $visu.='<tr bgcolor="'.$color.'"><td>'.$name.'</td><td bgcolor="'.${'color_'.$code}.'">'.$CQ[0][$code].'</td><td>'.$CQ[1][$code].'</td></tr>';
        }
	    $visu.='<tr bgcolor="#EEEEEE"><td>Subject (EN)    </td><td bgcolor="'.$color_subject_en.'">'   .$CQ[0]['subject_en']   .'</td><td>'.$CQ[1]['subject_en']   .'</td></tr>';
	    $visu.='<tr bgcolor="#D0D0D0"><td>Subject (FR)    </td><td bgcolor="'.$color_subject_fr.'">'   .$CQ[0]['subject_fr']   .'</td><td>'.$CQ[1]['subject_fr']   .'</td></tr>';
	    $visu.='<tr bgcolor="#EEEEEE"><td>Subject (ZH)    </td><td bgcolor="'.$color_subject_zh.'">'   .$CQ[0]['subject_zh']   .'</td><td>'.$CQ[1]['subject_zh']   .'</td></tr>';
	    $visu.='<tr bgcolor="#D0D0D0"><td>Description (EN)</td><td bgcolor="'.$color_desc_en.'">'      .$CQ[0]['desc_en']      .'</td><td>'.$CQ[1]['desc_en']      .'</td></tr>';
	    $visu.='<tr bgcolor="#EEEEEE"><td>Description (FR)</td><td bgcolor="'.$color_desc_fr.'">'      .$CQ[0]['desc_fr']      .'</td><td>'.$CQ[1]['desc_fr']      .'</td></tr>';
	    $visu.='<tr bgcolor="#D0D0D0"><td>Description (ZH)</td><td bgcolor="'.$color_desc_zh.'">'      .$CQ[0]['desc_zh']      .'</td><td>'.$CQ[1]['desc_zh']      .'</td></tr>';
	    $visu.='<tr bgcolor="#EEEEEE"><td>Position        </td><td bgcolor="'.$color_position.'">'     .$CQ[0]['position']     .'</td><td>'.$CQ[1]['position']     .'</td></tr>';
	    $visu.='<tr bgcolor="#D0D0D0"><td>DMS# (EN)       </td><td bgcolor="'.$color_help_en.'">'      .$CQ[0]['help_en']      .'</td><td>'.$CQ[1]['help_en']      .'</td></tr>';
	    $visu.='<tr bgcolor="#EEEEEE"><td>DMS# (FR)       </td><td bgcolor="'.$color_help_fr.'">'      .$CQ[0]['help_fr']      .'</td><td>'.$CQ[1]['help_fr']      .'</td></tr>';
	    $visu.='<tr bgcolor="#D0D0D0"><td>DMS# (ZH)       </td><td bgcolor="'.$color_help_zh.'">'      .$CQ[0]['help_zh']      .'</td><td>'.$CQ[1]['help_zh']      .'</td></tr>';
	    $visu.='<tr bgcolor="#EEEEEE"><td>Picture (EN)    </td><td bgcolor="'.$color_attachment_en.'">'.$CQ[0]['attachment_en'].'</td><td>'.$CQ[1]['attachment_en'].'</td></tr>';
	    $visu.='<tr bgcolor="#D0D0D0"><td>Picture (FR)    </td><td bgcolor="'.$color_attachment_fr.'">'.$CQ[0]['attachment_fr'].'</td><td>'.$CQ[1]['attachment_fr'].'</td></tr>';
	    $visu.='<tr bgcolor="#EEEEEE"><td>Picture (ZH)    </td><td bgcolor="'.$color_attachment_zh.'">'.$CQ[0]['attachment_zh'].'</td><td>'.$CQ[1]['attachment_zh'].'</td></tr>';
	    $visu.='<tr bgcolor="#D0D0D0"><td>Answer Type     </td><td bgcolor="'.$color_answer_type.'">'  .$CQ[0]['answer_type']  .'</td><td>'.$CQ[1]['answer_type']  .'</td></tr>';
	    $visu.='<tr bgcolor="#EEEEEE"><td>Answer Unit     </td><td bgcolor="'.$color_answer_unit.'">'  .$CQ[0]['answer_unit']  .'</td><td>'.$CQ[1]['answer_unit']  .'</td></tr>';
	    $visu.='<tr bgcolor="#D0D0D0"><td>Component S/N   </td><td bgcolor="'.$color_component_sn.'">' .$CQ[0]['component_sn'] .'</td><td>'.$CQ[1]['component_sn'] .'</td></tr>';
	    $visu.='<tr bgcolor="#EEEEEE"><td>Answer Max      </td><td bgcolor="'.$color_answer_max.'">'   .$CQ[0]['answer_max']   .'</td><td>'.$CQ[1]['answer_max']   .'</td></tr>';
        $visu.='<tr bgcolor="#D0D0D0"><td>Answer Min      </td><td bgcolor="'.$color_answer_min.'">'   .$CQ[0]['answer_min']   .'</td><td>'.$CQ[1]['answer_min']   .'</td></tr>';
	    $visu.='<tr bgcolor="#EEEEEE"><td>GT1             </td><td bgcolor="'.$color_gt1.'">'          .$CQ[0]['gt1']          .'</td><td>'.$CQ[1]['gt1']          .'</td></tr>';
	    $visu.='<tr bgcolor="#D0D0D0"><td>GT3             </td><td bgcolor="'.$color_gt3.'">'          .$CQ[0]['gt3']          .'</td><td>'.$CQ[1]['gt3']          .'</td></tr>';
	    $visu.='<tr bgcolor="#EEEEEE"><td>Active          </td><td bgcolor="'.$color_active.'">'       .$CQ[0]['active']       .'</td><td>'.$CQ[1]['active']       .'</td></tr>';
		$visu.='</table>';

        if ($vars['restricted_notification'] === "1") {
            $cmoGrp = new tldGroup("role_CMO", 900);
            $assignee = implode(',', array_merge($cmoGrp->getEmailList(),[$user->getEmail()]));
        } else {
            // Notify involved people
            $query="select factory from pi_family_matrix where family='".$vars['model']."'";
            $factories = tldUtils::getSqlToAssocArray($query);
            $factoryArray = array_merge(
                ["TLD_GROUP"],
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

            //notify involved pilots
            if ($header['owner']=='ECQ') {
                $query.=" union select lastname, firstname, locations.location, email from people ";
                $query.="LEFT JOIN locations ON locations.id=people.bu_id ";
                $query.="where people.hidden!=1 and locations.location in (".$factoryList.") ";
                $query.="and people.id in (select parent_id from people_groups where group_name='pi_PILOT_".$vars['model']."') ";
            }

            $assignees = tldUtils::getSqlToAssocArray($query);
            $assigneeList = array_column($assignees, 'email');
            $assignee= implode(',', array_unique($assigneeList));
        }

        $subject=$header['owner'].' #'.$id.' - '.$vars['model'].' / Op '.$vars['t_opno'].' ('.$user->getFullname().')';
        $email2="<html><body>";
        $email2.=$subject.'<br><br>';
        $email2.='<a href="www.tld-gse.com/en/private/manufacturing/index.php?m[0]=pi&m[1]=view&m[2]=edit&id='.$id.'">Open question</a><br>';
        $email2.=$visu;
        $email2.="</body></html>";
        $e = tldUtils::emailAttachment($assignee,'noreply@tld-gse.com',$subject,$email2);

		$body .= getGeneralTab();
	break;
	default:
		$body .= getGeneralTab();
	break;
}


function createPNFields(HTML_QuickForm $form, array $postItems = [], array $headerItems = [], $disabled = false)
{
	//creation parameters for P/N fields
	$parameters = ['class' => 'pn_input', 'value' => ''];
	if ($disabled) {
		$parameters['disabled'] = 'disabled';
	}
	//add P/N fields to the form
	if (($form->isSubmitted() && isset($postItems)) || !empty($headerItems)) {
		//set pn fields default value
		$items = $form->isSubmitted() && !$disabled ? $postItems : $headerItems;
		foreach ($items as $index => $item) {
			$i = $index + 1;
			$parameters['value'] = $item;
			$form->addElement('text', 'items[]', "P/N #$i", $parameters);
		}
	} else {
		$form->addElement('text', 'items[]', 'P/N #1', $parameters);
	}

}

function limitEnableFields(HTML_QuickForm $form, array $enabledFields){

	foreach ($form->_elements as &$field){
		if(!array_key_exists($field->getname(),$enabledFields)){
			if($field->getType() === "select") {
				$field->setAttribute('disabled','');
			}else {
				//readonly because if disabled some value are not sent when the form is submitted
				$field->setAttribute('readonly','');
				$field->setAttribute('style',"color:gray;border:1px solid gray;");
			}
		}
	}
    return $form;
}
