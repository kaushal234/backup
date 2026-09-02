<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Support\EquipmentSerial;
use App\Entity\Support\ManualDocument;
use App\Entity\Support\ManualDocumentCategory;
use App\Entity\Support\ManualDocumentFile;
use App\FileSystem\Persistence\ContextProviders\FileAdapterContextProviderInterface;
use App\FileSystem\Persistence\PersistableFileManagerFactory;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Command\Helper\ImportBatchHelper;
use LegacyBundle\Command\Helper\SanitationHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\File\File;

#[AsCommand(name: 'legacy:import:equipment:manuals')]
class ImportManualEntitiesCommand extends Command
{
    private const TRANSACTION_SIZE = 100_000;

    private const LANGUAGES = [
        'BULGARIAN' => '',
        'CH' => 'CHINESE',
        'CHINESE' => 'CHINESE',
        'CZECH' => 'CZECH',
        'DANISH' => 'DANISH',
        'DE' => 'GERMAN',
        'e' => 'ENGLISH',
        'EN' => 'ENGLISH',
        'ENGLISH' => 'ENGLISH',
        'ES' => 'SPANISH',
        'f' => 'FRENCH',
        'FINNISH' => 'FINNISH',
        'FLEMISK' => 'FLEMISK',
        'FR' => 'FRENCH',
        'FRENCH' => 'FRENCH',
        'GERMAN' => 'GERMAN',
        'GREEK' => 'GREEK',
        'ITALIAN' => 'ITALIAN',
        'LATVIAN' => 'LATVIAN',
        'LITHUANIAN' => 'LITHUANIAN',
        'POLISH' => 'POLISH',
        'PORTUGUESE' => 'PORTUGUESE',
        'PT' => 'PORTUGUESE',
        'ROMANIAN' => 'ROMANIAN',
        'SPANISH' => 'SPANISH',
        'SWEDISH' => 'SWEDISH',
        'z' => 'CHINESE',
    ];

    private readonly Connection $legacyConnection;
    private readonly Connection $connection;
    private readonly SanitationHelper $sanitationHelper;
    private readonly EntityCacheHelperFactory $cacheHelperFactory;
    private readonly PersistableFileManagerFactory $persistableFileManagerFactory;
    private readonly string $legacyUploadDir;
    private readonly EntityManagerInterface $entityManager;

    public function __construct(
        Connection $legacyConnection,
        Connection $connection,
        EntityManagerInterface $entityManager,
        SanitationHelper $sanitationHelper,
        EntityCacheHelperFactory $cacheHelperFactory,
        PersistableFileManagerFactory $persistableFileManagerFactory,
        string $legacyUploadDir
    ) {
        parent::__construct();
        $this->setDescription('Import manuals using the DBAL query builder to perform inserts');
        $this->legacyConnection = $legacyConnection;
        $this->connection = $connection;
        $this->entityManager = $entityManager;
        $this->sanitationHelper = $sanitationHelper;
        $this->cacheHelperFactory = $cacheHelperFactory;
        $this->persistableFileManagerFactory = $persistableFileManagerFactory;
        $this->legacyUploadDir = $legacyUploadDir;
    }

    protected function configure(): void
    {
        ImportBatchHelper::addArguments($this);
        $this->addArgument('startId', InputArgument::OPTIONAL, 'Starting id', 0);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $logger = new ConsoleLogger($output);
        $persistableFileManager = $this->persistableFileManagerFactory->getManagerForClass(ManualDocumentFile::class);
        $adapter = $persistableFileManager->getAdapter();
        /** @var FileAdapterContextProviderInterface $contextProvider */
        $contextProvider = $adapter->getContextProvider();
        $directory = $contextProvider->getDirectory();
        $date = date(\DATE_ATOM);

        $categoryCache = $this->cacheHelperFactory->createEntityCache(ManualDocumentCategory::class, 'name');

        $startId = $input->getArgument('startId');
        if ($startId > 0) {
            $manuals = $this->legacyConnection->executeQuery(
                'SELECT * FROM manuals WHERE id >= :startId',
                [
                    'startId' => $startId,
                ],
                [
                    'startId' => ParameterType::INTEGER,
                ])->fetchAllAssociative();
        } else {
            $manuals = $this->legacyConnection->executeQuery(
                'SELECT * FROM manuals LIMIT :limit OFFSET :offset',
                [
                    'offset' => $input->getArgument('offset'),
                    'limit' => $input->getArgument('limit'),
                ],
                [
                    'offset' => ParameterType::INTEGER,
                    'limit' => ParameterType::INTEGER,
                ])->fetchAllAssociative();
        }

        // get an ORM querybuilder to fetch only the id and legacy id of a given resource
        $serialsDictionaryQueryBuilder = $this->entityManager->getRepository(EquipmentSerial::class)->createQueryBuilder('o');
        $results = $serialsDictionaryQueryBuilder
            ->addSelect('o.id')
            ->addSelect('o.serial')
            ->join('o.equipmentRecord', 'er')
            ->addSelect('er.id AS equipmentRecordId')
            ->where('o.component = :componentId')
            ->setParameter('componentId', 44)
            ->getQuery()
            ->getScalarResult()
        ;

        $serialsDictionary = [];
        foreach ($results as $result) {
            $serialsDictionary[$result['serial']] = ['id' => $result['id'], 'equipmentRecordId' => $result['equipmentRecordId']];
        }

        $i = 0;

        $manualQueryBuilder = $this->connection->createQueryBuilder()
            ->insert('manuals')
            ->setValue('description', ':description')
            ->setValue('features', ':features')
            ->setValue('language', ':language')
            ->setValue('status', ':status')
            ->setValue('created_at', ':createdAt')
            ->setValue('legacy_id', ':manualLegacyId')
            ->setValue('equipment_serial_id', ':serialId')
            ->setValue('equipment_record_id', ':equipmentRecordId')
        ;

        $manualDocumentQueryBuilder = $this->connection->createQueryBuilder()
            ->insert('manual_documents')
            ->setValue('manual_id', ':manualId')
            ->setValue('category_id', ':categoryId')
            ->setValue('position', ':position')
            ->setValue('factory_number', ':factoryNumber')
            ->setValue('revision', ':revision')
            ->setValue('type', ':type')
            ->setValue('description', ':description')
            ->setValue('other_description', ':otherDescription')
            ->setValue('legacy_id', ':manualDocumentLegacyId')
        ;

        $partQueryBuilder = $this->connection->createQueryBuilder()
            ->insert('manual_parts')
            ->setValue('position', ':position')
            ->setValue('part_number', ':partNumber')
            ->setValue('quantity', ':quantity')
            ->setValue('unit_of_measure', ':unitOfMeasure')
            ->setValue('description', ':description')
            ->setValue('other_description', ':otherDescription')
            ->setValue('critical', ':critical')
            ->setValue('preventive', ':preventive')
            ->setValue('maintenance', ':maintenance')
            ->setValue('overhaul', ':overhaul')
            ->setValue('document_id', ':documentId')
            ->setValue('legacy_id', ':legacyId')
        ;

        $fileQueryBuilder = $this->connection->createQueryBuilder()
            ->insert('files')
            ->setValue('created_at', ':date')
            ->setValue('discr', ':discr')
            ->setValue('file_path', ':filePath')
            ->setValue('description', ':description')
            ->setValue('sha', ':sha')
            ->setValue('mime_type', ':mimeType')
            ->setValue('extension', ':extension')
            ->setValue('size', ':size')
        ;

        $manualDocumentFileQueryBuilder = $this->connection->createQueryBuilder()
            ->insert('manual_document_files')
            ->setValue('id', ':fileId')
            ->setValue('manual_document_id', ':manualDocumentId')
        ;

        // wrapping all the generated queries is safer but also more performant, we do not execute inserts one by one but by batch
        $this->connection->beginTransaction();
        foreach ($manuals as $manual) {
            // The serial corresponding to the manual legacyId must exist on the api
            if (false === ($serialsDictionary[$manual['id']] ?? false)) {
                $logger->alert('Serial for manual with legacy ID {legacyId} was not found, but who cares?', ['legacyId' => $manual['id']]);
            }

            $logger->info('Importing manual with legacy ID {legacyId}', ['legacyId' => $manual['id']]);

            $this->connection->executeQuery($manualQueryBuilder->getSQL(), [
                'description' => $manual['description'] ? $this->sanitationHelper->parse($manual['description']) : null,
                'features' => $manual['features'] ? $this->sanitationHelper->parse($manual['features']) : null,
                'language' => $manual['lang'] ? (self::LANGUAGES[$manual['lang']] ?? null) : null,
                'status' => $manual['status'] ?? null,
                'createdAt' => isset($manual['date']) && '0000-00-00' !== $manual['date'] ? $manual['date'] : null,
                'manualLegacyId' => (int) $manual['id'],
                'serialId' => $serialsDictionary[$manual['id']]['id'] ?? null,
                'equipmentRecordId' => $serialsDictionary[$manual['id']]['equipmentRecordId'] ?? null,
            ]);

            $manualNewId = $this->connection->lastInsertId();

            $documents = $this->legacyConnection->executeQuery(<<<'SQL'
                    SELECT *
                    FROM manuals_docs
                    WHERE doc_num > 0 AND parent_id = :manualId
                SQL, ['manualId' => $manual['id']])->fetchAllAssociative();

            $logger->debug('Importing {count} documents', ['count' => \count($documents)]);
            foreach ($documents as $document) {
                $diagram = $this->legacyConnection->executeQuery(<<<'SQL'
                        SELECT id, factory_num, rev, category, doc_type, endescription, frdescription, diagram_filename, filesize
                        FROM manuals_diag
                        WHERE id = :diagId;
                    SQL, ['diagId' => $document['doc_num']])->fetchAssociative();
                if (!$diagram) {
                    continue;
                }

                /** @var ManualDocumentCategory|null $category */
                $category = $categoryCache->fetch(str_replace('/', '-', (string) $diagram['category']));

                $this->connection->executeQuery($manualDocumentQueryBuilder->getSQL(), [
                    'position' => $document['item'] ? (int) $this->sanitationHelper->parse($document['item']) : 0,
                    'factoryNumber' => $factoryNumber = ($diagram['factory_num'] ? $this->sanitationHelper->parse($diagram['factory_num']) : null),
                    'revision' => $revision = ($diagram['rev'] ? $this->sanitationHelper->parse($diagram['rev']) : null),
                    'categoryId' => null !== $category ? $category->getId() : null,
                    'type' => $diagram['doc_type'],
                    'otherDescription' => $diagram['frdescription'] ? $this->sanitationHelper->parse($diagram['frdescription']) : null,
                    'description' => $diagram['endescription'] ? $this->sanitationHelper->parse($diagram['endescription']) : null,
                    'manualDocumentLegacyId' => (int) $diagram['id'],
                    'manualId' => $manualNewId,
                ]);

                $documentId = $this->connection->lastInsertId();

                if ('' !== (string) ($filename = $diagram['diagram_filename'])) {
                    try {
                        $path = $this->legacyUploadDir.'/'.$directory.'/'.$filename;
                        // $path = $this->legacyUploadDir.'/annotations.map';
                        $logger->debug('Importing file {path}', ['path' => $path]);
                        $metadata = ['validate' => false];
                        $documentEntity = new ManualDocument();
                        $documentEntity->factoryNumber = $factoryNumber;
                        $documentEntity->revision = $revision;
                        $adapter->attach($documentEntity, new File($path, false), $metadata);
                        /** @var ManualDocumentFile $file */
                        $file = $documentEntity->getFiles()->last();

                        $this->connection->executeQuery($fileQueryBuilder->getSQL(), [
                            'date' => $date,
                            'discr' => 'manual_document_files',
                            'filePath' => $directory.'/'.$filename,
                            'description' => $file->getDescription(),
                            'sha' => $file->getSha(),
                            'mimeType' => $file->getMimeType(),
                            'extension' => $file->getExtension(),
                            'size' => $file,
                        ]);
                        ++$i;
                        $fileId = $this->connection->lastInsertId();
                        $this->connection->executeQuery($manualDocumentFileQueryBuilder->getSQL(), [
                            'fileId' => $fileId,
                            'manualDocumentId' => $documentId,
                        ]);
                        ++$i;
                    } catch (\Exception $e) {
                        $logger->debug(\sprintf('file not found : %s/%s .', $directory, $filename ?? ''));
                    }
                }

                $parts = $this->legacyConnection->executeQuery(<<<'SQL'
                        SELECT *
                        FROM manuals_parts
                        WHERE parent_id = :diagId;
                    SQL, ['diagId' => $document['doc_num']])->fetchAllAssociative();

                $logger->debug('Importing {count} parts', ['count' => \count($parts)]);
                foreach ($parts as $part) {
                    if (null === ($part['pn'] ?? null)) {
                        continue;
                    }
                    $this->connection->executeQuery($partQueryBuilder->getSQL(), [
                        'position' => $part['item'] ? (int) $this->sanitationHelper->parse($part['item']) : 0,
                        'partNumber' => $part['pn'],
                        'quantity' => $part['qty'] ? (float) $this->sanitationHelper->parse($part['qty']) : 0.0,
                        'unitOfMeasure' => $part['um'] ? $this->sanitationHelper->parse($part['um']) : null,
                        'description' => $part['en'] ? $this->sanitationHelper->parse($part['en']) : null,
                        'otherDescription' => $part['fr'] ? $this->sanitationHelper->parse($part['fr']) : null,
                        'critical' => 'C' === $part['group_c'],
                        'preventive' => 'P' === $part['group_p'],
                        'maintenance' => 'M' === $part['group_m'],
                        'overhaul' => 'O' === $part['group_o'],
                        'documentId' => $documentId,
                        'legacyId' => $part['id'],
                    ]);
                    ++$i;
                }
                ++$i;
            }

            if ($i >= self::TRANSACTION_SIZE) {
                try {
                    $logger->emergency('{time} {count} rows transaction committed', ['time' => date('H:i:s'), 'count' => $i]);
                    $this->connection->commit();
                    $this->connection->beginTransaction();
                    $i = 0;
                    continue;
                } catch (\Exception $exception) {
                    $this->connection->rollBack();
                    throw new \InvalidArgumentException(\sprintf('something went wrong when trying to commit a batch of manuals : %s', $exception->getMessage()), $exception->getCode(), $exception);
                }
            }
        }

        try {
            $this->connection->commit();
        } catch (\Exception $exception) {
            $this->connection->rollBack();
            throw new \InvalidArgumentException(\sprintf('something went wrong when trying to commit the final batch of manuals : %s', $exception->getMessage()), $exception->getCode(), $exception);
        }

        return Command::SUCCESS;
    }
}
