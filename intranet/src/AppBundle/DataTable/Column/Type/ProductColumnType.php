<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Column\Type;

use ApiBundle\Iri\Iri;
use Kreyu\Bundle\DataTableBundle\Column\Type\AbstractColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\LinkColumnType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProductColumnType extends AbstractColumnType
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label' => 'catalogue.products.product',
                'header_translation_domain' => 'catalogue',
                'href' => function (?array $product): ?string {
                    if (null === $product) {
                        return null;
                    }

                    return $this->urlGenerator->generate('sales_catalogue_product_show', ['id' => Iri::id($product['@id'])]);
                },
                'formatter' => static function (array $product) {
                    return $product['name'];
                },
            ])
        ;
    }

    public function getParent(): ?string
    {
        return LinkColumnType::class;
    }
}
