<?php

declare(strict_types=1);

namespace LegacyBundle\Controller;

use ApiBundle\Client;
use ApiBundle\Iri\Iri;
use AppBundle\Controller\Support\EquipmentSerialsController;
use AppBundle\Controller\Support\ManualController;
use LegacyBundle\Http\LegacyResourceNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProductSupportController extends AbstractController
{
    private readonly Client $client;

    private readonly TranslatorInterface $translator;

    public function __construct(Client $client, TranslatorInterface $translator)
    {
        $this->client = $client;
        $this->translator = $translator;
    }

    #[Route(path: '/product_support/index.ps.php', name: 'legacy_product_support', methods: 'GET|POST')]
    public function productSupport(Request $request): RedirectResponse
    {
        $m = $request->query->all()['m'] ?? [];

        if (!empty($m)) {
            switch ($m[0]) {
                case 'equipment':
                    $request->attributes->set('alvest_module', 'ER');

                    if (['view', 'serials'] === [$m[1] ?? null, $m[2] ?? null] && $request->query->has('id')) {
                        try {
                            $equipmentRecord = $this->client->findOneBy(EquipmentSerialsController::EQUIPMENT_RECORD_URL, ['legacyId' => $request->query->get('id')]);

                            if (isset($m[3])) {
                                return $this->redirectToRoute('equipment_serials_edit', ['id' => $equipmentRecord->getIriId()]);
                            }

                            return $this->redirectToRoute('equipment_serials_show', ['id' => $equipmentRecord->getIriId()]);
                        } catch (\RangeException $rangeException) {
                            $this->addFlash('error', $this->translator->trans('support.equipment_record.messages.not_imported', [], 'support'));

                            return $this->redirectToRoute('legacy_product_support', [
                                'm' => ['equipment', 'view'],
                                'id' => $request->query->get('id'),
                            ]);
                        }
                    }

                    if (['view', 'publications'] === [$m[1] ?? null, $m[2] ?? null] && $request->query->has('id')) {
                        if (['cdrom'] === [$m[3] ?? null]) {
                            throw $this->createNotFoundException();
                        }
                        try {
                            $equipmentRecord = $this->client->findOneBy(EquipmentSerialsController::EQUIPMENT_RECORD_URL, ['legacyId' => $request->query->get('id')]);

                            return $this->redirectToRoute('equipment_record_manuals_list', ['id' => $equipmentRecord->getIriId()]);
                        } catch (\RangeException $rangeException) {
                            $this->addFlash('error', $this->translator->trans('support.equipment_record.messages.not_imported', [], 'support'));

                            return $this->redirectToRoute('legacy_product_support', [
                                'm' => ['equipment', 'view'],
                                'id' => $request->query->get('id'),
                            ]);
                        }
                    }
                    if (['view', 'esrl'] === [$m[1] ?? null, $m[2] ?? null]) {
                        return $this->redirectToRoute('equipment_shipping_record_home');
                    }
                    break;
                case 'odp':
                    if (empty($m[1])) {
                        return $this->redirectToRoute('on_time_delivery_planning_home');
                    }
                    switch ($m[1]) {
                        case 'listing':
                        case 'reports':
                        case 'charts':
                            return $this->redirectToRoute('on_time_delivery_planning_show');
                        case 'matrix':
                            return $this->redirectToRoute('on_time_delivery_planning_home');
                    }
                    break;
                case 'pdc':
                    $request->attributes->set('alvest_module', 'PDC');
                    break;
                case 'sbs':
                case 'sb':
                    $request->attributes->set('alvest_module', 'SB3');
                    break;
                case 'wc':
                    $request->attributes->set('alvest_module', 'WC');

                    if (['view', 'vwc'] === [$m[1] ?? null, $m[2] ?? null] && $request->query->has('id')) {
                        return $this->redirectToRoute('vendor_warranty_claim_home', ['filter_vendor_warranty_claims[warrantyClaimId][value]' => $request->query->get('id')]);
                    }
                    break;
                case 'publications':
                    $request->attributes->set('alvest_module', 'PUBS');
                    if ('manuals' === ($m[1] ?? false) && 'search' === ($m[2] ?? false) && 'byBrandModel' === ($m[3] ?? false)) {
                        break;
                    }
                    if (['manuals', 'view'] === [$m[1] ?? null, $m[2] ?? null] && $request->query->has('id')) {
                        try {
                            $manual = $this->client->findOneBy(ManualController::RESOURCE_URL, ['legacyId' => $request->query->get('id')]);

                            return $this->redirectToRoute('manuals_show', ['id' => $manual->getIriId()]);
                        } catch (\RangeException $rangeException) {
                            throw new LegacyResourceNotFoundException();
                        }
                    }
                    if ('manuals' === ($m[1] ?? false) && 'form' === ($m[2] ?? false)) {
                        break;
                    }

                    return $this->redirectToRoute('manuals_home');
            }
        }

        throw $this->createNotFoundException();
    }

    #[Route(path: '/product_support/publications/manuals_admin.php', name: 'legacy_manual_admin', methods: 'GET|POST')]
    public function manualAdmin(Request $request): RedirectResponse
    {
        $request->attributes->set('alvest_module', 'PUBS');

        return $this->redirectToRoute('manuals_home');
    }

    #[Route(path: '/product_support/publications/documents_admin.php', name: 'legacy_manual_document_admin', methods: 'GET|POST')]
    public function manualDocumentAdmin(Request $request): RedirectResponse
    {
        $request->attributes->set('alvest_module', 'PUBS');

        return $this->redirectToRoute('manuals_home');
    }

    #[Route(path: '/product_support/publications/zipped.php', name: 'legacy_manual_download_cd', methods: 'GET|POST')]
    public function manualDownloadCD(Request $request): never
    {
        $request->attributes->set('alvest_module', 'PUBS');

        throw $this->createNotFoundException();
    }

    #[Route(path: '/product_support/admin/airport_codes_admin.php', name: 'legacy_airport_admin', methods: 'GET|POST')]
    public function router(Request $request): RedirectResponse
    {
        if ($request->request->has('m')) {
            throw $this->createNotFoundException();
        }

        switch ($request->query->get('mode')) {
            case null:
                return $this->redirectToRoute('support_iata_code_list');
            case 'record_view':
                try {
                    $iataCode = $this->client->findOneBy('iata_codes', ['legacyId' => $request->query->get('id')]);
                } catch (\RangeException $e) {
                    throw new LegacyResourceNotFoundException();
                }

                return $this->redirectToRoute('support_iata_code_show', ['id' => Iri::id($iataCode)]);
            case 'form_add':
                return $this->redirectToRoute('support_iata_code_add');
            case 'form_edit':
                try {
                    $iataCode = $this->client->findOneBy('iata_codes', ['legacyId' => $request->query->get('id')]);
                } catch (\RangeException $e) {
                    throw new LegacyResourceNotFoundException();
                }

                return $this->redirectToRoute('support_iata_code_edit', ['id' => Iri::id($iataCode)]);
            case 'duplicate':
            case 'del':
        }

        throw $this->createNotFoundException();
    }

    #[Route(path: '/product_support/admin/models_admin.php', name: 'legacy_models_admin', methods: 'GET|POST')]
    public function routerModelsAdmin(): RedirectResponse
    {
        return $this->redirectToRoute('sales_catalogue_product_home', ['visibility' => 'show']);
    }

    #[Route(path: '/pickup.php', name: 'legacy_pickup_manual', methods: 'GET|POST')]
    public function manualPickup(Request $request): RedirectResponse
    {
        $request->attributes->set('alvest_module', 'PUBS');

        return $this->redirectToRoute('manuals_home');
    }

    #[Route(path: '/product_support/nto/nto_admin.php', name: 'legacy_nto_admin', methods: 'GET|POST')]
    public function routerNTOAdmin(): RedirectResponse
    {
        return $this->redirectToRoute('aircraft_compatibility_home');
    }
}
