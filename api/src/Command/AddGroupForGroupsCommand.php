<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Acl;
use App\Entity\Directory\People;
use App\Entity\Group;
use App\Repository\Directory\PeopleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:insert:group_for_groups')]
class AddGroupForGroupsCommand extends Command
{
    private readonly EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->setDescription('Add a group to users who are in some other groups');

        $this->addArgument('group', InputArgument::REQUIRED, 'group to add');
        $this->addArgument('groups', InputArgument::IS_ARRAY, 'groups for which the group should be added', []);
        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var PeopleRepository $peopleRepository */
        $peopleRepository = $this->entityManager->getRepository(People::class);
        $groupRepository = $this->entityManager->getRepository(Group::class);

        /** @var string $groupName */
        $groupName = $input->getArgument('group');
        /** @var array $groupNames */
        $groupNames = $input->getArgument('groups');

        $group = $groupRepository->findOneBy(['name' => $groupName]);

        if (!$group instanceof Group) {
            $output->writeln(\sprintf('<error>Group %s does not exist, please create it first</error>', $groupName));

            return 1;
        }

        $i = 0;
        foreach ($peopleRepository->findGroupsMembers($groupNames) as $people) {
            $acl = (new Acl())
                ->setUser($people)
                ->setGroup($group)
                ->setLocation(null !== ($businessUnit = $people->getBusinessUnit()) ? $businessUnit->getLocation() : null);

            $output->writeln(\sprintf('Adding group %s to %s', $group->getName(), $people->getUserIdentifier()));

            $this->entityManager->persist($acl);

            ++$i;
            if (0 === $i % 100) {
                $this->entityManager->flush();
            }
        }

        $this->entityManager->flush();

        return 0;
    }
}
