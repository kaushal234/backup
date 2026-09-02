<?php

declare(strict_types=1);

namespace AppBundle\Controller\Sales;

use ApiBundle\Hydra\HydraCollection;
use AppBundle\Configuration\ApiValueResolverAttribute;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/sales/customers', defaults: ['alvest_module' => 'ECUST'])]
class CustomerWatchListController
{
    #[Route(path: '/watch-list', name: 'sales_customers_watch_list_quick_edit')]
    #[Template('sales/customers/watch_list.html.twig')]
    #[IsGranted('FEATURE_CUSTOMER_WATCH_LIST')]
    public function quickEdit(#[ApiValueResolverAttribute(parameters: ['resource' => CustomerController::RESOURCE_URL, 'filters' => ['watchList' => '1', 'normalization_groups' => 'customer:watch']])] HydraCollection $customers)
    {
        return [
            'initialState' => ['customers' => ['customers' => $customers->getSimpleArrayCopy()]],
        ];
    }
}
