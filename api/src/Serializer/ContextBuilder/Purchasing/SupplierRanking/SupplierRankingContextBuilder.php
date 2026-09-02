<?php

declare(strict_types=1);

namespace App\Serializer\ContextBuilder\Purchasing\SupplierRanking;

use ApiPlatform\State\SerializerContextBuilderInterface;
use App\Entity\Purchasing\SupplierRanking\SupplierRanking;
use App\Entity\Purchasing\VendorUser;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

class SupplierRankingContextBuilder implements SerializerContextBuilderInterface
{
    public function __construct(
        public readonly SerializerContextBuilderInterface $decorated,
        public readonly Security $security
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);

        if (SupplierRanking::class !== $context['resource_class']) {
            return $context;
        }

        $user = $this->security->getUser();

        if ($user instanceof VendorUser) {
            $context[AbstractObjectNormalizer::GROUPS][] = 'supplier:light';
        } else {
            $context[AbstractObjectNormalizer::GROUPS][] = 'supplier';
        }

        return $context;
    }
}
