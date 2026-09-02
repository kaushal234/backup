<?php

declare(strict_types=1);

namespace App\Pi\Utils;

use App\Client\ApiClient;
use App\Pi\User;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OperationController
{
    public const OPEN = 0;
    public const CLOSED = 1;
    protected $operationsState;
    protected $firstCrabOpen;
    protected $firstOpNotAns;
    protected $firstDeroMissing;
    protected $operationsList = [];

    /**
     * @var HttpClientInterface
     */
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function set($firstCrab, $firstOpNotAns, $firstDeroMissing)
    {
        $this->operationsState = [];
        $this->firstCrabOpen = $firstCrab;
        $this->firstOpNotAns = $firstOpNotAns;
        $this->firstDeroMissing = $firstDeroMissing;
    }

    public function getTabRights()
    {
        return [
            'ASSEMBLYCTRL' => ['OPERATION' => '9', 'RIGHTS' => ['pi_TESTER', 'pi_GL', 'pi_QAM']],
            509 => ['RIGHTS' => ['pi_TESTER', 'pi_GL', 'pi_QAM']],
            970 => ['OPERATION' => '<', 'RIGHTS' => ['pi_GL', 'pi_QAM']],
            975 => ['OPERATION' => '=970', 'RIGHTS' => ['pi_TESTER', 'pi_QAM', 'pi_PM']],
            976 => ['OPERATION' => '<', 'RIGHTS' => ['pi_TESTER', 'pi_GL', 'pi_QAM']],
            985 => ['OPERATION' => '<', 'RIGHTS' => ['pi_TESTER', 'pi_QAM', 'pi_PM']],
            990 => ['CRABS' => true, 'DEROGATION' => true, 'OPERATION' => '<', 'RIGHTS' => ['pi_PM', 'pi_PS']],
            991 => ['CRABS' => true, 'DEROGATION' => true, 'OPERATION' => '<', 'RIGHTS' => ['pi_QAM']],
            992 => ['OPERATION' => '<', 'RIGHTS' => ['pi_TESTER', 'pi_QAM']],
            993 => ['RIGHTS' => ['pi_OPERATOR', 'pi_QAM']],
            995 => ['CRABS' => true, 'DEROGATION' => true, 'OPERATION' => '<', 'RIGHTS' => ['pi_QAM']],
            996 => ['CRABS' => true, 'DEROGATION' => true, 'OPERATION' => '<', 'RIGHTS' => ['pi_QAM']],
        ];
    }

    public function controlPreviousOp($opno, $condition)
    {
        $conditionValid = false;
        switch (mb_substr($condition, 0, 1)) {
            case '=':
                $conditionValid = (self::CLOSED === $this->operationsState[mb_substr($condition, 1)]);
                break;
            case '<':
                $conditionValid = !($this->firstOpNotAns < $opno
                    && null !== $this->firstOpNotAns);
                break;
            case '9' :
                for ($op = $opno - 9; $op < $opno; ++$op) {
                    if (\array_key_exists($op, $this->operationsState)) {
                        if (self::CLOSED !== $this->operationsState[$op]) {
                            break 2;
                        }
                    }
                }
                $conditionValid = true;
                break;
            default:
                return $conditionValid;
        }

        return $conditionValid;
    }

    public function getWareHouseNotification($erp, $cprj, $pdno, $operationsNumber): array
    {
        $opParamQuery = implode("','", $operationsNumber);
        $warehouseQuery = <<<SQL
            SELECT t_opno, warehouseNotified FROM pi_operations_status
                    WHERE comp="$erp"
                    AND t_cprj="$cprj"
                    AND t_pdno="$pdno"
                    AND t_opno in ('$opParamQuery');
SQL;

        return \tldUtils::getSqlToAssocArray($warehouseQuery);
    }

    public function getAllCrabs($erp, $cprj, $pdno, $operationsNumber): array
    {
        $opParamQuery = implode("','", $operationsNumber);
        $crabsQuery = <<<SQL
            SELECT count(*) AS nb, t_opno ,status FROM pi_crab_eap A
            INNER JOIN crabs B ON A.parent_id=B.id
            WHERE t_opno IN ('$opParamQuery')
            AND  t_cprj ='$cprj'
            AND comp = '$erp'
            AND t_pdno = '$pdno'
            GROUP BY t_opno,status
            ORDER BY CONVERT(t_opno, SIGNED INTEGER) ASC;
SQL;

        return \tldUtils::getSqlToAssocArray($crabsQuery);
    }

    public function getOperationsStatus($erp, $cprj, $pdno, array $operationNumbers): array
    {
        $query = 'SELECT t_opno FROM pi_operations_status
                WHERE comp='.$erp."
                AND t_cprj='".$cprj."'
                AND t_opno IN ('".implode("','", $operationNumbers)."')
                AND t_pdno='".$pdno."'
                GROUP BY t_opno;";

        return array_column(\tldUtils::getSqlToAssocArray($query), 't_opno');
    }

    public function insertOperationsStatus(array $insertStatusOp, $erp, $cprj, $pdno)
    {
        $query = 'INSERT INTO pi_operations_status (id, comp, t_cprj, t_pdno, t_opno, status, date_status) VALUES ';
        $statuses = [];
        foreach ($insertStatusOp as $value) {
            $statuses[] = <<<SQL
(NULL, '$erp', '$cprj', '$pdno', '$value', 'New', NOW())
SQL;
        }
        \tldUtils::sqlInsert($query.implode(',', $statuses));
    }

    public function getAllQuestionsAns($erp, $cprj, $pdno, $operationsNumber, $er): array
    {
        try {
            $customizedBillOfMaterials = $this->client->request('GET', \sprintf('ion/customized_bill_of_materials/site=%d;project=%s', $erp, $cprj))->toArray();
        } catch (\Exception $exception) {
            error_log(\sprintf('CBOM error: [%s] %s', $exception->getCode(), $exception->getMessage()));
            $_SESSION['message'] .= $exception->getMessage();
        }

        $allPN = implode("','", array_column($customizedBillOfMaterials['items'], 'partNumber'));
        $operationsNumberQueryParameter = implode("','", $operationsNumber);
        $query = <<<SQL
            SELECT qst.id,
            qst.t_item,
            qst.t_opno,
            qst.answer_type,
            qst.answer_min,
            qst.answer_max,
            qst.non_conformity,
            qst.subject_fr,
            qst.subject_en,
            qst.subject_zh,
            qst.desc_fr,
            qst.desc_en,
            qst.desc_zh,
            qst.answer_unit,
            LTRIM(RTRIM(ans.answer)) AS answer,
            LTRIM(RTRIM(ans.derogation)) AS derogation,
            ans.answerComponent,
            ans.answerModel,
            ans.answerSerial,
            ans.answerBrand,
            CONCAT(peo.firstname, ' ',peo.lastname) as answer_name
            FROM pi_questions_unit AS qst
            LEFT JOIN pi_answers AS ans
            ON ans.parent_id = qst.id AND (ans.active = 'Y' OR ans.active IS NULL)
            LEFT JOIN pi_answers as ans2
            ON ans.parent_id = ans2.parent_id
            AND ans.created_on < ans2.created_on
            LEFT JOIN  people AS peo
            ON peo.id= ans.entered_by
            WHERE
            comp='$erp'
            AND t_cprj='$cprj'
            AND t_pdno='$pdno'
            AND unit = '$er'
            AND t_opno IN ('$operationsNumberQueryParameter' )
            AND t_item IN ('$allPN','')
            AND qst.active='Y'
            AND ans2.id IS NULL AND ans2.id IS NULL GROUP BY qst.id ORDER BY t_opno;
SQL;

        return \tldUtils::getSqlToAssocArray($query);
    }

    public function controlCrab($opno)
    {
        return !((int) $this->firstCrabOpen < (int) $opno && null !== $this->firstCrabOpen);
    }

    public function isAccessible($user, $opno, $condition)
    {
        $crabs = $dero = $op = $rights = true;
        if (!empty($condition)) {
            foreach ($condition as $condType => $cond) {
                switch ($condType) {
                    case 'CRABS':
                        $crabs = $this->controlCrab($opno['t_opno']);
                        break;
                    case 'DEROGATION':
                        $dero = !((int) $this->firstDeroMissing < (int) $opno['t_opno']
                            && null !== $this->firstDeroMissing);
                        break;
                    case 'OPERATION':
                        $op = $this->controlPreviousOp((int) $opno['t_opno'], $cond);
                        break;
                    case 'RIGHTS':
                        $rights = $user->isInGroups($cond);
                        break;
                    default:
                        return true;
                }
            }
        }

        if ($opno['answered'] === $opno['questions']) {
            $this->operationsState[(int) $opno['t_opno']] = self::CLOSED;
        } else {
            $this->operationsState[(int) $opno['t_opno']] = self::OPEN;
        }

        return $crabs && $dero && $op && $rights;
    }

    public function getPIOperations($erp, $cprj, $pdno, User $piUser, $er): array
    {
        $operations = $_SESSION['project']['productionOrders'][0]['operations'] ?? [];

        if (empty($operations)) {
            return [];
        }

        $operations = array_merge([['operationIdentifier' => 0, 'taskNumber' => 0, 'referenceDescription' => 'Default']], $operations);
        $operationsNumber = array_unique(array_column($operations, 'operationIdentifier'));
        $opInStatus = $this->getOperationsStatus($erp, $cprj, $pdno, $operationsNumber);
        if (!empty($operationStatusToInsert = array_diff($operationsNumber, $opInStatus))) {
            $this->insertOperationsStatus($operationStatusToInsert, $erp, $cprj, $pdno);
        }

        // If the location = Asia then we must get the chinese translation
        if ($erp > '600' && $erp < '800') {
            $operationsDescriptionParameters = implode("','", array_column($operations, 'referenceDescription'));
            $query = "SELECT lang1,lang2 FROM descriptions_translate
                      WHERE module='TIROU003' AND CONVERT(lang1 USING utf8mb4) COLLATE utf8mb4_unicode_ci in ('$operationsDescriptionParameters')";
            $chineseTranslation = \tldUtils::getSqlToAssocArray($query);
            $chineseTrad = [];
            foreach ($chineseTranslation as $res) {
                $chineseTrad[$res['lang1']] = $res['lang2'];
            }
            foreach ($operations as $indent => $op) {
                if (isset($op['description']) && isset($chineseTrad[$op['description']])) {
                    $operations[$indent]['referenceDescription'] = $chineseTrad[$op['referenceDescription']];
                }
            }
        }

        $allQuestionsUnit = $this->getAllQuestionsAns($erp, $cprj, $pdno, $operationsNumber, $er);
        $conditionOp = [];

        foreach ($operations as $op) {
            $conditionOp[$op['operationIdentifier']] = [
                't_opno' => $op['operationIdentifier'],
                't_dsca' => $op['referenceDescription'],
                't_tano' => $op['taskNumber'] ?? '',
                'questions' => 0,
                'answered' => 0,
                'derogationMissing' => false,
                'CLOSED' => 0,
                'TO-FIX' => 0,
                'TO-INSPECT' => 0,
                'FOR-DEROGATION' => 0,
                'OperationUserAuthorized' => true,
                'OperationRulesSatisfied' => true,
                'CRABrulesSatisfied' => true,
                'Notifiable' => true,
            ];
        }
        $firstOpNotAns = null;
        $firstDeroMissing = null;
        $firstCrabOpen = null;
        foreach ($allQuestionsUnit as $qst) {
            ++$conditionOp[$qst['t_opno']]['questions'];
            if (null !== $qst['answer'] && '' !== trim($qst['answer'])) {
                ++$conditionOp[$qst['t_opno']]['answered'];
                if ($conditionOp[$qst['t_opno']]['derogationMissing']) {
                    continue;
                }
                if ('YES/NO' === $qst['answer_type']) {
                    $conditionOp[$qst['t_opno']]['derogationMissing'] = ('NO' === trim($qst['answer']) && '' === trim($qst['derogation']));
                } elseif ('DECIMAL' === $qst['answer_type']) {
                    $conditionOp[$qst['t_opno']]['derogationMissing'] = ($qst['answer'] < $qst['answer_min'] || $qst['answer'] > $qst['answer_max']);
                }
                continue;
            }
            if (null === $firstOpNotAns || $firstOpNotAns > $qst['t_opno']) {
                $firstOpNotAns = $qst['t_opno'];
            }
        }

        // ////////GET CRABS ///////////////////////////////////////////////
        $allCrabs = $this->getAllCrabs($erp, $cprj, $pdno, $operationsNumber);
        if (!empty($allCrabs)) {
            foreach ($allCrabs as $crab) {
                $conditionOp[$crab['t_opno']][$crab['status']] = $crab['nb'];
                if ('CLOSED' !== $crab['status'] && null === $firstCrabOpen) {
                    $firstCrabOpen = $crab['t_opno'];
                }
            }
        }
        // ///////GET WAREHOUSE NOTIFICATION ////////////////////////////////
        foreach ($this->getWareHouseNotification($erp, $cprj, $pdno, $operationsNumber) as $warehouse) {
            $conditionOp[$warehouse['t_opno']]['warehouseNotified'] = ('YES' === $warehouse['warehouseNotified']);
        }
        $this->set($firstCrabOpen, $firstOpNotAns, $firstDeroMissing);
        foreach ($conditionOp as $indentOp => $op) {
            if (\array_key_exists($indentOp, $this->getTabRights())) {
                $categoryOp = $indentOp;
            } elseif ($indentOp < 900 && '9' === mb_substr((string) $indentOp, -1)) {
                $categoryOp = 'ASSEMBLYCTRL';
            } else {
                $categoryOp = 'ASSEMBLY';
            }
            $conditionOp[$indentOp]['accessible'] = $this->isAccessible($piUser, $op, $this->getTabRights()[$categoryOp] ?? null);
        }

        return $conditionOp;
    }

    public function attachFile($pdfFile, $erId, $name, $description = '')
    {
        if (null === $pdfFile->getFile()->getFilePath()) {
            return 'PDF not generated';
        }
        $fileInformation = [
            'tmp_name' => $pdfFile->getFile()->getFilePath(),
            'name' => $name,
        ];
        if ('' == $fileInformation['tmp_name']) {
            return "ERROR: You didn't upload a file...";
        }
        // link to the ER
        // this information are inserted into mod_files table
        $informationToInsert = [
            'parent_id' => $erId,
            'module' => 'ER',
            'poster' => '',
            'description' => 'report 3.P&I Answers by Filters '.$description,
            'filename' => $fileInformation['name'],
            'level' => 0,
        ];
        $e = \tldModFile::insert($informationToInsert, $fileInformation);
        if (\is_string($e)) {
            return "ERROR: There was a problem attaching the file. Reason: $e";
        }

        return true;
    }

    /**
     * execute action asked for the operations closure.
     *
     * @param int $opno   operation number
     * @param int $ERid   ER id and not the SN id
     * @param int $userId User id
     * @param int $pdno   Work order number
     *
     * @return string error or confirmation
     */
    public function operationClosure($opno, $ERid, $userId, $pdno, $lang = 'en')
    {
        switch ($opno) {
            case 991:
                // Yellow tag
                $er = new \tldEquipment($ERid);
                $sn = $er->getSN();
                // get ODP information
                $odp = \tldODP::byQuery(['sn' => "%$sn%"], 'BySN');
                if (1 !== \count($odp)) {
                    return "ERROR: Unit {$er->getSN()} - ODP not found or several ODP found";
                }
                $odp = current($odp);
                $oldGt = $er->getYT();
                $newYt = date('Y-m-d');
                $response = '';
                if ($odp['nb_crabs'] > 0) {
                    return 'All CRAB are not Closed, YT impossible';
                }

                if (empty($er->setYellowTag($newYt, 'YT Date'))) {
                    $response .= "Unit Yellow tag \n";
                    // create email subject
                    $subject = \sprintf('ODP Update - SOR#%s SOL#%s SN#%s - Actual YT change', $odp['sorid'], $odp['solid'], $odp['sn']);
                    // mail information
                    $title_cust = $title_prno = $title_pdno = '';
                    if ($odp['cu_nama']) {
                        $title_cust = " for customer {$odp['cu_nama']}";
                    }
                    // Project# Case
                    if ($odp['t_prno']) {
                        $title_prno = ", Project#: {$odp['t_prno']}";
                    }
                    // Work Order# Case
                    $title_pdno = ", Work Order#: {$pdno}";

                    $log = "YT Date updated from {$oldGt} to $newYt";
                    // log
                    $er->addLogEntry($userId, $log);
                    // get assignees for notification
                    $notificationsInformation['man_location'] = $odp['man_location'];
                    $notificationsInformation['bu'] = $odp['bu'];
                    $notificationsInformation['asm'] = $odp['asm'];
                    // Add email msg
                    $msg = <<<EOF
Unit SN <a href="https://www.tld-gse.com/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=search&m[2]=bySN&sn={$er->getSN()}">{$er->getSN()}</a> {$odp['model']}$title_cust$title_prno$title_pdno
<ul>
EOF;
                    $msg .= "<li>$log</li></ul>";
                    $emaillist = \tldODP::getRecipients(['YT' => true], $notificationsInformation);
                    // Send email
                    if (!empty($emaillist)) {
                        $e = \tldUtils::emailAttachment(
                            array_unique($emaillist),
                            'noreply@tld-gse.com',
                            $subject,
                            $msg,
                            ''
                        );
                        if ($e) {
                            $response .= "and ODP notification Sent. \n";
                        }
                    }
                }

                return $response;
            case 995:
            case 996:
                // get ER to update
                $er = new \tldEquipment($ERid);
                // get new GT date and old
                $newGt = date('Y-m-d');
                $oldGt = $er->getGT();
                // get odp to have more information about the ER
                $odp = \tldODP::byQuery(['sn' => "%{$er->getSn()}%"], 'withoutSSO');
                if (1 !== \count($odp)) {
                    return "ERROR: Unit {$er->getSN()} - ODP not found or several ODP found";
                }
                // get odp and use it to test if the green tag is available
                $response = '';
                $odp = current($odp);
                // check SOL condition
                if ('P' !== $er->getSN()[0] && 0 === (int) $er->getParentID() && (empty($odp['solid']) || ('IN_PROGRESS' !== $odp['sol_status']))) {
                    return 'No SOL or SOL status not IN PROGRESS';
                }

                if ('0000-00-00' === $er->getFirstGT()) {
                    $cbom = null;
                    /** @var ApiClient $client */
                    $client = $this->client;
                    try {
                        $cbom = $client->get(\sprintf('/ion/customized-bill-of-materials/multi_level_views/site=%d;project=%s', $er->getFactoryERP(), $er->getSN()), ['query' => ['depth' => 20]]);
                    } catch (\Exception $exception) {
                        $er->addLogEntry($userId, 'INTERNAL ERROR: Could not find last cbom update date.');
                    }
                    if (null !== ($cbom['items'] ?? null)) {
                        $maxDate = max(array_map('strtotime', array_column($cbom['items'], 'engineeringRevisionEffectiveDate')));
                        $er->updateRecord(
                            ['last_cbom_update_date' => date('Y-m-d', $maxDate)],
                            ['last_cbom_update_date']
                        );
                    }
                }

                $gtIsSet = $er->setGreenTagDt($newGt, $userId, $odp);
                if (empty($gtIsSet)) {
                    $response .= "Unit Green tag \n";
                    // ODP notification
                    // create email subject
                    $subject = \sprintf('ODP Update - SOR#%s SOL#%s SN#%s - Actual GT Change', $odp['sorid'], $odp['solid'], $odp['sn']);
                    $title_cust = $title_prno = $title_pdno = '';
                    if ($odp['cu_nama']) {
                        $title_cust = " for customer {$odp['cu_nama']}";
                    }
                    // Project# Case
                    if ($odp['t_prno']) {
                        $title_prno = ", Project#: {$odp['t_prno']}";
                    }
                    // Work Order# Case
                    $title_pdno = ", Work Order#: {$pdno}";
                    // mail information
                    $log = "GT Date updated from {$oldGt} to $newGt";
                    // log
                    $er->addLogEntry($userId, $log);
                    // get assignees for notification
                    $notificationsInformation['man_location'] = $odp['man_location'];
                    $notificationsInformation['bu'] = $odp['bu'];
                    $notificationsInformation['asm'] = $odp['asm'];
                    // Add email msg
                    $msg = <<<EOF
Unit SN <a href="https://www.tld-gse.com/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=search&m[2]=bySN&sn={$er->getSN()}">{$er->getSN()}</a> {$odp['model']}$title_cust$title_prno$title_pdno
<ul>
EOF;
                    $msg .= "<li>$log</li>{$er->getLinkHtmlInfo()}</ul>";
                    // assignee
                    $emailList = \tldODP::getRecipients(['GT_ACT' => true], $notificationsInformation);
                    // Send email
                    if (!empty($emailList)) {
                        $e = \tldUtils::emailAttachment(
                            array_unique($emailList),
                            'noreply@tld-gse.com',
                            $subject,
                            $msg,
                            ''
                        );
                        if ($e) {
                            $response .= "and ODP notification Sent. \n";
                        }
                    }
                } else {
                    if (is_numeric($gtIsSet)) {
                        $response .= "SEQUENCE CREATED #$gtIsSet\n";
                    } else {
                        return $gtIsSet[0];
                    }
                }

                /**
                 * get operation from the ER
                 * to test in baantest change $er->getFactoryERP() to 502 or 403.
                 */
                $operations = $_SESSION['project']['productionOrders'][0]['operations'] ?? [];
                // create list use in query ( ex : select * from table where op is in (list) )
                $ops = [];
                foreach ($operations as $indent => $op) {
                    if (999 !== $op['operationIdentifier']) {
                        $ops[] = $op['operationIdentifier'];
                    }
                }
                // get all information needed about questions and answers
                $questionsAnswers = $this->getAllQuestionsAns($er->getFactoryERP(), $er->itsDetails['t_prno'], $pdno, $ops, $er->getSN());
                // associate a description to a operation.
                $desc = [];
                foreach ($operations as $op) {
                    // Convert chinese description of operation to entity numeric.
                    // As this text is clean from LN, mb_convert_encoding function will have no effect.
                    // We can also remove mb_convert_encoding, but not sure about result in other language.
                    $desc[$op['operationIdentifier']] = mb_encode_numericentity($op['referenceDescription'], [0x0, 0xFFFF, 0, 0xFFFF], 'UTF-8');
                }
                // add operation description to the question
                // needed to use correctly the function tldEquipment::generatePDF()
                foreach ($questionsAnswers as $indent => $questAns) {
                    $questionsAnswers[$indent]['desc'] = $questAns['desc_en'];
                    $questionsAnswers[$indent]['subject'] = $questAns['subject_en'];
                    $questionsAnswers[$indent]['op_desc'] = $desc[$questAns['t_opno']];
                }
                $file = $er->generatePDF($questionsAnswers, $pdno);
                $isAttach = $this->attachFile(
                    $file,
                    $er->getID(),
                    'P&I document (EN - report 3.P&I Answers by Filters - output PDF).pdf',
                    'EN');

                if (true !== $isAttach) {
                    return $response."EN file not attached Error : $isAttach";
                }

                switch ($er->getFactoryERP()) {
                    case 500:// MTL
                    case 502:
                    case 403:
                    case 510:// DTV
                    case 520:// STL
                    case 420:// SHE
                    case 570:
                        $keyDesc = 'desc_fr';
                        $keySub = 'subject_fr';
                        break;
                    case 640:// SHA
                    case 660:// WUX
                        $keyDesc = 'desc_zh';
                        $keySub = 'subject_zh';
                        break;
                    default:
                        return 'File successfully attached';
                }
                foreach ($questionsAnswers as $indent => $questAns) {
                    $questionsAnswers[$indent]['desc'] = $questAns[$keyDesc];
                    $questionsAnswers[$indent]['subject'] = $questAns[$keySub];
                    $questionsAnswers[$indent]['op_desc'] = $desc[$questAns['t_opno']];
                }
                // create the pdf, copy of the code in the odp module
                $localFile = $er->generatePDF($questionsAnswers, $pdno);
                $isAttach = $this->attachFile(
                    $localFile,
                    $er->getID(),
                    'P&I document (report 3.P&I Answers by Filters - output PDF).pdf',
                    'Local language (ZH or FR)');

                if (true !== $isAttach) {
                    return $response.' FR or ZH file not attached';
                }

                $response .= 'Files successfully attached.';

                /** @var ApiClient $client */
                $client = $this->client;
                $equipmentRecord = $client->findOneBy('/equipment_records', [
                    'legacyId' => $ERid,
                    'normalization_groups' => ['publishable'],
                ]);

                if (!$equipmentRecord['publishable']) {
                    error_log(\sprintf('Manual cannot be created. ER %s is not publishable.', $er->getID()));

                    return $response.'Manual cannot be created. ER is not publishable';
                }

                try {
                    $payload = [
                        'mainEquipmentRecord' => $equipmentRecord['@id'],
                        'force' => true,
                        'language' => 'CH' === $lang ? 'zh' : mb_strtolower($lang),
                        'status' => 'RELEASED',
                    ];

                    $client->save('/support/manuals', $payload);
                } catch (ClientException $exception) {
                    error_log(\sprintf('Manual not created for ER %s : [%s] %s', $er->getID(), $exception->getCode(), $exception->getMessage()));

                    return $response.'Manual not created automatically. Please create the manual manually on the ER pubs page.';
                }

                return $response.'Manual successfully created';
            default:
                return '';
        }
    }
}
