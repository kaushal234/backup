<?php

declare(strict_types=1);

namespace LegacyBundle\Controller;

use ApiBundle\Client;
use LegacyBundle\Http\LegacyResourceNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MobileController extends AbstractController
{
    private readonly Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Migration HACK for mobile page.
     */
    #[Route(path: '/mobile/', name: 'legacy_mobile', methods: 'GET|POST')]
    #[Route(path: '/mobile/index.php', name: 'legacy_mobile_index', methods: 'GET|POST')]
    public function router(Request $request): RedirectResponse
    {
        switch ($request->query->get('page')) {
            case 'sfr':
                switch ($request->query->get('action')) {
                    case 'create':
                        return $this->redirectToRoute('sales_forecasts_add');
                    case 'view':
                        $id = $request->request->get('sfr_id');
                        try {
                            $sfr = $this->client->findOneBy('sales/sales_forecasts', ['legacyId' => $id]);
                        } catch (\RangeException $e) {
                            throw new LegacyResourceNotFoundException();
                        }

                        return $this->redirectToRoute('sales_forecasts_show', ['id' => $sfr['id']]);
                    case 'update':
                        $id = $request->request->get('sfr_id');
                        try {
                            $sfr = $this->client->findOneBy('sales/sales_forecasts', ['legacyId' => $id]);
                        } catch (\RangeException $e) {
                            throw new LegacyResourceNotFoundException();
                        }

                        return $this->redirectToRoute('sales_forecasts_edit', ['id' => $sfr['id']]);
                    case 'listing':
                        $customerId = $request->request->get('sfr_by_customer');
                        try {
                            $customer = $this->client->findOneBy('sales/customers', ['legacyId' => $customerId]);
                        } catch (\RangeException $e) {
                            throw new LegacyResourceNotFoundException();
                        }

                        return $this->redirectToRoute('sales_forecasts_home', ['buyer' => $customer['@id']]);
                }
                break;
            case 'mim':
                if ('create' === $request->query->get('action')) {
                    return $this->redirectToRoute('market_intelligence_add');
                }
                break;
            case 'customers':
                if ('listing' === $request->query->get('action')) {
                    $people = $this->client->findOneBy('people', ['legacyId' => $request->query->get('legacyId')]);

                    return $this->redirectToRoute('sales_customers_home', ['asm' => $people['@id']]);
                }
                break;
            case 'odp':
                if ('listing' === $request->query->get('action')) {
                    return $this->redirectToRoute('on_time_delivery_planning_home');
                }
                break;
        }
        throw $this->createNotFoundException();
    }
}
