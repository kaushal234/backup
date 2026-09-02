<?php

declare(strict_types=1);

namespace App\Pi;

use App\Client\ApiClient;
use App\Translator;
use Symfony\Component\HttpFoundation\Session\Session;

class Routing
{
    /**
     * @var ApiClient
     */
    private $client;

    /**
     * @var Session
     */
    private $session;

    public function __construct(ApiClient $client, Session $session)
    {
        $this->client = $client;
        $this->session = $session;
    }

    public function getPICRABSfromQuestion($erp, $cprj, $orderNumber, $operationNumber, $question)
    {
        $query = "SELECT count(*) AS nb FROM pi_crab_eap WHERE comp='$erp' AND t_cprj='$cprj' AND t_pdno='$orderNumber' AND t_opno='$operationNumber' AND question_id='$question'";
        $row = \tldUtils::getSqlRowToAssocArray($query);

        return $row['nb'];
    }

    public function getPIOpenCRABSfromQuestion($erp, $cprj, $orderNumber, $operationNumber, $question)
    {
        $query = "SELECT count(*) AS nb FROM pi_crab_eap A, crabs B WHERE A.comp='$erp' AND A.t_cprj='$cprj' AND A.t_pdno='$orderNumber' AND A.t_opno='$operationNumber' AND A.question_id='$question' AND A.parent_id=B.id AND B.status<>'CLOSED'";
        $row = \tldUtils::getSqlRowToAssocArray($query);

        return $row['nb'];
    }

    public function getPIOpenCRABSfromUnit($erp, $cprj, $orderNumber)
    {
        $query = "SELECT count(*) AS nb FROM pi_crab_eap A, crabs B  WHERE A.comp='$erp' AND A.t_cprj='$cprj' AND A.t_pdno='$orderNumber' AND A.parent_id=B.id AND B.status<>'CLOSED'";
        $row = \tldUtils::getSqlRowToAssocArray($query);

        return $row['nb'];
    }

    public function getPITaskDescFromTano($tano)
    {
        foreach ($_SESSION['project']['tasks'] ?? [] as $task) {
            if ($task['taskNumber'] === (int) $tano) {
                return $task['referenceDescription'];
            }
        }

        return '';
    }

    public function getPITaskDescFromOpno($operationNumber)
    {
        $description = '';
        foreach ($_SESSION['project']['productionOrders'][0]['operations'] ?? [] as $task) {
            if ($task['operationIdentifier'] === $operationNumber) {
                $description = $task['referenceDescription'];
            }
        }

        if ('ASIA' === $_SESSION['LOCATION']) {
            $query = "SELECT lang2 FROM descriptions_translate WHERE module='TIROU003' AND lang1='{$description}' LIMIT 1";
            $chineseTranslation = \tldUtils::getSqlRowToAssocArray($query);
            if (!empty($chineseTranslation)) {
                return $chineseTranslation['lang2'];
            }
        }

        return $description;
    }

    public function postLogTimeKeeping($erp, $user, $orderNumber, $operationNumber, $tano, $koot)
    {
        $clientIp = \tldUtils::getClientIp();

        $query = 'INSERT INTO pi_timekeeping_log (id, comp, int_user, baan_user, t_koot, t_pdno, t_opno, t_tano, created_on, user_agent, remote_addr) VALUES ( NULL, ';
        $query .= "'".$_SESSION['pi_erp']."', ";
        $query .= "'".$_SESSION['pi_user_id']."', ";
        $query .= "'".$this->client->getUserId()."', ";
        $query .= "'".$koot."', ";
        $query .= "'".$orderNumber."', ";
        $query .= "'".$operationNumber."', ";
        $query .= "'".$tano."', ";
        $query .= 'NOW(), ';
        $query .= "'".$_SERVER['HTTP_USER_AGENT']."', ";
        $query .= "'".$clientIp."') ";

        return \tldUtils::sqlInsert($query);
    }

    // Time keeping: create record in LN
    public function postPITimeKeeping($orderNumber, $operationNumber, Translator $translator, $comment = '')
    {
        $buName = $this->client->getUser()->all()['businessUnit']['name'];
        $locale = \in_array($buName, ['TLD MTL', 'TLD STL', 'TLD SHE'], true) ? 'fr' : 'en';
        try {
            if ('999' === (string) $operationNumber) {
                $this->client->save('/ion/time_keepings', [
                    'employeeNumber' => (string) $this->client->getUserId(),
                    'transactionType' => 'INDIRECT',
                    'task' => '972', // TODO replace with correct task id once migrated. it should become 500972 with 500 beeing the erp
                    'comment' => $comment,
                ]);

                return;
            }

            $this->client->save('/ion/time_keepings', [
                'employeeNumber' => (string) $this->client->getUserId(),
                'transactionType' => 'DIRECT',
                'orderNumber' => (string) $orderNumber,
                'operationNumber' => (int) $operationNumber,
                'comment' => $comment,
            ]);
        } catch (\Exception $exception) {
            error_log(\sprintf('Timekeeping POST to ION error: [%s] %s', $exception->getCode(), $exception->getMessage()));
            $this->session->getFlashBag()->add('error', $translator->trans('pio.error_transaction_message', [], 'pio', $locale));
            $this->session->getFlashBag()->add('error', $exception->getMessage());
        }
    }
}
