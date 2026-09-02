<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Column\Type;

use ApiBundle\Iri\Iri;
use Kreyu\Bundle\DataTableBundle\Column\Type\AbstractColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\LinkColumnType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PeopleColumnType extends AbstractColumnType
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label' => 'directory.user.name',
                'header_translation_domain' => 'directory',
                'href' => function (?array $people): ?string {
                    if (null === $people) {
                        return null;
                    }

                    return $this->urlGenerator->generate('directory_people_show', ['id' => Iri::id($people['@id'])]);
                },
                'formatter' => static function (array $people) {
                    return \sprintf('%s %s', $people['lastname'], $people['firstname']);
                },
            ])
        ;
    }

    public function getParent(): ?string
    {
        return LinkColumnType::class;
    }
}
