<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Acl;
use App\Entity\Directory\People;
use App\Entity\Directory\Position;
use App\Entity\Group;
use App\Repository\Directory\PeopleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

#[AsCommand(name: 'api:insert:group_for_position')]
class AddGroupForPositionCommand extends Command
{
    private readonly EntityManagerInterface $em;

    private readonly PeopleRepository $peopleRepository;

    /**
     * AddRoleForPositionCommand constructor.
     */
    public function __construct(EntityManagerInterface $em, PeopleRepository $peopleRepository)
    {
        parent::__construct();
        $this->setDescription('Add a group to a specific position');

        $this->em = $em;
        $this->peopleRepository = $peopleRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $positionRepository = $this->em->getRepository(Position::class);
        $groupRepository = $this->em->getRepository(Group::class);

        $availablePositions = array_reduce($positionRepository->findAll(), static function ($memo, Position $position) {
            $memo[] = $position->getDescription();

            return $memo;
        }, []);

        $availableGroups = array_reduce($groupRepository->findAll(), static function ($memo, Group $group) {
            $memo[] = $group->getName();

            return $memo;
        }, []);

        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $question = new Question('Which group do you want to add ? ');
        $question->setAutocompleterValues($availableGroups);

        if (!$chosenGroup = $helper->ask($input, $output, $question)) {
            return 0;
        }

        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $question = new Question(\sprintf('To which position do you want to add the group %s ? ', $chosenGroup));
        $question->setAutocompleterValues($availablePositions);

        if (!$chosenPosition = $helper->ask($input, $output, $question)) {
            return 0;
        }

        $targetGroup = $groupRepository->findOneBy(['name' => $chosenGroup]);
        $targetPosition = $positionRepository->findOneBy(['description' => $chosenPosition]);

        if (!$targetGroup instanceof Group) {
            $output->writeln(\sprintf('<error>Group "%s" not found</error>', $chosenGroup));

            return 1;
        }

        if (!$targetPosition instanceof Position) {
            $output->writeln(\sprintf('<error>Position "%s" not found</error>', $chosenPosition));

            return 1;
        }

        $targets = $this->peopleRepository->findBy(['position' => $targetPosition]);

        /** @var People $people */
        foreach ($targets as $people) {
            if (null === $people->getBusinessUnit()) {
                $output->writeln(\sprintf('<error>People "%d" does not have any BU </error>', $people->getId()));

                continue;
            }
            $acl = new Acl();
            $acl
              ->setLocation($people->getBusinessUnit()->getLocation())
              ->setGroup($targetGroup)
              ->setUser($people)
            ;

            $this->em->persist($acl);
        }

        $this->em->flush();

        return 0;
    }
}
