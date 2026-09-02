<?php

declare(strict_types=1);

namespace App\Command\Directory;

use App\Entity\Acl;
use App\Entity\Directory\ContractType;
use App\Entity\Directory\People;
use App\Entity\Group;
use App\Repository\AclRepository;
use App\Repository\Directory\PeopleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:people:activate')]
class ActivatePeopleCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
        $this->setDescription('Activate people and give them ACL_AUTH_INTRANET')
            ->addOption('since', null, InputOption::VALUE_REQUIRED, 'Lower bound (format: Y-m-d) for the arrival date (enableAt) window, instead of today. Use this for a manual catch-up after a missed run: pass the earliest enableAt date you want to sweep in, e.g. the last date the daily cron ran successfully, NOT the date you are running the command')
            ->setHelp(
                <<<'HELP'
                    By default this command activates people whose arrival date (enableAt) falls between today and tomorrow — this is what the daily cron runs.

                    If the cron failed to run on one or more days, people who arrived in that gap are never picked up automatically (enableAt is a one-shot field, not reset). Use --since to catch them up manually:

                        <info>php bin/console api:people:activate --since=2026-08-06</info>

                    --since is a lower bound on enableAt: pass the earliest arrival date you want to include (e.g. the last day the cron ran successfully), not the date you are running the command. The upper bound stays fixed at tomorrow.
                    HELP
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $since = $input->getOption('since');
        $sinceDate = null !== $since ? new \DateTime($since) : new \DateTime();
        $untilDate = new \DateTime('+ 1 days');

        /** @var PeopleRepository $peopleRepository */
        $peopleRepository = $this->entityManager->getRepository(People::class);
        $groupRepository = $this->entityManager->getRepository(Group::class);

        /** @var AclRepository $aclRepository */
        $aclRepository = $this->entityManager->getRepository(Acl::class);

        $peopleToActivate = $peopleRepository->findPeopleToActivate($sinceDate);
        $output->writeln(\sprintf('<comment>Looking for people with an arrival date (enableAt) between %s and %s: %d found.</comment>', $sinceDate->format('Y-m-d'), $untilDate->format('Y-m-d'), \count($peopleToActivate)));

        $skippedAcl = 0;

        /** @var People $people */
        foreach ($peopleToActivate as $people) {
            $peopleRepository->enableAndUnhidePeople($people);

            // SFE is for Shop Floor Employees, they don't need intranet access if they are in temporary contract type
            if ('SFE' !== $people->getPosition()?->getCode() || ContractType::TEMP_AND_CONSULTANTS !== $people->getContractType()?->name) {
                $aclRepository->updatePeopleAclByGroup($people, $groupRepository->findOneBy(['name' => 'ACL_AUTH_INTRANET']));
                $this->entityManager->flush();
                $output->writeln(\sprintf('<info>Arrival %s, activated %s (ACL_AUTH_INTRANET granted).</info>', $people->getEnableAt()?->format('Y-m-d'), $people->getLastname().', '.$people->getFirstname()));
            } else {
                ++$skippedAcl;
                $output->writeln(\sprintf('<info>Arrival %s, activated %s (temporary SFE, ACL_AUTH_INTRANET skipped).</info>', $people->getEnableAt()?->format('Y-m-d'), $people->getLastname().', '.$people->getFirstname()));
            }
        }

        $output->writeln(\sprintf('<info>activated %d people.</info>', \count($peopleToActivate)));
        $output->writeln(\sprintf('<comment>skipped ACL_AUTH_INTRANET for %d temporary SFE.</comment>', $skippedAcl));

        return Command::SUCCESS;
    }
}
