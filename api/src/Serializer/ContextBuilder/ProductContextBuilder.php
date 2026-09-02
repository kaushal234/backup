<?php

declare(strict_types=1);

namespace App\Serializer\ContextBuilder;

use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\State\ProviderInterface;
use ApiPlatform\State\SerializerContextBuilderInterface;
use App\Entity\Sales\Product;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

class ProductContextBuilder implements SerializerContextBuilderInterface
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
        if (Product::class !== $context['resource_class'] || $normalization) {
            return $context;
        }

        if (!$request->isMethod(Request::METHOD_PUT)) {
            return $context;
        }

        $product = $this->provider->provide($this->resourceMetadataCollectionFactory->create(Product::class)->getOperation(), ['id' => $request->attributes->get('id')]);

        if (
            $this->security->isGranted('CATALOG_EDIT_VOTER', $product)
            || $this->security->isGranted('FEATURE_CATALOG_EDIT')
            || $this->security->isGranted('MOO_CAT')
        ) {
            $context[AbstractObjectNormalizer::GROUPS][] = 'catalogue_product_write';
        }

        if ($this->security->isGranted('FEATURE_PRODUCT_MANUFACTURING_WRITE')) {
            $context[AbstractObjectNormalizer::GROUPS][] = 'product_manufacturing:write';
        }

        if ($this->security->isGranted('FEATURE_PRODUCT_STANDARD_ITEM_WRITE') || $this->security->isGranted('MOO_CAT')) {
            $context[AbstractObjectNormalizer::GROUPS][] = 'product_standard_item_write';
        }

        if (
            $this->security->isGranted('CATALOG_EDIT_VOTER', $product)
            || $this->security->isGranted('FEATURE_CATALOG_EDIT')
            || $this->security->isGranted('FEATURE_CATALOG_FINANCE_ADMIN')
            || $this->security->isGranted('FEATURE_PRODUCT_MANUFACTURING_WRITE')
        ) {
            $context[AbstractObjectNormalizer::GROUPS][] = 'catalogue_product_finance_admin';
        }

        return $context;
    }
}
