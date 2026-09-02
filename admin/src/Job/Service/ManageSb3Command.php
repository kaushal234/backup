<?php

declare(strict_types=1);

namespace App\Job\Service;

use App\Job\LoggerAwareCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

include_once 'product_support.inc.php';

#[AsCommand(name: 'service:sb')]
class ManageSb3Command extends LoggerAwareCommand
{
    public function getDescription(): string
    {
        return 'SB3 check SSD approval, check SSD decision, close SB with closed lines only, check customer decision';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->logger->info('SB3 check CSM_APPROVAL');
        \tldSB3::checkCSM_APPROVAL();

        $this->logger->info('SB3 check SSD_DECISION');
        \tldSB3::checkSSD_DECISION();

        $this->logger->info('SB3 close SB with closed lines only');
        \tldSB3::autocloseSBWithClosedLines();

        $this->logger->info('SB3 check ecust with ER in many SSO');
        \tldSB3::checkEcustERMultipleSSO();

        $this->logger->info('SB3 check CUSTOMER_TO_DECIDE()');
        \tldSB_Line::checkCUSTOMER_TO_DECIDE();

        return Command::SUCCESS;
    }
}
