<?php

declare(strict_types=1);

namespace App\Job\Manufacturing;

use App\Job\LoggerAwareCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

include_once 'erp.inc.php';
include_once 'sales_service.inc.php';

#[AsCommand(name: 'manufacturing:outbound')]
class OutboundTrackingNumberCommand extends LoggerAwareCommand
{
    public function getDescription(): string
    {
        return 'Send tracking numbers by email';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $start = new \DateTimeImmutable('yesterday');
        $end = new \DateTimeImmutable('today');

        $query = "SELECT dinos.*, docs.doc_type, docs.uid, docs.xml, docs.num, docs.filepath
            FROM erp_dino_trno as dinos
            JOIN erp_archive as docs ON docs.erp=dinos.erp AND docs.num=dinos.dino
            WHERE
                docs.doc_type = 'PACKING SLIP'
            AND docs.uid != ''
            AND docs.dt >= '{$start->format('Y-m-d H:i:s')}'
            AND docs.dt < '{$end->format('Y-m-d H:i:s')}'";

        $rows = \tldUtils::getSqlToAssocArray($query);
        if (0 === \count($rows)) {
            $this->logger->warning('No transactions to process...');

            return Command::SUCCESS;
        }

        $adds = [];
        foreach ($rows as $row) {
            try {
                $xml = new \SimpleXMLElement($row['xml'], \LIBXML_ERR_WARNING);
            } catch (\Exception $e) {
                $this->logger->error('Could not parse input xml -> '.$e->getMessage());

                continue;
            }

            $this->logger->info("{$row['dt']}|{$row['erp']}|{$row['doc_type']}|{$row['num']}");

            $erp = $row['erp'];
            $cdel = trim((string) $xml->TLDHEADER->SOCDEL);
            $cuno = trim((string) $xml->TLDHEADER->PARTNERID);
            $refa = trim((string) $xml->TLDHEADER->PONUM);

            // Prepare body
            $body = '<p>Thank you for your order. Your order has shipped and can be tracked with the link below.</p>';
            if ($erp >= 500 && $erp < 560) {
                $doc_type = 'Suivi de commande';
                $body .= '
                    <p>
                        Merci pour votre commande. Votre commande a été envoyée et peut être suivie en cliquant sur le lien ci-dessous.
                    </p>';
            }

            $body .= "
            <p>
                <a href=\"http://www.tld-gse.com/shared/redirect_courier.php?courier={$row['courier']}&trno={$row['trno']}\">
                    {$row['courier']} {$row['trno']}
                </a>
            </p>";

            $this->logger->info("$cuno|$cdel|$refa|uid={$row['uid']}");

            // Get address by role in customer addressbook
            if (empty($adds[$erp][$cuno][$cdel]['fl_NOT_SPR_PS'])) {
                $adds[$erp][$cuno][$cdel]['fl_NOT_SPR_PS'] = \tldCRT::getAddresses($erp, $cuno, $cdel, 'fl_NOT_SPR_PS');
            }
            $team = $adds[$erp][$cuno][$cdel]['fl_NOT_SPR_PS'];

            // Construct recipients
            $TO = null;
            if (empty($team['cust_emails'])) {
                $body .= "<h1>No online customer information</h1>\n";
                $TO = $row['uid'];
            } else {
                $TO = $team['cust_emails'];
            }
            $CC = $row['uid'];
            if (!empty($team['cust_emails'])) {
                $CC .= ','.$team['sph_emails'];
            }

            $TOToLog = \is_array($TO) ? implode(',', $TO) : $TO;
            $CCToLog = \is_array($CC) ? implode(',', $CC) : $CC;
            $this->logger->info("About to send mail TO: $TOToLog\n with CC: $CCToLog");

            // Prepare email subject
            $subject = "$cuno/Courier Tracking";
            if (!empty($doc_type)) {
                $subject .= "/$doc_type";
            }
            $subject .= '/'.trim($row['num']);
            if (!empty($refa)) {
                $subject .= '/Ref#'.trim($refa);
            }
            // Send email notification
            $wasEmailSent = \tldUtils::emailAttachment(
                $TO,
                $row['uid'],
                $subject,
                $body,
                '/mnt/grpfps10.strs_pdf/ARCHIVE/'.$row['filepath'],
                $CC,
                null,
                ['charset' => 'UTF-8']
            );
            if ($wasEmailSent) {
                $this->logger->info('Email sent successfully...');
            } else {
                $this->logger->error('ERROR: problem sending email...');
            }
        }

        return Command::SUCCESS;
    }
}
