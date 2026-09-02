<?php

declare(strict_types=1);

namespace App\Serializer\ContextBuilder;

use ApiPlatform\State\SerializerContextBuilderInterface;
use App\Entity\Sales\SalesForecast;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

class SalesForecastContextBuilder implements SerializerContextBuilderInterface
{
    private readonly SerializerContextBuilderInterface $decorated;

    private readonly AuthorizationCheckerInterface $authorizationChecker;

    public function __construct(SerializerContextBuilderInterface $decorated, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->decorated = $decorated;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);

        if (SalesForecast::class !== $context['resource_class'] || $normalization) {
            return $context;
        }

        if (
            $this->authorizationChecker->isGranted('FEATURE_SALES_FORECAST_ADMIN_EDIT')
            || $this->authorizationChecker->isGranted('MOO_SFR')
        ) {
            $context[AbstractObjectNormalizer::GROUPS][] = 'sales_forecast_admin_edit';

            return $context;
        }

        if ($this->authorizationChecker->isGranted('FEATURE_SALES_FORECAST_RESTRICTED_EDIT')) {
            $context[AbstractObjectNormalizer::GROUPS][] = 'sales_forecast_restricted_edit';

            return $context;
        }

        if ($this->authorizationChecker->isGranted('FEATURE_SALES_FORECAST_FACTORY_EDIT')) {
            $context[AbstractObjectNormalizer::GROUPS][] = 'sales_forecast_factory_edit';

            return $context;
        }

        return $context;
    }
}
