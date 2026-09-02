<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Pi\Inspect;
use App\Pi\Utils\Config;
use App\Pi\Parts;
use App\Pi\Routing;
use App\Pi\User;
use App\Pi\Utils\OperationController;
use App\Pi\Utils\QuestionManager;
use App\Pi\Utils\UnitManager;

//          PPPPPP       &&&&&       IIIIIIII
//          P     P     &     &         II
//          P      P   &       &        II
//          P      P   &       &        II
//          P      P    &     &         II
//          P     P      &   &          II
//          P    P        & &           II
//          Ppppp          &            II
//          P             & &           II
//          P            &   &    &     II
//          P           &     &  &      II
//          P          &       &&       II
//          P          &       &&       II
//          P           &     &  &      II
//          P            &&&&&    &  IIIIIIII

if(!isset($_SESSION))
{
    session_start();
}

$_SESSION['pi_font_size'] = 'medium'; // xx-small -> x-small -> small -> medium -> large -> x-large -> xx-large
$_SESSION['answer_mode'] = 'next'; // standard: return to questions list / next: go to next question
$DEFAULT_MENU = "";
$_SESSION['LOCATION'] = $LOCATION['region'];
$_SESSION['sol_status'] = '';

$user = User::fromId($_SESSION['pi_user_id']);

if (!$user->isInGroups(["pi_OPERATOR"])) {
    $_SESSION['error'] = _('User not authorized');
    header("location:/shop/autoselect.php");
    exit;
}

// Go to operations by default if user connected
if ($_SESSION['pi_user'] != '' && !isset($m[1]) && !isset($m[2])) {
    $m[1] = 'form';
    $m[2] = 'operation';
}

// Manage indirect time-keeping authorization
$pi_config = new Config();
$_SESSION['pi_indirect_allowed'] = $pi_config->getPIConfig($_SESSION['pi_erp'] ?? 0, "000", "indirect");

//initialization of operation controler
//important for notification and access operations
$opController = new OperationController($client);
if(isset($_SESSION['$firstCrabOpen'], $_SESSION['$firstOpNotAns'], $_SESSION['$firstDeroMissing'])){
    $opController->set($firstCrabOpen, $firstOpNotAns, $firstDeroMissing);
}


switch ($m[1] ?? null) {
    case 'form':
        $DEFAULT_TITLE .= "\Manufacturing Module";

        switch ($m[2] ?? null) {
            case 'unit':
                $zMenu_unit = '';
                $pi_debug = false;
                //Session variable for ER
                unset($_SESSION['pi_er_input']);
                $_SESSION['pi_sn'] = '';
                $_SESSION['pi_cprj'] = '';
                $_SESSION['pi_pdno'] = '';
                $_SESSION['pi_family'] = '';
                $_SESSION['pi_snid'] = '';
                $_SESSION['pi_opno'] = '';
                $_SESSION['pi_tano_dsca'] = '';

            case 'operation':
                $m[2]='operation';
                $unitManager = new UnitManager($client);
                //check if the user has the right to access PIO
                if (!$user->isInGroups(["pi_OPERATOR"])) {
                    header("location:/shop/autoselect.php?m[0]=pi&m[1]=form&m[2]=ident");
                    break;
                }
                $piUser = User::fromId($_SESSION['pi_user_id']);
                $error = null;
                if(!($_SESSION['pi_sn'] ?? null)) {
                    if (!$_SESSION['pi_er_input'] = isset($_POST['pi_er_input']) ? TldDatabase::escape(strtoupper(trim($_POST['pi_er_input']))) : '') {
                        $body = include("$PATH/unit.pi.tpl.php");
                        break;
                    }
                    $query = <<<SQL
                    SELECT erp, location FROM locations AS loc
                            LEFT JOIN service AS sn ON loc.location = sn.man_location 
                    WHERE sn='{$_SESSION['pi_er_input']}';
SQL;
                    $rowsErp = tldUtils::getSqlRowToAssocArray($query);
                    $_SESSION['pi_erp'] = $rowsErp['erp'];
                    $_SESSION['pi_man_location'] = $rowsErp['location'];

                    if ($_SESSION['pi_erp'] === '') {
                        $body = include("$PATH/unit.pi.tpl.php");
                        break;
                    }

                    try {
                        $unit = $unitManager->getUnit($_SESSION['pi_erp'], $_SESSION['pi_er_input'], $LANG);
                    }catch (Exception $exception){
                            $error = $exception->getMessage();
                            $body = include("$PATH/unit.pi.tpl.php");
                            break;
                    }

                    $_SESSION['pi_sn'] = $unit['sn'];
                    $_SESSION['pi_cprj'] = $unit['t_prno'];
                    $_SESSION['pi_pdno'] = $unit['t_pdno'];
                    $_SESSION['workOrderStatus'] = $unit['workOrderStatus'];
                    $_SESSION['pi_family'] = $unit['family'];
                    $_SESSION['pi_snid'] = $unit['id'];
                    $questionsAdd = QuestionManager::getQuestionsToAdd($unit, array_column($_SESSION['project']['productionOrders'][0]['operations'], 'operationIdentifier'), $_SESSION['pi_erp']);
                    QuestionManager::insertQuestionUnit($questionsAdd, $unit, $_SESSION['pi_erp']);
                }

                if(($_SESSION['pi_opno'] ?? '') !== '' || ($_GET['opno'] ?? '')) {
                    $_SESSION['pi_indirect'] = '';
                }

                $_SESSION['pi_warehouse'] = false;
                $pi_config = new Config();
                if($user->isInGroups(["pi_GL", "pi_PM"])) {
                   $cartsConfig = $pi_config->getAllPIConfig($_SESSION['pi_erp'], ["000", "009", "900"], "cart");
                   foreach ($cartsConfig as $config) {
                       if ($config['resu'] === 'Y') {
                           $_SESSION['pi_warehouse'] = true;
                       }
                       $indexedCartConfig[$config['koop']] = $config['resu'];
                   }
                }

                $operations = $opController->getPIOperations($_SESSION['pi_erp'], $_SESSION['pi_cprj'], $_SESSION['pi_pdno'], $piUser, $_SESSION['pi_sn']);
                if ($_SESSION['pi_warehouse']) {
                    $partsManager = new Parts($client);
                    $allParts = $partsManager->getPIParts($_SESSION['pi_erp'], $_SESSION['pi_pdno'], null, $LANG);
                    $operationsParts = [];

                    foreach($allParts as $partsOp){
                        $operationsParts[$partsOp['operation']][] = $partsOp;
                    }

                    foreach ($operations as &$operation) {
                        //check the kind of operation <900 , xx9, >=900
                        $opLastNumber = substr($operation['t_opno'],-1);
                        //affects operation in the right category
                        if($opLastNumber!=='9' || $operation['t_opno'] === 999)
                        {
                            $opLastNumber = "0";
                        }
                        $opFirstNumber = $operation['t_opno'] < 900 ? "0" : "9";

                        // check if parts can be call in prod for this operation
                        if (isset($indexedCartConfig) && $indexedCartConfig[$opFirstNumber."0".$opLastNumber] === "Y")
                        {
                            $parts = $operationsParts[$operation['t_opno']] ?? [];
                            $tmp_qune = $tmp_stoc = 0;

                            foreach ($parts as $key3 => $value3) {
                                $tmp_qune += $value3['netQuantity'];
                                if ($value3['inventoryOnHand'] > $value3['netQuantity']) {
                                    $tmp_stoc += $value3['netQuantity'];
                                } else {
                                    $tmp_stoc += $value3['inventoryOnHand'];
                                }
                                $parts[$key3]['total_qune'] = $tmp_qune;
                                $parts[$key3]['total_stoc'] = $tmp_stoc;
                            }
                            if ((float)$tmp_qune !== 0.0) {
                                $tmp_dispo = $tmp_stoc / $tmp_qune * 100;
                            } else {
                                $tmp_dispo = 0;
                            }
                            if ($tmp_dispo > 100) {
                                $tmp_dispo = 100;
                            }
                            $tmp_dispo = floor($tmp_dispo);
                            $tmp_dispo .= '%';
                            if (count($parts) === 0) {
                                $tmp_dispo = 'N/A';
                            }
                            $operation['dispo'] = $tmp_dispo;

                            $operation['nbParts'] = $key3 ?? null;
                            $HRADateTime = new DateTime();
                            $HRADateTime->add(new DateInterval('PT6H'));
                            $HRADate = $HRADateTime->format('Y-m-d');
                            $HRATime = $HRADateTime->format('H:i:s');
                            $operation['time'] = $HRATime;
                        }
                    }
                    unset($operation);
                }

                if (($_GET['opno'] ?? '') !== '') {
                    $_SESSION['pi_opno'] = $_GET['opno'];
                    $_SESSION['pi_tano'] = $_GET['tano'];
                    // Check SOL status
                    $_SESSION['sol_status'] = '';
                    if ($_SESSION['pi_sn'] != '' && ($_SESSION['pi_opno'] == '990' || $_SESSION['pi_opno'] == '995')) {
                        $query = "SELECT status FROM sor_lines
                                  WHERE status='IN_PROGRESS'
                                  AND id=(
                                    SELECT parent_id FROM sor_units
                                    WHERE id=(
                                      SELECT sor_uid
                                      FROM service
                                      WHERE sn='{$_SESSION['pi_sn']}'))";
                        $rows = tldUtils::getSqlToAssocArray($query);
                        $_SESSION['sol_status'] = $rows[0]['status'];
                    }

                    if (($pi_config->getPIConfig($_SESSION['pi_erp'],"000","punchout") === 'Y' && $_SESSION['pi_opno'] < 900 && substr($_SESSION['pi_opno'], -1) !== '9') ||
                        ($pi_config->getPIConfig($_SESSION['pi_erp'],"009","punchout") === 'Y' && $_SESSION['pi_opno'] < 900 && substr($_SESSION['pi_opno'], -1) === '9') ||
                        ($pi_config->getPIConfig($_SESSION['pi_erp'],"900","punchout") === 'Y' && $_SESSION['pi_opno'] >= 900)
                    ) {
                        if ($_SESSION['pi_user_id'] !== 0 && $_SESSION['pi_opno'] !== '0') {
                            $pi_time_keeping = new Routing($client, $session);
                            $pi_time_keeping->postLogTimeKeeping(
                                $_SESSION['pi_erp'],
                                $_SESSION['pi_user_id'],
                                $_SESSION['pi_pdno'],
                                $_SESSION['pi_opno'],
                                '',
                                'DIRECT'
                            );
                            $pi_time_keeping->postPITimeKeeping(
                                $_SESSION['pi_pdno'],
                                $_SESSION['pi_opno'],
                                $translator,
                                'timekeeping PIO'
                            );
                        }
                    }

                    $pi_TaskDesc = new Routing($client, $session);
                    $_SESSION['pi_tano_dsca'] = $pi_TaskDesc->getPITaskDescFromTano($_SESSION['pi_tano']);

                    if ($user->isInGroups(["pi_OPERATOR","pi_TESTER"]) && !$user->isInGroups(["pi_GL", "pi_PM", "pi_QAM"])) {
                        header("location:/shop/autoselect.php?m[0]=pi&m[1]=form&m[2]=inspection&m[3]=display");
                        break;
                    }
                }

                // Add CRABS created from CBOM
                $query = "INSERT INTO pi_crab_eap (id, parent_id, unit, comp, t_cprj, t_pdno, t_opno, t_item, model, question_id, user_id, date) SELECT NULL, ";
                $query .= "id, ";
                $query .= "'".$_SESSION['pi_sn']."', ";
                $query .= "'".$_SESSION['pi_erp']."', ";
                $query .= "'".$_SESSION['pi_cprj']."', ";
                $query .= "'".$_SESSION['pi_pdno']."', ";
                $query .= "'', ";
                $query .= "'', ";
                $query .= "'".$_SESSION['pi_family']."', ";
                $query .= "0, ";
                $query .= "init_emno, ";
                $query .= "dt ";
                $query .= "from crabs where erid = (select max(id) from service where sn='".$_SESSION['pi_sn']."') ";
                $query .= "and id not in (select parent_id from pi_crab_eap) ";
                tldUtils::sqlInsert($query);

                $body = include("$PATH/operation.pi.tpl.php");

                break;

            case 'warehouse':
                $user = User::fromId($_SESSION['pi_user_id']);
                if (!$user->isInGroups(["pi_OPERATOR"])) {
                    header("location:/shop/autoselect.php?m[0]=pi&m[1]=form&m[2]=ident");
                    break;
                }
                if ($_SESSION['pi_sn'] == '') {
                    header("location:/shop/autoselect.php?m[0]=pi&m[1]=form&m[2]=unit");
                    break;
                }

                if (!$user->isInGroups(["pi_GL", "pi_PM"])) {
                    header("location:/shop/autoselect.php?m[0]=pi&m[1]=form&m[2]=operation");
                    break;
                }

                $partsManager = new Parts($client);
                $piParts = $partsManager->getPIParts($_SESSION['pi_erp'], $_SESSION['pi_pdno'], $opno, $LANG);
                $tmp_qune = 0;
                $tmp_stoc = 0;
                foreach ($piParts as $part) {
                    $tmp_qune += $part['netQuantity'];
                    $tmp_stoc += ($part['inventoryOnHand'] > $part['netQuantity']) ? $part['netQuantity'] : $part['inventoryOnHand'] ;
                }

                $_SESSION['pi_dispo'] = ($tmp_qune !== 0) ? $tmp_stoc / $tmp_qune * 100 : 0;

                if ($_SESSION['pi_dispo'] > 100) {
                    $_SESSION['pi_dispo'] = 100;
                }

                $_SESSION['pi_dispo'] = floor($_SESSION['pi_dispo']);
                $warehouseAssignees = tldGroup::getUserListByMultipleGroup(["role_WS","gg_WAREHOUSE", "role_MPE", "role_PS", "role_GL"], $_SESSION['pi_erp']);
                $warehouseAssignee = $warehouseCC = [];
                foreach ($warehouseAssignees as $assignee){
                    if( $assignee['group_name'] === "role_WS"){
                        $warehouseAssignee[] = $assignee;
                    }elseif (\in_array($assignee['group_name'], ["gg_WAREHOUSE", "role_MPE", "role_PS", "role_GL"], true)) {
                        $warehouseCC[] = $assignee;
                    }
                }
                unset($warehouseAssignees);
                $_SESSION['whse_assignee'] = $warehouseAssignee;
                $_SESSION['whse_CC'] = $warehouseCC;
                $_SESSION['whse_components'] = $piParts;
                $_SESSION['whse_opno'] = $opno;

                $body = include("$PATH/warehouse.pi.tpl.php");

                break;

            case 'warehouseNotification':
                $zMenu_warehouseNotification = '';
                $assignee = implode(',', array_column((array) $_SESSION['whse_assignee'], 'email'));
                $CC = implode(',', array_column((array) $_SESSION['whse_CC'], 'email'));

                $pi_TaskDesc = new Routing($client, $session);
                $_SESSION['whse_tano_dsca'] = $pi_TaskDesc->getPITaskDescFromOpno($_SESSION['whse_opno']);
                $warehouseComponents = $_SESSION['whse_components'];

                $delay = $_POST['piWareDelay'] ?? null;
                $slot = TldDatabase::escape($_POST['slot'] ?? '');

                if (false === DateTime::createFromFormat('Y-m-d', $delay)) {
                    $session->getFlashBag()->add('error', $translator->trans('pio.incorrect_date', ['%delay%' => $delay], 'pio'));
                    header(sprintf('location:/shop/autoselect.php?m[0]=pi&m[1]=form&m[2]=warehouse&opno=%s', $_GET['opno']));

                    exit;
                }

                //$charset is defined in autoselect.php
                $ERLabel = mb_convert_encoding(_('ER'), "HTML-ENTITIES", $charset);
                $projectLabel = mb_convert_encoding(_('Project'), "HTML-ENTITIES", $charset);
                $WOLabel = mb_convert_encoding(_('Work Order'), "HTML-ENTITIES", $charset);
                $operationLabel = mb_convert_encoding(_('Operation'), "HTML-ENTITIES", $charset);
                $delayLabel = mb_convert_encoding(_('Delay'), "HTML-ENTITIES", $charset);
                $slotLabel = mb_convert_encoding(_('Slot'), "HTML-ENTITIES", $charset);
                $assignorLabel = mb_convert_encoding(_('Assignor'), "HTML-ENTITIES", $charset);
                $itemLabel =  mb_convert_encoding(_('Item'), "HTML-ENTITIES", $charset);
                $descriptionLabel =  mb_convert_encoding(_('Description'), "HTML-ENTITIES", $charset);
                $quantityLabel =  mb_convert_encoding(_('Quantity'), "HTML-ENTITIES", $charset);

                $email =<<<HTML
                <table border=0>
                    <tr>
                      <td style="font-size: medium; color:white;" bgcolor="#2971a8">$ERLabel</td>
                      <td style="font-size: medium;" bgcolor="#d0d0d0">{$_SESSION['pi_sn']}&nbsp;({$_SESSION['pi_family']})</td>
                    </tr>
                    <tr>
                      <td style="font-size: medium; color:white;" bgcolor="#2971a8">$projectLabel</td>
                      <td style="font-size: medium;" bgcolor="#eeeeee">{$_SESSION['pi_cprj']}</td>
                    </tr>
                    <tr>
                      <td style="font-size: medium; color:white;" bgcolor="#2971a8">$WOLabel</td>
                      <td style="font-size: medium;" bgcolor="#d0d0d0">{$_SESSION['pi_pdno']}</td>
                    </tr>
                    <tr>
                      <td style="font-size: medium; color:white;" bgcolor="#2971a8">$operationLabel</td>
                      <td style="font-size: medium;" bgcolor="#eeeeee">{$_SESSION['whse_opno']}&nbsp;({$_SESSION['whse_tano_dsca']})</td>
                    </tr>
                    <tr>
                      <td style="font-size: medium; color:white;" bgcolor="#2971a8">$delayLabel</td>
                      <td style="font-size: medium;" bgcolor="#d0d0d0">$delay</td>
                    </tr>
                    <tr>
                      <td style="font-size: medium; color:white;" bgcolor="#2971a8">$slotLabel</td>
                      <td style="font-size: medium;" bgcolor="#eeeeee">$slot</td>
                    </tr>
                    <tr>
                      <td style="font-size: medium; color:white;" bgcolor="#2971a8">$assignorLabel</td>
                      <td style="font-size: medium;" bgcolor="#d0d0d0">{$_SESSION['pi_user']}</td>
                    </tr>
                </table>
                <br>
                <table border=0>
                  <thead>
                    <tr style="background:#2971a8; color:white;">
                      <td width=15% style="font-size: {$_SESSION['pi_font_size']};">$itemLabel</td>
                      <td width=55% style="font-size: {$_SESSION['pi_font_size']};">$descriptionLabel</td>
                      <td width=15% style="font-size: {$_SESSION['pi_font_size']};">$quantityLabel</td>
                    </tr>
                  </thead>
                  <tbody>
HTML;
               foreach ($warehouseComponents as $key => $warehouseComponent) {
                   $color = ($key % 2) ? "#eeeeee" : "#d0d0d0";
                   $description = mb_convert_encoding($warehouseComponent['itemDescription'], "HTML-ENTITIES", $charset);
                   $email .=<<<HTML
                        <tr style="background:$color">
                        <td style="font-size: {$_SESSION['pi_font_size']};">{$warehouseComponent['item']}</td>
                        <td style="font-size: {$_SESSION['pi_font_size']};">{$description}</td>
                        <td style="font-size: {$_SESSION['pi_font_size']};">{$warehouseComponent['netQuantity']}</td>
                        </tr>
HTML;
                }
                $email .=<<<HTML
                    </tbody>
                    </table>
HTML;
                // Send notification
                $e = tldUtils::emailAttachment(
                    $assignee,
                    'noreply@tld-gse.com',
                    "P&I: Demande de pieces en production",
                    $email,
                    null,
                    $CC
                );
                // log notification
                $query = "UPDATE pi_operations_status SET warehouseNotified='YES', wh_not_date=NOW(), wh_not_delay='$delay', wh_not_user='{$_SESSION['pi_user_id']}', wh_not_slot='$slot' WHERE comp={$_SESSION['pi_erp']} AND t_cprj='{$_SESSION['pi_cprj']}' AND t_pdno='{$_SESSION['pi_pdno']}' AND t_opno=$opno";
                tldUtils::sqlExecute($query);
                header("location:/shop/autoselect.php?m[0]=pi&m[1]=form&m[2]=operation");
                break;

            case 'documentation':
                $zMenu_documentation = '';

                // default: display instructions
                if (($m[3] ?? '') === '') {
                    $m[3] = 'instructions';
                }

                $user = User::fromId($_SESSION['pi_user_id']);
                if (!$user->isInGroups(["pi_OPERATOR"])) {
                    header("location:/shop/autoselect.php?m[0]=pi&m[1]=form&m[2]=ident");
                    break;
                }
                if ($_SESSION['pi_sn'] == '') {
                    header("location:/shop/autoselect.php?m[0]=pi&m[1]=form&m[2]=unit");
                    break;
                }

                $_SESSION['pi_instructions'] = $m[3];
                $pi_docList = new Parts($client);

                switch ($m[3]) {
                    case 'docList':
                        $documentationList = $pi_docList->getPIDocList($_SESSION['pi_erp'], $_SESSION['pi_cprj'], 'CH' === $LANG ? 'zh' : mb_strtolower($LANG)); // Doc List
                        break;

                    case 'schemes':
                        $pi_TaskDesc = new Routing($client, $session);
                        $_SESSION['pi_tano_dsca'] = $pi_TaskDesc->getPITaskDescFromTano($_SESSION['pi_tano']);
                        $schemes = $pi_docList->getPIDSchemes(
                            $_SESSION['pi_erp'],
                            $_SESSION['pi_cprj'],
                            'CH' === $LANG ? 'zh' : mb_strtolower($LANG)
                        ); // Schemes & Programs
                        break;

                    case 'instructions':
                        $pi_TaskDesc = new Routing($client, $session);
                        $_SESSION['pi_tano_dsca'] = $pi_TaskDesc->getPITaskDescFromTano($_SESSION['pi_tano']);
                        $instructions = $pi_docList->getPIDocumentation(
                            $_SESSION['pi_erp'],
                            $_SESSION['pi_cprj'],
                            (int)$_SESSION['pi_opno'],
                                $_SESSION['pi_family'],
                            'CH' === $LANG ? 'zh' : mb_strtolower($LANG)
                        ); // Instructions
                        break;
                    case 'options':
                        $query = "SELECT id FROM service WHERE t_prno='{$_SESSION['pi_cprj']}' AND man_location='{$_SESSION['pi_man_location']}'";
                        $er = tldUtils::getSqlRowToAssocArray($query);
                        $eq = new tldEquipment($er['id']);
                        $eqh = $eq->getHeader();
                        $intCaty = tldList::optionsByListNameAsListItemListItem("list.sol.caty.int");
                        $intOptions = tldSOROpts::byParent(($eqh['sor_lid']), ["include" => $intCaty]);
                        $options = $intOptions;
                        break;
                }

                $body = include("$PATH/documentation.pi.tpl.php");
                break;

            case 'parts':
                $zMenu_parts = '';
                $user = User::fromId($_SESSION['pi_user_id']);
                if (!$user->isInGroups(["pi_OPERATOR"])) {
                    header("location:/shop/autoselect.php?m[0]=pi&m[1]=form&m[2]=ident");
                    break;
                }
                if ($_SESSION['pi_sn'] == '') {
                    header("location:/shop/autoselect.php?m[0]=pi&m[1]=form&m[2]=unit");
                    break;
                }
                if ($_SESSION['pi_opno'] == '') {
                    header("location:/shop/autoselect.php?m[0]=pi&m[1]=form&m[2]=operation");
                    break;
                }

                $_SESSION['pi_sitm'] = '';
                if (($sitm ?? '') !== '') {
                    $_SESSION['pi_sitm'] = $sitm;
                }

                $partsManager = new Parts($client);
                $piParts = $partsManager->getPIParts($_SESSION['pi_erp'], $_SESSION['pi_pdno'], $_SESSION['pi_opno'], $LANG);
                $_SESSION['parts_extended'] = $user->isInGroups(["pi_GL"]);

                $body = include("$PATH/parts.pi.tpl.php");
                break;

            case 'inspection':
                $user = User::fromId($_SESSION['pi_user_id']);
                if (!$user->isInGroups(["pi_OPERATOR"])) {
                    header("location:/shop/autoselect.php?m[0]=pi&m[1]=form&m[2]=ident");
                    break;
                }
                if (($_SESSION['pi_sn'] ?? '') === '') {
                    header("location:/shop/autoselect.php?m[0]=pi&m[1]=form&m[2]=unit");
                    break;
                }
                if ($_SESSION['pi_opno'] == '') {
                    header("location:/shop/autoselect.php?m[0]=pi&m[1]=form&m[2]=operation");
                    break;
                }

                switch ($m[3]) {
                    case 'display':
                        $pi_inspect = new Inspect($client, $session);
                        $questions = $pi_inspect->getPIInspect(
                            $_SESSION['pi_opno'],
                            $_SESSION['pi_sn'],
                            $_SESSION['pi_erp'],
                            $_SESSION['pi_cprj']
                        );
                        // Prepare Subject dropDownList
                        $subjects = [];
                        foreach ($questions as $question) {
                            if ($_SESSION['pi_erp'] < '420' || $_SESSION['pi_erp'] === '430') {
                                $subjects[] = $question['subject_en'];
                            }
                            elseif ($_SESSION['pi_erp'] <= '600'){
                                $subjects[] = $question['subject_fr'];
                            }
                            elseif ($_SESSION['pi_erp'] < '800') {
                                $subjects[] = $question['subject_zh'];
                            }
                            $subjects[] = $question['subject_en']; // @TODO remove
                        }

                        $subjects = array_unique($subjects);
                        $_SESSION['QuestionsGroup'] = "<option  name='QuestionsGroup' value=''></option>";
                        foreach ($subjects as $subject) {
                            $_SESSION['QuestionsGroup'] .= "<option name='QuestionsGroup' value='". $subject ."'>". $subject ."</option>";
                        }

                        // Check tolerance
                        foreach ($questions as $key => $question) {
                            $error = 'NO';
                            // response out of tolerance
                            if ($question['answer_min'] != '' && $question['answer_max'] != '' && $question['answer'] != '' && ((float) $question['answer'] < (float) $question['answer_min'] || (float) $question['answer'] > (float) $question['answer_max'])) {
                                $error = 'YES';
                            }
                            // negative response on Y/N question
                            if ($question['answer_type'] === 'YES/NO' && $question['answer'] === 'NO') {
                                $error = 'YES';
                            }

                            if ($error === 'YES') {
                                if ($question['derogation'] != '') {
                                    $questions[$key]['toleranceMsg'] = _('Answer out of tolerance + derogation');
                                } else {
                                    $questions[$key]['toleranceMsg'] = _('Answer out of tolerance');
                                }
                            }
                        }
                        $body = include("$PATH/inspection.multiple.pi.tpl.php");
                        break;

                    case 'alert':
                        $pi_inspect = new Inspect($client, $session);
                        $piQuestions = $pi_inspect->getPIInspect(
                            $_SESSION['pi_opno'],
                            $_SESSION['pi_sn'],
                            $_SESSION['pi_erp'],
                            $_SESSION['pi_cprj']
                        );
                        $body = include("$PATH/alert.pi.tpl.php");
                        break;

                    case 'saveAlert':
                        $pi_inspect = new Inspect($client, $session);
                        $piQuestions = $pi_inspect->getPIInspect(
                            $_SESSION['pi_opno'],
                            $_SESSION['pi_sn'],
                            $_SESSION['pi_erp'],
                            $_SESSION['pi_cprj']
                        );

                        $pi_inspect->postPIAlert($piQuestions[$_SESSION['inspectionLine']]['id'], $_POST['piAlert']);
                        header("location:/shop/autoselect.php?m[0]=pi&m[1]=form&m[2]=inspection&m[3]=display");
                        break;

                    case 'answerMultiple':
                        $ajaxResponse = null;
                        $result = null;
                        $answer =  mb_convert_encoding(trim(TldDatabase::escape($_POST['answer'])), "HTML-ENTITIES");
                        $idQuestion = mb_convert_encoding($_POST['idQuestion'], "HTML-ENTITIES");
                        $answerComponent =  TldDatabase::escape($_POST['answerComponent']);
                        $answerModel =  mb_convert_encoding(TldDatabase::escape($_POST['answerModel']),"HTML-ENTITIES");
                        $answerSerial =  mb_convert_encoding(TldDatabase::escape($_POST['answerSerial']),"HTML-ENTITIES");
                        $answerBrand =  mb_convert_encoding(TldDatabase::escape($_POST['answerBrand']),"HTML-ENTITIES");

                        //get the question by id
                        $queryGetQuestion=<<<SQL
                            SELECT id,
                            parent_id,
                            answer_type,
                            answer_min,
                            answer_max
                            FROM pi_questions_unit
                            WHERE id='$idQuestion';
SQL;

                        $question = tldUtils::getSqlRowToAssocArray($queryGetQuestion);
                        if(is_string($result) || (!$question)){
                            echo "Question not found";
                            exit;
                        }
                        //format decimal answer
                        if($question['answer_type'] === 'Decimal'){
                            $answer = str_replace(",",".",$answer);
                        }

                        $_SESSION['inspectionMessage'] = "";
                        // Check tolerance
                        $error = false;
                        // response out of tolerance
                        if ($question['answer_min'] !== ''
                            && $question['answer_max'] !== ''
                            && $answer !== ''
                            && ((float)$answer < (float)$question['answer_min']
                                || (float)$answer > (float)$question['answer_max']
                            )) {
                            $error = true;
                        }
                        // negative response on Y/N question
                        if ($question['answer_type'] === 'YES/NO' && $answer === 'NO') {
                            $error = true;
                        }
                        if ($question['answer_type'] === 'S/N' && $answer === '') {
                            $error = true;
                        }

                        if ($error) {
                            $question['toleranceMsg'] = _('Answer out of tolerance');
                            // Save response
                            //this function should be a static function
                            $pi_postAnswer = new Inspect($client, $session);
                            $result = $pi_postAnswer->postPIAnswer(
                                $opController,
                                $question['id'],
                                $answer,
                                $_SESSION['pi_user_id']
                            );
                            // If user is not pi_DER, create CRAB
                            $user = User::fromId($_SESSION['pi_user_id']);
                            if (!$user->isInGroups(["pi_DER"])) {
                                // Create Crab
                                unset($_SESSION['pi_data']);
                                $a = [
                                    'unit'        => $_SESSION['pi_sn'],
                                    'comp'        => $_SESSION['pi_erp'],
                                    't_cprj'      => $_SESSION['pi_cprj'],
                                    't_pdno'      => $_SESSION['pi_pdno'],
                                    't_opno'      => $_SESSION['pi_opno'],
                                    't_item'      => '',
                                    'model'       => $_SESSION['pi_family'],
                                    'question_id' => $question['id'],
                                    'question_parent_id' => $question['parent_id'],
                                    'user_id'     => $_SESSION['pi_user_id'],
                                    'baan_employee_id' => $user->getUser()['baan_employee_id'],
                                ];
                                $_SESSION['pi_data'] = $a;

                                // Fill standard CRAB session variables
                                $sess['er']['id'] = $_SESSION['pi_snid'];
                                $sess["cbom_id"] = $_SESSION['pi_cprj'];

                                if (!is_string($result) && $result !== null) {
                                    $ajaxResponse = "CRAB";
                                }

                            } else if (!is_string($result) && $result !== null) {
                                $ajaxResponse = "DEROGATION";
                            }

                        } else {
                            // $error=='NO'
                            if ($question['answer_type'] !== 'S/N') {
                                $answerComponent = '';
                                $answerModel = '';
                                $answerSerial = '';
                                $answerBrand = '';
                            }

                            $pi_postAnswer = new Inspect($client, $session);
                            $result = $pi_postAnswer->postPIAnswer(
                                $opController,
                                $question['id'],
                                $answer,
                                $_SESSION['pi_user_id'],
                                $answerComponent,
                                $answerModel,
                                $answerSerial,
                                $answerBrand
                            );
                        }
                        if($ajaxResponse === null) {
                            if (is_string($result) || $result === null) {
                                $ajaxResponse = 'ERROR: answer not inserted';
                            } else {
                                //disable old answer
                                $ajaxResponse = 'OK';
                            }
                        }
                        echo $ajaxResponse;
                        exit;

                    case 'createDerogation':
                        $pi_inspect = new Inspect($client, $session);
                        $piQuestions = $pi_inspect->getPIInspect(
                            $_SESSION['pi_opno'],
                            $_SESSION['pi_sn'],
                            $_SESSION['pi_erp'],
                            $_SESSION['pi_cprj']
                        );
                        $body = include("$PATH/derogation.pi.tpl.php");
                        break;

                    case 'derogation':
                        $derogationNumber = TldDatabase::escape($_POST['derogationNumber']);
                        $derogationIdQuestion = TldDatabase::escape($_POST['derogationIdQuestion']);
                        $template="NO_TEMPLATE";

                        if ($derogationNumber !== '' && isset($_POST['derogationIdQuestion'])) {
                            $pi_postDerogation = new Inspect($client, $session);
                            $pi_postDerogation->postPIDerogation(
                                $derogationIdQuestion,
                                $derogationNumber,
                                $_SESSION['pi_user_id']
                            );
                            echo "OK";
                        }else{
                            $query = <<<SQL
                              DELETE FROM pi_answers WHERE parent_id='$derogationIdQuestion' 
                              ORDER BY id DESC
                              limit 1;
SQL;
                        tldUtils::sqlExecute($query);
                        }
                        exit;
                }
                break;

            case 'crabs':
                $zMenu_crabs = '';
                $user = User::fromId($_SESSION['pi_user_id']);
                if (!$user->isInGroups(["pi_OPERATOR"])) {
                    header("location:/shop/autoselect.php?m[0]=pi&m[1]=form&m[2]=ident");
                    break;
                }
                if ($_SESSION['pi_sn'] == '') {
                    header("location:/shop/autoselect.php?m[0]=pi&m[1]=form&m[2]=unit");
                    break;
                }

                // Get CRABS & related pi questions information.
                $query = <<<SQL
SELECT pce.*, 
       c.dsca as descCrab, c.status, c.dt, c.fix_dt, c.insp_dt,
       pqu.subject_en, pqu.subject_zh, pqu.subject_fr, pqu.desc_en, pqu.desc_zh, pqu.desc_fr
FROM pi_crab_eap pce
LEFT JOIN crabs c on c.id=pce.parent_id
LEFT JOIN pi_questions_unit pqu on pqu.id=pce.question_id
WHERE pce.unit='{$_SESSION['pi_sn']}' AND pce.comp={$_SESSION['pi_erp']} AND pce.t_cprj='{$_SESSION['pi_cprj']}' AND pce.t_pdno='{$_SESSION['pi_pdno']}' ORDER BY t_opno ASC, DATE DESC
SQL;
                $crabs = tldUtils::getSqlToAssocArray($query);
                $items = [];

                try {
                    $crabsIdsString = array_column($crabs, 't_item');
                    if ('' !== ($crabsItemsFilter = implode('|', $crabsIdsString))) {
                        $items = ($client->request(
                            'GET',
                            sprintf('ion/items?site=%s&itemFilter=%s&itemFilterMethod=Equals', $_SESSION['pi_erp'], $crabsItemsFilter),
                        ))->toArray();

                        $items = $items['hydra:member']?? [];
                    }
                } catch (\Exception $exception) {
                    error_log(sprintf('Cannot find itemsBySite : %s', $exception->getMessage()));
                    $session->getFlashBag()->add('error', $exception->getMessage());
                }

                $pi_TaskDesc = new Routing($client, $session);
                foreach ($crabs as $key => &$crab){
                    $itemId = $crab['t_item'];

                    $item = array_filter($items, static function ($item) use ($itemId) {
                        return $item['item'] === $itemId;
                    });

                    if ($crab['t_opno'] !== '') {
                        $crab['t_dsca'] = $pi_TaskDesc->getPITaskDescFromOpno($crab['t_opno']);
                    }
                    $crab['t_dscb'] = (reset($item))['itemDescription'] ?? null;
                    $crab['dt'] = (int) $crab['dt'] !== 0 ? substr($crab['dt'], 0, 10) : '';
                    $crab['fix_dt'] = (int) $crab['fix_dt'] !== 0 ? substr($crab['fix_dt'], 0, 10) : '';
                    $crab['insp_dt'] = (int) $crab['insp_dt'] !== 0 ? substr($crab['insp_dt'], 0, 10) : '';
                    $crab['status'] = (int) $crab['fix_dt'] !== 0  && $crab['status'] === 'PENDING' ? 'FIXED' : $crab['status'];

                    // Add question description
                    switch ($_SESSION['pi_erp']) {
                        case ((int) $_SESSION['pi_erp'] < 420 || (int) $_SESSION['pi_erp'] === 430 || (int) $_SESSION['pi_erp'] >= 800):
                            $language = 'en';
                            break;
                        case ((int) $_SESSION['pi_erp'] > 600):
                            $language = 'zh';
                            break;
                        default:
                            $language = 'fr';
                    }
                    $crab['subject'] = $crab['subject_'.$language];
                    $crab['description'] = $crab['desc_'.$language];
                }
                unset($crab);

                // Images
                $images = null !==( $crab ?? null) ? tldModFile::byParent($crab['parent_id'], 'CRAB', 1) : [];
                if(!empty($images)){
                    foreach($images as $image){
                        $linkTitle = !empty($image['description']) ? $image['description'] : $image['filename'];
                        $crab['images'] .= "<a target='_blank' href='/shop/autoselect.php?_qf__frmER=&m[0]=crab&m[1]=image&id={$image['id']}'>{$linkTitle}</a><br/>";
                    }
                }

                $body = include("$PATH/crabs.pi.tpl.php");
                break;

            case 'crab':
                $zMenu_crab = '';
                $user = User::fromId($_SESSION['pi_user_id']);
                if (!$user->isInGroups(["pi_OPERATOR"])) {
                    $body .= "<script type='text/javascript'> window.close(); </script> ";
                    break;
                }
                if ($_SESSION['pi_sn'] == '') {
                    $body .= "<script type='text/javascript'> window.close(); </script> ";
                    break;
                }

                unset($_SESSION['pi_data']);
                $a = [
                    'unit'        => $_SESSION['pi_sn'],
                    'comp'        => $_SESSION['pi_erp'],
                    't_cprj'      => $_SESSION['pi_cprj'],
                    't_pdno'      => $_SESSION['pi_pdno'],
                    't_opno'      => $_SESSION['pi_opno'],
                    't_item'      => $_SESSION['pi_sitm'],
                    'model'       => $_SESSION['pi_family'],
                    'question_id' => '',
                    'user_id'     => $_SESSION['pi_user_id'],
                ];
                $_SESSION['pi_data'] = $a;

                // Fill standard CRAB session variables
                $sess['er']['id'] = $_SESSION['pi_snid'];
                $sess['cbom_id'] = $_SESSION['pi_cprj'];

                if ($_SESSION['pi_sitm'] != '') {
                    $sess["history"][] = $_SESSION['pi_sitm'];
                    header("location:/shop/autoselect.php?m[0]=crab&m[1]=new&pn={$_SESSION['pi_sitm']}");
                } else {
                    header("location:/shop/autoselect.php?m[0]=crab&m[1]=new&uid={$_SESSION['pi_user_id']}");
                }
                break;

            case 'ncr':
                $zMenu_ncr = '';
                $user = User::fromId($_SESSION['pi_user_id']);
                if (!$user->isInGroups(["pi_OPERATOR"])) {
                    header("location:/shop/autoselect.php?m[0]=pi&m[1]=form&m[2]=ident");
                    break;
                }
                if ($_SESSION['pi_sn'] == '') {
                    header("location:/shop/autoselect.php?m[0]=pi&m[1]=form&m[2]=unit");
                    break;
                }

                $body .= "<script type='text/javascript'>
                        let ncrTab = window.open();
                        ncrTab.location.href = '/shop/autoselect.php?m[0]=ncr'; 
                    </script> ";
                $body .= "<script type='text/javascript'> window.open('".$_SERVER['HTTP_REFERER']."', '_self'); </script> ";

                break;

            case 'indirectTransact':
                $zMenu_indirectTransact = '';
                $user = User::fromId($_SESSION['pi_user_id']);
                if (!$user->isInGroups(["pi_OPERATOR"])) {
                    header("location:/shop/autoselect.php?m[0]=pi&m[1]=form&m[2]=ident");
                    break;
                }
                if ($_SESSION['pi_sn'] == '') {
                    header("location:/shop/autoselect.php?m[0]=pi&m[1]=form&m[2]=unit");
                    break;
                }

                // List indirect tasks
                $pi_user_employee = new tldUser($_SESSION['pi_user_id']);

                $whereClause = '';
                switch ($_SESSION['pi_erp']) {
                    case '400':
                    case '410':
                        $whereClause = 't_tano NOT IN (5300, 9000, 9001, 9002) ';
                        break;
                    case '420':
                        $whereClause = 't_tano NOT IN (1, 97, 98, 99) ';
                        break;
                    case '500':
                    case '510':
                    case '520':
                    case '540':
                    case '570':
                    case '220':
                    default:
                        $whereClause = 't_tano NOT IN (8000, 8001, 8002, 9000) ';
                        break;
                }

                $emno = $pi_user_employee->getBannEmployeeID();
                $erpNum = $_SESSION['pi_erp'];

                try {
                    $tasks = $client->findBy('/ion/tasks');
                } catch (\Exception $exception) {
                    error_log(sprintf('Error when trying to fetch tasks from Ion: [%s] %s', $exception->getCode(), $exception->getMessage()));
                    $DEFAULT_ERROR[] = "ERROR: Indirect Tasks not found.";
                }

                switch ($m[3]) {
                    case 'display':
                        $body = include("$PATH/indirectTransact.pi.tpl.php");
                        break;
                    case 'punchOut':
                        $_SESSION['pi_opno'] = '';
                        $_SESSION['pi_indirect'] = $task;
                        $_SESSION['pi_tano_dsca'] = $taskdesc;

                        // Time keeping: manage time offset
                        $HRADateTime = new DateTime();

                        switch ($_SESSION['pi_erp']) {
                            case '500':
                            case '510':
                            case '520':
                            case '540':
                                $HRADateTime->add(new DateInterval('PT6H'));
                                break;
                            case '600':
                            case '620':
                            case '640':
                                $HRADateTime->add(new DateInterval('PT12H'));
                                break;
                        }

                        $HRADate = $HRADateTime->format('Y-m-d');
                        $HRATime = $HRADateTime->format('H:i:s');
                        $_SESSION['pi_indirect_start'] = $HRATime.'-'.$_SESSION['pi_erp'];

                        // Time keeping: log transaction
                        $pi_time_keeping = new Routing($client, $session);
                        $pi_time_keeping->postLogTimeKeeping(
                            $_SESSION['pi_erp'],
                            $_SESSION['pi_user_id'],
                            '',
                            '',
                            $task,
                            'INDIRECT'
                        );

                        try {
                            $transaction = $client->save('/ion/time_keepings', [
                                'employeeNumber' => (string) $client->getUserId(),
                                'transactionType' => 'INDIRECT',
                                'task' => (string) $task,
                                'comment' => 'timekeeping PIO'
                            ]);
                            foreach ($transaction['lines'] as $transaction) {
                                $session->getFlashBag()->add('success', $translator->trans('pio.transaction_posted', [], 'pio'));
                                $session->getFlashBag()->add('success', $translator->trans('pio.success_transaction_message', [
                                    '%employeeNumber%' => $transaction['employeeNumber'],
                                    '%transactionType%' => $transaction['transactionType'],
                                    '%task%' => $transaction['task'],
                                    '%status%' => $transaction['status'],
                                ], 'pio'));
                            }
                        } catch (\Exception $exception) {
                            error_log(sprintf('Timekeeping from PIO on indirect task error: [%s] %s', $exception->getCode(), $exception->getMessage()));
                            $session->getFlashBag()->add('error', $translator->trans('pio.error_transaction_message', [], 'pio'));
                            $session->getFlashBag()->add('error', $exception->getMessage());
                        }

                        header("location:/shop/autoselect.php?m[0]=pi&m[1]=form&m[2]=operation");
                        break;
                }
                break;

            case 'erFiles':
                if (!($_SESSION['pi_snid'] ?? null)) {
                    header("location:/shop/autoselect.php?m[0]=pi&m[1]=form&m[2]=unit");
                    break;
                }
                $user = User::fromId($_SESSION['pi_user_id'] ?? 0);
                if (!$user->isInGroups(["pi_OPERATOR"])) {
                    header("location:/shop/autoselect.php?m[0]=pi&m[1]=form&m[2]=ident");
                    break;
                }
                $id = $_SESSION['pi_snid'];
                $body = include("$PATH/erFiles.pi.tpl.php");
                $form= new HTML_QuickForm('erFile');
                $form->addElement('hidden', 'm[0]', 'pi');
                $form->addElement('hidden', 'm[1]', 'form');
                $form->addElement('hidden', 'm[2]', 'erFiles');
                $form->addElement('hidden', 'id', $id);
                $form->addElement('header', 'title', _('Upload files')." {$_SESSION['pi_sn']}");
                for ($i = 0; $i < 5; $i++) {
                    $form->addElement('textarea', "description[$i]", _('File Description'), ['wrap' => 'VIRTUAL', 'cols' => '60', 'rows' => '4']);
                    $form->addElement('file', "files[$i]", _('TLD File'));
                }
                $form->addElement('submit', 'btnSubmit', _('Submit'));

                if (!$form->validate()) {
                    $body .= $form->toHTML();
                    break;
                }
                $vars = tldUtils::cleanupFormInput($form->exportValues());
                $equipment = new tldEquipment($id);
                for ($i = 0; $i < 5; $i++) {
                    $file = $form->getElement("files[$i]");
                    $fileArray = $file->getValue();
                    if (!$fileArray['tmp_name']) {
                        continue;
                    }
                    $vars['filename'] = $fileArray['name'];
                    $e = tldModFile::insert(
                        [
                            'module' => 'ER',
                            'parent_id' => $id,
                            'description' => $vars['description'][$i] ?: 'Posted from the PIO',
                            'filename' => $vars['filename'],
                            'poster' => $user->getId(),
                        ],
                        $fileArray
                    );

                    if (is_string($e)) {
                        $body .= "ERROR: Problem adding the TLD File in the database...<br/> $e";
                        break;
                    }
                    $body .= "TLD File#$e uploaded and successfully attached to ER#{$_SESSION['pi_snid']}.<br/>";
                }
                break;
        }
        break;

    default:
        $body = include("$PATH/operation.pi.tpl.php");
        break;
}

// Hide every button
unset($buttons);
$body .= "<script type='text/javascript'> $( '.MnuOperation' ).css    ( 'visibility' , 'hidden' ); </script> ";
$body .= "<script type='text/javascript'> $( '.MnuParts' ).css        ( 'visibility' , 'hidden' ); </script> ";
$body .= "<script type='text/javascript'> $( '.MnuDocumentation' ).css( 'visibility' , 'hidden' ); </script> ";
$body .= "<script type='text/javascript'> $( '.MnuInspect' ).css      ( 'visibility' , 'hidden' ); </script> ";
$body .= "<script type='text/javascript'> $( '.MnuCrabList' ).css     ( 'visibility' , 'hidden' ); </script> ";
$body .= "<script type='text/javascript'> $( '.MnuCrab' ).css         ( 'visibility' , 'hidden' ); </script> ";
$body .= "<script type='text/javascript'> $( '.MnuNCR' ).css          ( 'visibility' , 'hidden' ); </script> ";

if ($m[2] === 'operation' || 'erFiles' === $m[2]) {
    if (($_SESSION['pi_opno'] ?? '') === '') {
        $buttons = ["MnuOperation", "MnuDocumentation", "MnuCrabList"];
    } else {
        $buttons = ["MnuOperation", "MnuDocumentation", "MnuCrabList", "MnuParts", "MnuInspect", "MnuCrab"];
    }
}

if ($m[2] === 'warehouse') {
    $buttons = ["MnuOperation"];
}

if ($m[2] === 'parts') {
    if ($_SESSION['pi_sitm'] == '') {
        $buttons = ["MnuOperation", "MnuParts", "MnuDocumentation", "MnuInspect", "MnuCrabList"];
    } else {
        $buttons = ["MnuOperation", "MnuParts", "MnuDocumentation", "MnuInspect", "MnuCrabList", "MnuCrab", "MnuNCR"];
    }
}

if ($m[2] === 'documentation' || $m[2] === 'inspection' || $m[2] === 'crabs') {
    $buttons = ["MnuOperation", "MnuParts", "MnuDocumentation", "MnuInspect", "MnuCrabList", "MnuCrab"];
}

if ($m[2] === 'indirectTransact') {
    $buttons = ["MnuOperation"];
}

if (isset($buttons) && is_array($buttons)) {
    foreach ($buttons as $value1) {
        $body .= "<script type='text/javascript'> $( '.$value1' ).css( 'visibility' , 'visible' ); </script> ";
    }
}

// Manage CRABS button color: red if some open CRABs, else green
if (($_SESSION['pi_sn'] ?? '') !== '') {
    $pi_crab = new Routing($client, $session);
    $a = $pi_crab->getPIOpenCRABSfromUnit($_SESSION['pi_erp'], $_SESSION['pi_cprj'], $_SESSION['pi_pdno']);
    $color = 0 === (int) $a ? '#00FF00' : '#FF0000';
    $body .= "<script type='text/javascript'> $( '.TblCrabList' ).css( 'background-color' , '$color' ); </script> ";
}
