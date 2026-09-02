<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type\Engineering\Pictogram;

use ApiBundle\Model\ApiData;
use AppBundle\Controller\Engineering\Pictogram\CategoryController;
use AppBundle\DataTable\Column\Type\ColorColumnType;
use AppBundle\DataTable\Query\ApiProxyQuery;
use Kreyu\Bundle\DataTableBundle\Action\Type\ButtonActionType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class CategoryDataTableType extends AbstractDataTableType
{
    public const string RESOURCE = 'engineering/pictogram/categories';

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly CsrfTokenManagerInterface $tokenManager,
        private readonly Security $security,
    ) {
    }

    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('id', TextColumnType::class)
            ->addColumn('name', TextColumnType::class, [
                'label' => 'fields.name',
                'sort' => 'name',
            ])
            ->addColumn('color', ColorColumnType::class, [
                'label' => 'fields.color',
            ])
        ;

        $builder
            ->addRowAction('update', ButtonActionType::class, [
                'label' => '',
                'href' => function (ApiData $category): string {
                    return $this->urlGenerator->generate('pictogram_category_edit', [
                        'id' => $category->getIriId(),
                    ]);
                },
                'icon' => 'fa7-solid:edit',
                'visible' => $this->security->isGranted('FEATURE_PICTOGRAM_CATEGORY_UPDATE'),
            ])
            ->addRowAction('delete', ButtonActionType::class, [
                'label' => '',
                'href' => function (ApiData $category): string {
                    return $this->urlGenerator->generate('pictogram_category_delete', [
                        'id' => $category->getIriId(),
                        '_token' => $this->tokenManager->getToken(CategoryController::DELETE_TOKEN),
                    ]);
                },
                'confirmation' => [
                    'label_title' => 'delete.popup.title',
                    'label_description' => 'delete.popup.message',
                    'translation_domain' => 'engineering_pictogram',
                ],
                'icon' => 'fa7-solid:trash',
                'variant' => 'danger',
                'visible' => $this->security->isGranted('FEATURE_PICTOGRAM_CATEGORY_DELETE'),
            ])
        ;

        $builder
            ->setSearchHandler(static function (ApiProxyQuery $query, string $search) {
                $query->search($search);
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'title' => 'categories',
            'translation_domain' => 'engineering_pictogram',
        ]);
    }
}
