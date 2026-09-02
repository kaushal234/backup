<?php

declare(strict_types=1);

namespace App\ION\Manager\MasterData\BusinessPartners;

use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use App\ION\Client\Request\ComparisonExpression;
use App\ION\Client\Request\LogicalExpression;
use App\ION\Client\Request\LogicalExpressionBuilderFactory;
use App\ION\DataProvider\CachedIONCollectionDataProvider;
use App\ION\DataProvider\CachedIONItemDataProvider;
use App\ION\Filter\IONFilter;
use App\ION\Resources\MasterData\BusinessPartners\BusinessPartner;
use App\ION\SourceProvider\SourceProvider;

class BusinessPartnerManager
{
    public function __construct(
        private readonly LogicalExpressionBuilderFactory $logicalExpressionBuilderFactory,
        private readonly ResourceMetadataCollectionFactoryInterface $resourceMetadataFactory,
        private readonly CachedIONItemDataProvider $itemProvider,
        private readonly CachedIONCollectionDataProvider $collectionProvider,
        private readonly SourceProvider $sourceProvider,
    ) {
    }

    public function findSupplier(string $supplierNumber): ?BusinessPartner
    {
        return $this->getBusinessPartner($supplierNumber, 'supplier');
    }

    public function findCustomer(string $supplierNumber): ?BusinessPartner
    {
        return $this->getBusinessPartner($supplierNumber, 'customer');
    }

    private function getBusinessPartner(string $supplierNumber, string $role): ?BusinessPartner
    {
        $metadata = $this->resourceMetadataFactory->create(BusinessPartner::class);
        $resourceSourceProvider = $this->sourceProvider->getResourceSourceProvider(BusinessPartner::class);

        $logicalExpressionBuilder = $this->logicalExpressionBuilderFactory->create($resourceSourceProvider->getResource());
        $logicalExpressionBuilder->addCondition(ComparisonExpression::ION_DEFAULT_COMPARISON_OPERATOR, $supplierNumber, 'code');

        $logicalExpressionRoleBuilder = $this->logicalExpressionBuilderFactory->create($resourceSourceProvider->getResource(), LogicalExpression::ION_LOGICAL_OPERATOR_OR);
        $logicalExpressionRoleBuilder
            ->addCondition(ComparisonExpression::ION_DEFAULT_COMPARISON_OPERATOR, $role, 'role')
            ->addCondition(ComparisonExpression::ION_DEFAULT_COMPARISON_OPERATOR, 'both', 'role')
        ;

        /** @var BusinessPartner[] $suppliers */
        $suppliers = $this->collectionProvider->provide($metadata->getOperation(forceCollection: true), [], [
            IONFilter::CONTEXT_LOGICAL_EXPRESSION_KEY => $logicalExpressionBuilder->getLogicalExpression()->addLogicalExpression($logicalExpressionRoleBuilder->getLogicalExpression()),
            'filters' => ['itemsPerPage' => 1],
        ]);

        if (null === ($suppliers[0] ?? null)) {
            return null;
        }

        return $this->itemProvider->provide($metadata->getOperation(), ['code' => $supplierNumber]);
    }
}
