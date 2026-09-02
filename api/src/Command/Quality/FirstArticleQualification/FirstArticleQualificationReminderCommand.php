<?php

declare(strict_types=1);

namespace App\Command\Quality\FirstArticleQualification;

use App\Entity\Quality\FirstArticleQualification\FirstArticleQualification;
use App\Manager\Quality\FirstArticleQualification\FirstArticleQualificationDateReminderManager;
use App\Notifier\Quality\FirstArticleQualification\FirstArticleQualificationNotifier;
use App\Repository\Quality\FirstArticleQualification\FirstArticleQualificationRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'tld:faq:reminder')]
class FirstArticleQualificationReminderCommand extends Command
{
    private readonly FirstArticleQualificationRepository $firstArticleQualificationRepository;
    private readonly FirstArticleQualificationDateReminderManager $manager;
    private readonly FirstArticleQualificationNotifier $notifier;

    public function __construct(FirstArticleQualificationRepository $firstArticleQualificationRepository, FirstArticleQualificationDateReminderManager $manager, FirstArticleQualificationNotifier $notifier)
    {
        parent::__construct();
        $this->setDescription('Reminder for FAQ due date and plan definition date');
        $this->firstArticleQualificationRepository = $firstArticleQualificationRepository;
        $this->manager = $manager;
        $this->notifier = $notifier;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var FirstArticleQualification[] $firstArticleQualifications */
        $firstArticleQualifications = $this->firstArticleQualificationRepository->findFAQForReminderDateCommand();

        if ([] === $firstArticleQualifications) {
            return 0;
        }

        $this->notifier->sendReminderStatus($this->manager->handleFirstArticleQualifications($firstArticleQualifications));

        return 0;
    }
}
