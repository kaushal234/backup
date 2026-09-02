<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Column\Type\Sales\Catalog;

use ApiBundle\Iri\Iri;
use Kreyu\Bundle\DataTableBundle\Column\Type\AbstractColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\LinkColumnType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProductTypeColumnType extends AbstractColumnType
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label' => 'catalogue.type.product_type',
                'translation_domain' => 'catalogue',
                'href' => function (?array $productType = null): ?string {
                    if (null === $productType) {
                        return null;
                    }

                    return $this->urlGenerator->generate('sales_catalogue_type_show', ['id' => Iri::id($productType['@id'])]);
                },
                'formatter' => static function (?array $productType = null): ?string {
                    return $productType['englishName'] ?? null;
                },
            ])
        ;
    }

    public function getParent(): ?string
    {
        return LinkColumnType::class;
    }
}
