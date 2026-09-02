<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type\Parts\SparePartsRequest;

use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Column\Type\AddressColumnType;
use AppBundle\DataTable\Column\Type\AirportColumnType;
use AppBundle\DataTable\Column\Type\ContactTooltipColumnType;
use AppBundle\DataTable\Filter\Type\AirportFilterType;
use AppBundle\DataTable\Filter\Type\BooleanFilterType;
use AppBundle\DataTable\Filter\Type\Directory\People\ExtranetUserFilterType;
use AppBundle\DataTable\Filter\Type\TextFilterType;
use Kreyu\Bundle\DataTableBundle\Action\Type\ButtonActionType;
use Kreyu\Bundle\DataTableBundle\Column\Type\BooleanColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\DateTimeColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class DeliveryAddressesDataTableType extends AbstractDataTableType
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('contact', ContactTooltipColumnType::class, [
                'sort' => 'contact.lastname',
            ])
            ->addColumn('lastname', TextColumnType::class, [
                'label' => 'contacts.fields.lastname',
                'sort' => true,
            ])
            ->addColumn('firstname', TextColumnType::class, [
                'label' => 'contacts.fields.firstname',
                'sort' => true,
            ])
            ->addColumn('phone', TextColumnType::class, [
                'label' => 'contacts.fields.phone',
            ])

            ->addColumn('address', AddressColumnType::class, [
                'sort' => false,
            ])

            ->addColumn('airport', AirportColumnType::class, [
                'sort' => 'airport.code',
            ])
            ->addColumn('lastUsedAt', DateTimeColumnType::class, [
                'label' => 'spare_parts_request.delivery_addresses.fields.last_used',
                'header_translation_domain' => 'spare_parts_request',
                'format' => 'Y-m-d',
                'sort' => true,
            ])
            ->addColumn('archived', BooleanColumnType::class, [
                'label' => 'spare_parts_request.delivery_addresses.fields.archived',
                'header_translation_domain' => 'spare_parts_request',
                'value_translation_domain' => 'spare_parts_request',
            ])
        ;

        $builder->addRowAction('toggle_archive', ButtonActionType::class, [
            'label' => '',
            'href' => function (ApiData $row): string {
                return $this->urlGenerator->generate('delivery_address_toggle_archive', [
                    'id' => $row->getIriId(),
                ]);
            },
            'icon' => static function (ApiData $row): string {
                return ($row['archived'] ?? false) ? 'fa7-solid:rotate-left' : 'fa7-solid:box-archive';
            },
            'attr' => fn (ApiData $row): array => [
                'class' => 'btn btn-sm btn-outline-secondary',
                'data-bs-toggle' => 'tooltip',
                'data-bs-placement' => 'top',
                'title' => ($row['archived'] ?? false)
                    ? $this->translator->trans('spare_parts_request.delivery_addresses.actions.activate', [], 'spare_parts_request')
                    : $this->translator->trans('spare_parts_request.delivery_addresses.actions.archive', [], 'spare_parts_request'),
            ],
        ]);

        $builder
            ->addFilter('contact', ExtranetUserFilterType::class, [
                'label' => 'contacts.extranet_user',
            ])
            ->addFilter('archived', BooleanFilterType::class, [
                'label' => 'spare_parts_request.delivery_addresses.fields.archived',
                'translation_domain' => 'spare_parts_request',
            ])
            ->addFilter('airport', AirportFilterType::class)
            ->addFilter('country', TextFilterType::class, [
                'query_path' => 'address.country',
            ])
            ->addFilter('city', TextFilterType::class, [
                'query_path' => 'address.city',
            ])
            ->addFilter('town', TextFilterType::class, [
                'query_path' => 'address.town',
            ]);

        $builder->setDefaultSortingData(SortingData::fromArray([
            'lastUsedAt' => 'desc',
        ]));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'title' => 'contacts.fields.delivery_addresses',
            'translation_domain' => 'contacts',
        ]);
    }
}
