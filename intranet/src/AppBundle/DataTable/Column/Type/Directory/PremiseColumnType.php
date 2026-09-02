<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Column\Type\Directory;

use ApiBundle\Iri\Iri;
use Kreyu\Bundle\DataTableBundle\Column\Type\AbstractColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PremiseColumnType extends AbstractColumnType
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label' => 'directory.premise.title',
                'header_translation_domain' => 'directory',
                'property_path' => '[premise?][name]',
                'href' => function (?array $premise): ?string {
                    if (null === $premise) {
                        return null;
                    }

                    return $this->urlGenerator->generate('directory_premise_show', ['id' => Iri::id($premise['@id'])]);
                },
            ])
        ;
    }

    public function getParent(): ?string
    {
        return TextColumnType::class;
    }
}
