<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Parts\NonConformityPart;
use App\Entity\Quality\NonConformity;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Command\Helper\ImportHelper;
use LegacyBundle\Command\Helper\SanitationHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:quality:ncr_parts')]
class ImportNonConformitiesPartsCommand extends Command
{
    private readonly ImportHelper $helper;
    private readonly Connection $legacyConnection;
    private readonly EntityCacheHelperFactory $cacheFactory;
    private readonly SanitationHelper $sanitationHelper;
    private readonly EntityManagerInterface $entityManager;

    public function __construct(ImportHelper $helper, Connection $legacyConnection, EntityCacheHelperFactory $cacheFactory, SanitationHelper $sanitationHelper, EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->setDescription('Imports NCR Parts from legacy');
        $this->helper = $helper;
        $this->legacyConnection = $legacyConnection;
        $this->cacheFactory = $cacheFactory;
        $this->sanitationHelper = $sanitationHelper;
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Import NCR Parts
        $sql = <<<'SQL'
                SELECT ncr_parts.id, ncr_parts.parent_id, ncr_parts.part_number, ncr_parts.short_desc, ncr_parts.qty, ncr_parts.ref_type, ncr_parts.ref, ncr_parts.sn
                FROM ncr_parts
                LEFT JOIN ncr on ncr_parts.parent_id = ncr.id
                WHERE ncr.t_emno != 0
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);

        $ncrCache = $this->cacheFactory->createEntityCache(NonConformity::class, 'id');

        $this->helper->disableValidation();
        $this->helper->setBatchSize(10_000);

        $this->helper->progressiveImport(
            $output, $stmt, NonConformityPart::class, 'id', 'id',
            function (NonConformityPart $part, array $data) use ($ncrCache) {
                $nonConformity = $ncrCache->fetch($data['parent_id']);
                if (!$nonConformity instanceof NonConformity) {
                    throw new \InvalidArgumentException('NCR not found');
                }

                $part->nonConformity = $nonConformity;
                $part->createdAt = $nonConformity->createdAt;
                $part->createdBy = $nonConformity->reportedBy;
                $part->partNumber = mb_trim($this->sanitationHelper->parse($data['part_number']));
                $part->quantity = (float) $data['qty'];
                $part->description = mb_trim($this->sanitationHelper->parse($data['short_desc']));
                if ('' !== $data['ref_type']) {
                    $part->reference = mb_trim($this->sanitationHelper->parse($data['ref_type']));
                }
                if ('' !== $data['ref']) {
                    $part->referenceNumber = mb_trim($this->sanitationHelper->parse($data['ref']));
                }
                $part->unitOfMeasure = 'EA';
            }, false
        );

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
