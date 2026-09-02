<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Quality\NonConformity;
use App\Entity\Quality\NonConformityMainFile;
use App\FileSystem\Persistence\PersistableFileManagerFactory;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Logging\Middleware;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\File;

#[AsCommand(name: 'legacy:import:quality:ncr_photo')]
class ImportNonConformityPhotoCommand extends Command
{
    private readonly Connection $legacyConnection;
    private readonly EntityManagerInterface $entityManager;
    private readonly PersistableFileManagerFactory $fileManagerFactory;
    private readonly ParameterBagInterface $parameters;
    private readonly EntityCacheHelperFactory $cacheFactory;

    public function __construct(Connection $legacyConnection, EntityManagerInterface $entityManager, PersistableFileManagerFactory $fileManagerFactory, ParameterBagInterface $parameters, EntityCacheHelperFactory $cacheFactory)
    {
        parent::__construct();
        $this->setDescription('Import NCR photos from legacy');
        $this->legacyConnection = $legacyConnection;
        $this->entityManager = $entityManager;
        $this->fileManagerFactory = $fileManagerFactory;
        $this->parameters = $parameters;
        $this->cacheFactory = $cacheFactory;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sql = <<<'SQL'
            SELECT id, photo, date
            FROM ncr
            WHERE photo <> ''
            SQL;
        $results = $this->legacyConnection->executeQuery($sql);

        $this->entityManager->getConnection()->getConfiguration()->setMiddlewares([new Middleware(new NullLogger())]);

        $fileManager = $this->fileManagerFactory->getManagerForClass(NonConformityMainFile::class);
        $rowCount = $results->rowCount();

        $progress = new ProgressBar($output);
        $progress->setFormat(' %current%/%max% [%bar%] %percent:3s%% %remaining:10s% %memory:6s%');
        $progress->start($rowCount);

        $legacyUploadDir = $this->parameters->get('legacy.upload_dir');

        $operationCounter = 0;
        $ncrCache = $this->cacheFactory->createEntityCache(NonConformity::class, 'id');
        foreach ($results->fetchAllAssociative() as $result) {
            $progress->advance();

            /** @var NonConformity|null $nonConformity */
            if (null === ($nonConformity = $ncrCache->fetch($result['id']))) {
                $output->writeln(\sprintf('<error>NCR %s not found</error>', $result['id']));
                continue;
            }

            if (null !== $nonConformity->getMainFile()) {
                $output->writeln(\sprintf('<error>Main file for NCR %s already imported</error>', $result['id']));
                continue;
            }

            $filename = $result['photo'];
            $filepath = $legacyUploadDir."/ncr/$filename";

            if (!file_exists($filepath)) {
                $output->writeln(\sprintf('<error>file %s does not exist</error>', $filepath));
                continue;
            }

            if (0 === mb_strlen(mb_trim((string) $filepath))) {
                $output->writeln(\sprintf('<error>file %s skipped</error>', $filepath));
                continue;
            }

            try {
                $fileManager->attach(
                    $nonConformity,
                    new File($filepath),
                    $result['date'] ? ['created_at' => new \DateTime($result['date'])] : []
                );
            } catch (\Exception $e) {
                $output->writeln(\sprintf('<error>file %s could not be imported. %s</error>', $filepath, $e->getMessage()));
            }

            ++$operationCounter;
        }

        $progress->finish();

        $output->writeln('');
        $output->writeln(\sprintf('<info>Imported <comment>%d</comment> %s</info>', $operationCounter, NonConformityMainFile::class));

        return Command::SUCCESS;
    }
}
