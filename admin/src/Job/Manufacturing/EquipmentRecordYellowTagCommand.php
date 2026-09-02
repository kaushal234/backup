<?php

declare(strict_types=1);

namespace App\Job\Manufacturing;

use App\Job\LoggerAwareCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

include_once 'product_support.inc.php';
include_once 'sales_service.inc.php';

#[AsCommand(name: 'manufacturing:yellow-tag')]
class EquipmentRecordYellowTagCommand extends LoggerAwareCommand
{
    private const WITHOUT_NOTIFICATION = 'without_notification';

    public function __construct()
    {
        parent::__construct();
        $this->setDescription('Add yellow tags on ER');

        $this->addArgument('without_notification', InputArgument::OPTIONAL, 'update Er light without notification');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $NB_DAYS = 30;
        $NB_DAYS_LIGHT_ER = 90;
        $notify = null === $input->getArgument(self::WITHOUT_NOTIFICATION);

        $query = "SELECT er.*, customers.customer_name AS buyer_customer_display
            FROM service AS er
            LEFT JOIN customers ON customers.id=er.buyer_customer_id
            WHERE date_shipped='0000-00-00'
            AND dgt_act!='0000-00-00'
            AND DATEDIFF(NOW(),dgt_act)> IF(light = 1, $NB_DAYS_LIGHT_ER, $NB_DAYS)
            AND (dyt='0000-00-00' OR DATEDIFF(dyt,dgt_act)<=0)";

        $rows = \tldUtils::getSqlToAssocArray($query);

        if (!$rows) {
            $this->logger->warning('No ER found to YT');
        }

        foreach ($rows as $row) {
            $isLight = $row['light'] ? 'light' : '';

            $this->logger->info("Processing ER#{$row['id']} $isLight");
            $ER = new \tldEquipment($row['id']);
            // Update YT date
            $newYT = date('Y-m-d');
            $a = ['dyt' => $newYT, 'dgt_act' => '0000-00-00'];
            $updateError = $ER->updateRecord($a, ['dyt', 'dgt_act']);
            if (\is_string($updateError)) {
                $this->logger->error("ERROR: ER yellow tag update for ER#{$row['id']} $isLight FAILED");

                continue;
            }
            // Add log
            $daysCount = $row['light'] ? $NB_DAYS_LIGHT_ER : $NB_DAYS;
            $MSG = "[AUTO] Yellow Tag triggered by sequence rule: GT date exceeded $daysCount days with no shipment. YT date updated: {$row['dyt']} → $newYT";
            $logEntryError = $ER->addLogEntry(0, \TldDatabase::escape($MSG));
            if (\is_string($logEntryError)) {
                $this->logger->error("ERROR: log of yellow tag update for ER#{$row['id']} $isLight FAILED");
            }

            if ($notify) {
                // 3 - Notify YT update
                $subject = "{$row['buyer_customer_display']}, {$row['model']}, {$row['man_location']}, ER#{$row['sn']}, YT Date update notification";
                $days = $row['light'] ? $NB_DAYS_LIGHT_ER : $NB_DAYS;
                $body = "
                <p>ER#{$row['sn']} is not shipped and has been GT for more than $days days.</p>
                <p>$MSG</p>
                <p>
                    <a href=\"http://www.tld-gse.com/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=view&id={$row['id']}\">
                        Click here to see ER#{$row['sn']}
                    </a>
                </p>";

                $TO = [];
                // Factory QAM and PSM
                $ERP_FACTORY = \tldLocation::getERPByLocation($row['man_location']);
                $grpQAM = new \tldGroup('role_QAM', $ERP_FACTORY);
                $TO[] = $grpQAM->getEmailList();
                $grpPSM = new \tldGroup('role_PSM', $ERP_FACTORY);
                $TO[] = $grpPSM->getEmailList();
                $grpPSA = new \tldGroup('role_PSA', $ERP_FACTORY);
                $TO[] = $grpPSA->getEmailList();
                $grpQE = new \tldGroup('role_QE', $ERP_FACTORY);
                $TO[] = $grpQE->getEmailList();
                $grpPM = new \tldGroup('role_PM', $ERP_FACTORY);
                $TO[] = $grpPM->getEmailList();
                $grpPS = new \tldGroup('role_PS', $ERP_FACTORY);
                $TO[] = $grpPS->getEmailList();
                $grpEM = new \tldGroup('role_EM', $ERP_FACTORY);
                $TO[] = $grpEM->getEmailList();
                $grpCOO = new \tldGroup('role_COO', $ERP_FACTORY);
                $TO[] = $grpCOO->getEmailList();
                $grpFC = new \tldGroup('role_FC', $ERP_FACTORY);
                $TO[] = $grpFC->getEmailList();
                $grpMLM = new \tldGroup('role_MLM', $ERP_FACTORY);
                $TO[] = $grpMLM->getEmailList();
                $grpPlanner = new \tldGroup('role_planner', $ERP_FACTORY);
                $TO[] = $grpPlanner->getEmailList();
                // SSO ASM of the order if linked and SSO SA
                if (!empty($ER->itsDetails['sor_lid'])) {
                    $sol = new \tldSOL($ER->itsDetails['sor_lid']);
                    $asm = new \tldUser($sol->itsHeader['asmID']);
                    $TO[] = [$asm->getEmail()];
                    $grpSA = new \tldGroup('role_SA', $sol->itsHeader['sso_erp']);
                    $TO[] = $grpSA->getEmailList();
                } elseif (!empty($row['sales_org'])) {
                    $ERP_SSO = \tldLocation::getERPByLocation($row['sales_org']);
                    $grpSA = new \tldGroup('role_SA', $ERP_SSO);
                    $TO[] = $grpSA->getEmailList();
                }

                // Notify the update
                $wasEmailSent = \tldUtils::emailAttachment(
                    array_unique(array_merge(...$TO)),
                    'noreply@tld-gse.com',
                    $subject,
                    $body
                );

                if (!$wasEmailSent) {
                    $this->logger->error('ERROR: problem sending email...');

                    continue;
                }

                $this->logger->info('Email sent successfully...');
            }
        }

        return Command::SUCCESS;
    }
}
