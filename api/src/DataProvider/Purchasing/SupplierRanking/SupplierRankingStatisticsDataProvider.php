<?php

declare(strict_types=1);

namespace App\DataProvider\Purchasing\SupplierRanking;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Purchasing\SupplierRanking\SupplierRankingStatisticsDto;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class SupplierRankingStatisticsDataProvider implements ProviderInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        #[Autowire(service: 'api_platform.doctrine.orm.state.collection_provider')]
        private readonly ProviderInterface $collectionProvider,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $supplierRankings = $this->collectionProvider->provide(
            $operation,
            $uriVariables,
            $context
        );

        $totalCompletionRate = 0;
        $mandatoryCompletionRate = 0;
        foreach ($supplierRankings as $supplierRanking) {
            $category = [];
            $requiredNames = ['Code Ethic', 'ESG'];
            $status = [
                'Code Ethic' => true,
                'ESG' => true,
            ];
            $found = [
                'Code Ethic' => false,
                'ESG' => false,
            ];
            foreach ($supplierRanking->getFiles() as $file) {
                $name = $file->category->name;
                $expired = $file->isExpired();
                if (!isset($category[$name])) {
                    $category[$name] = true;
                }
                if (true === $expired) {
                    $category[$name] = false;
                }
                if (\in_array($name, $requiredNames, true)) {
                    $found[$name] = true;
                    if (true === $expired) {
                        $status[$name] = false;
                    }
                }
            }
            foreach ($category as $name => $value) {
                if (true === $value) {
                    $totalCompletionRate += 1 / 8;
                }
            }
            $result = $found['Code Ethic'] && $found['ESG'] && $status['Code Ethic'] && $status['ESG'];
            if (true === $result) {
                ++$mandatoryCompletionRate;
            }
        }

        if (0 !== \count($supplierRankings)) {
            $totalCompletionRate = $totalCompletionRate / \count($supplierRankings) * 100;
            $mandatoryCompletionRate = $mandatoryCompletionRate / \count($supplierRankings) * 100;
        }

        return new SupplierRankingStatisticsDto(
            totalCompletionRate: round($totalCompletionRate, 2),
            mandatoryCompletionRate: round($mandatoryCompletionRate, 2)
        );
    }
}
