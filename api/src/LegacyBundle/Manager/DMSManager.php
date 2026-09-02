<?php

declare(strict_types=1);

namespace LegacyBundle\Manager;

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Cache\CacheInterface;

class DMSManager
{
    private readonly Connection $legacyConnection;
    private readonly string $legacyUploadDir;
    private readonly CacheInterface $arrayCache;

    public function __construct(Connection $legacyConnection, CacheInterface $arrayCache, string $legacyUploadDir)
    {
        $this->legacyConnection = $legacyConnection;
        $this->legacyUploadDir = $legacyUploadDir;
        $this->arrayCache = $arrayCache;
    }

    public function getDmsFilename(int $dmsId): ?string
    {
        return $this->getDmsFileData($dmsId)['filename'] ?? null;
    }

    public function getDmsFilepath(int $dmsId): ?string
    {
        return $this->getDmsFileData($dmsId)['filepath'] ?? null;
    }

    public function getDmsFile(int $dmsId): File
    {
        if (null === ($path = $this->getDmsFilepath($dmsId))) {
            throw new NotFoundHttpException('No PDF file could be found');
        }

        return new File($path);
    }

    private function getDmsFileData(int $dmsId): array
    {
        return $this->arrayCache->get('dms_'.$dmsId, function () use ($dmsId) {
            $qb = $this->legacyConnection->createQueryBuilder();
            $qb
                ->select('file.filename')
                ->addSelect('file.filepath AS filepath')
                ->from('dms', 'dms')
                ->innerJoin('dms', 'dms_revision', 'dms_revision', 'dms.rev_id=dms_revision.id')
                ->innerJoin('dms_revision', 'file', 'file', 'dms_revision.pub_fid = file.id')
                ->andWhere('dms.id = :dmsId')
                ->setParameter('legacy_upload_dir', $this->legacyUploadDir)
                ->setParameter('dmsId', $dmsId)
            ;

            $result = $this->legacyConnection->executeQuery($qb->getSQL(), $qb->getParameters())->fetchAssociative();

            if (false === $result) {
                return [];
            }

            $result['filepath'] = str_replace([
                '/var/www/intranet/current/uploads',
                '/var/www/intranet/current/legacy/uploads',
                '/var/www/alvest-web-portals/current/intranet/legacy/uploads',
                '/var/www/alvest-web-portals/current/intranet/uploads',
            ], $this->legacyUploadDir, (string) $result['filepath']);

            return $result;
        });
    }
}
