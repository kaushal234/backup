<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type\Engineering\Pictogram;

use ApiBundle\Model\ApiData;
use AppBundle\Controller\Engineering\Pictogram\PictogramController;
use AppBundle\DataTable\Action\Type\ShowButtonActionType;
use AppBundle\DataTable\Column\Type\LabelColumnType;
use AppBundle\DataTable\Column\Type\PictureColumnType;
use AppBundle\DataTable\Filter\Type\Engineering\Pictogram\CategoryFilterType;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\DataTable\Type\AbstractGridDataTableType;
use Kreyu\Bundle\DataTableBundle\Action\Type\ButtonActionType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class PictogramDataTableType extends AbstractGridDataTableType
{
    public const string RESOURCE = 'engineering/pictograms';

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly CsrfTokenManagerInterface $tokenManager,
        private readonly Security $security,
    ) {
    }

    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('picture', PictureColumnType::class, [
                'label' => null,
                'property_path' => '[picture?][filePath]',
            ])
            ->addColumn('description', TextColumnType::class, [
                'label' => null,
            ])
            ->addColumn('category', LabelColumnType::class, [
                'label' => null,
                'property_path' => '[category][name]',
                'color_property_path' => '[category][color]',
            ])
        ;

        $builder
            ->addRowAction('show', ShowButtonActionType::class, [
                'route' => 'pictogram_show',
            ])
            ->addRowAction('delete', ButtonActionType::class, [
                'label' => '',
                'href' => function (ApiData $quote): string {
                    return $this->urlGenerator->generate('pictogram_delete', [
                        'id' => $quote->getIriId(),
                        '_token' => $this->tokenManager->getToken(PictogramController::DELETE_TOKEN),
                    ]);
                },
                'confirmation' => [
                    'label_title' => 'delete.popup.title',
                    'label_description' => 'delete.popup.message',
                    'translation_domain' => 'engineering_pictogram',
                ],
                'icon' => 'fa7-solid:trash',
                'variant' => 'danger',
                'visible' => $this->security->isGranted('FEATURE_PICTOGRAM_DELETE'),
            ])
        ;

        $builder->addFilter('category', CategoryFilterType::class, [
            'label' => 'categories',
        ]);

        $builder
            ->setSearchHandler(static function (ApiProxyQuery $query, string $search) {
                $query->search($search);
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'title' => 'pictograms',
            'translation_domain' => 'engineering_pictogram',
            'per_page_choices' => [12, 24, 36],
            'personalization_enabled' => true,
        ]);
    }
}
