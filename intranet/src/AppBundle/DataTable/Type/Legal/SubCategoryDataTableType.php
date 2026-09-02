<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type\Legal;

use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Column\Type\LegalCategoryColumnType;
use AppBundle\DataTable\Filter\Type\TextFilterType;
use AppBundle\DataTable\Query\ApiProxyQuery;
use Kreyu\Bundle\DataTableBundle\Action\Type\ButtonActionType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SubCategoryDataTableType extends AbstractDataTableType
{
    public const string RESOURCE = 'contract/sub_categories';

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
            ->addColumn('category', LegalCategoryColumnType::class, [
                'label' => 'legal.fields.category',
                'header_translation_domain' => 'legal',
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
                'href' => function (ApiData $subCategory): string {
                    return $this->urlGenerator->generate('sub_category_edit', [
                        'id' => $subCategory->getIriId(),
                    ]);
                },
                'icon' => 'fa7-solid:edit',
                'visible' => static function (ApiData $subCategory): bool {
                    return $subCategory['isAllow'];
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
    }
}
