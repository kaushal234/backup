<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type\Notifications;

use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Filter\Type\BooleanFilterType;
use AppBundle\DataTable\Filter\Type\DateRangeFilterType;
use AppBundle\DataTable\Filter\Type\Module\ModuleFilterType;
use AppBundle\DataTable\Query\ApiProxyQuery;
use Kreyu\Bundle\DataTableBundle\Action\Type\ButtonActionType;
use Kreyu\Bundle\DataTableBundle\Column\Type\DateTimeColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\HtmlColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class NotificationsDataTableType extends AbstractDataTableType
{
    public const string RESOURCE = 'notifications';

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $boldFormatter = $this->getUnreadFormatter();

        $builder
            ->addColumn('unread', HtmlColumnType::class, [
                'label' => '',
                'formatter' => static function (bool $value): string {
                    return $value ? '<span class="tld-notification-dot"></span>' : '';
                },
            ])
            ->addColumn('textDisplayed', HtmlColumnType::class, [
                'label' => 'notifications.fields.text',
                'formatter' => $boldFormatter,
            ])
            ->addColumn('name', HtmlColumnType::class, [
                'label' => 'notifications.fields.module',
                'property_path' => '[template][module][name]',
                'formatter' => $boldFormatter,
            ])
            ->addColumn('createdAt', DateTimeColumnType::class, [
                'label' => 'fields.created_at',
                'header_translation_domain' => 'messages',
                'format' => 'Y-m-d',
                'sort' => true,
            ])
            ->addColumn('url', TextColumnType::class, [
                'visible' => false,
            ])
        ;
        $builder
            ->addFilter('createdAt', DateRangeFilterType::class, [
                'label' => 'fields.created_at',
                'translation_domain' => 'messages',
            ])
            ->addFilter('unread', BooleanFilterType::class, [
                'label' => 'notifications.fields.unread',
            ])
            ->addFilter('name', ModuleFilterType::class, [
                'label' => 'notifications.fields.module',
                'query_path' => 'template.module.name',
            ])
        ;
        $builder
            ->setSearchHandler(static function (ApiProxyQuery $query, string $search) {
                $query->search($search);
            })
            ->setDefaultSortingData(SortingData::fromArray([
                'createdAt' => 'desc',
            ]))
            ->addRowAction('show_and_read', ButtonActionType::class, [
                'label' => 'Show',
                'variant' => 'primary',
                'href' => fn (ApiData $row) => $this->urlGenerator->generate(
                    'read_and_redirect_notification',
                    ['id' => $row['id']]
                ),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'title' => 'notifications.title.my_notifications',
            'translation_domain' => 'notifications',
        ]);
    }

    private function getUnreadFormatter(): \Closure
    {
        return static function ($value, ApiData $row): string {
            return $row['unread'] ? "<b>$value</b>" : (string) $value;
        };
    }
}
