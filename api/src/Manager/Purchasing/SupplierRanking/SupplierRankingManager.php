<?php

declare(strict_types=1);

namespace App\Manager\Purchasing\SupplierRanking;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use App\Entity\Directory\People;
use App\ION\DataProvider\CachedIONCollectionDataProvider;
use App\ION\DataProvider\CachedIONItemDataProvider;
use App\ION\Filter\DataAreaFilter;
use App\ION\Resources\MasterData\BusinessPartners\Buyer;
use App\ION\Resources\MasterData\BusinessPartners\Turnover;
use Doctrine\ORM\EntityManagerInterface;

class SupplierRankingManager
{
    private iterable $turnovers = [];

    public function __construct(
        private readonly CachedIONCollectionDataProvider $collectionProvider,
        private readonly CachedIONItemDataProvider $itemProvider,
        private readonly ResourceMetadataCollectionFactoryInterface $resourceMetadataFactory,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @return iterable<Turnover>
     */
    public function getTurnovers(): iterable
    {
        if (!empty($this->turnovers)) {
            return $this->turnovers;
        }

        $metadata = $this->resourceMetadataFactory->create(Turnover::class);
        $date = new \DateTime();
        $this->turnovers = $this->collectionProvider->provide(
            $metadata->getOperation(forceCollection: true),
            [],
            [
                'operation_type' => GetCollection::class,
                DataAreaFilter::CONTEXT_DATA_AREA_KEY => [
                    'orderLineDateBefore' => $date->format('Y-m-d\TH:i:s\Z'),
                    'orderLineDateAfter' => $date->modify('-2 year')->format('Y-m-d\TH:i:s\Z'),
                ],
            ]
        );

        return $this->turnovers;
    }

    public function findTurnoverWithSupplierCode(string $supplierCode): Turnover|bool
    {
        foreach ($this->getTurnovers() as $turnover) {
            if ($turnover->code === $supplierCode) {
                return $turnover;
            }
        }

        return false;
    }

    public function findBuyer(string $supplierCode, int $erpCode): ?People
    {
        $metadata = $this->resourceMetadataFactory->create(Buyer::class);
        $buyer = $this->itemProvider->provide(
            $metadata->getOperation(),
            [
                'code' => $supplierCode,
                'site' => $erpCode,
            ],
        );

        if (empty($buyer->buyerCode)) {
            return null;
        }

        return $this->entityManager->getRepository(People::class)->find($buyer->buyerCode);
    }

    public function findTurnoverWithSite(int $erpCode, int $lastYear = 1)
    {
        $date = new \DateTime();

        $metadata = $this->resourceMetadataFactory->create(Turnover::class);

        return $this->collectionProvider->provide(
            $metadata->getOperation(forceCollection: true),
            [],
            [
                'operation_type' => GetCollection::class,
                DataAreaFilter::CONTEXT_DATA_AREA_KEY => [
                    'code' => null,
                    'title' => (string) $erpCode,
                    'orderLineDateBefore' => $date->format('Y-m-d\TH:i:s\Z'),
                    'orderLineDateAfter' => $date->modify(\sprintf('-%d year', $lastYear))->format('Y-m-d\TH:i:s\Z'),
                ],
            ]
        );
    }
}
