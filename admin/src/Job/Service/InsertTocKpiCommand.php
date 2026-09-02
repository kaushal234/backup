<?php

declare(strict_types=1);

namespace App\Job\Service;

use App\Job\LoggerAwareCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'service:toc:kpi_insert')]
class InsertTocKpiCommand extends LoggerAwareCommand
{
    public function getDescription(): string
    {
        return 'Insert TOC KPI in mod_kpi table';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $oneMonthAgo = new \DateTime('1 month ago');

        $locations = \tldLocation::getSalesOrgList('smartyOptionsIDLocation');
        $activityTypes = \tldTOC::getActivityTypeList() + ['all' => null];

        foreach ($locations as $buid => $location) {
            foreach ($activityTypes as $name => $activityType) {
                $query = "ssoid=$buid";
                if ('all' !== $name) {
                    $query .= " and activity_type='$activityType'";
                }

                $rows = \tldTOC::getKPIByConstraints(
                    $query,
                    ['periodConstraints' => "p.nam_period='{$oneMonthAgo->format('Ym')}'"]
                );

                foreach (['nto', 'nts', 'tir', 'tat', 'tol'] as $type) {
                    $a = [
                        'module' => 'TOC',
                        'key1' => $buid,
                        'y' => $oneMonthAgo->format('Y'),
                        'm' => $oneMonthAgo->format('m'),
                        'name' => $type,
                        'val' => $rows[0][$type],
                    ];

                    if (null !== $activityType) {
                        $a['key2'] = 'other' !== $name ? $activityType : $name;
                    }

                    $e = \tldModKPI::insert($a);
                    \is_string($e) ? $this->logger->error(\sprintf('tldModKPI::insert error: %s', $e)) : $this->logger->info(\sprintf('TOC KPI inserted. Type: %s, SSO: %s, Activity Type: %s', $type, $location, $name));
                }
            }
        }

        return Command::SUCCESS;
    }
}
