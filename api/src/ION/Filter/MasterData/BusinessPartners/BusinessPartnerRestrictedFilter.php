<?php

declare(strict_types=1);

namespace App\ION\Filter\MasterData\BusinessPartners;

use ApiPlatform\Serializer\Filter\FilterInterface;
use App\Entity\Purchasing\VendorUser;
use App\ION\Client\Request\ComparisonExpression;
use App\ION\Client\Request\LogicalExpression;
use App\ION\Client\Request\LogicalExpressionBuilderFactory;
use App\ION\Filter\IONFilter;
use App\ION\Resources\MasterData\BusinessPartners\BusinessPartner;
use App\ION\SourceProvider\SourceProvider;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\TypeInfo\TypeIdentifier;

class BusinessPartnerRestrictedFilter implements FilterInterface
{
    final public const FILTER_PROPERTY = 'restricted_business_partners';

    public function __construct(
        private readonly LogicalExpressionBuilderFactory $logicalExpressionBuilderFactory,
        private readonly SourceProvider $sourceProvider,
        private readonly Security $security,
    ) {
    }

    public function apply(Request $request, bool $normalization, array $attributes, array &$context): void
    {
        $user = $this->security->getUser();
        if (!$user instanceof VendorUser) {
            return;
        }

        if (BusinessPartner::class !== $request->attributes->get('_api_resource_class')) {
            throw new \Exception('This filter is restricted to the BusinessPartner resource');
        }

        $resourceSourceProvider = $this->sourceProvider->getResourceSourceProvider(BusinessPartner::class);

        if (null === $ionResource = $resourceSourceProvider->getResource()) {
            return;
        }

        $logicalExpressionBuilder = $this->logicalExpressionBuilderFactory->create($ionResource, LogicalExpression::ION_LOGICAL_OPERATOR_OR);

        foreach ($user->getBusinessPartnerCodes() as $code) {
            $logicalExpressionBuilder
                ->addCondition(
                    ComparisonExpression::ION_DEFAULT_COMPARISON_OPERATOR,
                    $code,
                    'code'
                )
            ;
        }

        if (0 === \count($user->getBusinessPartnerCodes())) {
            $logicalExpressionBuilder
                ->addCondition(
                    ComparisonExpression::ION_DEFAULT_COMPARISON_OPERATOR,
                    'no_business_partner_allowed',
                    'code'
                )
            ;
        }

        /** @var LogicalExpression $logicalExpression */
        $logicalExpression = $context[IONFilter::CONTEXT_LOGICAL_EXPRESSION_KEY];
        $logicalExpression->addLogicalExpression($logicalExpressionBuilder->getLogicalExpression());
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            static::FILTER_PROPERTY => [
                'property' => static::FILTER_PROPERTY,
                'type' => TypeIdentifier::NULL->value,
                'required' => false,
            ],
        ];
    }
}
