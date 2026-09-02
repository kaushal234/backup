<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Column\Type\Sales\Catalog;

use ApiBundle\Iri\Iri;
use Kreyu\Bundle\DataTableBundle\Column\Type\AbstractColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\LinkColumnType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProductFamilyColumnType extends AbstractColumnType
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label' => 'catalogue.family.product_family',
                'translation_domain' => 'catalogue',
                'href' => function (?array $productFamily = null): ?string {
                    if (null === $productFamily) {
                        return null;
                    }

                    return $this->urlGenerator->generate('sales_catalogue_family_show', ['id' => Iri::id($productFamily['@id'])]);
                },
                'formatter' => static function (?array $productFamily = null): ?string {
                    return $productFamily['name'] ?? null;
                },
            ])
        ;
    }

    public function getParent(): ?string
    {
        return LinkColumnType::class;
    }
}
