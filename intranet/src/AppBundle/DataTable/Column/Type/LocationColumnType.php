<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Column\Type;

use ApiBundle\Iri\Iri;
use Kreyu\Bundle\DataTableBundle\Column\Type\AbstractColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\LinkColumnType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class LocationColumnType extends AbstractColumnType
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label' => 'location',
                'formatter' => static function (?array $location = null): ?string {
                    return $location['name'];
                },
                'href' => function (?array $location = null): ?string {
                    if (null === $location) {
                        return null;
                    }

                    return $this->urlGenerator->generate('directory_locations_show', ['id' => Iri::id($location['@id'])]);
                },
            ])
        ;
    }

    public function getParent(): ?string
    {
        return LinkColumnType::class;
    }
}
