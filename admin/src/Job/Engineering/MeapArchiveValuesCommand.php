<?php

declare(strict_types=1);

namespace App\Job\Engineering;

use App\Job\LoggerAwareCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

include_once 'eng.inc.php';

#[AsCommand(name: 'engineering:meap:archive')]
class MeapArchiveValuesCommand extends LoggerAwareCommand
{
    public function getDescription(): string
    {
        return 'Update MEAP archive values';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $process_time = mktime(0, 0, -1);  // get end of day yesterday
        $Ym = date('Y-m', $process_time);
        $fulldate = date('Y-m-d H:i:s', $process_time);

        // Get open MEAPs
        $query = "SELECT id FROM meap WHERE status NOT IN('CLOSED','REJECTED')";
        $meaps = \tldUtils::getSqlToAssocArray($query);

        // Iterate MEAPs
        foreach ($meaps as $value) {
            $meapId = (int) $value['id'];
            $meap = new \tldMEAP($meapId);
            $hours = $meap->refreshActualDevHours();

            // Check if monthly archive exists
            $query = "SELECT COUNT(*) AS `count`
                FROM meap_archive
                WHERE
                    parent_id={$meapId} AND
                    archive_type='econ_actual_dh' AND
                    archive_dt LIKE '{$Ym}%'";

            $res = \tldUtils::getSqlRowToAssocArray($query);
            if ($res['count'] > 0) {
                // Do update
                $query = "UPDATE meap_archive
                    SET
                        archived_value='{$hours}',
                        archive_dt='{$fulldate}'
                    WHERE
                        parent_id={$meapId} AND
                        archive_type='econ_actual_dh' AND
                        archive_dt LIKE '{$Ym}%'";
                \tldUtils::sqlQuery($query);
                $this->logger->info(\sprintf('Updated MEAP archive for MEAP#%s', $meapId));
            } else {
                // Do insert
                $query = "INSERT INTO meap_archive
                    SET
                        parent_id={$meapId},
                        archive_type='econ_actual_dh',
                        archived_value='{$hours}',
                        archive_dt='{$fulldate}'";
                \tldUtils::sqlQuery($query);
                $this->logger->info(\sprintf('Inserted MEAP archive for MEAP#%s', $meapId));
            }
        }

        return Command::SUCCESS;
    }
}
