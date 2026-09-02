<?php

declare(strict_types=1);

namespace LegacyBundle\Controller;

use ApiBundle\Client;
use ApiBundle\Iri\Iri;
use LegacyBundle\Http\LegacyResourceNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SalesController extends AbstractController
{
    private readonly Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    #[Route(path: '/sales_service/sales.php', name: 'legacy_sales', methods: 'GET|POST')]
    public function router(Request $request): RedirectResponse
    {
        $m = $request->query->all()['m'] ?? [];
        $id = $request->query->get('id');

        if (!isset($m[0])) {
            throw $this->createNotFoundException();
        }

        switch ($m[0]) {
            case 'customers':
                if (empty($m[1])) {
                    return $this->redirectToRoute('sales_customers_home');
                }

                switch ($m[1]) {
                    case 'approval':
                        if (!isset($m[2]) || 'new' === $m[2]) {
                            return $this->redirectToRoute('sales_customers_add');
                        }
                        break;
                    case 'reports':
                    case 'byType':
                    case 'cleanup':
                    case 'list':
                    case 'last_seq':
                    case 'forms':
                        return $this->redirectToRoute('sales_customers_home');
                    case 'view':
                        try {
                            $customer = $this->client->findOneBy('sales/customers', ['legacyId' => $id]);
                        } catch (\RangeException $e) {
                            throw new LegacyResourceNotFoundException();
                        }

                        if (empty($m[2])) {
                            return $this->redirectToRoute('sales_customers_show', ['id' => Iri::id($customer)]);
                        }
                        switch ($m[2]) {
                            case 'contact':
                                return $this->redirectToRoute('sales_xu_for_customer', ['id' => Iri::id($customer)]);
                            case 'edit':
                                return $this->redirectToRoute('sales_customers_edit', ['id' => Iri::id($customer)]);
                            case 'editcrt':
                                return $this->redirectToRoute('customer_relationship_team_home');
                            case 'contacts':
                            case 'hierarchy':
                            case 'crt':
                            case 'log':
                                return $this->redirectToRoute('sales_customers_show', ['id' => Iri::id($customer)]);
                            case 'delete':
                                return $this->redirectToRoute('sales_customers_delete', ['id' => Iri::id($customer)]);
                            case 'inv':
                                return $this->redirectToRoute('sales_customers_crt_linked', ['id' => Iri::id($customer), 'link' => 'invoices']);
                            case 'so':
                                return $this->redirectToRoute('sales_customers_crt_linked', ['id' => Iri::id($customer), 'link' => 'sales_orders']);
                            case 'ps':
                                return $this->redirectToRoute('sales_customers_crt_linked', ['id' => Iri::id($customer), 'link' => 'packing_slips']);
                            case 'email':
                                if (empty($m[3])) {
                                    return $this->redirectToRoute('sales_customers_crt_linked', ['id' => Iri::id($customer), 'link' => 'email']);
                                }
                                break;
                            case 'zip':
                                if (empty($m[3])) {
                                    return $this->redirectToRoute('sales_customers_crt_linked', ['id' => Iri::id($customer), 'link' => 'zip']);
                                }
                        }
                }
                break;
            case 'crt':
                if (empty($m[1])) {
                    return $this->redirectToRoute('customer_relationship_team_home');
                }

                switch ($m[1]) {
                    case 'forms':
                        switch ($m[2]) {
                            case 'new':
                            case 'new2':
                                return $this->redirectToRoute('customer_relationship_team_add');
                            case 'search':
                            case 'byNum':
                                return $this->redirectToRoute('customer_relationship_team_home');
                        }
                        break;
                    case 'lists':
                    case 'reports':
                        return $this->redirectToRoute('customer_relationship_team_home');
                    case 'view':
                        try {
                            $crt = $this->client->findOneBy('sales/customer_relationship_teams', ['legacyId' => $id]);
                        } catch (\RangeException $e) {
                            throw new LegacyResourceNotFoundException();
                        }

                        if (empty($m[2])) {
                            return $this->redirectToRoute('customer_relationship_team_show', ['id' => Iri::id($crt)]);
                        }
                        switch ($m[2]) {
                            case 'edit':
                            case 'task':
                            case 'edit2':
                                return $this->redirectToRoute('customer_relationship_team_edit', ['id' => Iri::id($crt)]);
                            case 'duplicate':
                                return $this->redirectToRoute('sales_crt_duplicate', ['id' => Iri::id($crt)]);
                            case 'log':
                                return $this->redirectToRoute('customer_relationship_team_show', ['id' => Iri::id($crt)]);
                            case 'delete':
                                return $this->redirectToRoute('customer_relationship_team_delete', ['id' => Iri::id($crt)]);
                        }
                }
                break;
            case 'extranet':
                if (empty($m[1])) {
                    return $this->redirectToRoute('sales_contact_home');
                }

                switch ($m[1]) {
                    case 'byNumber':
                    case 'listing':
                    case 'reports':
                        return $this->redirectToRoute('sales_contact_home');
                    case 'view':
                        try {
                            $extranetUser = $this->client->findOneBy('sales/extranet_users', ['legacyId' => $id]);
                        } catch (\RangeException $exception) {
                            throw new LegacyResourceNotFoundException();
                        }

                        if (empty($m[2])) {
                            return $this->redirectToRoute('sales_contact_show', ['id' => $extranetUser['id']]);
                        }
                        switch ($m[2]) {
                            case 'edit':
                                return $this->redirectToRoute('sales_contact_edit', ['id' => $extranetUser['id']]);
                            case 'roles':
                                if (empty($m[3])) {
                                    return $this->redirectToRoute('sales_contact_show_crt_roles', ['id' => $extranetUser['id']]);
                                }
                                if ('add' === $m[3]) {
                                    return $this->redirectToRoute('sales_contact_add_crt_roles', ['id' => $extranetUser['id']]);
                                }

                                return $this->redirectToRoute('sales_contact_show', ['id' => $extranetUser['id']]);
                            case 'email':
                                return $this->redirectToRoute('sales_contact_email', ['id' => $extranetUser['id']]);
                            case 'log':
                                return $this->redirectToRoute('sales_contact_show', ['id' => $extranetUser['id']]);
                        }
                }
                break;
            case 'catalogue':
                if (empty($m[1])) {
                    return $this->redirectToRoute('sales_catalogue_type_home');
                }

                switch ($m[1]) {
                    case 'cat':
                        try {
                            $type = $this->client->findOneBy('sales/product_types', ['legacyId' => $id]);
                        } catch (\RangeException $e) {
                            throw new LegacyResourceNotFoundException();
                        }

                        return $this->redirectToRoute('sales_catalogue_type_show', ['id' => Iri::id($type)]);
                    case 'getFile':
                    case 'model':
                        try {
                            $family = $this->client->findOneBy('sales/product_families', ['legacyId' => $id]);
                        } catch (\RangeException $e) {
                            throw new LegacyResourceNotFoundException();
                        }
                        if (empty($m[2])) {
                            return $this->redirectToRoute('sales_catalogue_family_show', ['id' => $family['id']]);
                        }

                        switch ($m[2]) {
                            case 'files':
                            case 'history':
                                return $this->redirectToRoute('sales_catalogue_family_show', ['id' => Iri::id($family)]);
                        }
                }
                break;
            case 'cor':
                if (empty($m[1])) {
                    return $this->redirectToRoute('sales_competitors_home');
                }

                switch ($m[1]) {
                    case 'byType':
                        try {
                            $productType = $this->client->findOneBy('sales/product_types', ['englishName' => $request->query->get('type')]);
                        } catch (\RangeException $e) {
                            throw new LegacyResourceNotFoundException();
                        }

                        return $this->redirectToRoute('sales_competitors_home', ['productTypes' => [$productType['id']]]);
                    case 'byNumber':
                    case 'search':
                        return $this->redirectToRoute('sales_competitors_home');
                    case 'view':
                        if (isset($m[2]) && \in_array($m[2], ['links', 'mim', 'fcr', 'cpr'], true)) {
                            break;
                        }
                        try {
                            $competitor = $this->client->findOneBy('sales/competitors', ['legacyId' => $id]);
                        } catch (\RangeException $e) {
                            throw new LegacyResourceNotFoundException();
                        }

                        if (isset($m[2]) && 'files' === $m[2]) {
                            return $this->redirectToRoute('sales_competitors_files', ['id' => $competitor['id']]);
                        }

                        return $this->redirectToRoute('sales_competitors_show', ['id' => $competitor['id']]);
                }
                break;
            case 'sfr':
                $request->attributes->set('alvest_module', 'SFR');

                if (empty($m[1])) {
                    return $this->redirectToRoute('sales_forecasts_home');
                }

                switch ($m[1]) {
                    case 'home':
                        if (empty($m[2])) {
                            return $this->redirectToRoute('sales_forecasts_home');
                        }

                        switch ($m[2]) {
                            case 'asmDashboard':
                            case 'selectASM':
                                return $this->redirectToRoute('sales_forecasts_dashboard_asm');
                        }
                        break;
                    case 'reports':
                        return $this->redirectToRoute('sales_forecasts_list');
                    case 'listing':
                        if (empty($m[2])) {
                            return $this->redirectToRoute('sales_forecasts_list');
                        }

                        switch ($m[2]) {
                            case 'mySFRByCustomer':
                            case 'byOpenStatusERPASM':
                            case 'byOpenStatusDelinquantERPASM':
                            case 'byRecentlyClosedERPASM':
                            case 'bySubordinates':
                                return $this->redirectToRoute('sales_forecasts_dashboard');
                            case 'byAllCustomerID':
                                $customerId = $request->query->get('x');
                                try {
                                    $customer = $this->client->findOneBy('sales/customers', ['legacyId' => $customerId]);
                                } catch (\RangeException $e) {
                                    throw new LegacyResourceNotFoundException();
                                }

                                return $this->redirectToRoute('sales_forecasts_list', ['buyer' => $customer['@id']]);
                            case 'edit':
                                return $this->redirectToRoute('sales_forecasts_list_quick_edit');
                            case 'search':
                                return $this->redirectToRoute('sales_forecasts_list_gantt', ['search' => true]);
                        }
                        break;
                    case 'form':
                        if (empty($m[2])) {
                            return $this->redirectToRoute('sales_forecasts_list');
                        }

                        switch ($m[2]) {
                            case 'byNum':
                                return $this->redirectToRoute('sales_forecasts_list');
                            case 'newSFR':
                                return $this->redirectToRoute('sales_forecasts_add');
                        }

                        return $this->redirectToRoute('sales_forecasts_add');
                    case 'view':
                        try {
                            $sfr = $this->client->findOneBy('sales/sales_forecasts', ['legacyId' => $id]);
                        } catch (\RangeException $e) {
                            throw new LegacyResourceNotFoundException();
                        }
                        if (empty($m[2])) {
                            return $this->redirectToRoute('sales_forecasts_show', ['id' => $sfr['id']]);
                        }

                        switch ($m[2]) {
                            case 'tasks':
                            case 'files':
                            case 'log':
                            case 'linked':
                                return $this->redirectToRoute('sales_forecasts_show', ['id' => $sfr['id']]);
                            case 'editASM':
                            case 'editPSM':
                                return $this->redirectToRoute('sales_forecasts_edit', ['id' => $sfr['id']]);
                        }

                        return $this->redirectToRoute('sales_forecasts_add');
                }
                break;
            case 'fcr':
                if (empty($m[1])) {
                    return $this->redirectToRoute('forecast_closures_home');
                }

                switch ($m[1]) {
                    case 'search':
                        switch ($m[2]) {
                            case 'searchFCR':
                                return $this->redirectToRoute('forecast_closures_home');
                            case 'searchCPR':
                                return $this->redirectToRoute('competitor_pricings_home');
                        }
                        break;
                    case 'lookup':
                        return $this->redirectToRoute('forecast_closures_home');
                    case 'view':
                        try {
                            $fcr = $this->client->findOneBy('sales/forecast_closures', ['legacyId' => $id]);
                        } catch (\RangeException $e) {
                            throw new LegacyResourceNotFoundException();
                        }

                        return $this->redirectToRoute('forecast_closures_show', ['id' => $fcr['id']]);
                    case 'closeSFR':
                        $sfrId = $request->query->get('sfrid');
                        try {
                            $sfr = $this->client->findOneBy('sales/sales_forecasts', ['legacyId' => $sfrId]);
                        } catch (\RangeException $e) {
                            throw new LegacyResourceNotFoundException();
                        }
                        if (empty($m[2])) {
                            return $this->redirectToRoute('sales_forecasts_show', ['id' => $sfr['id']]);
                        }

                        if ('setCPR' === $m[2]) {
                            return $this->redirectToRoute('sales_forecasts_add_cpr', ['id' => $sfr['id']]);
                        }
                }
                break;
            case 'cpr':
                if (empty($m[1])) {
                    return $this->redirectToRoute('competitor_pricings_home');
                }

                switch ($m[1]) {
                    case 'byNum':
                    case 'search':
                        return $this->redirectToRoute('competitor_pricings_home');
                    case 'view':
                        if (empty($m[2])) {
                            try {
                                $cpr = $this->client->findOneBy('sales/competitor_pricings', ['legacyId' => $id]);
                            } catch (\RangeException $e) {
                                throw new LegacyResourceNotFoundException();
                            }

                            return $this->redirectToRoute('competitor_pricings_show', ['id' => $cpr['id']]);
                        }
                }
                break;
            case 'sor':
                if (empty($m[1])) {
                    return $this->redirectToRoute('sales_orders_home');
                }

                switch ($m[1]) {
                    case 'form':
                        if (!isset($m[2])) {
                            break 2;
                        }

                        switch ($m[2]) {
                            case 'byNum':
                                return $this->redirectToRoute('sales_orders_home');
                            case 'transfer':
                                return $this->redirectToRoute('sales_orders_transfer');
                            case 'newSOR1':
                            case 'newSOR':
                                return $this->redirectToRoute('sales_orders_add');
                        }

                        break 2;
                    case 'listing':
                        if (isset($m[2]) && 'search' === $m[2]) {
                            return $this->redirectToRoute('sales_orders_home');
                        }
                        break 2;
                    case 'view':
                        if (!$id) {
                            throw $this->createNotFoundException();
                        }
                        try {
                            $sor = $this->client->findOneBy('sales/orders', ['legacyId' => $id]);
                        } catch (\RangeException $e) {
                            throw new LegacyResourceNotFoundException();
                        }

                        if (empty($m[2]) || \in_array($m[2], ['xmlSource', 'changeStatus'], true)) {
                            return $this->redirectToRoute('sales_orders_show', ['id' => $sor['id']]);
                        }
                        switch ($m[2]) {
                            case 'log':
                                return $this->redirectToRoute('sales_orders_logs', ['id' => $sor['id']]);
                            case 'edit':
                            case 'edit2':
                                return $this->redirectToRoute('sales_orders_edit', ['id' => $sor['id']]);
                            case 'dup':
                                return $this->redirectToRoute('sales_orders_duplicate', ['id' => $sor['id']]);
                            case 'tasks':
                                return $this->redirectToRoute('sales_orders_tasks', ['id' => $sor['id']]);
                            case 'files':
                                return $this->redirectToRoute('sales_orders_files', ['id' => $sor['id']]);
                        }

                        break 2;
                }
                break;
            case 'mim':
                if (empty($m[1])) {
                    return $this->redirectToRoute('market_intelligence_home');
                }

                switch ($m[1]) {
                    case 'byNum':
                    case 'list':
                    case 'reports':
                    case 'matrix':
                        return $this->redirectToRoute('market_intelligence_home');
                    case 'add':
                        return $this->redirectToRoute('market_intelligence_add');
                    case 'not':
                        if (empty($m[2])) {
                            return $this->redirectToRoute('market_intelligence_subscription_home');
                        }
                        switch ($m[2]) {
                            case 'delConf':
                            case 'del':
                                return $this->redirectToRoute('market_intelligence_subscription_home');
                            case 'add':
                                return $this->redirectToRoute('market_intelligence_subscription_add');
                        }
                        break 2;
                    case 'view':
                        if (!$id) {
                            throw $this->createNotFoundException();
                        }
                        try {
                            $mim = $this->client->findOneBy('sales/market_intelligences', ['legacyId' => $id]);
                        } catch (\RangeException $e) {
                            throw new LegacyResourceNotFoundException();
                        }

                        if (empty($m[2])) {
                            return $this->redirectToRoute('market_intelligence_show', ['id' => $mim['id']]);
                        }
                        switch ($m[2]) {
                            case 'files':
                                return $this->redirectToRoute('market_intelligence_files', ['id' => $mim['id']]);
                            case 'edit':
                                return $this->redirectToRoute('market_intelligence_edit', ['id' => $mim['id']]);
                            case 'links':
                            case 'log':
                            case 'tasks':
                            case 'AddComment':
                                return $this->redirectToRoute('market_intelligence_show', ['id' => $mim['id']]);
                        }
                        break 2;
                }
                break;
            case 'sol':
                $request->attributes->set('alvest_module', 'SOL');
                break;
            case 'equotes':
                $request->attributes->set('alvest_module', 'EQUO');
                break;
            case 'odp':
                $request->attributes->set('alvest_module', 'ODP');
                break;
            case 'inventory':
                $request->attributes->set('alvest_module', 'INV');
                break;
            case 'esr':
                if (empty($m[1])) {
                    return $this->redirectToRoute('equipment_shipping_record_home');
                }
                switch ($m[1]) {
                    case 'listing':
                        return $this->redirectToRoute('equipment_shipping_record_home');
                    case 'form':
                        switch ($m[2]) {
                            case 'add':
                                return $this->redirectToRoute('equipment_shipping_record_add');
                            default:
                                return $this->redirectToRoute('equipment_shipping_record_home');
                        }
                        // no break
                    case 'view':
                        if (!$id) {
                            throw $this->createNotFoundException();
                        }
                        try {
                            $esr = $this->client->findOneBy('sales/equipment_shipping_records', ['legacyId' => $id]);
                        } catch (\RangeException $e) {
                            throw new LegacyResourceNotFoundException();
                        }

                        if (empty($m[2])) {
                            return $this->redirectToRoute('equipment_shipping_record_show', ['id' => $esr['id']]);
                        }
                        switch ($m[2]) {
                            case 'edit':
                                return $this->redirectToRoute('equipment_shipping_record_edit', ['id' => $esr['id']]);
                            default:
                                return $this->redirectToRoute('equipment_shipping_record_show', ['id' => $esr['id']]);
                        }
                }
                break;
            case 'gse_aircraft_data':
                if (empty($m[1])) {
                    return $this->redirectToRoute('aircraft_compatibility_home');
                }
                switch ($m[1]) {
                    case 'dl':
                    default:
                        return $this->redirectToRoute('aircraft_compatibility_home');
                }
                // no break
            case 'ccr':
                $request->attributes->set('alvest_module', 'CCR');
                break;
            case 'sac':
                $request->attributes->set('alvest_module', 'CMS');
                break;
            case 'activity2':
                $request->attributes->set('alvest_module', 'ACT');
                break;
        }
        throw $this->createNotFoundException();
    }

    #[Route(path: '/sales_service/customers/customers_admin.php', name: 'legacy_customers_admin', methods: 'GET|POST')]
    public function routerAdminCustomer(): RedirectResponse
    {
        return $this->redirectToRoute('sales_customers_home');
    }

    #[Route(path: '/sales_service/cor/cor_admin.php', name: 'legacy_customers_admin', methods: 'GET|POST')]
    public function routerAdminCOR(): RedirectResponse
    {
        return $this->redirectToRoute('sales_competitors_home');
    }

    #[Route(path: '/sales_service/extranet/extranet_admin.php', name: 'legacy_extranet_user_admin', methods: 'GET|POST')]
    public function routerAdminExtranetUser(): RedirectResponse
    {
        return $this->redirectToRoute('sales_contact_home');
    }

    #[Route(path: '/sales_service/catalogue/products_admin.php', name: 'legacy_catalogue_admin', methods: 'GET|POST')]
    public function routerAdminCatalogue(): RedirectResponse
    {
        return $this->redirectToRoute('sales_catalogue_type_home');
    }

    #[Route(path: '/sales_service/catalogue/products_zh_admin.php', name: 'legacy_catalogue_zh_admin', methods: 'GET|POST')]
    public function routerChineseAdminCatalogue(): RedirectResponse
    {
        return $this->redirectToRoute('sales_catalogue_type_home');
    }

    #[Route(path: '/sales_service/catalogue/products_ja_admin.php', name: 'legacy_catalogue_ja_admin', methods: 'GET|POST')]
    public function routerJapaneseAdminCatalogue(): RedirectResponse
    {
        return $this->redirectToRoute('sales_catalogue_type_home');
    }

    #[Route(path: '/sales_service/cpr/cpr_admin.php', name: 'legacy_cpr_admin', methods: 'GET|POST')]
    public function routerCPRAdmin(): RedirectResponse
    {
        return $this->redirectToRoute('competitor_pricings_home');
    }

    #[Route(path: '/sales_service/fcr/fcr_admin.php', name: 'legacy_fcr_admin', methods: 'GET|POST')]
    public function routerFCRAdmin(): RedirectResponse
    {
        return $this->redirectToRoute('forecast_closures_home');
    }

    #[Route(path: '/sales_service/sfr/sfr_admin.php', name: 'legacy_sfr_admin', methods: 'GET|POST')]
    public function routerSFRAdmin(): RedirectResponse
    {
        return $this->redirectToRoute('sales_forecasts_home');
    }

    #[Route(path: '/sales_service/mim/mim_admin.php', name: 'legacy_mim_admin', methods: 'GET|POST')]
    public function routerMIMAdmin(): RedirectResponse
    {
        return $this->redirectToRoute('market_intelligence_home');
    }

    #[Route(path: '/sales_service/mim/mim_not_admin.php', name: 'legacy_mim_not_admin', methods: 'GET|POST')]
    public function routerMIMNOTAdmin(): RedirectResponse
    {
        return $this->redirectToRoute('market_intelligence_subscription_home');
    }

    #[Route(path: '/sales_service/esr/esr_admin.php', name: 'legacy_esr_admin', methods: 'GET|POST')]
    public function esrAdmin()
    {
        return $this->redirectToRoute('equipment_shipping_record_home');
    }
}
