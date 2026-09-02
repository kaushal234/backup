<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Column\Type;

use ApiBundle\Iri\Iri;
use Kreyu\Bundle\DataTableBundle\Column\Type\AbstractColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\LinkColumnType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DepartmentColumnType extends AbstractColumnType
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label' => 'directory.department.name',
                'header_translation_domain' => 'directory',
                'formatter' => static fn (?array $department) => $department['name'] ?? null,
                'href' => function (?array $department): ?string {
                    if (null === $department) {
                        return null;
                    }

                    return $this->urlGenerator->generate('directory_departments_show', ['id' => Iri::id($department['@id'])]);
                },
            ])
        ;
    }

    public function getParent(): ?string
    {
        return LinkColumnType::class;
    }
}
