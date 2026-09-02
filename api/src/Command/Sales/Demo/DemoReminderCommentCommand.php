<?php

declare(strict_types=1);

namespace App\Command\Sales\Demo;

use App\Notifier\Sales\Demo\DemoNotifier;
use App\Repository\Sales\DemoRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:sales:reminder_comment_demos')]
class DemoReminderCommentCommand extends Command
{
    private readonly DemoRepository $demoRepository;
    private readonly DemoNotifier $notifier;

    public function __construct(DemoRepository $demoRepository, DemoNotifier $notifier)
    {
        parent::__construct();

        $this->demoRepository = $demoRepository;
        $this->notifier = $notifier;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->demoRepository->findUncommentedDemo() as $demo) {
            $this->notifier->sendReminderEmail($demo);
        }

        return 0;
    }
}
