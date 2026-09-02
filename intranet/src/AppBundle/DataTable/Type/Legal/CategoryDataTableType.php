<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type\Legal;

use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Filter\Type\TextFilterType;
use AppBundle\DataTable\Query\ApiProxyQuery;
use Kreyu\Bundle\DataTableBundle\Action\Type\ButtonActionType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CategoryDataTableType extends AbstractDataTableType
{
    public const string RESOURCE = 'contract/categories';

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('displayedName', TextColumnType::class, [
                'label' => 'fields.name',
                'header_translation_domain' => 'messages',
            ])
        ;

        $builder->setSearchHandler(static function (ApiProxyQuery $query, string $search) {
            $query->search($search);
        });

        $builder
            ->addFilter('name', TextFilterType::class, [
                'label' => 'fields.name',
                'translation_domain' => 'messages',
            ])
        ;

        $builder
            ->addRowAction('update', ButtonActionType::class, [
                'label' => '',
                'href' => function (ApiData $category): string {
                    return $this->urlGenerator->generate('category_edit', [
                        'id' => $category->getIriId(),
                    ]);
                },
                'icon' => 'fa7-solid:edit',
                'visible' => static function (ApiData $category): bool {
                    return $category['isAllow'];
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
    }
}
