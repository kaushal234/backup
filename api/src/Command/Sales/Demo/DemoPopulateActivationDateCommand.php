<?php

declare(strict_types=1);

namespace App\Command\Sales\Demo;

use App\Entity\Sales\Demo;
use App\Repository\Common\LogRepository;
use App\Repository\Sales\DemoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:sales:populate_activation_date_demos')]
class DemoPopulateActivationDateCommand extends Command
{
    private readonly EntityManagerInterface $em;
    private readonly DemoRepository $demoRepository;
    private readonly LogRepository $logRepository;

    public function __construct(EntityManagerInterface $em, DemoRepository $demoRepository, LogRepository $logRepository)
    {
        parent::__construct();
        $this->setDescription('Calculate and populate the activation date of all Demos');

        $this->em = $em;
        $this->demoRepository = $demoRepository;
        $this->logRepository = $logRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var Demo $demo */
        foreach ($this->demoRepository->findAll() as $demo) {
            $logs = $this->logRepository->findAllResourceUpdates($demo);
            foreach ($logs as $log) {
                if (isset($log['changeSet']['status']) && 'ACTIVE' === $log['changeSet']['status'][1]) {
                    $demo->setActivatedAt($log['createdAt']);
                    break;
                }
            }
            $this->em->persist($demo);
        }
        $this->em->flush();

        return 0;
    }
}
