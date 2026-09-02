<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Module\ChangeLog;
use App\Entity\Module\Module;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'mis:tickets:report')]
class TicketsReportCommand extends Command
{
    private const TICKETS_WITH_CODE_UPDATE = 'Tickets with code update';
    private const TICKETS = 'Tickets';
    private readonly Connection $legacyConnection;
    private readonly EntityManagerInterface $entityManager;

    public function __construct(
        Connection $legacyConnection,
        EntityManagerInterface $entityManager
    ) {
        parent::__construct();
        $this->setDescription('Internal command to analyze tickets');

        $this
            ->addArgument('date_from', InputArgument::REQUIRED, 'Start date of report (inclusive)')
            ->addArgument('date_to', InputArgument::REQUIRED, 'End date of report (exclusive)')
        ;

        $this->legacyConnection = $legacyConnection;
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $qb = $this->legacyConnection->createQueryBuilder();

        $moduleRepository = $this->entityManager->getRepository(Module::class);
        $changeLogRepository = $this->entityManager->getRepository(ChangeLog::class);

        $qb
            ->select('id', 'task', 'assignor', 'cat')
            ->from('tasks')
            ->where($qb->expr()->like('task', ':search'))
            ->andWhere($qb->expr()->gte('date', ':date_from'))
            ->andWhere($qb->expr()->lt('date', ':date_to'))
            ->setParameters([
                'date_from' => $input->getArgument('date_from'),
                'date_to' => $input->getArgument('date_to'),
            ])
        ;

        $out = fopen('php://output', 'w');
        $headers = false;

        foreach ($moduleRepository->findBy([], ['name' => 'ASC']) as $module) {
            $acronym = $module->getName();
            $moduleQb = clone $qb;
            $moduleQb->setParameter('search', \sprintf('%%<b>Module</b>: %s%%', $acronym));

            $stmt = $moduleQb->executeQuery();
            $line = [
                'Module' => $acronym,
                'MIS owner' => $module->getMisOwner(),
                'MOO' => $module->getOperationalOwner(),
                self::TICKETS => $stmt->rowCount(),
                self::TICKETS.' A' => 0,
                self::TICKETS.' B' => 0,
                self::TICKETS.' C' => 0,
                self::TICKETS_WITH_CODE_UPDATE => 0,
                'C '.self::TICKETS_WITH_CODE_UPDATE => 0,
            ];

            $assignors = [];
            foreach ($stmt->fetchAllAssociative() as $ticket) {
                $assignors[] = $ticket['assignor'];
                $logs = $changeLogRepository->findBy(['ticket' => $ticket['id']]);
                if ([] !== $logs) {
                    ++$line[self::TICKETS_WITH_CODE_UPDATE];
                    if ('C' === $ticket['cat']) {
                        ++$line['C '.self::TICKETS_WITH_CODE_UPDATE];
                    }
                }
                if (!\in_array($ticket['cat'], ['A', 'B', 'C'], true)) {
                    continue;
                }
                ++$line[self::TICKETS.' '.$ticket['cat']];
            }

            $line['Distinct assignors'] = \count(array_unique($assignors));
            $line['Percentage of code update per tickets'] = $line[self::TICKETS] > 0 ? round($line[self::TICKETS_WITH_CODE_UPDATE] / $line[self::TICKETS] * 100, 2) : null;
            $line['Percentage of code update per C tickets'] = $line[self::TICKETS.' C'] > 0 ? round($line['C '.self::TICKETS_WITH_CODE_UPDATE] / $line[self::TICKETS.' C'] * 100, 2) : null;

            if (!$headers) {
                fputcsv($out, array_keys($line));
                $headers = true;
            }

            fputcsv($out, $line);
        }

        fclose($out);

        return 0;
    }
}
