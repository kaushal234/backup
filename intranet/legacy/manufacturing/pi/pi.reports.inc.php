<?php
require_once('HTML/QuickForm/advmultiselect.php');

use ApiBundle\Client;
use AppBundle\Chart\ChartBuilder;
use AppBundle\Chart\ChartBuilderFactory;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Exception\ServerException;

global $kernel;
$container = $kernel->getContainer();
$chartBuilderFactory = $container->get(ChartBuilderFactory::class);
$client = $container->get(Client::class);
$DEFAULT_TITLE .= "\Reports";

switch($m[2]){
    case 'piQuestionsLogs':
        if(!$user->isInGroup(["pi_PCQ", "pi_QCQ", "pi_ECQ","ROLE_RME"])) {
            $DEFAULT_ERROR[] = "ERROR: You do not have permission to access this module";
            return;
        }

        $xItems = array_merge(
            [
                "id"					=>"ID#",
                "parent_id"				=>"Parent ID#",
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
                "help_en"				=>"Help (EN)",
                "help_fr"				=>"Help (FR)",
                "help_zh"				=>"Help (ZH)",
                "attachment_en"		    =>"Picture (EN)",
                "attachment_fr"		    =>"Picture (FR)",
                "attachment_zh"   		=>"Picture (ZH)",
                "answer_type"			=>"Answer Type",
                "answer_unit"			=>"Answer Unit",
                "component_sn"			=>"Component S/N",
                "answer_max"			=>"Answer Max",
                "answer_min"			=>"Answer Min",
                "gt1"					=>"GT1",
                "gt3"					=>"GT3",
                "active"				=>"Active",
                "updated_on"   			=>"Updated on",
                "updated_by"			=>"Updated by",
                "name"      			=>"Name",
            ]
        );

        $families=tldPI::getPiFamilies();
        $family[]='';
        foreach ($families as $key1 => $value1) {
            $family[]=$families[$key1]['family'];
        }
        //Get form
        $form = new HTML_QuickForm('frmQuestionsLogs', 'post');
        $form->addElement(	'hidden', 	'm[0]', 			'pi');
        $form->addElement(	'hidden', 	'm[1]', 			'reports');
        $form->addElement(	'hidden', 	'm[2]', 			'piQuestionsLogs');
        $form->addElement(	'header', 	'title', 			'Select filters');
        $form->addElement(	'text', 	'parent_id',		'Question ID#');
        $form->addElement(	'text', 	't_opno', 			'Operation');
        $form->addElement(	'select', 	'model', 			'Family',				array_combine($family, $family));

        $form->addElement(  'submit', 	'btnSubmit', 		'Submit');
        $form->addRule('model', 'Required', 'required');
        $form->setDefaults(array("parent_id"=>$id));

        $body = $form->toHTML();
        $vars = tldUtils::cleanupFormInput($form->exportValues());

        $TITLE = "Search Results";
        $body = $form->toHTML();
        $query='select ';
        $query.='logs.id, logs.parent_id, logs.t_opno, logs.t_item, logs.model, logs.owner, ';
        foreach (tldPI::getFactoriesList() as $code => $name) {
            $query.="logs.$code, ";
        }
        $query.='logs.subject_en, logs.subject_fr, logs.subject_zh, ';
        $query.='logs.desc_en, logs.desc_fr, logs.desc_zh, logs.position, logs.help_en, ';
        $query.='logs.help_fr, logs.help_zh, logs.attachment_en, logs.attachment_fr, ';
        $query.='logs.attachment_zh, logs.answer_type, logs.answer_unit, logs.component_sn, ';
        $query.='logs.answer_max, logs.answer_min, logs.updated_on, logs.updated_by, ';
        $query.='logs.gt1, logs.gt3, logs.active, ';
        $query.="(SELECT concat(lastname, ' ', firstname) FROM people where id=logs.updated_by) as name ";
        $query.='from pi_questions_logs logs where 1=1 ';

        if ($vars['parent_id']!='') { $query.='and parent_id='.$vars['parent_id'].' '; }
        if ($vars['t_opno']!='') { $query.='and t_opno='.$vars['t_opno'].' '; }
        $query.='and model="'.$vars['model']. '" ';
        $rows = tldUtils::getSqlToAssocArray($query);
        if(isset($_SESSION['data_report']))
            unset($_SESSION['data_report']);
        $_SESSION['data_report']=$rows;
        if($rows){
            $report = new tldReportColumnar(
                $rows,
                array(
                    "xItems"=>$xItems,
                    "title"=>"P&I Questions logs",
                    "stickyHeader" => true
                )
            );
            $body .= $report->fetch();
        }else{
            $body = $form->toHTML();
            $body .= "<br>No records...";
        }
    break;

    case 'piByFilter':
        if(!$user->isInGroup(['pi_PCQ', 'pi_QCQ', 'pi_ECQ', 'ROLE_RME', 'ROLE_PSM', 'ROLE_QAM'])) {
            $DEFAULT_ERROR[] = "ERROR: You do not have permission to access this module";
            return;
        }

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
                    "edit"	       			=>"Edit",
                    "duplicate"				=>"Duplicate",
                    "delete"				=>"Delete",
            ]
        );

        $xItemsCSV = array_merge(
            [
                    "id"					=>"ID#",
                    "t_opno"				=>"Operation",
                    "t_item"				=>"P/N",
                    "model"					=>"Model",
                    "owner"					=>"Owner",
            ],
            tldPI::getFactoriesList(), [
                    "subject_en"			=>"Subject (EN)",
                    "subject_fr"			=>"Subject (FR)",
                    "subject_zh"			=>"Subject (ZH)",
                    "desc_en"				=>"Description (EN)",
                    "desc_fr"				=>"Description (FR)",
                    "desc_zh"				=>"Description (ZH)",
                    "position"				=>"Position",
                    "help_en"				=>"Help (EN)",
                    "help_fr"				=>"Help (FR)",
                    "help_zh"				=>"Help (ZH)",
                    "attachment_en"		    =>"Picture (EN)",
                    "attachment_fr"		    =>"Picture (FR)",
                    "attachment_zh"   		=>"Picture (ZH)",
                    "answer_type"			=>"Answer Type",
                    "answer_unit"			=>"Answer Unit",
                    "component_sn"			=>"Component (S/N question)",
                    "match_list"			=>"Match List",
                    "answer_max"			=>"Answer Max",
                    "answer_min"			=>"Answer Min",
                    "gt1"					=>"GT1",
                    "gt3"					=>"GT3",
                    "active"				=>"Active",
            ]
        );
        switch($m[3]){
            case 'fullCSV':
                $data = $_SESSION['data_report'];
                $report = new tldCSV(
                        $data,
                        array(
                                "xItems"=>$xItemsCSV,
                                "showTitles"=>true
                        )
                );
                $report->out("pi_report.csv");
                exit;
                break;
            case 'xls':
                $data = $_SESSION['data_report'];
                $report = new tldXLS(
                        $data,
                        array(
                                "xItems"=>$xItemsCSV,
                                "showTitles"=>true
                        )
                );
                $report->out("pi_report.xls");
                exit;
                break;
        }
        $families=tldPI::getPiFamilies();
        foreach ($families as $key1 => $value1) {
            $family[]=$families[$key1]['family'];
        }
        //Get form
        $form = new HTML_QuickForm('frmHoursAmount', 'post');
        $form->addElement(	'hidden', 	'm[0]', 			'pi');
        $form->addElement(	'hidden', 	'm[1]', 			'reports');
        $form->addElement(	'hidden', 	'm[2]', 			'piByFilter');
        $form->addElement(	'header', 	'title', 			'Select filters');
        $form->addElement(	'text', 	'ownerTmp',			'Owner', array('disabled'=>'disabled'));
        $form->addElement(	'text', 	't_opno', 			'Operation');
        $form->addElement(	'text', 	't_item', 			'P/N');
        $form->addElement(	'select', 	'model', 			'Family',				array_combine($family, $family));
        $ams =& $form->addElement(
            'advmultiselect', 'factories', null,
            tldPI::getFactoriesList(),
            ['size' => 11, 'class' => 'pool', 'style' => 'width:200px;']
        );
        $ams->setLabel(['Select factories:']);
        $ams->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand']);
        $ams->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);
        $form->addElement(	'text', 	'subject_en', 		'Subject (EN)');
        $form->addElement(	'text', 	'sn', 		'S/N');
        $form->addElement(  'submit', 	'btnSubmit', 		'Submit');
        $form->addRule('location', 'Required', 'required');
        $form->addRule('hours_per_day', 'Required', 'required');
        if($user->isInGroup(array("pi_QCQ"))) {
            $form->setDefaults(array("ownerTmp"=>"QCQ"));
            $owner='QCQ';
        } else {
            if($user->isInGroup(array("pi_ECQ"))) {
                $form->setDefaults(array("ownerTmp"=>"ECQ"));
                $owner='ECQ';
            } else {
                if($user->isInGroup(array("pi_PCQ"))) {
                    $form->setDefaults(array("ownerTmp"=>"PCQ"));
                    $owner='PCQ';
                } else {
                    $form->setDefaults(array("ownerTmp"=>""));
                    $owner='';
                }
            }
        }
        $body = $form->toHTML();
        $vars = tldUtils::cleanupFormInput($form->exportValues());
        // Security checks
        if(empty($vars['t_opno']) && empty($vars['t_item']) && empty($vars['model'])){
            $DEFAULT_ERROR[] = "ERROR: This report requires at least one of these filters to be filled: Operation, P/N, Model";
            break;
        }
        $acl_form_fields = ["t_opno","t_item","model","subject_en", "owner", "sn", 'factories'];
        $a = [];
        foreach($vars as $key=>$raw){
            if(!in_array($key,$acl_form_fields) || empty($raw) || in_array($raw,["%"])){
                continue;
            }
            if (is_array($raw)) {
                $a[$key] = $raw;
                continue;
            }
            $a[$key]=trim($raw);
        }
        //if the request comes from the cbom page, we want a filter by Owner
        if(isset($_POST['owner'])){
            $a['owner'] = $_POST['owner'];
        }
        if(empty($a)){
            $DEFAULT_ERROR[]="ERROR: Not enough constraints to run a safe search...";
            break;
        }

        $location = new tldLocation($user->getBUID());
        $erp = $location->getERP();
        //ROAD Free departure/arrival field
        $TITLE = "Search Results";
        $body = $form->toHTML();
        $result = tldPI::byFilters($a, $erp);
        $rows = $result['questions'];

//        $descriptions = $result['descriptions'];
        if(isset($_SESSION['data_report']))
            unset($_SESSION['data_report']);
        $_SESSION['data_report']=$rows;
        if($rows){
            foreach ($rows as $key1 => $value1) {
//                foreach ($descriptions as $description) {
//                    if ($value1['t_opno'] === (string) $description['t_opno']) {
//                        $rows[$key1]['t_opno'] .= " {$description['t_dsca']}";
//                    }
//                }
                $rows[$key1]['t_item'] = str_replace(',',', ',$value1['t_item']);
               $dupliDelete='NO';
               $edit='NO';
               if($owner=='QCQ' && $rows[$key1]['owner']=='QCQ') {  $dupliDelete='YES'; $edit='YES'; }
               if($owner=='QCQ' && $rows[$key1]['owner']=='PCQ') {  $dupliDelete='YES'; $edit='YES'; }
               if($owner=='QCQ' && $rows[$key1]['owner']=='ECQ') {  $edit='YES'; }
               if($owner=='PCQ' && $rows[$key1]['owner']=='QCQ') {  $edit='YES'; }
               if($owner=='PCQ' && $rows[$key1]['owner']=='PCQ') {  $dupliDelete='YES'; $edit='YES'; }
               if($owner=='PCQ' && $rows[$key1]['owner']=='ECQ') {  $edit='YES'; }
               if($owner=='ECQ' && $rows[$key1]['owner']=='ECQ') {  $dupliDelete='YES'; $edit='YES'; }

                if($dupliDelete=='YES') {
                    $rows[$key1]['duplicate']='<a href="/en/private/manufacturing/index.php?m[0]=pi&m[1]=add&copyid='.$rows[$key1]['id'].'">       <img src="/shared/icons/application/copy.png"   alt="Duplicate" /> </a>';
                    $rows[$key1]['delete']   ='<a href="/en/private/manufacturing/index.php?m[0]=pi&m[1]=delete&copyid='.$rows[$key1]['id'].'">    <img src="/shared/icons/application/delete.png" alt="Delete" />    </a>';
                }
                if($edit=='YES') {
                    $rows[$key1]['edit']     ='<a href="/en/private/manufacturing/index.php?m[0]=pi&m[1]=view&m[2]=edit&id='.$rows[$key1]['id'].'"><img src="/shared/icons/miscellaneous/edit.png" alt="Edit" />      </a>';
                }

                $rows[$key1]['id']='<a href="/en/private/manufacturing/index.php?m[0]=pi&m[1]=reports&m[2]=piQuestionsLogs&id='.$rows[$key1]['id'].'">'.$rows[$key1]['id'].'</a>';
            }

            $report = new tldReportColumnar(
                $rows,
                array(
                        "xItems"=>$xItems,
                        "title"=>"P&I Question by Filters",
                        'stickyHeader' => true
                )
            );
            $DEFAULT_MENU .=<<<EOF
        <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <a href="$php_self?m[0]=pi&m[1]=reports&m[2]=piByFilter&m[3]=xls">Download XLS</a>&nbsp;|&nbsp;
        <a href="$php_self?m[0]=pi&m[1]=reports&m[2]=piByFilter&m[3]=fullCSV">Download CSV</a>
EOF;
            $body .= $report->fetch();
        }else{
            $body = $form->toHTML();
            $body .= "<br>No records...";
        }
    break;

    case 'piERFamilies':
        $xItems = array(
            "comp"=>"Company",
            "unit"=>"ER",
            "family"=>"Family",
            "delete"=>"Delete"
        );

        // Get listing
        $erpList = tldLocation::getERPList("smartyOptions");
        $form = new HTML_QuickForm('frmpiERFamilies', 'get');
        $form->addElement(	'hidden', 'm[0]', 'pi');
        $form->addElement(	'hidden', 'm[1]', 'reports');
        $form->addElement(	'hidden', 'm[2]', 'piERFamilies');
        $form->addElement(	'header', 'title', 'Select ID:');
        $form->addElement(	'select', 'y', 'Company from',	array(""=>"")+$erpList);
        $form->addElement(	'select', 'z', 'Company to',	array(""=>"")+$erpList);
        $form->addElement(	'text', 	'unit',      		'ER');
        $form->addElement(	'submit', 'btnSubmit', 'Submit');
        $form->addRule('ID', 'This is required', 'required');
        $form->setDefaults(array("y"=>array_shift(array_keys($erpList))));
        $form->setDefaults(array("z"=>array_pop(array_keys($erpList))));
        $body = $form->toHTML();
        $vars = tldUtils::cleanupFormInput($form->exportValues());
        $vars['unit'] = trim(strtoupper($vars['unit']));
        // Get result
        $query="select unit, family, ";
        $query.="CASE WHEN EXISTS(SELECT erp FROM locations where factory='Y' and location=(select man_location from service where sn=pi_unit_family.unit)) THEN (SELECT erp FROM locations where factory='Y' and location=(select man_location from service where sn=pi_unit_family.unit)) ELSE '999' END as comp ";
        $query.="from pi_unit_family where 1=1 ";
        if($vars['unit']!='') { $query.=" and unit='".$vars['unit']."' "; }
        $query.="order by unit asc";

        $rowsTmp1 = tldUtils::getSqlToAssocArray($query);
        foreach ($rowsTmp1 as $key1 => $value1) {
            if($rowsTmp1[$key1]['comp']<$vars['y'] || $rowsTmp1[$key1]['comp']>$vars['z']) { unset($rowsTmp1[$key1]); }
        }

        foreach ($rowsTmp1 as $key1 => $value1) {
            $rowsTmp1[$key1]['delete']="<a href='/en/private/manufacturing/index.php?m[0]=pi&m[1]=reports&m[2]=piDeleteERFamily&m[3]=".$rowsTmp1[$key1]['unit']."&m[4]=".$rowsTmp1[$key1]['family']."'>Delete</a>";
        }

        $caption = "<a href='/en/private/manufacturing/index.php?m[0]=pi&m[1]=reports&m[2]=piNewERFamilies'>Add new P&I ER in list</a><br><br>P&I ER Families";
        $report = new tldReportColumnar($rowsTmp1, array("xItems"=>$xItems, "title"=>$caption, 'stickyHeader'=>true));
        $body .= $report->fetch();

    break;

    case 'piDeleteERFamily':
        if (isset($m[3])) { $_SESSION['unit']=$m[3]; } else { $m[3]=$_SESSION['unit']; }
        if (isset($m[4])) { $_SESSION['family']=$m[4]; } else { $m[4]=$_SESSION['family']; }

        $xItems = array(
            "unit"=>"ER",
            "family"=>"Family"
        );
        $unitList[]='';
        $unitList[]=$m[3];

        // Get listing
        $erpList = tldLocation::getERPList("smartyOptions");
        $form = new HTML_QuickForm('frmpiDeleteERFamily', 'get');
        $form->addElement(	'hidden', 'm[0]', 'pi');
        $form->addElement(	'hidden', 'm[1]', 'reports');
        $form->addElement(	'hidden', 'm[2]', 'piDeleteERFamily');
        $form->addElement(	'header', 'title', 'Delete Unit from family list:');
        $form->addElement(	'select', 'unit', 'Unit',  array_combine($unitList, $unitList));
        $form->addElement(	'submit', 'btnSubmit', 'Submit');
        $form->setDefaults(array("unit"=>""));
        $body = $form->toHTML();
        $vars = tldUtils::cleanupFormInput($form->exportValues());
        if($form->validate() && $unit != ''){
            $query="select count(*) as nb from pi_unit_family where unit='".$m[3]."'";
            $rowsTmp1 = tldUtils::getSqlToAssocArray($query);
            if ($rowsTmp1[0]['nb']==0) {
                $caption = "ER already deleted";
            } else {
                $query="delete from pi_unit_family where unit='".$m[3]."'";
                $rowsTmp = tldUtils::sqlExecute($query);
                $caption ="ER deleted";
            }
            $rows[0]['unit'] = $m[3];
            $rows[0]['family'] = $m[4];
            $report = new tldReportColumnar($rows, array("xItems"=>$xItems, "title"=>$caption));
            $body .= $report->fetch();
        }

    break;

    case 'piNewERFamilies':
        $xItems = array(
            "unit"=>"ER",
            "family"=>"Family"
        );

        $families=tldPI::getPiFamilies(['status' => 'Final']);
        $familyList[]='';
        foreach ($families as $key1 => $value1) {
            $familyList[]=$families[$key1]['family'];
        }

        $userBusinessUnit = $user->getBUName();
        $query=<<<SQL
SELECT DISTINCT sn FROM service 
WHERE (sn LIKE 'T%' 
OR sn LIKE 'P%')
AND sn NOT IN (SELECT unit FROM pi_unit_family)
AND man_location='{$userBusinessUnit}';
SQL;

        $rowsTmp1 = tldUtils::getSqlToAssocArray($query);
        if (empty($rowsTmp1)) {
            $DEFAULT_ERROR[] = sprintf('No ER was found for your factory (%s)', $userBusinessUnit);
            break;
        }
        $unitList[]='';
        foreach ($rowsTmp1 as $key1 => $value1) {
            $unitList[]=$rowsTmp1[$key1]['sn'];
        }

        // Get listing
        $erpList = tldLocation::getERPList("smartyOptions");
        $form = new HTML_QuickForm('frmpiNewERFamilies', 'get');
        $form->addElement(	'hidden', 'm[0]', 'pi');
        $form->addElement(	'hidden', 'm[1]', 'reports');
        $form->addElement(	'hidden', 'm[2]', 'piNewERFamilies');
        $form->addElement(	'header', 'title', 'Select Unit & Family to add:');
        $form->addElement(	'select', 	'unit',      		'ER',     array_combine($unitList, $unitList));
        $form->addElement(	'select', 	'family',      		'Family', array_combine($familyList, $familyList));
        $form->addElement(	'submit', 'btnSubmit', 'Submit');
        $form->addRule('unit', 'This is required', 'required');
        $form->addRule('family', 'This is required', 'required');
        $body = $form->toHTML();
        $vars = tldUtils::cleanupFormInput($form->exportValues());
        if($form->validate()){
            $query="SELECT erp FROM locations where factory='Y' and location=(select man_location from service where sn='".$unit."')";
            $erps = tldUtils::getSqlToAssocArray($query);
            $query="SELECT t_prno, t_pdno  FROM service where sn='".$unit."'";
            $projectNumber = tldUtils::getSqlToAssocArray($query)[0]['t_prno'];
            $productionOrder = tldUtils::getSqlToAssocArray($query)[0]['t_pdno'];
            $ok = true;

            if(!empty($erps[0]['erp'])) {
                $erp = $erps[0]['erp'];

                if ('' === $productionOrder) {
                    try {
                        $response = $client->get(sprintf('ion/projects/site=%d;project=%s', $erp, $projectNumber));
                    } catch (\Exception $exception) {
                        $DEFAULT_ERROR[] = 'ERROR: API request error. Please open a TTS or contact your supervisor.';
                        break;
                    }
                    if (null === $item = ($response['productionOrders'][0] ?? null)) {
                        $DEFAULT_ERROR[] = sprintf('ERROR: Production Order not found on project %s on LN.', $projectNumber);
                        break;
                    }
                    if (null === ($productionOrder = ($item['productionOrderIdentifier'] ?? null))) {
                        $DEFAULT_ERROR[] = sprintf('ERROR: Production Order not found on project %s on LN.', $projectNumber);
                        break;
                    }
                }

                try {
                    $project = $client->get(sprintf('ion/projects/site=%d;project=%s', $erp, $projectNumber), [
                        'query' => [
                            'productionOrder' => $productionOrder,
                            'languageID' => 'en',
                        ],
                    ]);
                } catch (ClientException $exception) {
                    $DEFAULT_ERROR[] = sprintf('ERROR: Project %s not found on LN with Production Order %s.', $projectNumber, $productionOrder);
                    break;
                } catch (ServerException $exception) {
                    $DEFAULT_ERROR[] = 'ERROR: API request error. Please open a TTS or contact your supervisor.';
                    break;
                }

                if ([] === $project['productionOrders']) {
                    $DEFAULT_ERROR[] = sprintf('ERROR: Production order not found for Project %s.', $projectNumber);
                    break;
                }

                $operations = $project['productionOrders'][0]['operations'];

                $caption = "Some operation(s) in ".$unit." routing does not have any Active Question:<br><br><font color=black size=2>";
                foreach ($operations as $key => $operation) {
                    $query="select count(*) as nb from pi_questions where  active='Y' and model='".$family."' and t_opno='".$operation['operationIdentifier']."'";
                    $numberOfActiveQuestions = tldUtils::getSqlToAssocArray($query);
                    if ($numberOfActiveQuestions[0]['nb'] === '0') {
                        $ok = false;
                        $caption.='Operation '.$operation['operationIdentifier'].'<br>';
                    }
                }
                $caption.='</font>';
            }
            if (!$ok) {
                $rows[0]['unit'] = $unit;
                $rows[0]['family'] = $family;
            } else {
                $query="select count(*) as nb from pi_unit_family where unit='".$unit."' and family='".$family."'";
                $rowsTmp1 = tldUtils::getSqlToAssocArray($query);
                if ($rowsTmp1[0]['nb']==0) {
                    $query="insert into pi_unit_family (id, unit, family) values (null, ";
                    $query.="'".$unit."', ";
                    $query.="'".$family."')";
                    $rowsTmp = tldUtils::sqlInsert($query);
                    $rows[0]['unit'] = $unit;
                    $rows[0]['family'] = $family;
                    $caption = "New P&I ER added in list";
                } else {
                    $rows[0]['unit'] = $unit;
                    $rows[0]['family'] = $family;
                    $caption = "ER already exists in list";
                }
        }
            $report = new tldReportColumnar($rows, array("xItems"=>$xItems, "title"=>$caption));
            $body .= $report->fetch();
        }

    break;

    case 'piERList':
        $xItems = array(
        "comp"=>"Company",
        "unit"=>"ER",
        "family"=>"Family",
        "model"=>"Model",
        "t_cprj"=>"Project",
        "t_pdno"=>"Production Order",
//        "t_osta"=>"Order Status",
        "t_first"=>"First Answer Date",
        "t_last"=>"Last Answer Date",
        "t_k_first"=>"First Time-keeping Date",
        "t_k_last"=>"Last Time-keeping Date"
            );

        // Get listing
        $erpList = tldLocation::getERPList("smartyOptions");
        $ERStatus = array("Open", "Closed", "All");
        $families=tldPI::getPiFamilies();
        foreach ($families as $key1 => $value1) {
            $family[]=$families[$key1]['family'];
        }

        $form = new HTML_QuickForm('frmpiERList', 'get');
        $form->addElement(	'hidden', 'm[0]', 'pi');
        $form->addElement(	'hidden', 'm[1]', 'reports');
        $form->addElement(	'hidden', 'm[2]', 'piERList');
        $form->addElement(	'header', 'title', 'Select ID:');
        $form->addElement(	'select', 'selectedErp', 'Company',	array("All"=>"All")+$erpList);
        $form->addElement(	'select', 'selectedFamily', 'Family', array(" "=>" ")+array("All"=>"All")+array_combine($family, $family));
//        $form->addElement(	'select', 'selectedERStatus', 'ER Status', array_combine($ERStatus, $ERStatus));
        $form->addElement(	'submit', 'btnSubmit', 'Submit');
        $form->addRule('ID', 'This is required', 'required');
        $location = new tldLocation($user->getBUID());
        $form->setDefaults(array("erp"=>$location->getERP()));
        $form->setDefaults(array("selectedFamily"=>" "));

        $body = $form->toHTML();
        $vars = tldUtils::cleanupFormInput($form->exportValues());
//        if(!isset($vars['selectedERStatus'])) { $vars['selectedERStatus']='Open'; }
        if(!isset($vars['selectedFamily'])) { $vars['selectedFamily']='All'; }
        if(!isset($vars['selectedErp'])) { $vars['selectedErp']='All'; }

        $query = <<<SQL
        SELECT distinct pi_unit_1.comp, pi_unit_1.unit, pi_unit_1.t_cprj, pi_unit_1.t_pdno,
        f.family as family,
        (select model from service where sn=pi_unit_1.unit LIMIT 1) as model,
        (select min(created_on) from pi_answers where parent_id in(select pi_unit_2.id from pi_questions_unit as pi_unit_2 where pi_unit_1.comp=pi_unit_2.comp and pi_unit_1.unit=pi_unit_2.unit)) as t_first,
        (select max(created_on) from pi_answers where parent_id in(select pi_unit_2.id from pi_questions_unit as pi_unit_2 where pi_unit_1.comp=pi_unit_2.comp and pi_unit_1.unit=pi_unit_2.unit)) as t_last,
        (select min(pi_time_2.created_on) from pi_timekeeping_log as pi_time_2 where pi_time_2.comp=pi_unit_1.comp and pi_time_2.t_pdno=pi_unit_1.t_pdno) as t_k_first,
        (select max(pi_time_2.created_on) from pi_timekeeping_log as pi_time_2 where pi_time_2.comp=pi_unit_1.comp and pi_time_2.t_pdno=pi_unit_1.t_pdno) as t_k_last
        from pi_questions_unit as pi_unit_1
        LEFT JOIN pi_unit_family as f on f.unit = pi_unit_1.unit
SQL;
        $query.=" where 1=1 ";
        if($vars['selectedErp'] != 'All') { $query.=" and pi_unit_1.comp='".$vars['selectedErp']."' "; }
        if($vars['selectedFamily'] != 'All') { $query.=" and f.family='".$vars['selectedFamily']."' "; }
        $query.=" order by pi_unit_1.unit asc";
        $ERList = tldUtils::getSqlToAssocArray($query);

        // the following section is calling baan to filter the results of ER based on "ER Status" selected
       //  we can't call LN for every ER result to check the corresponding workorder status, so for now, the filter "ER Status" is not working anymore
        // we need answers from erp team to understand what are tables ttisfc001 and ttdltc001 and what they were used for.
       // this filter is wrongly named, it's not the ER status we are checking, but the project/workorder
        /**
        foreach ($ERList as $key1 => $value1) {
            if($ERList[$key1]['t_pdno'] != '') {
                //  get workorder, ttisfc001
                $query="select t_osta from ttisfc001".$ERList[$key1]['comp']." where t_pdno=".$ERList[$key1]['t_pdno'];
                $rowsTmp3 = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);

                switch($rowsTmp3[0]['t_osta']){
                    case '1': $ERList[$key1]['t_osta']='Free'; Break;
                    case '2': $ERList[$key1]['t_osta']='Planned'; Break;
                    case '3': $ERList[$key1]['t_osta']='Documents Printed'; Break;
                    case '4': $ERList[$key1]['t_osta']='Released (P&I ok)'; Break;
                    case '5': $ERList[$key1]['t_osta']='Active (P&I ok)'; Break;
                    case '6': $ERList[$key1]['t_osta']='Completed'; Break;
                    case '7': $ERList[$key1]['t_osta']='Closed'; Break;
                    case '8': $ERList[$key1]['t_osta']='Archived'; Break;
                    case '9': $ERList[$key1]['t_osta']='Cancelled'; Break;
                }
            }
            if($ERList[$key1]['t_cprj'] == '') {
                //  get workorder, ttisfc001
                $query="select t_cprj from ttisfc001".$ERList[$key1]['comp']." where t_pdno=".$ERList[$key1]['t_pdno'];
                $rowsTmp3 = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
                //$ERList[$key1]['t_cprj']=$rowsTmp3[0]['t_cprj'];

                // get project, ttdltc001
                $query="select t_clot from ttdltc001".$ERList[$key1]['comp']." where t_cprj=".$ERList[$key1]['t_cprj'];
                $rowsTmp4 = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
                //$ERList[$key1]['unit']=$rowsTmp4[0]['t_clot'];
            }
            if($vars['selectedERStatus']=="Open" && ($ERList[$key1]['t_osta']=="Closed" || $ERList[$key1]['t_osta']=="Archived" || $ERList[$key1]['t_osta']=="Completed")) { unset($ERList[$key1]); }
            if($vars['selectedERStatus']=="Closed" && ($ERList[$key1]['t_osta']!="Closed" && $ERList[$key1]['t_osta']!="Archived" && $ERList[$key1]['t_osta']!="Completed")) { unset($ERList[$key1]); }
        }
        */

        $caption = "P&I ER List";
        $report = new tldReportColumnar($ERList, array("xItems"=>$xItems, "title"=>$caption, 'stickyHeader'=>true));
        $body .= $report->fetch();

        break;

    case 'piUsersList':
            $xItems = [
            "id"=>"account id",
            "company"=>"Company",
            "lastname"=>"Last name",
            "firstname"=>"First name",
            "dpt"=>"Department",
            "fct"=>"Function",
            "title"=>"Title",
            "baan_employee_id"=>"Baan ID",
            "supervisor_lastname"=>"supervisor Last name",
            "supervisor_firstname"=>"Supervisor First name",
            "pi_operator"=>"pi_OPERATOR",
            "pi_gl"=>"pi_GL",
            "pi_pm"=>"pi_PM",
            "pi_tester"=>"pi_TESTER",
            "pi_qam"=>"pi_QAM",
            "pi_der"=>"pi_DER",
            "pi_qcq"=>"pi_QCQ",
            "pi_ecq"=>"pi_ECQ",
            "pi_pcq"=>"pi_PCQ",
            "mpe"=>"MPE",
            ];

            // Get PI roles
            $query="select distinct group_name, description from people_groups_select where group_name like 'pi_%' order by group_name";
            $rowsGroups = tldUtils::getSqlToAssocArray($query);

            foreach ($rowsGroups as $row) {
                $rowsG[$row['group_name']] = $row['group_name'].' ('.$row['description'].')';
                //add the columns pi_PILOT to the reports
                if (strstr($row['group_name'],"pi_PILOT")){
                    $xItems[strtolower($row['group_name'])] = $row['group_name'];
                }

            }
            $rowsG['MPE']='MPE (METHOD AND PROCESS ENGINEER)';

            // Get listing
            $erpList = tldLocation::getERPList("smartyOptions");
            $form = new HTML_QuickForm('frmpiUsersList', 'get');
            $form->addElement(	'hidden', 'm[0]', 'pi');
            $form->addElement(	'hidden', 'm[1]', 'reports');
            $form->addElement(	'hidden', 'm[2]', 'piUsersList');
            $form->addElement(	'select', 'r', 'Role',	array("ALL"=>"ALL")+$rowsG);
            $form->addElement(	'select', 'z', 'Company',	array("ALL"=>"ALL")+$erpList);
            $form->addElement(	'submit', 'btnSubmit', 'Submit');

            $body = $form->toHTML();
            $vars = tldUtils::cleanupFormInput($form->exportValues());


        // Get result
        $query =<<<SQL
SELECT
    p.id,
    p.lastname,
    p.firstname,
    p.title,
    p.baan_employee_id,
    l.location AS company,
    d.dpt AS dpt,
    f.dsc AS fct,
    f.code,
    s.lastname AS supervisor_lastname,
    s.firstname AS supervisor_firstname,
    group_name
FROM people_groups
LEFT JOIN people AS p ON p.id = people_groups.parent_id
LEFT JOIN locations AS l ON l.id = p.bu_id
LEFT JOIN tld_departments AS d ON d.id = p.dpt_id
LEFT JOIN tld_functions AS f ON f.id = p.fct_id
LEFT JOIN people AS s ON s.id = p.reports_to
WHERE group_name LIKE 'pi_%' AND p.hidden = 0
SQL;


        if ($vars['z']!=='ALL') {
            $query.=" AND  l.erp='{$vars['z']}'";
        }
        if ($vars['r'] === 'MPE') {
            $query .= " AND f.code='MPE' ";
        } elseif ($vars['r'] !== 'ALL') {
            $query.=" AND p.id IN ( SELECT parent_id FROM people_groups WHERE group_name='{$vars['r']}')";
        }
        $query.=" ORDER BY p.title ASC;";
        $acls = tldUtils::getSqlToAssocArray($query);

        $people = [];
        foreach ($acls as $acl) {
            $matches = false;
            foreach ($people as &$person) {
                //if already exits in the tab $people
                if ($person['id'] === $acl['id']) {
                    //add the group name as a key
                    $groupName = strtolower($acl['group_name']);
                    $person[$groupName] = 'Y';
                    $matches = true;
                    break;
                }
            }
            if (!$matches) {
                //add the group name as a key
                $groupName = strtolower($acl['group_name']);
                $acl[$groupName] = 'Y';
                unset($acl['group_name']);
                //
                if ($acl['code'] === "MPE") {
                    $acl['mpe'] = "Y";
                }

                $people[] = $acl;
            }

        }
        $caption = "P&I Users List";
        $report = new tldReportColumnar($people, array("xItems"=>$xItems, "title"=>$caption, 'stickyHeader'=>true));
        $body .= $report->fetch();

        break;

    case 'piAnsHisto':
        $xItems = array(
                "t_opno"=>"Operation",
                "op_desc"=>"Description",
                "t_item"=>"Item",
                "it_desc"=>"Description",
                "position"=>"Position",
                "subject_fr"=>"Subject",
                "desc_fr"=>"Description",
                "answer"=>"Answer",
                "answer_unit"=>"Unit",
                "answer_date"=>"date",
                "answer_user"=>"User ID",
                "answer_name"=>"Name",
                "derogation"=>"Derogation",
                "derogation_date"=>"Date",
                "derogation_user"=>"User ID",
                "derogation_name"=>"Name",
                "CRAB"=>"CRAB",
                "CRAB_date"=>"Date",
                "CRAB_user"=>"User ID",
                "CRAB_name"=>"Name"
        );

        // Get listing
        $erpList = tldLocation::getERPList("smartyOptions");
        $form = new HTML_QuickForm('frmpiAnsHisto', 'get');
        $form->addElement(	'hidden', 'm[0]', 'pi');
        $form->addElement(	'hidden', 'm[1]', 'reports');
        $form->addElement(	'hidden', 'm[2]', 'piAnsHisto');
        $form->addElement(	'header', 'title', 'Select ID:');
        $form->addElement(	'text',	'ID', 'ID');
        $form->addElement(	'submit', 'btnSubmit', 'Submit');
        $form->addRule('ID', 'This is required', 'required');
        $form->setDefaults(array("ID"=>$id));
        $body = $form->toHTML();
        $vars = tldUtils::cleanupFormInput($form->exportValues());

        // Get questions
        $query="select * from pi_questions_unit, pi_answers where pi_questions_unit.id='".$vars['ID']."' and pi_questions_unit.id=pi_answers.parent_id order by t_opno, position";
        $questions = tldUtils::getSqlToAssocArray($query);


        foreach ($questions as $key1 => $question) {

            // Add answers data
            $questions[$key1]['answer_date'] = $question['created_on'];
            $questions[$key1]['answer_user'] = $question['entered_by'];
            $questions[$key1]['derogation'] = $question['derogation'];
            $questions[$key1]['derogation_date'] = $question['d_created_on'];
            $questions[$key1]['derogation_user'] = $question['d_entered_by'];

            if(empty($question['comp']) || empty($question['t_cprj']) || empty($question['t_opno'])) {
                continue;
            }

            // Get item description
//            $query="select t_dsca from ttiitm001".$questions[$key1]['comp']." where t_item='".$questions[$key1]['t_item']."'";
//            $rowsTmp2 = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
//            $questions[$key1]['it_desc'] = $rowsTmp2[0]['t_dsca'];
            if (!empty($question['t_item'])){
                try {
                    $item = $client->get(sprintf('/ion/bill_of_material_items/site=%d;project=;product=%s', $question['comp'], $question['t_item']),
                        [
                            'query' => [
                                'date' => (new \DateTime())->format(\DateTimeInterface::ATOM),
                                'depth' => 0,
                            ]
                        ]
                    );
                    $questions[$key1]['it_desc'] = $item['itemDescription'];
                } catch (ClientException $exception) {
                    $DEFAULT_ERROR[] = _("ERROR: BOM not found for this item.");
                    break;
                }
            }

            // Get operation description
            if (!empty($question['t_pdno'])) {
//                $query="select ROU003.t_dsca t_dsca from ttirou003".$questions[$key1]['comp']." ROU003 where ROU003.t_tano= ";
//                $query.="(select PCS023.t_tano from ttipcs023".$questions[$key1]['comp']." PCS023 where PCS023.t_cprj=".$questions[$key1]['t_cprj']." and PCS023.t_opno=".$questions[$key1]['t_opno'].")";
//                $rowsTmp3 = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
//                $questions[$key1]['op_desc']=$rowsTmp3[0]['t_dsca'];

                try {
                    $project = $client->get(sprintf('ion/projects/site=%d;project=%s', $question['comp'], $question['t_cprj']), [
                        'query' => [
                            'productionOrder' => $question['t_pdno'],
                            'languageID' => 'en',
                        ],
                    ]);
                } catch (ClientException $exception) {
                    $DEFAULT_ERROR[] = _("ERROR: Project not found for this ER.");
                } catch (ServerException $exception) {
                    $DEFAULT_ERROR[] = _("ERROR: API request error.");
                }

                if ([] === $project['productionOrders']) {
                    $DEFAULT_ERROR[] = _("ERROR: Production order not found for this ER.");
                }
                $operations = $project['productionOrders'][0]['operations'];
                foreach ($operations as $key => $operation){
                    if ($operation['operationIdentifier'] !== $question['t_opno']){
                        continue;
                    }
                    $questions[$key1]['op_desc'] = $operation['referenceDescription'];
                    break;
                }
            }

            // Get CRABS
            $query="select * from pi_crab_eap where question_id=".$vars['ID'];
            $crabs = tldUtils::getSqlToAssocArray($query);
            $questions[$key1]['CRAB'] = $crabs[0]['parent_id'];
            $questions[$key1]['CRAB_date'] = $crabs[0]['date'];
            $questions[$key1]['CRAB_user'] = $crabs[0]['user_id'];

            // Get answer user name
            if($question['answer_user'] != '') {
                $query="select firstname, lastname from people where id=".$question['answer_user'];
                $answerUser = tldUtils::getSqlToAssocArray($query);
                $questions[$key1]['answer_name'] = $answerUser[0]['firstname']." ".$answerUser[0]['lastname'];
            }

            // Get derogation user name
            if($question['derogation_user'] != '') {
                $query="select firstname, lastname from people where id=".$question['derogation_user'];
                $derogationUser = tldUtils::getSqlToAssocArray($query);
                $questions[$key1]['derogation_name'] = $derogationUser[0]['firstname']." ".$derogationUser[0]['lastname'];
            }

            // Get CRAB user name
            if($question['CRAB_user'] != '') {
                $query="select firstname, lastname from people where id=".$question['CRAB_user'];
                $crabUser = tldUtils::getSqlToAssocArray($query);
                $questions[$key1]['CRAB_name'] = $crabUser[0]['firstname']." ".$crabUser[0]['lastname'];
            }

            // Manage serial number answers
            if ($question['answer_type'] === 'S/N') {
                $answer ='<table width=100%>';
                $answer.='<tr width=100%><td width=100%><b>Component:&nbsp;'.$question['answerComponent'].'</b></td></tr>';
                $answer.='<tr width=100%><td width=100%>Model:&nbsp;'.$question['answerModel'].'</td></tr>';
                $answer.='<tr width=100%><td width=100%>Serial:&nbsp;'.$question['answerSerial'].'</td></tr>';
                $answer.='<tr width=100%><td width=100%>Brand:&nbsp;'.$question['answerBrand'].'</td></tr>';
                $answer.='</table>';
                $questions[$key1]['answer'] = $answer;
            }
        }

        $caption = "ER {$vars['unit']} Inspection Revue";
        $report = new tldReportColumnar($questions, array("xItems"=>$xItems, "title"=>$caption));
        $body .= $report->fetch();
    break;

    case 'piAnsByFilter':

        // Get listing
        $alertsStatus = array("All", "Yes", "No");
        $crabsStatus = array("All", "Yes", "No");
        $snStatus = array("No", "Yes");
        $language = array("En", "Fr", "Zh");
        $output = array("Display", "Excel", "Pdf");
        $erpList = tldLocation::getERPList("smartyOptions");
        $form = new HTML_QuickForm('frmpiAnsByFilter', 'get');
        $form->addElement(	'hidden', 'm[0]', 'pi');
        $form->addElement(	'hidden', 'm[1]', 'reports');
        $form->addElement(	'hidden', 'm[2]', 'piAnsByFilter');
        $form->addElement(	'header', 'title', 'Select Filters:');
        $form->addElement(	'select', 'selectedErp', 'Company#', array(""=>"")+$erpList);
        $form->addElement(	'text',	'unit', 'ER');
        $form->addElement(	'select', 'selectedAlertStatus', 'Alerts Status', array_combine($alertsStatus, $alertsStatus));
        $form->addElement(	'select', 'selectedCrabStatus', 'CRABS Status', array_combine($crabsStatus, $crabsStatus));
        $form->addElement(	'select', 'selectedSerialNumbersOnly', 'Serial Numbers Only', array_combine($snStatus, $snStatus));
        $form->addElement(	'select', 'selectedOutput', 'Output', array_combine($output, $output));
        $form->addElement(	'select', 'selectedLanguage', 'Language', array_combine($language, $language));
        $form->addElement(	'submit', 'btnSubmit', 'Submit');
        $form->addRule('selectedErp', 'This is required', 'required');
        $form->addRule('er', 'This is required', 'required');
        $body = $form->toHTML();

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        $unit = trim(strtoupper($vars['unit']));
        if($vars['selectedLanguage'] === 'En') {
            $xItems = array(
                "t_opno"=>"Operation",
                "op_desc"=>"Description",
                "t_item"=>"Item",
                "it_desc"=>"Description",
                "position"=>"Position",
                "subject"=>"Subject",
                "desc"=>"Description",
                "answer"=>"Active Answer",
                "answer_unit"=>"Unit",
                "answer_min"=>"Min",
                "answer_max"=>"Max",
                "answer_date"=>"date",
                "answer_user"=>"User ID",
                "answer_name"=>"Name",
                "derogation"=>"Derogation",
                "derogation_date"=>"Date",
                "derogation_user"=>"User ID",
                "derogation_name"=>"Name",
                "CRAB"=>"CRAB",
                "CRAB_date"=>"Date",
                "CRAB_user"=>"User ID",
                "CRAB_name"=>"Name",
                "alert"=>"Alert message",
                "answer_count"=>"Answers count",
            );
        }
        if($vars['selectedLanguage'] === 'Fr') {
            $xItems = array(
                "t_opno"=>htmlentities("Opération", ENT_QUOTES, "UTF-8"),
                "op_desc"=>"Description",
                "t_item"=>"Article",
                "it_desc"=>"Description",
                "position"=>"Position",
                "subject"=>"Sujet",
                "desc"=>"Description",
                "answer"=>htmlentities("Réponse", ENT_QUOTES, "UTF-8"),
                "answer_unit"=>htmlentities("Unité", ENT_QUOTES, "UTF-8"),
                "answer_min"=>"Min",
                "answer_max"=>"Max",
                "answer_date"=>"date",
                "answer_user"=>"User ID",
                "answer_name"=>"Nom",
                "derogation"=>htmlentities("Dérogation", ENT_QUOTES, "UTF-8"),
                "derogation_date"=>"Date",
                "derogation_user"=>"User ID",
                "derogation_name"=>"Nom",
                "CRAB"=>"CRAB",
                "CRAB_date"=>"Date",
                "CRAB_user"=>"User ID",
                "CRAB_name"=>"Nom",
                "alert"=>"Alert message",
                "answer_count"=>htmlentities("Nb réponse", ENT_QUOTES, "UTF-8"),
            );
        }
        if($vars['selectedLanguage'] === 'Zh') {
            $xItems = array(
                "t_opno"=>mb_convert_encoding('操作步骤',  'HTML-ENTITIES','UTF-8' ),
                "op_desc"=>mb_convert_encoding('流程描述',  'HTML-ENTITIES','UTF-8' ),
                "t_item"=>mb_convert_encoding('件号',  'HTML-ENTITIES','UTF-8' ),
                "it_desc"=>mb_convert_encoding('描述',  'HTML-ENTITIES','UTF-8' ),
                "position"=>"Position",
                "subject"=>mb_convert_encoding('检验项',  'HTML-ENTITIES','UTF-8' ),
                "desc"=>mb_convert_encoding('描述',  'HTML-ENTITIES','UTF-8' ),
                "answer"=>mb_convert_encoding('数值',  'HTML-ENTITIES','UTF-8' ),
                "answer_unit"=>mb_convert_encoding('设备',  'HTML-ENTITIES','UTF-8' ),
                "answer_min"=>"Min",
                "answer_max"=>"Max",
                "answer_date"=>mb_convert_encoding('创建日期',  'HTML-ENTITIES','UTF-8' ),
                "answer_user"=>mb_convert_encoding('用户',  'HTML-ENTITIES','UTF-8' ),
                "answer_name"=>mb_convert_encoding('用户',  'HTML-ENTITIES','UTF-8' ),
                "derogation"=>"Derogation",
                "derogation_date"=>mb_convert_encoding('创建日期',  'HTML-ENTITIES','UTF-8' ),
                "derogation_user"=>mb_convert_encoding('用户',  'HTML-ENTITIES','UTF-8' ),
                "derogation_name"=>mb_convert_encoding('用户',  'HTML-ENTITIES','UTF-8' ),
                "CRAB"=>"CRAB",
                "CRAB_date"=>mb_convert_encoding('创建日期',  'HTML-ENTITIES','UTF-8' ),
                "CRAB_user"=>mb_convert_encoding('用户',  'HTML-ENTITIES','UTF-8' ),
                "CRAB_name"=>mb_convert_encoding('用户',  'HTML-ENTITIES','UTF-8' ),
                "alert"=>mb_convert_encoding('警告',  'HTML-ENTITIES','UTF-8' ),
                "answer_count"=>"Answers count",
            );
        }
        $erp = $vars['selectedErp'];

        // Get active questions for ER
        $query="select * from pi_questions_unit where unit='".$unit."' and active='Y' order by cast(t_opno as unsigned), position";
        $questions = tldUtils::getSqlToAssocArray($query);

        if (!empty($unit)) {
            $query = "SELECT id, t_prno, t_pdno  FROM service where sn='" . $unit . "'";
            $erId = tldUtils::getSqlToAssocArray($query)[0]['id'];
            $projectNumber = tldUtils::getSqlToAssocArray($query)[0]['t_prno'];
            $productionOrder = tldUtils::getSqlToAssocArray($query)[0]['t_pdno'];

            try {
                $cbom = $client->get(sprintf('/ion/customized_bill_of_materials/site=%d;project=%s', $erp, $unit), [
                    'query' => [
                        'date' => (new \DateTime())->format(\DateTimeInterface::ATOM),
                        'otherLanguage' => $vars['selectedLanguage'],
                    ]
                ]);
                $items = $cbom['items'];
            } catch (ClientException $exception) {
                $DEFAULT_ERROR[] = _("ERROR: CBOM not found.");
                break;
            }

            if ('' === $productionOrder) {
                try {
                    $response = $client->get(sprintf('ion/projects/site=%d;project=%s', $erp, $projectNumber));
                } catch (\Exception $exception) {
                    $DEFAULT_ERROR[] = _("ERROR: API request error.");
                }
                if (null === $item = ($response['productionOrders'][0] ?? null)) {
                    $DEFAULT_ERROR[] = _("ERROR: Project not found for this ER.");
                }
                if (null === ($productionOrder = ($item['productionOrderIdentifier'] ?? null))) {
                    $DEFAULT_ERROR[] = _("ERROR: Production order not found for this project.");
                }
            }

            try {
                $project = $client->get(sprintf('ion/projects/site=%d;project=%s', $erp, $projectNumber), [
                    'query' => [
                        'productionOrder' => $productionOrder,
                        'languageID' => $vars['selectedLanguage'],
                    ],
                ]);
            } catch (ClientException $exception) {
                $DEFAULT_ERROR[] = _("ERROR: Project not found for this ER.");
            } catch (ServerException $exception) {
                $DEFAULT_ERROR[] = _("ERROR: API request error.");
            }

            if ([] === $project['productionOrders']) {
                $DEFAULT_ERROR[] = _("ERROR: Production order not found for this ER.");
            }
            $operations = $project['productionOrders'][0]['operations'];
        }

//        // Get project
//        $query="select t_cprj from ttdltc001".$vars['selectedErp']." where t_clot='".$vars['unit']."'";
//        $rowsCprj = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
//        $cprj = empty($rowsCprj) ? trim($rowsCprj[0]['t_cprj']) : null;
//
//
//        if (null !== $cprj) {
//             // Get Work order
//             $query="select distinct t_pdno from ttisfc001".$vars['selectedErp']." where t_cprj=".$cprj;
//             $rowsPdno = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
//             $pdno = trim($rowsPdno[0]['t_pdno']);
//
//            // Get CBOM
//            $query="select distinct t_sitm from ttipcs022".$vars['selectedErp']." where t_cprj=".$cprj;
//            $rowsCBOM = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
//        }

        foreach ($questions as $key1 => $value1) {
            // Get answers & derogation
            $query="select * from pi_answers where parent_id=".$questions[$key1]['id']." and active='Y' order by created_on desc limit 1";
            $answers = tldUtils::getSqlToAssocArray($query);

            $questions[$key1]['answer']=$answers[0]['answer'];
            $questions[$key1]['answerComponent']=$answers[0]['answerComponent'];
            $questions[$key1]['answerModel']=$answers[0]['answerModel'];
            $questions[$key1]['answerSerial']=$answers[0]['answerSerial'];
            $questions[$key1]['answerBrand']=$answers[0]['answerBrand'];
            $questions[$key1]['answer_date']=$answers[0]['created_on'];
            $questions[$key1]['answer_user']=$answers[0]['entered_by'];
            $questions[$key1]['derogation']=$answers[0]['derogation'];
            $questions[$key1]['derogation_date']=$answers[0]['d_created_on'];
            $questions[$key1]['derogation_user']=$answers[0]['d_entered_by'];

            // Get CRABS
            $query="select * from pi_crab_eap where unit='".$unit."' and question_id=".$questions[$key1]['id'];
            $crabs = tldUtils::getSqlToAssocArray($query);
            $questions[$key1]['CRAB'] = $crabs[0]['parent_id'];
            $questions[$key1]['CRAB_date'] = $crabs[0]['date'];
            $questions[$key1]['CRAB_user'] = $crabs[0]['user_id'];

            // Get answer user name
            if($questions[$key1]['answer_user'] != '') {
                $query="select firstname, lastname from people where id=".$questions[$key1]['answer_user'];
                $answerUser = tldUtils::getSqlToAssocArray($query);
                $questions[$key1]['answer_name'] = $answerUser[0]['firstname']." ".$answerUser[0]['lastname'];
            }

            // Get derogation user name
            if($questions[$key1]['derogation_user'] != '') {
                $query="select firstname, lastname from people where id=".$questions[$key1]['derogation_user'];
                $derogationUser = tldUtils::getSqlToAssocArray($query);
                $questions[$key1]['derogation_name'] = $derogationUser[0]['firstname']." ".$derogationUser[0]['lastname'];
            }

            // Get CRAB user name
            if($questions[$key1]['CRAB_user'] !== null) {
                $query="select firstname, lastname from people where id=".$questions[$key1]['CRAB_user'];
                $crabUser = tldUtils::getSqlToAssocArray($query);
                $questions[$key1]['CRAB_name'] = $crabUser[0]['firstname']." ".$crabUser[0]['lastname'];
            }

            // Get answers count
            $query="select count(*) as nb from pi_answers where parent_id=".$questions[$key1]['id']." and active='Y' order by created_on asc";
            $answersCount = tldUtils::getSqlToAssocArray($query);
            $questions[$key1]['answer_count'] = $answersCount[0]['nb'];

            // Add hyperlink on answer count
            if ($questions[$key1]['answer_count'] > 1) {
                $questions[$key1]['answer_count']="<a href='./index.php?m[0]=pi&m[1]=reports&m[2]=piAnsHisto&id=".$questions[$key1]['id']."'>".$answersCount[0]['nb']."</a>";
            }

//            $query="select t_dsca from ttiitm001".$erp." where t_item='".$questions[$key1]['t_item']."'";
//            $rowsTmp4 = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
            // if item is not empty on the question, get item description from cbom
            $questions[$key1]['it_desc'] = '';
            if (!empty($questions[$key1]['t_item']) && isset($items)){
                foreach ($items as $key => $item){
                    if ($item['partNumber'] !== $questions[$key1]['t_item']){
                        continue;
                    }
                    $questions[$key1]['it_desc'] = $item['itemDescription'];
                    break;
                }
            }
//            $questions[$key1]['it_desc']=$rowsTmp4[0]['t_dsca'];

            // Translate item description in Chinese
//            if($vars['selectedLanguage'] == 'Zh') {
//                 $query="select t_dsca from ttitld890400 where rtrim(ltrim(t_clan))='CH' and t_eitm='".$questions[$key1]['t_item']."'";
//                 $rowsCH = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
//                 if($rowsCH[0]['t_dsca']!='') { $questions[$key1]['it_desc']=$rowsCH[0]['t_dsca']; }
//            }

            // Get operation description from project
            $questions[$key1]['op_desc'] = '';
            if(!empty($operations) && !empty($questions[$key1]['t_opno'])) {
//                $query ="select ROU003.t_dsca t_dsca from ttirou003".$vars['selectedErp']." ROU003 where ROU003.t_tano= ";
//                $query.= "(select PCS023.t_tano from ttipcs023".$vars['selectedErp']." PCS023 where PCS023.t_cprj=".$cprj." and PCS023.t_opno=".$questions[$key1]['t_opno'].")";
//                $rowsTmp5 = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
//                $questions[$key1]['op_desc']=$rowsTmp5[0]['t_dsca'];
                foreach ($operations as $key => $operation){
                    if ($operation['operationIdentifier'] !== $questions[$key1]['t_opno']){
                        continue;
                    }
                    $questions[$key1]['op_desc'] = $operation['referenceDescription'];
                    break;
                }
            }

            // Exclude questions linked to a non-existing first-level item, if item is filled in question
//            if (!empty($cprj) && $questions[$key1]['t_item'] != '') {
//                $query="select count(*) as nb from ttipcs022".$vars['selectedErp']." PCS022 where PCS022.t_cprj=".$cprj." and PCS022.t_sitm='" . $questions[$key1]['t_item']  . "'";
//                $rowsTmp6 = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
//                if ($rowsTmp6[0]['nb'] == 0) {
//                    unset($questions[$key1]);
//                }
//            }

            // Manage CRABS status selection
            if ($vars['selectedCrabStatus'] === 'Yes' && $questions[$key1]['CRAB'] == '') { unset($questions[$key1]); }
            if ($vars['selectedCrabStatus'] === 'No' && $questions[$key1]['CRAB'] != '') { unset($questions[$key1]); }

            // Manage Alerts status selection
            if ($vars['selectedAlertStatus'] === 'Yes' && $questions[$key1]['alert'] == '') { unset($questions[$key1]); }
            if ($vars['selectedAlertStatus'] === 'No' && $questions[$key1]['alert'] != '') { unset($questions[$key1]); }

            // Manage serial number answers
            if ($questions[$key1]['answer_type'] === 'S/N') {
                $answer ='<table width=100%>';
                $answer.='<tr width=100%><td width=100%><b>Component:&nbsp;'.$questions[$key1]['answerComponent'].'</b></td></tr>';
                $answer.='<tr width=100%><td width=100%>Model:&nbsp;'.$questions[$key1]['answerModel'].'</td></tr>';
                $answer.='<tr width=100%><td width=100%>Serial:&nbsp;'.$questions[$key1]['answerSerial'].'</td></tr>';
                $answer.='<tr width=100%><td width=100%>Brand:&nbsp;'.$questions[$key1]['answerBrand'].'</td></tr>';
                $answer.='</table>';
                $questions[$key1]['answer'] = $answer;
            }

            // Exclude questions linked to out-of-CBOM items
            $isPartOfCbom = false;
            foreach ($items as $key => $item) {
                if ($item['partNumber'] === $questions[$key1]['t_item']) { $isPartOfCbom = true; }
            }
            if (null === $questions[$key1]['t_item'] && !$isPartOfCbom) { unset($questions[$key1]); }

            // Exclude not S/N questions if filter is set
            if ($vars['selectedSerialNumbersOnly'] === 'Yes' && $questions[$key1]['answer_type'] !== 'S/N') { unset($questions[$key1]); }

        }

        // Count questions
        $numberOfQuestions = count($questions);
        $numberOfAnswers = 0;
        foreach ($questions as $key1 => $value1) {
            if($questions[$key1]['answer'] != '') {
                ++$numberOfAnswers;
            }
        }

        // Translate data
        foreach ($questions as $key1 => $value1) {
            if($vars['selectedLanguage'] === 'En') {
                $questions[$key1]['subject'] = $questions[$key1]['subject_en'];
                $questions[$key1]['desc'] = $questions[$key1]['desc_en'];
            }
            if($vars['selectedLanguage'] === 'Fr') {
                $questions[$key1]['subject'] = $questions[$key1]['subject_fr'];
                $questions[$key1]['desc'] = $questions[$key1]['desc_fr'];
            }
            if($vars['selectedLanguage'] === 'Zh') {
                $questions[$key1]['subject'] = $questions[$key1]['subject_zh'];
                $questions[$key1]['desc'] = $questions[$key1]['desc_zh'];

                // Convert chinese description of operation to entity numeric.
                // As this text is clean from LN, mb_convert_encoding function will have no effect.
                // We can also remove mb_convert_encoding, but not sure about result in other language.
                $questions[$key1]['op_desc'] = mb_encode_numericentity($questions[$key1]['op_desc'], [0x0, 0xFFFF, 0, 0xFFFF], "UTF-8");
            }
        }

        $caption = "ER {$unit} Inspection Revue: Project {$projectNumber} - Work Order {$productionOrder} ({$numberOfAnswers}/{$numberOfQuestions})";
        $report = new tldReportColumnar($questions, array("xItems"=>$xItems, "title"=>$caption, 'stickyHeader'=>true));

        if ($vars['selectedOutput'] == 'Pdf') {
            $dateTmp1 = new DateTime(date('Y-m-d'));
            $dateTmp2 =	$dateTmp1->format('Y-m-d');

            $er = new tldEquipment($erId);
            $pdf = $er->generatePDF($questions, $productionOrder);
            $pdf->outFile("aaa.pdf");
            exit;
        }

        if ($vars['selectedOutput'] == 'Display') {
            $body .= $report->fetch();
            break;
        }

        if ($vars['selectedOutput'] == 'Excel') {
            $report = new tldXLS(
                    $questions,
                    array(
                            "xItems"=>$xItems,
                            "showTitles"=>true
                    )
            );
            $report->out("pi_report.xls");
            exit;
        }

    break;

    case 'unitsQuestions':
        if(!$user->isInGroup(['pi_PCQ', 'pi_QCQ', 'pi_ECQ', 'ROLE_RME'])) {
            $DEFAULT_ERROR[] = 'ERROR: You do not have permission to access this module';
            return;
        }

        $erpList = tldLocation::getERPList('smartyOptions');
        asort($erpList);
        $families= array_column(tldPI::getPiFamilies(), 'family');

        $form = new HTML_QuickForm('unitsQuestions', 'get');
        $form->addElement(	'hidden', 'm[0]', 'pi');
        $form->addElement(	'hidden', 'm[1]', 'reports');
        $form->addElement(	'hidden', 'm[2]', 'questionsUnit');
        $form->addElement(	'header', 'title', 'Filters');
        $form->addElement(	'select', 'erp', 'Company',	$erpList);
        $form->addElement(	'select', 'family', 'Family', array_combine($families, $families));
        $form->addElement(	'text',	'unitFrom', 'ER From');
        $form->addElement(	'text',	'unitTo', 'ER To');
        $form->addElement(	'text',	'pn', 'PN');
        $form->addElement(	'text',	'id', 'Question ID');
        $form->addElement(  'text', 'qst_dt_from', 'Answer from', ['class' => 'datepicker', 'autocomplete' => 'off']);
        $form->addElement(  'text', 'qst_dt_to', 'Answer to', ['class' => 'datepicker', 'autocomplete' => 'off']);
        $form->addElement(	'submit', 'btnSubmit', 'Submit');
        $form->addRule('family', 'This is required', 'required');
        $form->addRule('erp', 'This is required', 'required');

        $location = new tldLocation($user->getBUID());
        $form->setDefaults(['erp' =>$location->getERP()]);

        $body = $form->toHTML();
        $vars = tldUtils::cleanupFormInput($form->exportValues());
        if ($form->validate() && $form->isSubmitted())
        {
            $vars['erp'] = trim($vars['erp']);
            $query = <<<SQL
              SELECT qst.*, ans.answer, ans.created_on date_answered
              FROM pi_questions_unit qst 
              LEFT JOIN pi_answers ans ON qst.id = ans.parent_id AND ans.active='Y'
              WHERE qst.active ='Y' AND 
              qst.comp ='{$vars['erp']}' AND 
              model = '{$vars['family']}'
SQL;
            if ('' !== $erFrom = trim($vars['unitFrom'])) {
                $query .= <<<SQL
                AND unit >= '$erFrom'
SQL;
            }
            if ('' !== $erTo = trim($vars['unitTo'])) {
                $query .= <<<SQL
                AND unit <= '$erTo'
SQL;
            }

            if ('' !== $pn = trim($vars['pn'])) {
                $query .= <<<SQL
                AND t_item = '$pn'
SQL;
            }

            if (is_numeric($id = trim($vars['id']))) {
                $query .= <<<SQL
                AND qst.parent_id =$id
SQL;
            }

            if ('' !== $qstFrom = $vars['qst_dt_from']) {
                $qstFrom = DateTime::createFromFormat('Y-m-d', $vars['qst_dt_from'])->setTime(00, 00, 00);
                $qstFrom = $qstFrom->format('Y-m-d H:i:s');
                $query  .= <<<SQL
                AND '$qstFrom' <= ans.created_on
SQL;
            }
            if ('' !== $qstTo = $vars['qst_dt_to']) {
                $qstTo = DateTime::createFromFormat('Y-m-d', $vars['qst_dt_to'])->setTime(23, 59, 59);
                $qstTo = $qstTo->format('Y-m-d H:i:s');
                $query .= <<<SQL
                AND '$qstTo' >= ans.created_on 
SQL;
            }

            $rowsTmp1 = tldUtils::getSqlToAssocArray($query);
            $xItems = [
                "parent_id" => "Parent ID#",
                "unit" => "ER",
                "t_opno" => "Operation",
                "t_item" => "P/N",
                "model" => "Model",
                "owner" => "Owner",
                "position" => "Position",
                "subject_en" => "Subject (EN)",
                "subject_fr" => "Subject (FR)",
                "subject_zh" => "Subject (ZH)",
                "desc_en" => "Description (EN)",
                "desc_fr" => "Description (FR)",
                "desc_zh" => "Description (ZH)",
                "answer_type" => "Answer Type",
                "answer_unit" => "Answer Unit",
                "component_sn" => "Component S/N",
                "answer_max" => "Answer Max",
                "answer_min" => "Answer Min",
                "active" => "Active",
                "updated_on" => "Updated on",
                "updated_by" => "Updated by",
                "name" => "Name",
                "alert" => "Alert",
                "answer" => "Answer",
                "date_answered" => "Date Answered",
            ];
            if (!empty($rowsTmp1)) {
                $report = new tldXLS(
                    $rowsTmp1,
                    [
                        "xItems" => $xItems,
                        "showTitles" => true
                    ]
                );
                $report->out("pi_report_units_questions.xls");
                exit;
            }

            $DEFAULT_ERROR[] = "No result in the selection.";
        }
        break;

    case 'questionsByModelAndFactory':
        $columnFactories = tldPI::getFactoriesList();
        $PIFamilies = array_column(tldPI::getPiFamilies(['status' => 'Final']), 'family');
        $PIFamilies = array_combine($PIFamilies, $PIFamilies);

        $form = new HTML_QuickForm('questionsByModelAndFactoryForm', 'get');
        $form->addElement('hidden', 'm[0]', 'pi');
        $form->addElement('hidden', 'm[1]', 'reports');
        $form->addElement('hidden', 'm[2]', 'questionsByModelAndFactory');
        $form->addElement('header', 'title', 'CQs By Factory and P&I Family');
        $form->addElement('select', 'PIFamily', 'P&I Families', ['' => '', 'ALL' => 'ALL'] + $PIFamilies);
        $form->addElement('select', 'factory', 'Factory',  ['' => '', 'ALL' => 'ALL'] + $columnFactories);
        $form->addElement('submit', 'btnSubmit', 'Submit');

        // Set required
        $required = ['PIFamily', 'factory'];
        foreach ($required as $field) {
            $form->addRule($field, 'Required', 'required');
        }

        if (!$form->validate()) {
            $body = $form->toHTML();
            break;
        }

        $PIFamilyQuery = $PIFamily !== 'ALL' ? sprintf('piq.model="%s" AND', $PIFamily) : '';
        $factoryQuery = sprintf('%s != 0 AND', $factory);
        $factoryLabel = $factory;
        if($factory === 'ALL') {
            $factoryLabel = 'all_factories';
            $factoryQuery = '';
        }

        $data = tldPI::questionsByConstraints("$factoryQuery $PIFamilyQuery piq.active='Y' AND piq.created_on != ''", ['select' => "owner Owner, COUNT(*) $factoryLabel", 'groupBy' => 'owner']);
        $report = new tldReportColumnar($data, ['xItems' => ['Owner' => 'Owner', $factoryLabel => 'Quantity'],]);
        $body .= sprintf('<h4>CQs For P&I Family : <b>%s</b> and Factory : <b>%s</b></h4>', $PIFamily,  $factory === 'ALL' ? 'ALL' : $columnFactories[$factory]);
        $body .= $report->fetch();
        if(empty($data)){
            break;
        }

        // KPI
        $data = tldPI::questionsByConstraints("$factoryQuery $PIFamilyQuery  piq.active='Y' AND piq.created_on != ''" , ['select' => "owner owner, COUNT(*) quantity, DATE_FORMAT(piq.created_on, '%Y-%m') creation_date", 'groupBy' => 'owner, creation_date', 'orderBy' => 'creation_date']);
        $totalData = tldPI::questionsByConstraints("$factoryQuery $PIFamilyQuery  piq.active='Y' AND piq.created_on != ''" , ['select' => "'ALL' owner, COUNT(*) quantity, DATE_FORMAT(piq.created_on, '%Y-%m') creation_date", 'groupBy' => 'creation_date', 'orderBy' => 'creation_date']);

        $runningTotalData  = $totalData;
        $runningTotal  = 0;
        foreach ($runningTotalData as ['owner' => &$owner, 'quantity' => &$quantity]) {
            $owner = 'Running Total';
            $runningTotal += $quantity;
            $quantity = $runningTotal;
        }

        $periods = array_column($runningTotalData, 'creation_date');
        $indexedValues = array_combine($periods, array_column($runningTotalData, 'quantity'));
        $startingDate = current($periods);
        $endingDate = end($periods);

        $interval = new \DateInterval('P1M');
        $previousValue = null;
        /** @var \DateTime $dt */
        foreach (new \DatePeriod(new \DateTime($startingDate), new \DateInterval('P1M'), new \DateTime($endingDate)) as $dt) {
            $formattedDate = $dt->format('Y-m');
            $value = $indexedValues[$formattedDate] ?? null;
            if (null === $value && null !== $previousValue) {
                $value = $previousValue;
                $runningTotalData[] = ['owner' => 'Running Total', 'quantity' => $previousValue, 'creation_date' => $formattedDate];
            }
            $previousValue = $value;
            $periods[] = $dt->format('Y-m');
        }

        $data = array_filter(array_merge($data, $totalData, $runningTotalData), static function ($plot) {
            return (int) $plot['quantity'] !== 0;
        });

        $chart = $chartBuilderFactory
            ->getLineChartBuilder()
            ->setTitle(sprintf('CQs evolution For Model : <b>%s</b> and Factory : <b>%s</b>', $PIFamily, $factory === 'ALL' ? 'ALL' : $columnFactories[$factory]))
            ->addYAxis('Quantity', ['min' => 0])
            ->addXAxisOptions('ALL', ['visible' => false])
            ->addXAxisOptions('Running Total', ['visible' => false]);

        $chart->addChartOptions(['zoomType' => 'x']);

        foreach ($data as $key => $row) {
            $chart->addPlot(
                $row['owner'],
                $row['creation_date'],
                (int)$row['quantity']
            );
        }

        $startingDate = new \DateTime(reset($data)['creation_date']);
        $endingDate = new \DateTime(end($data)['creation_date']);
        $period = new \DatePeriod($startingDate, new \DateInterval('P1M'), $endingDate->modify('+1 month'));

        $chart->reMapXValues(array_map([ChartBuilder::class, 'formatMonth'], iterator_to_array($period)), 0);
        $chart = json_encode($chart->buildConfig(), JSON_THROW_ON_ERROR);


        $body .= <<<EOF
<br/><br/>
<div id="container-kpi" style="width:100%; height:400px;"></div>
<script>
	$(document).ready(function() {
	  $('#container-kpi').highcharts($chart)
});
</script>
EOF;
        break;

    default:
        $body = $smarty->fetch("$PATH/pi/reports/homepage.reports.tpl");
    break;
    }

?>
