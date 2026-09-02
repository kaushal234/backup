<?php

declare(strict_types=1);

namespace App\Command\AI;

use App\Entity\AI\AIFile;
use App\Entity\AI\AILog;
use App\Entity\Directory\People;
use App\Repository\AI\AILogRepository;
use App\Repository\Directory\PeopleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(name: 'api:ai:clean-logs', description: 'Remove AI logs older than 30 days and keep at most 30 logs per active user')]
class CleanAILogsCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Filesystem $filesystem,
        private readonly string $legacyUploadDir,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var PeopleRepository $peopleRepository */
        $peopleRepository = $this->entityManager->getRepository(People::class);

        /** @var AILogRepository $logRepository */
        $logRepository = $this->entityManager->getRepository(AILog::class);

        $activeUsers = $peopleRepository->findBy(['hidden' => false, 'disabled' => false]);

        $totalDeleted = 0;

        foreach ($activeUsers as $people) {
            $idsToDelete = array_unique(array_merge(
                $logRepository->findIdsOlderThan30DaysForPeople($people->getId()),
                $logRepository->findIdsExceedingLimitForPeople($people->getId()),
            ));

            if ([] === $idsToDelete) {
                continue;
            }

            foreach ($idsToDelete as $id) {
                $log = $logRepository->find($id);

                if (null === $log) {
                    continue;
                }

                $filePaths = array_map(
                    fn (AIFile $file) => $this->legacyUploadDir.'/'.$file->getFilePath(),
                    $log->getFiles()
                );

                $this->entityManager->remove($log);
                ++$totalDeleted;

                foreach ($filePaths as $filePath) {
                    $this->filesystem->remove($filePath);
                }
            }

            $this->entityManager->flush();
            $this->entityManager->clear();
        }

        $output->writeln(\sprintf('Deleted %d AI log(s).', $totalDeleted));

        return Command::SUCCESS;
    }
}
