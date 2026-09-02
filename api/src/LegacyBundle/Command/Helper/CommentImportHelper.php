<?php

declare(strict_types=1);

namespace LegacyBundle\Command\Helper;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Activity\Comment;
use App\Entity\Directory\People;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CommentImportHelper
{
    private readonly ImportHelper $importHelper;

    private readonly IriConverterInterface $iriConverter;

    private readonly EntityCacheHelperFactory $cacheFactory;

    private readonly Connection $legacyConnection;

    private readonly SanitationHelper $sanitationHelper;
    private readonly EntityManagerInterface $entityManager;

    public function __construct(ImportHelper $importHelper, IriConverterInterface $iriConverter, EntityCacheHelperFactory $cacheFactory, Connection $legacyConnection, SanitationHelper $sanitationHelper, EntityManagerInterface $entityManager)
    {
        $this->importHelper = $importHelper;
        $this->iriConverter = $iriConverter;
        $this->cacheFactory = $cacheFactory;
        $this->legacyConnection = $legacyConnection;
        $this->sanitationHelper = $sanitationHelper;
        $this->entityManager = $entityManager;
    }

    public function progressiveImport(OutputInterface $output, string $module, string $resourceClass, string $legacyTable, string $descProperty = 'legacyId')
    {
        $iriConverter = $this->iriConverter;
        $peopleCache = $this->cacheFactory->createEntityCache(People::class, 'legacyId');
        $resourceCache = $this->cacheFactory->createEntityCache($resourceClass, $descProperty);

        $sql = \sprintf('SELECT mod_logs.id, mod_logs.parent_id, mod_logs.date, mod_logs.poster, mod_logs.comment, mod_logs.log_num
FROM mod_logs
LEFT JOIN %s ON %s.id = mod_logs.parent_id
INNER JOIN people ON mod_logs.poster = people.id
WHERE mod_logs.module = "%s" AND %s.id IS NOT NULL', $legacyTable, $legacyTable, $module, $legacyTable);

        $stmt = $this->legacyConnection->executeQuery($sql);

        $events = $this->entityManager->getClassMetadata(Comment::class)->lifecycleCallbacks;
        $this->entityManager->getClassMetadata(Comment::class)->setLifecycleCallbacks([]);

        $this->importHelper->disableValidation();
        $this->importHelper->setBatchSize(10_000);

        $this->importHelper->progressiveImport($output,
            $stmt,
            Comment::class,
            'legacyId',
            'id',
            function (Comment $comment, array $data) use ($resourceCache, $iriConverter, $peopleCache, $module) {
                if (!$resource = $resourceCache->fetch((string) $data['parent_id'])) {
                    throw new \InvalidArgumentException(\sprintf('%s not found', $module));
                }

                $comment
                    ->setMessage($this->sanitationHelper->parse($data['comment']))
                    ->setCreatedAt($date = new \DateTime($data['date']))
                    ->setUpdatedAt($date)
                    ->setResource($iriConverter->getIriFromResource($resource))
                    ->setUser($peopleCache->fetch((string) $data['poster']))
                    ->setPublic(0 === (int) $data['log_num'])
                ;
            }
        );

        $this->entityManager->getClassMetadata(Comment::class)->setLifecycleCallbacks($events);
    }
}
