<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Common\Airport;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'import:airport_localization')]
class ImportAirportLocalizationCommand extends Command
{
    private readonly EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->setDescription('Imports airport localization');
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $file = fopen(__DIR__.'/iata-icao.csv', 'r');
            $content = [];
            while (!feof($file)) {
                $content[] = fgetcsv($file);
            }

            fclose($file);
        } catch (\Exception $exception) {
            $output->writeln(\sprintf('<error>%s</error>', $exception->getMessage()));

            return Command::FAILURE;
        }

        $output->writeln(\sprintf('<info>%d Airport localization found</info>', \count($content)));

        $airportRepository = $this->entityManager->getRepository(Airport::class);

        foreach ($content as $key => $airport) {
            // key === 0 : header
            // false === $aiport : empty last line
            if (0 === $key || false === $airport) {
                continue;
            }

            try {
                $code = $airport[0];
                $latitude = $airport[1];
                $longitude = $airport[2];

                $airport = $airportRepository->findOneBy(['code' => $code]);

                if (!$airport) {
                    $output->writeln(\sprintf('<error>Airport %s not found on API database</error>', $code));
                    continue;
                }

                $airport
                    ->setLatitude((float) $latitude)
                    ->setLongitude((float) $longitude)
                ;

                $this->entityManager->persist($airport);

                $output->writeln(\sprintf('<info>Airport %s updated</info>', $code));
            } catch (\Exception $exception) {
                $output->writeln(\sprintf('<error>%s</error>', $exception->getMessage()));

                return Command::FAILURE;
            }
        }

        $this->entityManager->flush();
        $output->writeln('<info>Airport localization updated</info>');

        try {
            $file = fopen(__DIR__.'/iata-icao-2.csv', 'r');
            $content = [];
            while (!feof($file)) {
                $content[] = fgetcsv($file, null, ';');
            }

            fclose($file);
        } catch (\Exception $exception) {
            $output->writeln(\sprintf('<error>%s</error>', $exception->getMessage()));

            return Command::FAILURE;
        }

        $output->writeln(\sprintf('<info>%d Airport localization found</info>', \count($content)));

        $airportRepository = $this->entityManager->getRepository(Airport::class);

        foreach ($content as $key => $airport) {
            // key === 0 : header
            // false === $aiport : empty last line
            if (0 === $key || false === $airport) {
                continue;
            }

            try {
                $code = $airport[0];
                $latitude = $airport[1];
                $longitude = $airport[2];

                $airport = $airportRepository->findOneBy(['code' => $code]);

                if (!$airport) {
                    $output->writeln(\sprintf('<error>Airport %s not found on API database</error>', $code));
                    continue;
                }

                $airport
                    ->setLatitude((float) $latitude)
                    ->setLongitude((float) $longitude)
                ;

                $this->entityManager->persist($airport);

                $output->writeln(\sprintf('<info>Airport %s updated</info>', $code));
            } catch (\Exception $exception) {
                $output->writeln(\sprintf('<error>%s</error>', $exception->getMessage()));

                return Command::FAILURE;
            }
        }

        $this->entityManager->flush();
        $output->writeln('<info>Airport localization updated</info>');

        return Command::SUCCESS;
    }
}
