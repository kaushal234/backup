<?php

declare(strict_types=1);

namespace App\Job\Service;

use App\Job\LoggerAwareCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'service:toc:daily_report')]
class SendDailyTocReport extends LoggerAwareCommand
{
    public function getDescription(): string
    {
        return 'Send Daily TOC report';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $ssos = \tldLocation::getSalesOrgList('smartyOptionsIDLocation');
        $yesterday = (new \DateTime())->modify('-24 hours')->format('Y-m-d');

        foreach ($ssos as $ssoId => $ssoName) {
            $body = '';
            $reports = [
                'New' => "WHERE toc.dt > '{$yesterday}' AND toc.status NOT IN ('CLOSED', 'SOLVED') AND (toc.ssoid={$ssoId} OR er.sso_service = {$ssoId})",
                'Solved' => "WHERE toc.dt_closed > '{$yesterday}' AND toc.status NOT IN ('IN PROGRESS', 'SUSPENDED') AND (toc.ssoid={$ssoId} OR er.sso_service = {$ssoId})",
            ];

            $extranetReport = \tldTOC::getTocsFromExtranetDailyReport($ssoName, $yesterday);
            if (!empty($extranetReport)) {
                $body .= $extranetReport->fetch();
            }

            foreach ($reports as $reportType => $sqlWhere) {
                $report = \tldTOC::getDailyReport(\sprintf('Daily %s TOC Recap - SSO/SSO Service: %s ', $reportType, $ssoName), $sqlWhere);
                if (!empty($report)) {
                    $body .= $report->fetch();
                }
            }

            if ('' === $body) {
                continue;
            }

            $recipients = \tldTOC::getDailyRecapEmailRecipients($ssoId);
            $emailSent = \tldUtils::emailAttachment($recipients, 'noreply@tld-gse.com', \sprintf('Daily TOC Recap - SSO/SSO Service: %s ', $ssoName), $body);

            if (!$emailSent) {
                $this->logger->error(\sprintf('ERROR : Daily %s TOC Recap - SSO/SSO Service: %s not sent.', $reportType, $ssoName));
                continue;
            }
            $this->logger->info(\sprintf('Daily %s TOC Recap - SSO/SSO Service: %s sent to %s', $reportType, $ssoName, implode(', ', $recipients)));
        }

        return Command::SUCCESS;
    }
}
