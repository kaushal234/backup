<?php

use ApiBundle\Client;
use Symfony\Component\HttpClient\Exception\ClientException;

$DEFAULT_TITLE .= "\Publications";

if ($user->isInGroup(['gg_SUPPORT', 'gg_ENG'])) {
    $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;<a href="$php_self?m[0]=equipment&m[1]=view&m[2]=publications&m[3]=testcbomtransfer&id=$id&erp=$ERP&sn=${header['t_prno']}&date=$date"
	title="Test CBOM Transfer to Online Manuals System">Test CBOM Transfer</a>
EOF;
}

$isPublishable = $eq->isPublishable();
$CBOMTEST_ERROR = $CBOMTEST_FATAL_ERROR = '';

$DEFAULT_MENU .= _getPubsSubMenus();

switch ($m[3]) {
    case 'testcbomtransfer':
        $body = "This page has been migrated and should not be displayed anymore.";
        //<br><font color="#FF0000"> You are about to test the transfer to manual for this CBOM.<br>
//This might take some time depending on the CBOM Size, File number and size.<br>
//Please click <a href="${_SERVER['REQUEST_URI']}&m[4]=conf&lang=EN">here</a> to confirm and start the test.<br><br>
//Important note: this test will use the CBOM as of the Ship Date of the ER, if not available the current date will be used.<br>
//EOF;


//            $body .= <<<EOF
//                The ER was successfully set to Publishable.
//                If you would like to Publish it please click <a href="$php_self?m[0]=equipment&m[1]=view&m[2]=publications&m[3]=publish&id=$id&erp=$ERP&sn={$header['t_prno']}&date=$date&lang=$lang"
//                title="Publish">Here</a><br>
//            EOF;

//            $body .= <<<EOF
//<font color="#FF0000">FATAL ERROR: there was 1 of more Fatal error during this process, they need to be fixed before the Process can continue.<br>
//Please fix them and run this test again.<br>
//$CBOMTEST_FATAL_ERROR<br>


        // if there are warnings, send them to the screen and log file
//        if ($CBOMTEST_ERROR && !$isPublishable) {
//            $body .= <<<EOF
//<font color="#FF0000">There were some Warnings during this test that require your attention.<br>
//This ER is currently not Publishable, if you would like to set this ER to Publishable anyway please click <a href="$php_self?m[0]=equipment&m[1]=view&m[2]=publications&m[3]=setpublishable&id=$id&erp=$ERP&sn=${header['t_prno']}&date=$date"
//title="Make Publishable">Here</a><br>
//$CBOMTEST_ERROR
//EOF;

//            $body .= <<<EOF
//<font color="#FF0000">There were some Warnings during this test that require your attention.<br>
//This ER is already Publishable, if you would like to Publish its manual anyway please click on the link "Publish"<br>
//$CBOMTEST_ERROR
//EOF;
        break;

    case 'setpublishable':
        $body = "This page has been migrated and should not be displayed anymore.";
        break;

    case 'publish':
        $body = "This page has been migrated and should not be displayed anymore.";
        //            $body .= <<<EOF
//This ER is currently not Publishable. In order to be change to Publishable, this ER s CBOM need to be tested first.<br>
//Please click <a href="$url" title="Test CBOM Transfer to Online Manuals System">here</a> to test it.<br>
//EOF;
        break;

//            $body .= <<<EOF
//You are about to publish a CBOM manual for this equipment.<br>
//This ER have already been tested at least once but might have been changed since.<br>
//If you are sure this ER does not need to be tested again, Please select the Type and Language for this manual:
//<br><br>
//EOF;

//        if (!empty($vars['otherEquipments'])) {
//            foreach (explode(',', $vars['otherEquipments']) as $possibleRange) {
//                $matches = [];
//                /**
//                 * T15156-T15176 => will become [0 => T15156-T15176, 'sn1' => T15156, 1 => T15156, 'id1' => 15156, 2 => 15156, 'sn2' => T15176, 3 => T15176, 'id2' => 15176), 4 => 15176]
//                 * T15156 => will become [0 => T15156, 'sn1' => T15156, 1 => T15156, 'id1' => 15156]
//                 */
//                if (!preg_match('/^(?P<sn1>T(?P<id1>\d{5}))(?:-(?P<sn2>T(?P<id2>\d{5})))?$/', $possibleRange, $matches)) {
//                    $DEFAULT_ERROR[] = "ERROR: '$possibleRange' is an incorrect value";
//                    break;
//                }
//                // Single SN
//                if (!isset($matches['sn2'])) {
//                    if ($matches['sn1'] === $eq->getSN()) {
//                        // Already the root SN
//                        continue;
//                    }
//                    $sns[] = $matches['sn1'];
//                    continue;
//                }
//                if (($start = (int)$matches['id1']) >= ($end = (int)$matches['id2'])) {
//                    $DEFAULT_ERROR[] = sprintf("ERROR: There was a in splitting the range '%s', %d is not strictly inferior to %d.", $possibleRange, $start, $end);
//                    break;
//                }
//
//                for ($i = $start; $i <= $end; $i++) {
//                    if ("T$i" === $eq->getSN()) {
//                        // Already the root SN
//                        continue;
//                    }
//                    $sns[] = "T$i";
//                }
//            }
//
//            if (!$sns) {
//                $DEFAULT_ERROR[] = 'ERROR: No valid ERs were passed.';
//                break;
//            }
//
//            if (($total = count($sns)) !== count(array_unique($sns))) {
//                $DEFAULT_ERROR[] = 'ERROR: Some serial numbers were specified more than once, please double check.';
//                break;
//            }
//
//            if ($total > $max = 30) {
//                $DEFAULT_ERROR[] = "ERROR: This feature is limited to $max equipment record maximum for performance reason.";
//                break;
//            }
//
//            $ers = tldEquipment::bySNs($sns);
//            if ($total !== count($ers)) {
//                $DEFAULT_ERROR[] = 'ERROR: Some serial numbers used were not found or are not unique.';
//                break;
//            }
//
//            try {
//                $ersIds = array_column($ers, 'id');
//                $apiEquipmentsResult = $client->findBy('equipment_records', ['legacyId' => $ersIds]);
//                $apiEquipmentsResultLegacyIds = array_column($apiEquipmentsResult->getSimpleArrayCopy(), 'legacyId');
//
//                if ($diff = array_diff($ersIds, $apiEquipmentsResultLegacyIds)) {
//                    $diffIdsList = implode(', ', $diff);
//                    $DEFAULT_ERROR[] = "ERROR: ERs with ID $diffIdsList are not yet processed by the system. Please try again tomorrow and open a ticket if problem persist";
//                    break;
//                }
//
//                foreach ($apiEquipmentsResult->getSimpleArrayCopy() as $apiEquipmentResult) {
//                    $apiEquipments[$apiEquipmentResult['legacyId']] = $apiEquipmentResult;
//                }
//            } catch (Exception $exception) {
//                $DEFAULT_ERROR[] = "ERROR: Some ERs is not yet processed by the system. Please try again tomorrow and open a ticket if problem persist";
//                break;
//            }

//            foreach ($ers as $er) {
//                if ($header['man_location'] !== $er['man_location']) {
//                    $DEFAULT_ERROR[] = "ERROR: The ER {$er['sn']} doesn't belongs to the same manufacturing location (ERP) as ER {$header['sn']}";
//                    $error = true;
//                    continue;
//                }
//                if ($header['type'] !== $er['type'] || $header['model'] !== $er['model'] || $header['options_desc'] !== $er['options_desc']) {
//                    $DEFAULT_ERROR[] = "ERROR: The ER {$er['sn']} doesn't match in type/model/option description with ER {$header['sn']}";
//                    $error = true;
//                    continue;
//                }
//
//                $equipments[$er['sn']] = [
//                    'er' => $er['id'],
//                    'tldEquipment' => null,
//                    'cbom' => $er['t_prno'],
//                    'tldCBOM' => new tldCBOM($erp, $date, $er['t_prno'], ['lang' => $langSelected]),
//                    'manual' => null,
//                    'manualId' => null,
//                    'document' => null,
//                    'documentId' => null,
//                    'iri' => $apiEquipments[$er['id']]['@id'],
//                ];
//
//            }
        break;

    case 'cdrom':
        $body .= <<<EOF
<br><font color="#FF0000"> WARNING: You are not supposed to use this downloading function for anything else that providing a supplemental CD Rom manual to the customer of this specific piece of equipment.
Downloading full manuals on computers hard drives is strictly prohibited, no matter if they are TLD computers or personal computers.
By continuing this process, you should also know that a formal notification of your action will be automatically sent to the TLD PSM's and to your supervisor.
Are you sure you want to proceed?.<br>
Please click <a href="/en/private/product_support/publications/zipped.php?eqid=$id">here</a> to confirm.<br>
EOF;
        break;
    default:
        $manuals = $eq->getManuals();
        if (count($manuals) === 1) {
            $body .= "<a href=\"$php_self?m[0]=equipment&m[1]=view&m[2]=publications&m[3]=cdrom&id=$id\">CDROM Equipment Manual (includes Schematics for this unit)</a>";
            $manual_id = $manuals[0]['id'];
            if ($user->isInGroup(['gg_ADMIN', 'role_PSM', 'role_PSE', 'role_PSA', 'role_planner', 'GG_PARTS'])) {
                $body .= "<br><a href=\"$php_self?m[0]=publications&m[1]=manuals&m[2]=view&m[3]=evendor&id=$manual_id&eqid=$id\">eVendor Printing</a>";
            }

            $body .= "<br /><br /><a href=\"$php_self?m[0]=publications&m[1]=manuals&m[2]=form&id=$manual_id&eqid=$id&drivingManual=true\">Combine standard manual PDF (Beta)</a>";
            $body .= "<br /><a href=\"$php_self?m[0]=publications&m[1]=manuals&m[2]=form&id=$manual_id&eqid=$id&\">Combine full manual PDF (Beta)</a>";

        }
        $form = new tldReportColumnar(
            $manuals,
            [
                'xItems' => [
                    'id' => 'Manual ID#',
                    'date' => 'Published Date',
                    'lang' => 'Language',
                    'status' => 'Status',
                    'description' => 'Description',
                    'features' => 'Features',
                ],
                'title' => 'Manuals',
                'links' => ['id' => "/en/private/product_support/index.ps.php?m[0]=publications&m[1]=manuals&m[2]=view&eqid=$id&id="],
            ]
        );
        $body .= $form->fetch();

        $er_upgrades = tldEquipment_Upgrade::getUpgradesByER($id);
        $form2 = new tldReportColumnar(
            $er_upgrades,
            [
                'xItems' => [
                    'id' => 'ID#',
                    'poster_fullname' => 'Poster',
                    'dt_open' => 'Created on',
                    'dt_upgrade' => 'Effective Upgrade Date',
                    'description' => 'Description',
                    'mod_link' => 'File',
                ],
                'title' => 'ER Upgrades',
                'links' => [
                    'mod_link' => [
                        'url' => '/en/private/common/index.php?m[0]=files&m[1]=view&id=',
                        'params' => ['id' => 'mod_file'],
                    ],
                    'id' => [
                        'url' => "$php_self?m[0]=er_upgrade&m[1]=view",
                        'params' => ['id' => 'id'],
                    ],
                ],
            ]
        );
        $body .= '<br>';
        $body .= $form2->fetch();
        break;
}
