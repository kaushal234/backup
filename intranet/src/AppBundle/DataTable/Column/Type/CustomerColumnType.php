<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Column\Type;

use ApiBundle\Iri\Iri;
use Kreyu\Bundle\DataTableBundle\Column\Type\AbstractColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\LinkColumnType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CustomerColumnType extends AbstractColumnType
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label' => 'fields.customer',
                'header_translation_domain' => 'messages',
                'href' => function (?array $customer = null): ?string {
                    if (null === $customer) {
                        return null;
                    }

                    return $this->urlGenerator->generate('sales_customers_show', ['id' => Iri::id($customer['@id'])]);
                },
                'formatter' => static function (?array $customer = null): ?string {
                    return $customer['name'] ?? null;
                },
            ])
        ;
    }

    public function getParent(): ?string
    {
        return LinkColumnType::class;
    }
}
