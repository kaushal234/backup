<?php

declare(strict_types=1);

namespace App\Job\DMS;

use App\Job\LoggerAwareCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

include_once 'dms.inc.php';

#[AsCommand(name: 'dms:expiration')]
class CheckDmsExpirationCommand extends LoggerAwareCommand
{
    public function getDescription(): string
    {
        return 'Check expired DMS';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Get list of DMS that should be expired
        $a = <<<'EOF'
dms.status LIKE 'ACTIVE' AND DATE_ADD(dms.dt_act, INTERVAL dms.periodicity MONTH) < NOW()
EOF;
        $dmsList = \tldDMS::byConstraints($a);
        // Any results?
        if (0 === \count($dmsList)) {
            $this->logger->error('No records found');

            return Command::SUCCESS;
        }
        // Set Expired
        foreach ($dmsList as $dmsInfo) {
            $dms = new \tldDMS($dmsInfo['id']);
            $e = $dms->updateStatus('EXPIRED', 0);
            $id = $dms->getID();
            if (\is_string($e)) {
                $this->logger->error('DMS#'.$id." status error: $e");
                continue;
            }
            $this->logger->info('DMS#'.$id.' status set to EXPIRED');
            $message = <<<EOF
            'DMS#'.$id.'has been EXPIRED. Please update the DMS, if you want to ACTIVE it'
            <br><br><br>
            <a href=" https://dms.tld-group.com/index.php?m[0]=view&id=$id"</a>
EOF;
            $dms->notifyOwner('is EXPIRED', $message, []);
        }

        return Command::SUCCESS;
    }
}
