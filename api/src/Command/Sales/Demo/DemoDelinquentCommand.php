<?php

declare(strict_types=1);

namespace App\Command\Sales\Demo;

use App\Entity\Sales\Demo;
use App\Notifier\Sales\Demo\DemoNotifier;
use App\Repository\Sales\DemoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:sales:delinquent_demos')]
class DemoDelinquentCommand extends Command
{
    private readonly EntityManagerInterface $em;
    private readonly DemoRepository $demoRepository;
    private readonly DemoNotifier $notifier;

    public function __construct(EntityManagerInterface $em, DemoRepository $demoRepository, DemoNotifier $notifier)
    {
        parent::__construct();
        $this->setDescription('Check last comment of Demo to make it delinquent or not');

        $this->em = $em;
        $this->demoRepository = $demoRepository;
        $this->notifier = $notifier;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $expiredDemos = $this->demoRepository->findExpiredDemos();

        /** @var Demo $demo */
        foreach ($expiredDemos as $demo) {
            $demo->setDelinquent(true);
            $this->em->persist($demo);

            switch (true) {
                case $demo->getRevisedEndDate() < new \DateTime():
                    $reason = 'revised end date';
                    break;
                case $demo->getLastCommentedAt() < new \DateTime('3 month ago') && Demo::ACTIVE !== $demo->getStatus():
                    $reason = 'the last comment is older than 3 month ago and the demo is not active';
                    break;
                case $demo->getLastCommentedAt() < new \DateTime('1 month ago'):
                    $reason = 'the last comment is older than 1 month ago';
                    break;
                case $demo->getExpectedEndDate() < new \DateTime():
                    $reason = 'expected end date';
                    break;
                default:
                    $reason = 'Demo is not commented';
                    break;
            }

            $this->notifier->sendDelinquentEmail($demo, $reason);
        }

        $this->em->flush();

        return 0;
    }
}
