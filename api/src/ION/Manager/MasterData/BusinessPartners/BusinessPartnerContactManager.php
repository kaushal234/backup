<?php

declare(strict_types=1);

namespace App\ION\Manager\MasterData\BusinessPartners;

use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use App\Client\Exception\SoapException;
use App\ION\Client\Request\ComparisonExpression;
use App\ION\Client\Request\LogicalExpressionBuilderFactory;
use App\ION\DataProvider\CachedIONCollectionDataProvider;
use App\ION\DataProvider\CachedIONItemDataProvider;
use App\ION\Filter\IONFilter;
use App\ION\Resources\MasterData\BusinessPartners\BusinessPartnerContact;
use App\ION\Resources\MasterData\BusinessPartners\BusinessPartnerContactCategory;
use App\ION\SourceProvider\SourceProvider;

class BusinessPartnerContactManager
{
    public function __construct(
        private readonly CachedIONItemDataProvider $itemProvider,
        private readonly CachedIONCollectionDataProvider $collectionProvider,
        private readonly LogicalExpressionBuilderFactory $logicalExpressionBuilderFactory,
        private readonly ResourceMetadataCollectionFactoryInterface $resourceMetadataCollectionFactory,
        private readonly SourceProvider $sourceProvider
    ) {
    }

    public function findByEmail(string $email): ?BusinessPartnerContact
    {
        $metadata = $this->resourceMetadataCollectionFactory->create(BusinessPartnerContact::class);
        $resourceSourceProvider = $this->sourceProvider->getResourceSourceProvider(BusinessPartnerContact::class);

        $logicalExpressionBuilder = $this->logicalExpressionBuilderFactory->create($resourceSourceProvider->getResource());
        $logicalExpressionBuilder->addCondition(ComparisonExpression::ION_DEFAULT_COMPARISON_OPERATOR, $email, 'emailAddress');
        $operation = $metadata->getOperation(forceCollection: true);

        /** @var BusinessPartnerContact[] $contacts */
        $contacts = $this->collectionProvider->provide($operation, [], [
            IONFilter::CONTEXT_LOGICAL_EXPRESSION_KEY => $logicalExpressionBuilder->getLogicalExpression(),
            'filters' => ['itemsPerPage' => 1],
        ]);

        if (null === ($contact = $contacts[0] ?? null)) {
            return null;
        }

        if (!$contact->isGrantedCategory(BusinessPartnerContactCategory::REQUIRED_CATEGORY_NAME)) {
            return null;
        }

        // not all the properties are returned by the List operation, we need to fetch the full contact using the Show operation
        return $this->findByErpIdentifier($contact->contactCode);
    }

    public function findByErpIdentifier(string $identifier): ?BusinessPartnerContact
    {
        try {
            $operation = $this->resourceMetadataCollectionFactory->create(BusinessPartnerContact::class)->getOperation();

            /* @var BusinessPartnerContact|null $contact */
            return $this->itemProvider->provide($operation, ['contactCode' => $identifier]);
        } catch (SoapException $e) {
            return null;
        }
    }
}
