<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Directory\People;
use App\Entity\Directory\Premise;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'import:user-premise')]
class ImportPremiseCommand extends Command
{
    private readonly EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->setDescription('Imports premises for users');
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $data = [];
        try {
            if (($handle = fopen(__DIR__.'/user-premises.csv', 'r')) !== false) {
                while (($row = fgetcsv($handle, 4000, ';')) !== false) {
                    $data[] = $row;
                }
                fclose($handle);
            } else {
                throw new \Exception('Unable to open the file.');
            }
        } catch (\Exception $exception) {
            $output->writeln(\sprintf('<error>%s</error>', $exception->getMessage()));

            return Command::FAILURE;
        }

        $output->writeln(\sprintf('<info>%d users found in csv file</info>', \count($data)));

        $userRepository = $this->entityManager->getRepository(People::class);
        $premiseRepository = $this->entityManager->getRepository(Premise::class);
        $updates = 0;

        foreach ($data as $key => $row) {
            // key === 0 : header
            if (0 === $key) {
                continue;
            }

            try {
                $premise = $premiseRepository->findOneBy(['name' => $row[4]]);
                if (!$premise) {
                    $output->writeln(\sprintf('<error>Premise %s not found in database, skipping.</error>', $row[4]));
                    continue;
                }

                $user = $userRepository->find($row[0]);
                if (!$user) {
                    $output->writeln(\sprintf('<error>User %s not found in database, skipping.</error>', $row[0]));
                    continue;
                }

                $user->setPremise($premise);
                $this->entityManager->persist($user);
                $output->writeln(\sprintf('<info>User %s updated</info>', $user->getId()));
                ++$updates;
            } catch (\Exception $exception) {
                $output->writeln(\sprintf('<error>%s</error>', $exception->getMessage()));

                return Command::FAILURE;
            }
        }
        $this->entityManager->flush();
        $output->writeln(\sprintf('<info>%d users updated out of %d</info>', $updates, \count($data)));

        return Command::SUCCESS;
    }
}
