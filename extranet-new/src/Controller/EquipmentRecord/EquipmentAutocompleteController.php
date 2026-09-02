<?php

declare(strict_types=1);

namespace App\Controller\EquipmentRecord;

use App\Controller\AbstractAutocompleteController;
use App\CQRS\Query\EquipmentRecord\FindAllEquipmentRecordsQuery;
use App\Sdk\Resource\Customer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(path: '/equipments/autocomplete', name: 'autocomplete_equipments')]
final class EquipmentAutocompleteController extends AbstractAutocompleteController
{
    protected function configureOptions(OptionsResolver $resolver, Request $request): void
    {
        /** @var Customer $activeCustomer */
        $activeCustomer = $request->getSession()->get('customer');

        $resolver->setDefaults([
            'routeName' => 'autocomplete_equipments',
            'orderProperty' => 'id',
            'searchKey' => 'autocomplete',
            'queryOptions' => ['by_customer' => $activeCustomer->getIri()],
            'formatter' => static fn ($item) => \sprintf('SN : %s - Asset Number : %s', $item->serialNumber, $item->customerSerialNumber),
            'queryBus' => static function ($page, $options) {
                return new FindAllEquipmentRecordsQuery(
                    page: $page,
                    itemsPerPage: 25,
                    options: $options
                );
            },
        ]);
    }
}
