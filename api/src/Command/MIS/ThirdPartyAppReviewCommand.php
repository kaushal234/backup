<?php

declare(strict_types=1);

namespace App\Command\MIS;

use App\Entity\Module\ThirdPartyApp\Type\Light;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Manager\TaskManager;
use LegacyBundle\Model\Task;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsCommand(name: 'api:mis:third_party_app:review', description: 'Create security and account review tasks.')]
class ThirdPartyAppReviewCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TaskManager $taskManager,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
        parent::__construct();
    }

    public function isTaskOpened(?int $taskId): bool
    {
        if (null === $taskId) {
            return false;
        }

        $task = $this->taskManager->getTask($taskId);

        if (isset($task['status']) && 'OPEN' === $task['status']) {
            return true;
        }

        return false;
    }

    /**
     * Check if the start date with frequency is matching the current date.
     */
    public function isReviewNeeded(\DateTime $startDate, int $frequency): bool
    {
        $currentDate = new \DateTime();

        if ($startDate->format('Y-m-d') >= $currentDate->format('Y-m-d')) {
            return false;
        }

        while ($startDate->format('Y-m-d') < $currentDate->format('Y-m-d')) {
            $startDate->modify(\sprintf('+%d months', $frequency));
            if ($startDate->format('Y-m-d') === $currentDate->format('Y-m-d')) {
                return true;
            }
        }

        return false;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $modulesAccountReviewNeeded = $this->entityManager->getRepository(Light::class)->findAccountReviewNeeded();
        /* @var Light $thirPartyApp */
        foreach ($modulesAccountReviewNeeded as $thirdPartyApp) {
            if (!$this->isTaskOpened($thirdPartyApp->lastAccountReviewTaskId) && $this->isReviewNeeded($thirdPartyApp->accountReviewDateStart, $thirdPartyApp->accountReviewFrequency)) {
                $this->createAccountReviewTask($thirdPartyApp);
            }
        }

        $modulesSecurityReviewNeeded = $this->entityManager->getRepository(Light::class)->findSecurityReviewNeeded();
        /* @var Light $thirPartyApp */
        foreach ($modulesSecurityReviewNeeded as $thirdPartyApp) {
            if (!$this->isTaskOpened($thirdPartyApp->lastSecurityReviewTaskId) && $this->isReviewNeeded($thirdPartyApp->securityReviewDateStart, $thirdPartyApp->securityReviewFrequency)) {
                $this->createSecurityReviewTask($thirdPartyApp);
            }
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }

    protected function createAccountReviewTask(Light $thirdPartyApp): void
    {
        $description = '
You are MOO or Administrator of the 3rd party application %1$s,<br \>
According to defined frequency, you have to perform the account review for this application.<br />
Please go to the administration page of this module following the link here below and fill the Account Audit page as specified.<br />
<a href="%2$s">Account audit page of %1$s</a><br />
Once done, close this task to confirm you submitted the Account Audit results in the module.
';
        $accountReviewLink = $this->urlGenerator->generate('third_party_app_account_review', [
            'id' => $thirdPartyApp->getId(),
        ]);

        $taskId = $this->createTask($thirdPartyApp, \sprintf($description, $thirdPartyApp->getName(), $accountReviewLink));

        $thirdPartyApp->lastAccountReviewTaskId = $taskId;
    }

    protected function createSecurityReviewTask(Light $thirdPartyApp): void
    {
        $description = '
You are MOO or Administrator of the 3rd party application %1$s,<br \>
According to defined frequency, you have to perform the Information Security Review.<br />
Please go to the administration page of this module following the link here below and fill the Security Audit page as specified.<br />
<a href="%2$s">Security Audit page of %1$s</a><br />
Once done, close this task to confirm you submitted the Security Audit results in the module.
';
        $accountReviewLink = $this->urlGenerator->generate('third_party_app_security_review', [
            'id' => $thirdPartyApp->getId(),
        ]);

        $taskId = $this->createTask($thirdPartyApp, \sprintf($description, $thirdPartyApp->getName(), $accountReviewLink));

        $thirdPartyApp->lastSecurityReviewTaskId = $taskId;
    }

    protected function createTask(Light $thirdPartyApp, string $description): int
    {
        $assignee = $thirdPartyApp->getOperationalOwner();
        if ($mainAdmin = $thirdPartyApp->getMainAdmin()) {
            $assignee = $mainAdmin;
        }

        $task = (new Task())
            ->setAssignee($assignee)
            ->setAssignor($thirdPartyApp->getOperationalOwner())
            ->setModule($thirdPartyApp->getName())
            ->setLocation($assignee->getBusinessUnit()->getLocation())
            ->setDescription($description)
        ;

        return $this->taskManager->insert($task);
    }
}
