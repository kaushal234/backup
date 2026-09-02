<?php

declare(strict_types=1);

namespace LegacyBundle\Controller;

use ApiBundle\Client;
use LegacyBundle\Http\LegacyResourceNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class FinanceController extends AbstractController
{
    private readonly Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    #[Route(path: '/finance/finance.php', name: 'legacy_finance', methods: 'GET|POST', defaults: ['alvest_module' => 'FINR'])]
    public function router(Request $request): RedirectResponse
    {
        $m = $request->query->all()['m'] ?? [];
        $id = $request->query->get('id');

        switch ($m[0] ?? null) {
            case 'forex':
                if (empty($m[1])) {
                    return $this->redirectToRoute('forex_home');
                }

                if ($m === ['forex', 'form', 'addRates']) {
                    return $this->redirectToRoute('forex_add');
                }

                if ($m === ['forex', 'reports', 'csv']) {
                    return $this->redirectToRoute('forex_search', ['download' => '']);
                }

                break;
            case 'mfg_margins':
                if (empty($m[1])) {
                    return $this->redirectToRoute('manufacturing_margin_home');
                }
                switch ($m[1]) {
                    case 'byNum':
                        return $this->redirectToRoute('manufacturing_margin_home');
                    case 'add':
                        return $this->redirectToRoute('manufacturing_margin_add');
                    case 'addmultiple':
                        return $this->redirectToRoute('manufacturing_margin_upload');
                    case 'reports':
                        return $this->redirectToRoute('manufacturing_margin_reports');
                    case 'view':
                        try {
                            $manufacturingMargin = $this->client->findOneBy('finance/manufacturing_margins', ['legacyId' => $id]);
                        } catch (\RangeException $exception) {
                            throw new LegacyResourceNotFoundException();
                        }

                        if (empty($m[2])) {
                            return $this->redirectToRoute('manufacturing_margin_show', ['id' => $manufacturingMargin['id']]);
                        }
                        switch ($m[2]) {
                            case 'edit':
                                return $this->redirectToRoute('manufacturing_margin_edit', ['id' => $manufacturingMargin['id']]);
                            case 'log':
                                return $this->redirectToRoute('manufacturing_margin_show', ['id' => $manufacturingMargin['id']]);
                        }
                }
                break;
            case 'po':
                $request->attributes->set('alvest_module', 'PO');
                break;
        }

        throw $this->createNotFoundException();
    }

    #[Route(path: '/finance/forex/forex_admin.php', name: 'legacy_forex_admin', methods: 'GET|POST')]
    public function routerForexAdmin(): RedirectResponse
    {
        return $this->redirectToRoute('forex_search');
    }

    #[Route(path: '/finance/sor_tran/sor_tran_admin.php', name: 'legacy_tran_admin', methods: 'GET|POST')]
    public function routerTranAdmin(Request $request): never
    {
        $request->attributes->set('alvest_module', 'TRAN');

        throw $this->createNotFoundException();
    }

    #[Route(path: '/finance/mfg_margins/mfg_margins_admin.php', name: 'legacy_manufacturing_margin_admin', methods: 'GET|POST')]
    public function routerManufacturingAdminAdmin(): RedirectResponse
    {
        return $this->redirectToRoute('manufacturing_margin_home');
    }
}
