<?php

declare(strict_types=1);

namespace App\Pi;

use App\Pi\Utils\OperationController;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Inspect
{
    /**
     * @var HttpClientInterface
     */
    private $client;

    /**
     * @var Session
     */
    private $session;

    public function __construct(HttpClientInterface $client, Session $session)
    {
        $this->client = $client;
        $this->session = $session;
    }

    public function getPIInspect($opno, $sn, $erp, $cprj, $allPN = null): array
    {
        if (null === $allPN) {
            $allPN = $this->getAllPartnerNumbers((int) $erp, $cprj);
        }

        $query = <<<SQL
SELECT
         component_sn,
         qst.id AS idQst,
         ans.id AS idAns,
         t_item,
         answer_type,
         ans.answer,
         ans.answerModel,
         ans.answerBrand,
         ans.answerSerial,
         ans.created_on,
         qst.alert,
         ans.derogation,
         qst.t_pdno,
         qst.comp,
         qst.non_conformity,
         qst.answer_unit,
         qst.answer_min,
         qst.answer_max,
         qst.subject_en,
         qst.subject_fr,
         qst.subject_zh,
         qst.desc_en,
         qst.desc_fr,
         qst.desc_zh,
         qst.help_en,
         qst.help_fr,
         qst.help_zh,
         qst.attachment_en,
         qst.attachment_fr,
         qst.attachment_zh,
         qst.position,
         qst.owner,
         CONCAT(peo.lastname,' ',peo.firstname) as answer_name
        FROM pi_questions_unit AS qst
 LEFT JOIN pi_answers AS ans ON ans.parent_id = qst.id
            AND (ans.active = 'Y'OR ans.active IS NULL)
 LEFT JOIN pi_answers as ans2 ON ans.parent_id = ans2.parent_id AND ans.created_on < ans2.created_on
 LEFT JOIN  people AS peo ON peo.id= ans.entered_by
                  WHERE unit='$sn'
                  AND t_opno = '$opno'
                  AND t_item IN ('','$allPN')
                  AND qst.active='Y'

                  AND ans2.id IS NULL
                  GROUP BY qst.id
                  ORDER BY position ASC;
SQL;

        $allQuestionsUnit = \tldUtils::getSqlToAssocArray($query);

        // Get questions
        $pi_crabs = new Routing($this->client, $this->session);
        foreach ($allQuestionsUnit as $i => &$question) {
            $question['id'] = $question['idQst'];
            $question['answerComponent'] = $question['component_sn'];

            // Get crabs
            $question['openCRABS'] = $pi_crabs->getPIOpenCRABSfromQuestion(
                $question['comp'],
                $cprj,
                $question['t_pdno'],
                $opno,
                $question['idQst']
            );
            $question['CRABS'] = $pi_crabs->getPICRABSfromQuestion(
                $question['comp'],
                $cprj,
                $question['t_pdno'],
                $opno,
                $question['idQst']
            );

            // add conformity
            $question['non_conformity'] = 'NO';
            if ('' != $question['answer']) {
                $error = 'NO';
                // response out of tolerance
                if ('' != $question['answer_min']
                    && '' != $question['answer_max']
                    && '' != $question['answer']
                    && (
                        (float) $question['answer'] < (float) $question['answer_min']
                        || (float) $question['answer'] > (float) $question['answer_max']
                    )
                ) {
                    if ('' == $question['derogation']) {
                        $error = 'YES';
                    }
                }
                // negative response on Y/N question
                if (
                    'YES/NO' === $question['answer_type']
                    && 'NO' === $question['answer']
                ) {
                    if ('' == $question['derogation']) {
                        $error = 'YES';
                    }
                }

                if ('YES' === $error) {
                    $question['non_conformity'] = 'YES';
                }
            }

            if (
                'YES' === $question['non_conformity']
                && 0 == $question['openCRABS']
                && '' == $question['derogation']
            ) {
                // clear answer in array
                $question['answer'] = '';
                $question['answer_name'] = '';
                $question['non_conformity'] = 'NO';
                // Delete related answer
                $query = 'DELETE FROM pi_answers WHERE id='.$question['idAns'];
                \tldUtils::sqlExecute($query);
            }

            // Manage language
            if (($erp < '420' || $erp >= '800' || '430' === $erp) && $erp <= '900') {
                $question['subject'] = $question['subject_en'];
                $question['desc'] = $question['desc_en'];
                $question['help'] = $question['help_en'];
                if (0 != $question['attachment_en']) {
                    $question['attachment'] = $question['attachment_en'];
                } else {
                    if (0 != $question['attachment_fr']) {
                        $question['attachment'] = $question['attachment_fr'];
                    } else {
                        $question['attachment'] = $question['attachment_zh'];
                    }
                }
            } elseif ($erp > '600' && $erp <= '900') {
                $question['subject'] = $question['subject_zh'];
                $question['desc'] = $question['desc_zh'];
                $question['help'] = $question['help_zh'];
                if (0 != $question['attachment_zh']) {
                    $question['attachment'] = $question['attachment_zh'];
                } else {
                    if (0 != $question['attachment_en']) {
                        $question['attachment'] = $question['attachment_en'];
                    } else {
                        $question['attachment'] = $question['attachment_fr'];
                    }
                }
            } else {
                $question['subject'] = $question['subject_fr'];
                $question['desc'] = $question['desc_fr'];
                $question['help'] = $question['help_fr'];
                if (0 != $question['attachment_fr']) {
                    $question['attachment'] = $question['attachment_fr'];
                } else {
                    if (0 != $question['attachment_en']) {
                        $question['attachment'] = $question['attachment_en'];
                    } else {
                        $question['attachment'] = $question['attachment_zh'];
                    }
                }
            }

            if ('' == trim($question['desc'])) {
                $question['desc'] = $question['desc_en'];
            }
            if ('' == trim($question['subject'])) {
                $question['subject'] = $question['subject_en'];
            }
        }

        return $allQuestionsUnit;
    }

    public function postPIAnswer(OperationController $opController, $id, $answer, $idUser, $answerComponent = '', $answerModel = '', $answerSerial = '', $answerBrand = '')
    {
        // Check if first answer in operation 995
        $first = false;
        if (\in_array($_SESSION['pi_opno'], ['995', '996'], true)) {
            $queryOperationStatus = <<<SQL
            SELECT count(*) AS nb
            FROM pi_operations_status
            WHERE first_answer<>0
            AND comp='{$_SESSION['pi_erp']}'
            AND t_cprj='{$_SESSION['pi_cprj']}'
            AND t_pdno='{$_SESSION['pi_pdno']}'
            AND t_opno='{$_SESSION['pi_opno']}';
SQL;
            $operationStatus = \tldUtils::getSqlRowToAssocArray($queryOperationStatus);
            if (0 === (int) $operationStatus['nb']) {
                $first = true;
            }
        }

        $query = <<<SQL
         INSERT INTO pi_answers (
            id,
            parent_id,
            answer,
            answerComponent,
            answerModel,
            answerSerial,
            answerBrand,
            active,
            entered_by,
            created_on)
        VALUES ( NULL,
            '$id',
            '$answer',
            '$answerComponent',
            '$answerModel',
            '$answerSerial',
            '$answerBrand',
            'Y',
            '$idUser',
            NOW());
SQL;
        $result = \tldUtils::sqlInsert($query);

        // disable old answers
        $queryDisableOldAnswers = <<<SQL
                                  UPDATE pi_answers
                                  SET active ='N'
                                  WHERE parent_id ='$id'
                                  AND id <> '$result';
SQL;
        \tldUtils::sqlExecute($queryDisableOldAnswers);

        $equipmentRecords = null;
        $snlist = null;
        $snErrorslist = null;
        $er = new \tldEquipment($_SESSION['pi_snid']);
        try {
            // fetch er from api
            $response = $this->client->request('GET', '/equipment_records', [
                'query' => [
                    'legacyId' => $er->getID(),
                ],
            ]);
            $results = json_decode($response->getContent(), true, 512, \JSON_THROW_ON_ERROR);
            $equipmentRecords = $results['hydra:member'] ?? [];
        } catch (\Exception $e) {
            $snErrorslist .= \sprintf('No ER with legacy ID %s could be found in the API, please open a ticket with this error message (reason: %s)', $er->getID(), $e->getMessage());
        }

        if ($first && (null !== $equipmentRecords) && (null !== ($equipmentRecord = array_shift($equipmentRecords)))) {
            $query = 'UPDATE pi_operations_status SET first_answer=now() WHERE first_answer=0 AND comp='.$_SESSION['pi_erp']." AND t_cprj='".$_SESSION['pi_cprj']."' AND t_pdno='".$_SESSION['pi_pdno']."' AND t_opno=".$_SESSION['pi_opno'];
            \tldUtils::sqlExecute($query);
            $piUser = User::fromId($idUser);
            $operations = $opController->getPIOperations($_SESSION['pi_erp'], $_SESSION['pi_cprj'], $_SESSION['pi_pdno'], $piUser, $_SESSION['pi_sn']);

            $allPN = $this->getAllPartnerNumbers((int) $_SESSION['pi_erp'], $_SESSION['pi_cprj']);

            foreach ($operations as $operation) {
                $tmp_opno = $operation['t_opno'];
                $piQuestions = $this->getPIInspect(
                    $tmp_opno,
                    $_SESSION['pi_sn'],
                    $_SESSION['pi_erp'],
                    $_SESSION['pi_cprj'],
                    $allPN
                );

                foreach ($piQuestions as $piQuestion) {
                    if (('S/N' === $piQuestion['answer_type']) && (null != $piQuestion['answer'])) {
                        $component = mb_strtolower(trim($piQuestion['answerComponent']));
                        $model = mb_strtoupper(trim($piQuestion['answerModel']));
                        $serial = mb_strtoupper(trim($piQuestion['answerSerial']));
                        $brand = mb_strtoupper(trim($piQuestion['answerBrand']));
                        if ('' !== $component) {
                            try {
                                // fetch component id
                                $response = $this->client->request('GET', '/equipment_serial_components', [
                                    'query' => [
                                        'name' => $component,
                                    ],
                                ]);
                                $results = json_decode($response->getContent(), true, 512, \JSON_THROW_ON_ERROR);
                                $serialComponents = $results['hydra:member'] ?? [];

                                if (null === ($serialComponent = array_shift($serialComponents))) {
                                    throw new \Exception(\sprintf('serialComponent: %s, not found on the API', $component), 404);
                                }

                                // post to serials
                                if (('' !== $model) || ('' !== $serial) || ('' !== $brand)) {
                                    $this->client->request('POST', '/equipment_serials', [
                                        'json' => [
                                            'equipmentRecord' => $equipmentRecord['@id'],
                                            'component' => $serialComponent['@id'],
                                            'model' => $model,
                                            'serial' => $serial,
                                            'brand' => $brand,
                                        ],
                                    ]);
                                }
                            } catch (\Exception $e) {
                                $snErrorslist .= \sprintf('Operation: #%s, Component: %s, Model: %s, Serial: %s, Brand: %s <br> Reason: %s<br>', $tmp_opno ?? 'empty', $piQuestion['answerComponent'], $piQuestion['answerModel'], $piQuestion['answerSerial'], $piQuestion['answerBrand'], $e->getMessage());
                            }
                            $snlist .= \sprintf('Operation: #%s, Component: %s, Model: %s, Serial: %s, Brand: %s <br>', $tmp_opno ?? 'empty', $piQuestion['answerComponent'], $piQuestion['answerModel'], $piQuestion['answerSerial'], $piQuestion['answerBrand']);
                        }
                    }
                }
            }
            if ($snErrorslist) {
                $er->addLogEntry($idUser, \sprintf('An error occurred while inserting the following SN : %s', $snlist));
            } else {
                $er->addLogEntry($idUser, null !== $snlist ? 'SNs have been imported to the ER' : 'SNs list was empty');
            }
        }

        return $result;
    }

    public function postPIAlert($id, $alert)
    {
        $query = "UPDATE pi_questions_unit SET alert='".$alert."' WHERE id=".$id;
        \tldUtils::sqlExecute($query);
    }

    public function postPIDerogation($id, $derogation, $user)
    {
        // Get last response
        $query = <<<SQL
SELECT max(id) AS id FROM pi_answers WHERE parent_id='$id';
SQL;
        $row = \tldUtils::getSqlRowToAssocArray($query);
        // Post derogation
        $query = <<<SQL
            UPDATE pi_answers SET
            derogation='$derogation',
            d_entered_by='$user',
            d_entered_on=NOW()
            where parent_id='$id'
            and id='{$row['id']}';
SQL;

        return \tldUtils::sqlExecute($query);
    }

    private function getAllPartnerNumbers(int $site, string $projectNumber)
    {
        try {
            $customizedBillOfMaterials = $this->client->request(
                'GET',
                \sprintf('ion/customized_bill_of_materials/site=%d;project=%s', $site, $projectNumber),
            )->toArray();
        } catch (\Exception $exception) {
            error_log(\sprintf('Call to CBOM error : %s', $exception->getMessage()));
            $_SESSION['message'] .= $exception->getMessage();
        }

        return implode("','", array_column($customizedBillOfMaterials['items'], 'partNumber'));
    }
}
