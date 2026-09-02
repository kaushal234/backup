<?php

declare(strict_types=1);

namespace LegacyBundle\Controller;

use ApiBundle\Client;
use ApiBundle\Iri\Iri;
use AppBundle\Controller\Purchasing\VendorWarrantyClaimController;
use LegacyBundle\Http\LegacyResourceNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PurchasingController extends AbstractController
{
    private readonly Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    #[Route(path: '/manufacturing/pur/dev.php', name: 'legacy_manufacturing_pur', methods: 'GET|POST', defaults: ['alvest_module' => 'PURR'])]
    public function router(Request $request): RedirectResponse
    {
        $m = $request->query->all()['m'] ?? [];
        $id = $request->query->get('id');

        switch ($m[0] ?? null) {
            case 'po':
                $request->attributes->set('alvest_module', 'PO');
                break;
            case 'isr':
                $request->attributes->set('alvest_module', 'ISR');
                break;
            case 'vwc':
                if (empty($m[1])) {
                    return $this->redirectToRoute('vendor_warranty_claim_home');
                }

                switch ($m[1]) {
                    case 'transfer':
                    case 'forms':
                    case 'reports':
                        return $this->redirectToRoute('vendor_warranty_claim_report');
                    case 'lists':
                        return $this->redirectToRoute('vendor_warranty_claim_home');
                    case 'email':
                    case 'view':
                        if ('scars' === ($m[2] ?? null)) {
                            break;
                        }

                        try {
                            $vendorWarrantyClaim = $this->client->find(VendorWarrantyClaimController::VENDOR_WARRANTY_CLAIM_URL, $id);
                        } catch (\RangeException $e) {
                            throw new LegacyResourceNotFoundException();
                        }

                        if (empty($m[2])) {
                            return $this->redirectToRoute('vendor_warranty_claim_show', ['id' => Iri::id($vendorWarrantyClaim)]);
                        }
                        switch ($m[2]) {
                            case 'parts':
                                return $this->redirectToRoute('vendor_warranty_claim_admin_parts', ['id' => Iri::id($vendorWarrantyClaim)]);
                            case 'edit':
                                return $this->redirectToRoute('vendor_warranty_claim_edit', ['id' => Iri::id($vendorWarrantyClaim)]);
                            case 'followers':
                            case 'links':
                            case 'tasks':
                            case 'log':
                            case 'shipping':
                            case 'finance':
                            case 'statusSelect':
                            case 'reopen':
                            case 'REWORK':
                            case 'SCRAP':
                            case 'RETURN_FOR_CREDIT':
                            case 'REPLACE':
                            case 'VENDOR_TO_RESPOND':
                            case 'REVIEW_VENDOR_RESPONSE':
                            case 'PENDING':
                            case 'CREATE_PO':
                            case 'SHIP_TO_VENDOR':
                            case 'ISSUE_CREDIT_NOTE':
                            case 'REC_FROM_VENDOR':
                            case 'ISSUE_DEBIT_NOTE':
                            case 'VALIDATE_SCAR':
                            case 'QA ANALYSIS':
                            case 'CLOSED_RESOLVED':
                            case 'CLOSED_LOW_VALUE':
                            case 'CLOSED_VENDOR_REJECTED':
                            case 'CLOSED_NOT_VENDOR_ISSUE':
                            case 'CLOSED':
                                return $this->redirectToRoute('vendor_warranty_claim_show', ['id' => Iri::id($vendorWarrantyClaim)]);
                        }
                }
                break;
            case 'rfq':
                $request->attributes->set('alvest_module', 'RFQ');
                break;
            case 'crab':
                $request->attributes->set('alvest_module', 'CRAB');
                break;
            case 'vendors':
                return $this->redirectToRoute('supplier_rankings_home');
            case 'xref':
                return $this->redirectToRoute('xref_home');
        }

        throw $this->createNotFoundException();
    }

    #[Route(path: '/manufacturing/pur/vwc/vwc_admin.php', name: 'legacy_manufacturing_vwc_admin ', methods: 'GET|POST', defaults: ['alvest_module' => 'PURR'])]
    public function routerAdmin(): RedirectResponse
    {
        return $this->redirectToRoute('vendor_warranty_claim_home');
    }
}
