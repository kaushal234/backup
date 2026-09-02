<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type\Sales;

use ApiBundle\Model\ApiData;
use AppBundle\Controller\Sales\QuoteController;
use AppBundle\DataTable\Query\ApiProxyQuery;
use Kreyu\Bundle\DataTableBundle\Action\Type\ButtonActionType;
use Kreyu\Bundle\DataTableBundle\Column\Type\ColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class QuoteDataTableType extends AbstractDataTableType
{
    public const RESOURCE = 'sales/quotes';

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly CsrfTokenManagerInterface $tokenManager,
    ) {
    }

    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('id', ColumnType::class, [
                'label' => 'display.table.scar_files.headers.id',
                'header_translation_domain' => 'messages',
                'sort' => true,
            ])
            ->addColumn('quoteNumber', TextColumnType::class, [
                'label' => 'quotes.fields.quote_number',
                'header_translation_domain' => 'sales_orders',
                'sort' => true,
            ])
            ->addRowAction('delete', ButtonActionType::class, [
                'label' => '',
                'href' => function (ApiData $quote): string {
                    return $this->urlGenerator->generate('sales_quotes_delete', [
                        'id' => $quote->getIriId(),
                        '_token' => $this->tokenManager->getToken(QuoteController::DELETE_TOKEN),
                    ]);
                },
                'icon' => 'fa7-solid:trash',
                'variant' => 'danger',
            ])

            // Simple search top right, using 'q' parameter of ApiPlatform
           ->setSearchHandler(static function (ApiProxyQuery $query, string $search) {
               $query->search($search);
           })
            ->setDefaultSortingData(SortingData::fromArray([
                'id' => 'desc',
            ]))
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'title' => 'quotes.title',
            'translation_domain' => 'sales_orders',
        ]);
    }
}
