<?php
include_once('common.inc.php');

$DEFAULT_TITLE .= ' \PIO Family Admin';
$user = new tldUser($_SERVER['PHP_AUTH_USER']);

global $kernel;
$authorizationChecker = $kernel->getContainer()->get('security.authorization_checker.legacy');
$isMOO = $authorizationChecker->isGranted('MOO_PI');

if (!$user->isInGroup(['superuser']) && !$isMOO) {
    $DEFAULT_ERROR[] = 'Sorry you cannot access to this page';
    return;
}

$factories = [
    400 => 'TLD WIN',
    410 => 'TLD WIM',
    420 => 'TLD SHE',
    430 => 'TLD WOL',
    500 => 'TLD MTL',
    510 => 'TLD DTV',
    520 => 'TLD STL',
    640 => 'TLD SHA',
    660 => 'TLD WUX',
    570 => 'TLD LEB',
    250 => 'AERO Specialties',
    220 => 'TLD PV',
    820 => 'TLD MNI'
];

switch ($m[2]) {
    case 'add':
        $form = new HTML_QuickForm('AddPIOFamily');
        $form->addElement('header', 'title', 'ADD PIO FAMILY');
        $form->addElement('hidden', 'm[0]', 'pi');
        $form->addElement('hidden', 'm[1]', 'familyManager');
        $form->addElement('hidden', 'm[2]', 'add');
        $form->addElement('text', 'familyName', 'Family Name', ['placeholder' => 'e.g. TMX-150-E']);
        $form->addElement('header', 'title', 'Factory(ies)');
        foreach ($factories as $idBu => $factoryName) {
            $form->addElement('checkbox', 'factories[' . $idBu . ']', $factoryName);
        }

        $form->addRule('familyName', 'Required', 'required');
        $form->addElement('submit', 'btnSubmit', 'Submit');

        if ($form->validate()) {
            //create
            $valuesAddFamily = tldUtils::cleanupFormInput($form->exportValues());
            if (!isset($valuesAddFamily['factories'])) {
                $DEFAULT_ERROR[] = 'Please select a factory.';
                $body = $form->toHTML();

            } else {
                $valuesAddFamily['familyName'] = trim($valuesAddFamily['familyName']);
                $query = <<<SQL
          INSERT INTO pi_family VALUES (NULL,'{$valuesAddFamily['familyName']}','{$valuesAddFamily['familyName']}','Final');
SQL;
                $errorFamily = tldUtils::sqlInsert($query);
                if (is_string($errorFamily)) {
                    $DEFAULT_ERROR[] = 'Error : Family not created' . $errorFamily;
                }
                $query = <<<SQL
          INSERT INTO pi_family_matrix VALUES 
SQL;
                $valuesToInsert = [];
                foreach ($valuesAddFamily['factories'] as $idBu => $checked) {
                    $valuesToInsert[] = <<<SQL
                (NULL,'{$valuesAddFamily['familyName']}','{$factories[$idBu]}')
SQL;
                }
                $query .= implode(',', $valuesToInsert);
                $errorFactories = tldUtils::sqlInsert($query);

                $errorMessage = 'Error : Family has not been linked to the selected factories';
                if (is_string($errorFamily) || $errorFamily === null) {
                    $DEFAULT_ERROR[] = $errorMessage . $errorFamily;
                } elseif (is_string($errorFactories) || $errorFactories === null) {
                    $DEFAULT_ERROR[] = $errorMessage . $errorFactories;
                } else {
                    $DEFAULT_SUCCESS[] = sprintf('%s Family has been added.', $valuesAddFamily['familyName']);
                    $form->setDefaults([]);
                }
            }
        }
        $body = $form->toHTML();
        break;

    case 'linkFactory':
        $disabled = '';
        $PIFamily = $PIFamily ?? null;
        $formFactories = $formFactories ?? null;

        $PIFamilies = array_column(tldPI::getPiFamilies(['status' => 'Final']), 'family');
        $PIFamilies = array_combine($PIFamilies, $PIFamilies);
        $form = new HTML_QuickForm('UpdatePIOFamily');
        $form->addElement('header', 'title', $PIFamily ? 'Selected Family : ' . $PIFamily : 'Select Family');
        $form->addElement('hidden', 'm[0]', 'pi');
        $form->addElement('hidden', 'm[1]', 'familyManager');
        $form->addElement('hidden', 'm[2]', 'linkFactory');

        if ($PIFamily !== null) {
            $form->addElement('hidden', 'PIFamily', $PIFamily);
            $form->addElement('header', 'title', 'Factories');
            $query = <<<SQL
SELECT factory
FROM pi_family_matrix pfm
WHERE family='{$PIFamily}'
SQL;
            $factoriesWithSelectedFamily = array_column(tldUtils::getSqlToAssocArray($query), 'factory');

            foreach ($factories as $idBu => $factoryName) {
                $box = &$form->addElement('checkbox', 'formFactories[' . $idBu . ']', $factoryName);
                if (in_array($factoryName, $factoriesWithSelectedFamily, true)) {
                    $box->setChecked(true);
                    $box->setAttribute('disabled');
                }
            }

            if ($formFactories) {
                // Check which boxes are new
                $columnFactories = array_column(tldPI::factoriesMapping(), 'code', 'erp');
                $SET = [];
                $INSERT = [];
                foreach ($formFactories as $erp => $formValue) {
                    if (!in_array($factories[$erp], $factoriesWithSelectedFamily, true)) {
                        $SET[] = sprintf('%s = 1', $columnFactories[$erp]);
                        $INSERT[] = "('$PIFamily', '$factories[$erp]')";

                    }
                }

                // Add Factories to Family
                $INSERT = implode(',', $INSERT);
                $insertQuery = <<<SQL
INSERT INTO pi_family_matrix (family, factory)
VALUES $INSERT
SQL;
                $insertResult = tldUtils::sqlQuery($insertQuery);

                // Update Questions
                $SET = implode(',', $SET);
                $updateQuery = <<<SQL
UPDATE pi_questions SET $SET WHERE model='$PIFamily'
SQL;
                $updateResult = tldUtils::sqlQuery($updateQuery);
                if ($insertResult !== null) {
                    $DEFAULT_ERROR[] = sprintf('<b>Error</b> : The selected factories have not been linked to the %s Family', $PIFamily);
                } else {
                    $DEFAULT_SUCCESS[] = sprintf('<b>Success</b> : The selected factories have been linked to the %s Family', $PIFamily);
                }

                if ($updateResult !== null) {
                    $DEFAULT_ERROR[] = sprintf('<b>Error</b> : %s questions have not been activated for the selected factories', $PIFamily);
                } else {
                    $DEFAULT_SUCCESS[] = sprintf('<b>Success</b> : %s questions have been activated for the selected factories', $PIFamily);
                }
                break;
            }
        }

        if (!$formFactories) {
            $form->addElement('select', 'PIFamily', 'P&I Families', ['' => ''] + $PIFamilies);
            $form->addRule('familyName', 'Required', 'required');
        }

        $form->addElement('submit', 'btnSubmit', 'Submit');
        $body = $form->toHTML();
        break;

    default:
        $body .= <<<HTML
<p>Welcome to the family manager</p>
HTML;

        break;
}

$DEFAULT_MENU .= <<<HTML
<br>
<a style="margin-left: 4%" href="$php_self?m[0]=pi&m[1]=familyManager">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=pi&m[1]=familyManager&m[2]=add">Create Family</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=pi&m[1]=familyManager&m[2]=linkFactory">Link Factories</a>
HTML;
