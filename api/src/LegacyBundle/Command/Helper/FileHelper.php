<?php

declare(strict_types=1);

namespace LegacyBundle\Command\Helper;

use App\Doctrine\Voter\ActivityLogVoter;
use App\Entity\Directory\People;
use App\FileSystem\Persistence\PersistableFileManagerFactory;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\ConstraintViolationException;
use Doctrine\DBAL\Logging\Middleware;
use Doctrine\DBAL\Result;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Doctrine\Voter\SynchronizationVoter;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

class FileHelper
{
    private readonly Connection $legacyConnection;

    private readonly EntityCacheHelperFactory $cacheFactory;

    private readonly PersistableFileManagerFactory $fileManagerFactory;

    private readonly SynchronizationVoter $syncVoter;

    private readonly ActivityLogVoter $activityLogVoter;

    private readonly EntityManagerInterface $em;

    private readonly Filesystem $fileSystem;

    private readonly ParameterBagInterface $parameters;

    public function __construct(
        Connection $legacyConnection,
        EntityCacheHelperFactory $cacheFactory,
        PersistableFileManagerFactory $fileManagerFactory,
        ParameterBagInterface $parameters,
        SynchronizationVoter $syncVoter,
        EntityManagerInterface $em,
        ActivityLogVoter $activityLogVoter,
        Filesystem $fileSystem
    ) {
        $this->legacyConnection = $legacyConnection;
        $this->cacheFactory = $cacheFactory;
        $this->fileManagerFactory = $fileManagerFactory;
        $this->syncVoter = $syncVoter;
        $this->em = $em;
        $this->activityLogVoter = $activityLogVoter;
        $this->fileSystem = $fileSystem;
        $this->parameters = $parameters;
    }

    public function importFromModFilesTable(string $module, string $class, string $fileClass, OutputInterface $output, string $descProperty = 'legacyId', int $startToLegacyId = 0)
    {
        $queryBuilder = $this->legacyConnection->createQueryBuilder();

        $queryBuilder
            ->select("mf.parent_id, module, f.poster, dt as created_at, description, REPLACE(REPLACE(REPLACE(REPLACE(f.filepath, '/var/www/intranet/current/uploads/mod_files/', ''), '/var/www/intranet/current/legacy/uploads/mod_files/', ''), '/var/www/alvest-web-portals/current/intranet/legacy/uploads/mod_files/', ''), '/var/www/alvest-web-portals/current/intranet/uploads/mod_files/', '') as filename, f.filename AS originalFilename, f.extension, f.mime")
            ->from('mod_files', 'mf')
            ->join('mf', 'file', 'f', 'mf.fid = f.id')
            ->where('module = :module')
            ->andWhere('mf.id >= :startToLegacyId')
            ->setParameter('module', $module)
            ->setParameter('startToLegacyId', $startToLegacyId)
        ;

        $result = $queryBuilder->executeQuery();

        $peopleCache = $this->cacheFactory->createEntityCache(People::class, 'email');

        $this->import(
            $result,
            $output,
            $class,
            $fileClass,
            'filename',
            'parent_id',
            'mod_files',
            static function ($data) use ($peopleCache) {
                $metadata = [];
                if ($data['description']) {
                    $metadata['description'] = $data['description'];
                }

                if (null !== $poster = $peopleCache->fetch($data['poster'])) {
                    $metadata['poster'] = $poster;
                }

                if ($data['created_at']) {
                    $metadata['created_at'] = new \DateTime($data['created_at']);
                }

                return $metadata;
            },
            $descProperty
        );
    }

    public function import(
        Result $result,
        OutputInterface $output,
        string $class,
        string $fileClass,
        string $filenameColumn,
        string $parentColumn,
        string $storageDirectory,
        callable $fillCallback,
        string $descProperty = 'legacyId',
        array $extraCacheFetchCriteria = []
    ) {
        $this->disableVoters();
        $this->em->getConnection()->getConfiguration()->setMiddlewares([new Middleware(new NullLogger())]);

        $cache = $this->cacheFactory->createEntityCache($class, $descProperty);
        $fileManager = $this->fileManagerFactory->getManagerForClass($fileClass);
        $rowCount = $result->rowCount();

        if (0 === $rowCount) {
            $output->writeln('<info>Nothing to import</info>');
            $this->enableVoters();

            return [];
        }

        $progress = new ProgressBar($output);
        $progress->setFormat(' %current%/%max% [%bar%] %percent:3s%% %remaining:10s% %memory:6s%');
        $progress->start($rowCount);

        $legacyUploadDir = $this->parameters->get('legacy.upload_dir');

        $operationCounter = 0;
        while ($data = $result->fetchAssociative()) {
            $delete = false;
            $filename = $data[$filenameColumn];
            $filepath = $legacyUploadDir."/$storageDirectory/$filename";

            if (!file_exists($filepath)) {
                $output->writeln(\sprintf('<error>file %s does not exist</error>', $filepath));
                $progress->advance();
                continue;
            }

            if (isset($data['extension']) && '' !== $data['extension'] && mb_substr((string) $filename, -(mb_strlen((string) $data['extension']) + 1)) !== '.'.$data['extension']) {
                // create the a temporary file with the extension so that it's correctly detected by the File object
                $newFilePath = $legacyUploadDir."/$storageDirectory/{$data['originalFilename']}";
                $this->fileSystem->copy($filepath, $newFilePath);
                $filepath = $newFilePath;
                $delete = true;
            }

            if (null === $parsedFilepath = $this->parse($filepath, [], false)) {
                $output->writeln(\sprintf('<error>file %s skipped</error>', $filepath));
                $progress->advance();
                continue;
            }

            $object = $cache->fetch((string) $data[$parentColumn], $extraCacheFetchCriteria);

            if (null === $object) {
                $output->writeln(\sprintf('<error>Parent ID %s could not be found for %s</error>', $data[$parentColumn], $filepath));
                $progress->advance();
                continue;
            }

            try {
                $fileManager->attach(
                    $object,
                    new File($parsedFilepath),
                    $fillCallback($data)
                );
            } catch (ConstraintViolationException $e) {
                $output->writeln(\sprintf('<error>file %s could not be imported. You have to fix your violation. %s</error>', $parsedFilepath, $e->getMessage()));

                return Command::FAILURE;
            } catch (\Exception $e) {
                $output->writeln(\sprintf('<error>file %s could not be imported. %s</error>', $parsedFilepath, $e->getMessage()));
            } finally {
                if ($delete) {
                    // remove the temporary files created with the right extension
                    $this->fileSystem->remove($parsedFilepath);
                }
            }

            $progress->advance();
            ++$operationCounter;
        }

        $progress->finish();

        $output->writeln('');
        $output->writeln(\sprintf('<info>Imported <comment>%d</comment> %s</info>', $operationCounter, $fileClass));

        $this->enableVoters();
    }

    public function parse(string $str = '', array $allowedExtension = [], bool $checkExistence = true): ?string
    {
        if (0 === mb_strlen(mb_trim($str))) {
            return null;
        }

        if ($checkExistence && !file_exists($str)) {
            return null;
        }

        if ($allowedExtension && !\in_array(mb_strtolower(pathinfo($str, \PATHINFO_EXTENSION)), $allowedExtension, true)) {
            return null;
        }

        return $str;
    }

    private function disableVoters()
    {
        $this->syncVoter->disable();
        $this->activityLogVoter->disable();
    }

    private function enableVoters()
    {
        $this->syncVoter->enable();
        $this->activityLogVoter->enable();
    }
}
