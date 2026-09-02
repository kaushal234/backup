<?php

declare(strict_types=1);

namespace App\Command\HumanResources;

use App\Entity\Country;
use App\Entity\Directory\Location;
use App\Entity\HumanResources\Event;
use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Spatie\Holidays\Exceptions\InvalidCountry;
use Spatie\Holidays\Holidays;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:human-resources:holidays')]
class ImportHolidaysCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $eventRepository = $this->entityManager->getRepository(Event::class);
        $locationRepository = $this->entityManager->getRepository(Location::class);
        $countryRepository = $this->entityManager->getRepository(Country::class);

        $year = (int) $input->getArgument('year');
        $forMonth = (bool) $input->getOption('forMonth');

        /** @var Location $location */
        foreach ($locationRepository->findAll() as $location) {
            if (null === ($country = $location->getAddress()->getCountry())) {
                continue;
            }

            try {
                $holidays = Holidays::for(country: mb_strtolower($country), year: $year)->get();
            } catch (InvalidCountry $exception) {
                $output->writeln(\sprintf('Country %s is not supported.', $country));
                continue;
            }

            foreach ($holidays as $holiday) {
                $alreadyExist = false;

                /** @var CarbonImmutable $date */
                $date = $holiday['date'];

                if ($forMonth && $date->format('m') !== (new \DateTime())->format('m')) {
                    continue;
                }

                /** @var Event $existingEvent */
                foreach ($eventRepository->findBy(['name' => $holiday['name']]) as $existingEvent) {
                    if ($existingEvent->startedAt->format('Y') === $date->format('Y')) {
                        $alreadyExist = true;
                    }
                }

                if (true === $alreadyExist) {
                    continue;
                }

                $event = new Event();
                $event->country = $countryRepository->findOneBy(['isoCode2' => $country]);
                $event->name = $holiday['name'];
                $event->startedAt = $date->toDate();
                $event->endedAt = $date->toDate();
                $event->dayOff = true;

                $this->entityManager->persist($event);
                $this->entityManager->flush();

                $output->writeln(\sprintf('Event %s created.', $holiday['name']));
            }
        }

        return Command::SUCCESS;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('year', InputArgument::OPTIONAL, 'Year to import holidays', (new \DateTime('next year'))->format('Y'))
            ->addOption('forMonth', 'm', InputOption::VALUE_OPTIONAL, 'Specify if we want only holidays for this month next year only', true)
        ;
    }
}
