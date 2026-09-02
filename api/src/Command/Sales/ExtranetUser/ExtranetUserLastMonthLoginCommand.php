<?php

declare(strict_types=1);

namespace App\Command\Sales\ExtranetUser;

use App\Notifier\Sales\ExtranetUser\ExtranetUserLoginNotifier;
use App\Repository\UserConnectionRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:extranet_user:last_login')]
class ExtranetUserLastMonthLoginCommand extends Command
{
    private readonly ExtranetUserLoginNotifier $notifier;
    private readonly UserConnectionRepository $repository;

    public function __construct(UserConnectionRepository $repository, ExtranetUserLoginNotifier $notifier)
    {
        parent::__construct();
        $this->setDescription('Send notifications with Extranet User that logged in last month');
        $this->notifier = $notifier;
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->notifier->sendEmail($this->repository->getExtranetUsersConnectionsForLastMonth());

        return 0;
    }
}
