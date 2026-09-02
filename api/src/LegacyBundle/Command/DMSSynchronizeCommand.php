<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Directory\People;
use App\Entity\DMS;
use Doctrine\DBAL\Connection;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Command\Helper\ImportHelper;
use LegacyBundle\Command\Helper\SanitationHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:synch:dms')]
class DMSSynchronizeCommand extends Command
{
    private readonly ImportHelper $importHelper;

    private readonly SanitationHelper $sanitationHelper;

    private readonly EntityCacheHelperFactory $cacheHelperFactory;

    private readonly Connection $legacyConnection;

    public function __construct(
        ImportHelper $importHelper,
        SanitationHelper $sanitationHelper,
        EntityCacheHelperFactory $cacheHelperFactory,
        Connection $legacyConnection
    ) {
        parent::__construct();
        $this->setDescription('Synchronize TLD DMS');
        $this->importHelper = $importHelper;
        $this->sanitationHelper = $sanitationHelper;
        $this->cacheHelperFactory = $cacheHelperFactory;
        $this->legacyConnection = $legacyConnection;
    }

    public function skipFilter(DMS $dms, array $data)
    {
        $parts = explode('uploads/', (string) $data['filepath']);
        $filepath = $parts[1] ?? null;

        return
            $dms->getTitle() === $this->sanitationHelper->parse($this->sanitationHelper->decodeChinese($data['title']))
            && $dms->getSubject() === $this->sanitationHelper->parse($this->sanitationHelper->decodeChinese($data['subject']), true, true, false, false)
            && $dms->getDescription() === $this->sanitationHelper->multiline($this->sanitationHelper->decodeChinese($data['description']))
            && $dms->getLanguage() === $this->sanitationHelper->parse($data['lang'])
            && $dms->getOwner()->getLegacyId() === $data['owner_id']
            && $dms->getPortal() === $data['portal']
            && $dms->getType() === $data['type']
            && $dms->getFilepath() === $filepath
            && $dms->getMimetype() === $data['mime']
            && $dms->getStatus() === $data['status']
            && (($dms->isConfidential() && 'CONFIDENTIAL' === $data['access_type']) || (!$dms->isConfidential() && 'PUBLIC' === $data['access_type']))
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $peopleCache = $this->cacheHelperFactory->createEntityCache(People::class, 'legacyId');
        $sanitationHelper = $this->sanitationHelper;

        // Import DMS
        $sql = <<<'SQL'
            SELECT dms.id, dms.title, dms.subject, dms.description, dms.lang, dms.portal, dms.owner_id, dms.dt, dmst.short_desc AS type, dms.status, dms.access_type, dms_revision.revision, file.filepath, file.mime
            FROM dms
            LEFT JOIN dms_type dmst on dms.type_id = dmst.id
            LEFT JOIN dms_revision on dms_revision.id = dms.rev_id
            LEFT JOIN file on dms_revision.pub_fid = file.id
            SQL;

        $stmt = $this->legacyConnection->executeQuery($sql);

        $this->importHelper->progressiveImport(
            $output, $stmt, DMS::class, 'legacyId', 'id',
            static function (DMS $dms, array $data) use ($peopleCache, $sanitationHelper) {
                $parts = explode('uploads/', (string) $data['filepath']);
                $filepath = $parts[1] ?? null;

                $dms
                    ->setTitle(false === mb_strpos((string) $data['lang'], 'zh') ? $sanitationHelper->parse($data['title']) : $sanitationHelper->decodeChinese($data['title']))
                    ->setSubject(false === mb_strpos((string) $data['lang'], 'zh') ? $sanitationHelper->parse($data['subject']) : $sanitationHelper->decodeChinese($data['subject']))
                    ->setDescription($sanitationHelper->multiline(false === mb_strpos((string) $data['lang'], 'zh') ? $sanitationHelper->parse($data['description']) : $sanitationHelper->decodeChinese($data['description'])))
                    ->setLanguage($sanitationHelper->parse($data['lang']))
                    ->setPortal($data['portal'])
                    ->setOwner($peopleCache->fetch((string) $data['owner_id']))
                    ->setType($data['type'])
                    ->setCreatedAt(new \DateTime($data['dt']))
                    ->setStatus($data['status'])
                    ->setConfidential('CONFIDENTIAL' === $data['access_type'])
                    ->setRevision((string) $data['revision'])
                    ->setFilepath($filepath)
                    ->setMimetype((string) $data['mime'])
                ;
            }, true,
            $this->skipFilter(...)
        );

        return 0;
    }
}
