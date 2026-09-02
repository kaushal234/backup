<?php

declare(strict_types=1);

namespace App\Command\Directory;

use App\Entity\Directory\People;
use App\Repository\Directory\PeopleRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:people:hide-and-unhide')]
class HideAndUnhideDisabledPeopleCommand extends Command
{
    public const LIMIT_VISIBILITY_BEFORE_ARRIVAL = '+1 weeks';
    public const LIMIT_VISIBILITY_AFTER_DEPARTURE = '-3 months';

    public function __construct(
        private readonly PeopleRepository $peopleRepository,
    ) {
        parent::__construct();
        $this->setDescription('Hide and unhidden people');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $hidden = 0;
        $unhidden = 0;
        /** @var People $people */
        foreach ($this->peopleRepository->findDisabledPeopleToUnhide() as $people) {
            $this->peopleRepository->unhidePeople($people);
            $output->writeln(\sprintf('<info>Arrival %s, unhidden %s. </info>', $people->getEnableAt()->format('Y-m-d'), $people->getLastname().', '.$people->getFirstname()));
            ++$unhidden;
        }

        /** @var People $people */
        foreach ($this->peopleRepository->findDisabledPeopleToHide() as $people) {
            $this->peopleRepository->hidePeople($people);
            $output->writeln(\sprintf('<info>Departure %s, hidden %s.</info>', $people->getDisabledAt()->format('Y-m-d'), $people->getLastname().', '.$people->getFirstname()));
            ++$hidden;
        }

        $output->writeln(\sprintf('<info>unhidden %d people.</info>', $unhidden));
        $output->writeln(\sprintf('<comment>hidden %d people.</comment>', $hidden));

        return Command::SUCCESS;
    }
}
