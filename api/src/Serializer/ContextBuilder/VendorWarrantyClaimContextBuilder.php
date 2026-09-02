<?php

declare(strict_types=1);

namespace App\Serializer\ContextBuilder;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\State\ProviderInterface;
use ApiPlatform\State\SerializerContextBuilderInterface;
use App\Entity\Purchasing\NCRVendorWarrantyClaim;
use App\Entity\Purchasing\VendorUser;
use App\Entity\Purchasing\VendorWarrantyClaim;
use App\Entity\Purchasing\WCVendorWarrantyClaim;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

class VendorWarrantyClaimContextBuilder implements SerializerContextBuilderInterface
{
    public function __construct(
        private readonly SerializerContextBuilderInterface $decorated,
        private readonly Security $security,
        #[Autowire(service: 'api_platform.doctrine.orm.state.item_provider')]
        private readonly ProviderInterface $provider,
        private readonly ResourceMetadataCollectionFactoryInterface $resourceMetadataCollectionFactory
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);
        if (!\in_array($context['resource_class'], [VendorWarrantyClaim::class, WCVendorWarrantyClaim::class, NCRVendorWarrantyClaim::class], true)) {
            return $context;
        }

        if ($normalization) {
            if (!($this->security->getUser()) instanceof VendorUser) {
                return $context;
            }

            if (($context['operation'] ?? null) instanceof Get || ($context['operation'] ?? null) instanceof Put) {
                $context[AbstractObjectNormalizer::GROUPS][] = 'non_conformity:detail';
                $context[AbstractObjectNormalizer::GROUPS][] = 'process';
                $context[AbstractObjectNormalizer::GROUPS][] = 'responsible';
                $context[AbstractObjectNormalizer::GROUPS][] = 'catalogue_public';
            }

            return $context;
        }

        if (!$request->isMethod(Request::METHOD_PUT)) {
            return $context;
        }

        $vendorWarrantyClaim = $this->provider->provide($this->resourceMetadataCollectionFactory->create(VendorWarrantyClaim::class)->getOperation(), ['id' => $request->attributes->get('id')]);

        if ($this->security->isGranted('BUSINESS_PARTNER_VOTER', $vendorWarrantyClaim)) {
            $context[AbstractObjectNormalizer::GROUPS] = ['vendor_warranty_claim:evendor_edit'];

            return $context;
        }

        if ($this->security->isGranted('FEATURE_VENDOR_WARRANTY_CLAIM_EDIT_FULL')
         || $this->security->isGranted('FEATURE_VENDOR_WARRANTY_CLAIM_EDIT')) {
            $context[AbstractObjectNormalizer::GROUPS][] = 'vendor_warranty_claim:edit';
            $context[AbstractObjectNormalizer::GROUPS][] = 'part:admin';
        }

        if ($this->security->isGranted('FEATURE_VWC_EDIT_FINANCE')) {
            $context[AbstractObjectNormalizer::GROUPS][] = 'vendor_warranty_claim:edit_finance';
        }

        if ($this->security->isGranted('FEATURE_VENDOR_WARRANTY_CLAIM_EDIT_SHIPPING')) {
            $context[AbstractObjectNormalizer::GROUPS][] = 'vendor_warranty_claim:edit_shipping';
        }

        return $context;
    }
}
