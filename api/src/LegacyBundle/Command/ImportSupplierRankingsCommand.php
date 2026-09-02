<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Command\UpdateSupplierNumberByArrayCommand;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\Purchasing\SupplierRanking\Classification;
use App\Entity\Purchasing\SupplierRanking\Criteria;
use App\Entity\Purchasing\SupplierRanking\ExpertiseLevel;
use App\Entity\Purchasing\SupplierRanking\Notation;
use App\Entity\Purchasing\SupplierRanking\SupplierRanking;
use App\Manager\Purchasing\SupplierRanking\SupplierRankingManager;
use App\Manager\Purchasing\SupplierRanking\ThresholdsManager;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Command\Helper\ImportHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:purchasing:supplier_rankings')]
class ImportSupplierRankingsCommand extends Command
{
    private readonly ImportHelper $helper;

    private readonly Connection $legacyConnection;

    private readonly EntityCacheHelperFactory $cacheHelperFactory;

    private readonly UpdateSupplierNumberByArrayCommand $commonDatas;

    private readonly SupplierRankingManager $supplierRankingManager;

    private readonly ThresholdsManager $thresholdsManager;

    private readonly EntityManagerInterface $entityManager;

    private array $supplierToBeMigrate = [];

    private array $criteriasMapping = [
        'cost' => 'Cost',
        'quality' => 'Quality',
        'delivery' => 'Logistic',
        'communication' => 'Communication, transparency and responsiveness',
        'innovation' => 'Innovation & Partnership',
        'support' => 'Product and field support',
        'esg' => 'Environmental, Social, Governance',
        'anti_corruption' => 'Anti-corruption',
    ];

    private array $existingDatas = [];

    public function __construct(
        ImportHelper $helper,
        Connection $legacyConnection,
        EntityCacheHelperFactory $cacheHelperFactory,
        UpdateSupplierNumberByArrayCommand $commonDatas,
        SupplierRankingManager $supplierRankingManager,
        ThresholdsManager $thresholdsManager,
        EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
        $this->setDescription('Import supplier rankings datas.');
        $this->helper = $helper;
        $this->legacyConnection = $legacyConnection;
        $this->cacheHelperFactory = $cacheHelperFactory;
        $this->commonDatas = $commonDatas;
        $this->supplierRankingManager = $supplierRankingManager;
        $this->thresholdsManager = $thresholdsManager;
        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        throw new \LogicException('This command should not be use anymore.');
        //        $peopleCache = $this->cacheHelperFactory->createEntityCache(People::class, 'legacyId');
        //        $classificationCache = $this->cacheHelperFactory->createEntityCache(Classification::class, 'id');
        //        $expertiseLevelCache = $this->cacheHelperFactory->createEntityCache(ExpertiseLevel::class, 'id');
        //        $locationCache = $this->cacheHelperFactory->createEntityCache(Location::class, 'erp');
        //        $criteriaCache = $this->cacheHelperFactory->createEntityCache(Criteria::class, 'name');

        // Request to get supplier rankings and notations.
        // master_erp and master_suno to NULL is a master data.
        // Order by last_eval to sort by the most recent rankings, this will ignore old duplicate supplier rankings.
        //        $sql = <<<'SQL'
        //            SELECT *
        //            FROM supp_classification
        //            WHERE
        //                master_erp IS NULL
        //                AND master_suno IS NULL
        //                AND erp IS NOT NULL AND erp > 0
        //                AND t_suno IS NOT NULL AND t_suno <> ''
        //            ORDER BY last_eval DESC
        //            SQL;
        //        $stmt = $this->legacyConnection->executeQuery($sql);
        //
        //        $this->helper->progressiveImport($output, $stmt, SupplierRanking::class, 'id', 'id',
        //            function (SupplierRanking $supplierRanking, array $data) use ($peopleCache, $classificationCache, $expertiseLevelCache, $locationCache, $criteriaCache) {
        //                if (!($newMasterErp = $this->findNewErpCode($data['erp']))) {
        //                    throw new \InvalidArgumentException(sprintf('ERP %s is not on the mapping list', $data['erp']));
        //                }
        //
        //                if (!($newSupplierNumber = $this->findNewSupplierCode($newMasterErp, $data['t_suno']))) {
        //                    throw new \InvalidArgumentException(sprintf('ERP %s and Supplier %s is not on the supplier number mapping list', $newMasterErp, $data['t_suno']));
        //                }
        //
        //                if (null === $location = $locationCache->fetch((string) $newMasterErp)) {
        //                    throw new \InvalidArgumentException(sprintf('ERP %s does not match with any location', $newMasterErp));
        //                }
        //
        //                // Check if the data is already exist to avoid duplicate entry.
        //                if (\in_array($newMasterErp.$newSupplierNumber, $this->existingDatas, true)) {
        //                    throw new \InvalidArgumentException(sprintf('Supplier number %s with ERP %s already exist.', $data['t_suno'], $newMasterErp));
        //                }
        //
        //                // Create notations
        //                foreach ($this->criteriasMapping as $criteriaFieldLegacy => $criteriaName) {
        //                    $this->addNotation($supplierRanking, $criteriaCache->fetch($criteriaName), $data[$criteriaFieldLegacy]);
        //                }
        //
        //                $supplierRanking->setSupplierNumber($newSupplierNumber);
        //                $supplierRanking->lastReviewAt = $data['last_eval'] ? new \DateTime($data['last_eval']) : null;
        //                $supplierRanking->lastReviewBy = $peopleCache->fetch((string) $data['last_eval_user']);
        //                $supplierRanking->classification = $classificationCache->fetch((string) $data['supp_pur_status_id']);
        //                $supplierRanking->expertiseLevel = $expertiseLevelCache->fetch((string) $data['supp_exper_class_id']);
        //                $supplierRanking->lastScreeningAt = '0000-00-00' === $data['last_screening_date'] ? null : new \DateTime($data['last_screening_date']);
        //                $supplierRanking->location = $location;
        //                $supplierRanking->setLegacyId($data['id']);
        //
        //                $this->existingDatas[] = $newMasterErp.$newSupplierNumber;
        //            }
        //        );
        //
        //        return Command::SUCCESS;
    }

    /**
     * Create Notation object, ignore N/A to not create useless entry.
     */
    protected function addNotation(SupplierRanking $supplierRanking, Criteria $criteria, $rank): void
    {
        if ('N/A' === $rank || !is_numeric($rank)) {
            return;
        }

        $notation = new Notation();

        $notation->criteria = $criteria;
        $notation->notation = (int) $rank;

        $supplierRanking->addNotation($notation);
    }

    protected function findNewErpCode(int $erp): bool|int
    {
        if (isset($this->commonDatas->commonFactories[$erp])) {
            return $this->commonDatas->commonFactories[$erp];
        }

        return false;
    }

    protected function findNewSupplierCode($newErpCode, $oldSupplierNumber)
    {
        $key = \sprintf('%s-%s', $newErpCode, $oldSupplierNumber);
        if (isset($this->commonDatas->newSupplierValues[$key])) {
            return $this->commonDatas->newSupplierValues[$key];
        }

        return false;
    }
}
