<?php

use ApiBundle\Client;
use GuzzleHttp\Exception\ClientException;

require_once 'PHPExcel.php';
require_once 'PHPExcel/Reader/Excel2007.php';
unset($msgError);
const UPLOAD_OK = 'File uploaded, problem project';
const UPLOAD_FILE_TOO_BIG = 'File is too big (max 36Mo)';
const UPLOAD_NO_FILE = 'No file submitted';
const UPLOAD_SOMETHING_WENT_WRONG = 'Something went wrong';
const FILE_NOT_UPLOADED = 0;
const FILE_NOT_CORRECT = 1;
const SHOW_ERROR = 2;
const INSERT = 3;

// Warning: If you add or remove header on excel, think to change insertion in data base line 477 : actually $numColumn === 35
$headerExcel = [
        'Operation',
        'P/N',
        'Model',
        'Owner',
        'TLD MTL',
        'TLD SHA',
        'TLD SHE',
        'TLD DTV',
        'TLD STL',
        'TLD WIN',
        'TLD WIM',
        'TLD WUX',
        'TLD LEB',
        'AEROSPECIALTIES',
        'TLD PV',
        'TLD MNI',
        'TLD WOL',
        'Subject (EN)',
        'Subject (FR)',
        'Subject (ZH)',
        'Description (EN)',
        'Description (FR)',
        'Description (ZH)',
        'Position',
        'Help (EN)',
        'Help (FR)',
        'Help (ZH)',
        'Picture (EN)',
        'Picture (FR)',
        'Picture (ZH)',
        'Answer Type',
        'Answer Unit',
        'Component (S/N question)',
        'Match List',
        'Answer Max',
        'Answer Min',
        'GT1',
        'GT3',
        'Active',
];

if (isset($_POST['templateButton'])) {

    $objWriter = new PHPExcel_Writer_Excel2007(createTemplateHeader($headerExcel));
    header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="Template.xlsx"');
    $objWriter->save('php://output');
    exit;

}
// first step, no file uploaded
 if ($_FILES['userfile']['error'] === null && !isset($_POST['insertButton'])) {
     if ($user->isInGroup(['role_MPE', 'role_QAM'])) {
         $display = FILE_NOT_UPLOADED;
         include 'insertQuestions/homepage.insertQuestions.tpl.php';
     }

  //the user upload a file.test to know if the file corresponding to rules
 } elseif (!isset($_POST['insertButton']) && !isCorrect($_FILES['userfile'])) {
     $display = FILE_NOT_CORRECT;
     //if not corresponding get a error message.
     $msgError = getUploadMessage($_FILES['userfile']['error']);
     include 'insertQuestions/homepage.insertQuestions.tpl.php';
 } elseif (isset($_POST['insertButton'])) {
     $display = INSERT;
     $toInsert = [];
     //for each row with operation error and/or PN error
     // test if the radio button insert is checked. If it's checked, the line is removed from the error line tab
     // if any radio button is checked for the line, it's not inserted.
     $errorLine = $_SESSION['errorLine'];
     foreach ($errorLine as $index => $err) {
         if ($_POST[$index] === 'insert' && isInsertable($err)) {
             unset($errorLine[$index]);
         }
     }

     foreach ($_SESSION['cells'] as $indent => $val) {
        if (!isset($errorLine[$indent])) {
            $toInsert[] = $indent;
        }
     }

     insertQuestion($toInsert, $_SESSION['cells'], $user);
     createErrorFile($errorLine, $_SESSION['cells'], $headerExcel);
 } else {
     $display = SHOW_ERROR;
     // open file
     $comp = $_POST['companyList'];
     $uploaddir = '/tmp/';
     $uploadfile = $uploaddir.basename($_FILES['userfile']['name']);
     //@TODO: manage error message
     if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
         //  echo "Le fichier est valide, et a �t� t�l�charg�";
     } else {
         // echo "Attaque potentielle par t�l�chargement de fichiers.";
     }

     /////////////////////////////////////////////////////////////////////
     //TEST if the file is xls or xlsx
     /////////////////////////////////////////////////////////////////////
     //fichier xlsx
     $objReader = new PHPExcel_Reader_Excel2007();
     $objReader->setReadDataOnly(true);
     $_SESSION['upload'] = $uploadfile;
     $excel = $objReader->load($uploadfile);

     $sheet = $excel->getSheet(0);
     if (!isTemplate($headerExcel, $sheet)) {
         $msgError = 'Your file differ from the template';
         $display = FILE_NOT_CORRECT;
     } else {
         $_SESSION['sheet'] = $sheet;
         $highestRow = $sheet->getHighestRow();
         $highestColumn = $sheet->getHighestColumn();
         $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
         $cells = [];
         $operations = [];
         $PN = [];
         $positionError = [];
         $answerTypeError = [];
         $unitError = [];
         $ownerError = [];
         $errorLine = [];
         $componentError = [];
         $listUnit = getUnitsList();
         $listComponent = getCompontSN();
         $nbColumn = count($headerExcel);
         for ($iCol = 0; $iCol < $nbColumn; ++$iCol) {
             for ($iRow = 2; $iRow <= $highestRow; ++$iRow) { //begin at 2 because 1 is the title

                 //suivant la colonne
                 $value = trim($sheet->getCellByColumnAndRow($iCol, $iRow)->getValue());
                 $value = mb_convert_encoding($value, 'HTML-ENTITIES', 'UTF-8');
                 $cells[$iRow][$iCol] = $value;
                 switch ($headerExcel[$iCol]) {
                     case 'Operation'://operation
                         // add operation as index and save the lines where it appears
                         $operations[$value][] = $iRow;
                         break;
                     case 'P/N': //PN
                         if ($value !== '') {
                             $pnQuestion = explode(",",$value);
                             foreach ($pnQuestion as $item) {
                                 $item=trim($item);
                                 if($item !=="") {
                                     $PN[$item][] = $iRow;
                                 }
                             }
                         }
                         break;
                     case 'Owner' :
                         if(!in_array($value, ['QCQ','PCQ','ECQ'])){
                            $ownerError[$iRow] = $value;
                             $errorLine[$iRow]['Owner'] = true;
                         }
                         break;
                     //if QCQ then all BU value must be 1
                     case 'TLD MTL' :
                     case 'TLD SHA' :
                     case 'TLD SHE' :
                     case 'TLD DTV' :
                     case 'TLD STL' :
                     case 'TLD WIN' :
                     Case 'TLD WIM' :
                     case 'TLD WUX' :
                     case 'TLD LEB' :
                     case 'AEROSPECIALTIES':
                     case 'TLD PV':
                     case 'TLD MNI':
                         if(in_array($cells[$iRow][3],['QCQ','ECQ'],true)){
                             $cells[$iRow][$iCol] = 1;
                         }
                         break;
                     case 'Position'://position
                         if ($value === '' || preg_match('/^[0-9]+$/', $value) === 0) {
                             $positionError[$iRow] = $value;
                             $errorLine[$iRow]['Position'] = 'Position';
                         }
                         break;
                     case 'Answer Type': //answer type
                         if (!answerTypeValid($value)) {
                             $answerTypeError[$iRow] = $value;
                             $errorLine[$iRow]['Answer Type'] = 'Answer Type';
                         }
                         break;
                     case 'Component (S/N question)' :
                         if (!array_key_exists($value,$listComponent) && $value !=="") {
                             $componentError[$iRow] = $value;
                             $errorLine[$iRow]['Component (S/N question)'] = true;
                         }
                         break;
                     case 'Answer Unit':
                         if(!array_key_exists($value, $listUnit) && $value!==""){
                             $unitError[$iRow] = $value;
                             $errorLine[$iRow]['Answer Unit'] = true;
                         }
                         break;
                     case 'Answer Max':
                     case 'Answer Min':
                         //force value to be a decimal
                         if($value !=='') {
                             $cells[$iRow][$iCol] = (float)str_replace(",", ".", $value);
                         }
                         break;
                     default:
                         break;
                 }
             }
         }

         $_SESSION['nbRow'] = $highestRow;
         $_SESSION['cells'] = $cells;
         $_SESSION['errorLine'] = $errorLine;
         $opError = operationNotValid($operations);
         $pnError = itemNotValid($PN);
     }
     //test if the PN are in baan
     include 'insertQuestions/homepage.insertQuestions.tpl.php';
 }

/**
 * @param $error
 *
 * @return string : error message
 */
function getUploadMessage($error)
{
    switch ($error) {
        case 0:
            return UPLOAD_OK;
        case 1:
        case 2:
            return UPLOAD_FILE_TOO_BIG;
        case 4:
            return UPLOAD_NO_FILE;
        default:
            return UPLOAD_SOMETHING_WENT_WRONG.' (error code'.$error.')';
    }
}

/**
 * verification of the extension file.
 *
 * @param $file
 *
 * @return bool
 */
function isCorrect($file)
{
        //get the extension file
    $fullName = explode('.', $file['name']);
    $fullName[count($fullName) - 1];

    return $_FILES['userfile']['error'] === 0 && $fullName[count($fullName) - 1] === 'xlsx';
}

/**
 * this is a very important function to check the file validity.
 *
 * @param $sheet : excel worksheet
 * @param $header :
 *
 * @return bool
 */
function isTemplate($header, $sheet)
{
    //test the column
    $nbcolumn = count($header);
    for($i = 0; $i < $nbcolumn; ++$i) {
        if (trim($sheet->getCellByColumnAndRow($i, 1)->getValue()) !== $header[$i]) {
            return false;
        }
    }
    return true;
}


function getUnitsList(){
    $units = tldPI::getUnits();
    $units = array_map('trim',$units);
    return array_flip($units);
}


/**
 * @param $operations : all operations to test
 *
 * @return mixed
 */
function operationNotValid($operations)
{
    //test if the operations are valid (number between 1 and 999)
    foreach ($operations as $operationIdentifier => $operation) {
        if (!empty($operationIdentifier) && 1 <= (int) $operationIdentifier && 999 >= (int) $operationIdentifier) {
            unset($operations[$operationIdentifier]);
        }
    }
    foreach ($operations as $errors) {
        foreach ($errors as $line) {
            $_SESSION['errorLine'][$line]['Operation'] = 'Operation';
        }
    }

    return $operations;
}

/**
 * function to return items not in LN engineering items.
 *
 * @param $PN : tab containing all pn link to the questionsLinesNumber
 *
 * @return mixed : return the tab with all PN not in LN
 */
function itemNotValid($PN)
{
    $invalidItems = [];
    if (!empty($PN)) {
        try {
            global $kernel;
            $client = $kernel->getContainer()->get(Client::class);
            $items = $client->get(
                sprintf('/ion/engineering_item_descriptions'),
                ['query' => ['itemsList' => implode('|', array_keys($PN))]]
            );

        } catch (ClientException $exception) {
            error_log(sprintf('PIO Insert Question xls GET items error : [%s] %s', $exception->getCode(), $exception->getMessage()));
        }

        if (count($PN) !== $items['hydra:totalItems']) {
            $invalidItems = array_diff($PN, array_column($items['hydra:member'], 'item'));
            foreach ($PN as $itemIdentifier => $err) {
                $_SESSION['errorLine'][$itemIdentifier]['P/N'] = true;
            }
        }

        foreach ($PN as $item => $lines) {
            if (count($lines) !== count(array_unique($lines))) {
                $invalidItems[$item]['t_dsca'] = sprintf('Item defined multiple times for one operation on line %s', implode(', ',array_diff_assoc($lines, array_unique($lines))));
                $_SESSION['errorLine'][$item]['P/N'] = true;
            }
        }
    }

    return $invalidItems;
}

/**
 * check the validity of the answer type.
 *
 * @param string $answerType : YES/NO, Alphanumeric, Decimal, S/N
 *
 * @return bool
 */
function answerTypeValid($answerType)
{
    return in_array($answerType, ['YES/NO', 'Alphanumeric', 'Decimal', 'S/N']);
}

/**
 *create a excel file and force the browser to display a window to download the file.
 *
 * @param $errorLine : tab with all the line
 * @param $cells
 * @param $header : header for excel file
 */
function createErrorFile($errorLine, $cells, $header)
{
    $objPHPExcel = createTemplateHeader($header);
    //first index for insert rows
    $i = 2;

    foreach ($errorLine as $iRow => $val) {
        $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(28.7);
        foreach ($header as $col => $cellValue) {
            if (isset($val[$header[$col]])) {
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $i)->applyFromArray(
                            [
                                'fill' => [
                                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                    'color' => ['rgb' => 'CD5C5C'],
                                ],
                            ]
                        );
            }
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $i, html_entity_decode($cells[$iRow][$col],ENT_HTML401 , 'UTF-8'));
        }
        ++$i;
    }
    $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
    header('Content-type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="Error.xlsx"');
    $objWriter->save('php://output');
    exit;
}

/**
 * Insert questionsLinesNumber and log questionsLinesNumber.
 *
 * @param $questionsLinesNumber : tab, contains number of the lines to insert
 * @param $cells : tab with cells value
 */
function insertQuestion($questionsLinesNumber, $cells, $user)
{
    $firstQuery = 'INSERT INTO pi_questions (';
    $secondQuery = 'INSERT INTO pi_questions_logs (';
    $parameters = 'parent_id, t_opno,t_item, model, owner, ';
    foreach (tldPI::getFactoriesList() as $code => $name) {
        $parameters .= "$code, ";
    }

    $parameters .= 'subject_en, 
              subject_fr, 
              subject_zh, 
              desc_en, 
              desc_fr, 
              desc_zh, 
              position, 
              help_en, 
              help_fr, 
              help_zh, 
              attachment_en, 
              attachment_fr, 
              attachment_zh,
              answer_type, 
              answer_unit, 
              component_sn, 
              match_list, 
              answer_max, 
              answer_min, 
              non_conformity, 
              created_on, 
              entered_by, 
              updated_on, 
              updated_by,
              dt_validity, 
              dt_expiration, 
              create_mode, 
              gt1, 
              gt3, 
              active)
              VALUES';
    $filter = function ($val){
        if($val ===''){
            return false;
        }
        return true;
    };
    foreach ($questionsLinesNumber as $index => $numLine) {
        $value = [];

        $res = null;
        foreach ($cells[$numLine] as $numColumn => $val) {
            $parentID = "('0',";
            $value[] = "'".addslashes($val)."'";

            if ($numColumn === 35) {
                //non conformity
                $value[] ="''";
                //created_on,
                $value[] = 'NOW()';
                //entered_by,
                $value[] = "'".$user->getID()."'";
                //updated_on,
                $value[] = "''";
                //updated_by,
                $value[] = "''";
                //dt validity
                $value[] ="''";
                //dt expiration
                $value[] ="''";
                //create mode
                $value[] = "'insertApp'";
            }
        }

        if (!empty($value)) {
            //insert pi_questions
            $finalQuery = $firstQuery.$parameters.$parentID.implode(',', $value).');';
            $res = tldUtils::sqlInsert($finalQuery);
            if ($res !== null) {
                if($cells[$numLine][1] !==''){
                    //insert ref between pn and the question
                    //separate the pn
                    $pn = explode(",",$cells[$numLine][1]);
                    //to erase "" value
                    $pnToInsert = array_filter($pn,$filter);
                    $value[1] = "'".implode(", ",$pnToInsert)."'";
                   $query =<<<SQL
    INSERT INTO pi_questions_pn_xref  (question_id, t_item) VALUES
SQL;
                    foreach ($pnToInsert as $item){
                        $query .="($res,'$item')";
                    }

                    $query = str_replace(")(","),(",$query);
                    $reqXref=tldUtils::sqlInsert($query.";");

                }
                $parentID = "('".$res."',";
                $finalQuery = $secondQuery.$parameters.$parentID.implode(',', $value).');';
                $res = tldUtils::sqlInsert($finalQuery);

                if(is_numeric($res)){
                    $models[] = $cells[$numLine][2];
                    $piQuestionLogsId[] = $res;
                }
            }
        }
    }
    tldPi::notifyOnInsertQuestionByXLSX($models, $piQuestionLogsId, $user);

}

/**
 * check if the line contains error who must be modify.
 *
 * @param $error : tab with all error type corresponding to one line
 *
 * @return bool
 */
function isInsertable($error)
{
    $forbiddenValue = ['Answer Type','Component (S/N question)','Answer Unit','Position','Owner',];
    return !array_intersect($forbiddenValue, array_keys($error));
}

function getCompontSN(){
    global $kernel;
    try {
        $container = $kernel->getContainer();
        $client = $container->get(Client::class);
    } catch (\Exception $e) {
        $DEFAULT_ERROR[] = 'Client could not be fetched.';
    }

    $formattedComponents = [];
    try {
        $components = $client->get('equipment_serial_components', ['query' => ['order' => ['name' => 'ASC']]]);
        foreach ($components['hydra:member'] as $component) {
            $formattedComponents[$component['name']] = $component['name'];
        }
    } catch (ClientException $exception) {
        $DEFAULT_ERROR[] = 'Components could not be fetched.';
    }

    return $formattedComponents;
}


function createTemplateHeader($columns)
{
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0);
    $objPHPExcel->getActiveSheet()->setTitle('template');

    //create header of columns
    $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(28.7);

    foreach ($columns as $index => $val) {
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($index, 1, "$val");
    }

    return $objPHPExcel;
}
