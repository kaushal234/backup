<?php

declare(strict_types=1);

namespace App\Command\Sales\ExtranetUser;

use App\Factory\ExtranetUserFactory;
use App\Http\ModuloClient;
use App\Manager\Sales\ExtranetUserManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'tld:extranet_user:handle')]
class HandleExtranetUserRequestCommand extends Command
{
    public function __construct(
        private readonly ExtranetUserManager $extranetUserManager,
        private readonly ExtranetUserFactory $extranetUserFactory,
        private readonly LoggerInterface $logger,
        private readonly ModuloClient $moduloClient
    ) {
        parent::__construct();
        $this->setDescription('Handle an Extranet User request from TLD website');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $extranetAccessRequests = $this->moduloClient->getContactFormSubmitted();
        $extranetUsersCreatedCount = 0;
        $moduloRequestsIdsToBeClosed = [];

        foreach ($extranetAccessRequests as $request) {
            $moduloRequestsIdsToBeClosed[] = $request['id'];
            $data = $request['form_data'];
            $email = mb_trim(mb_strtolower((string) $data['email']));

            $extranetUserFactory = $this->extranetUserFactory;
            $extranetUser = $extranetUserFactory(array_merge($data, ['email' => $email]));

            try {
                $this->extranetUserManager->handleExtranetUserRequest($extranetUser);
                ++$extranetUsersCreatedCount;
                $output->writeln(\sprintf('[%s]: %s %s created', (new \DateTime())->format('Y-m-d H:i:s'), $data['firstname'], $data['lastname']));
            } catch (\Exception $exception) {
                $this->logger->critical('Extranet User Request failed. Reason: '.$exception->getMessage());
                $output->writeln(\sprintf('[%s]: Extranet User Request failed. Reason: %s', (new \DateTime())->format('Y-m-d H:i:s'), $exception->getMessage()));
            }
        }

        $output->writeln(\sprintf('%s extranet user accounts created', $extranetUsersCreatedCount));
        $this->moduloClient->closeContactFormEntries($moduloRequestsIdsToBeClosed);

        return Command::SUCCESS;
    }
}
