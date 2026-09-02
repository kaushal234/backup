<?php

declare(strict_types=1);

namespace AppBundle\Controller\Purchasing\VendorWarrantyClaim;

use ApiBundle\Client;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Controller\Purchasing\VendorWarrantyClaimController;
use AppBundle\Controller\Quality\SupplierCorrectiveActionRequestController;
use AppBundle\Manager\Purchasing\VendorWarrantyClaim\StatusForm;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/purchasing/vendor-warranty-claims', defaults: ['alvest_module' => 'VWC', 'moduleDomain' => 'vendor_warranty_claim'])]
class ShowController extends AbstractController
{
    public function __construct(
        private readonly Client $client,
        private readonly StatusForm $statusForm,
    ) {
    }

    #[Route(path: '/{id}/show', name: 'vendor_warranty_claim_show', methods: ['GET'])]
    #[Template('purchasing/vendor_warranty_claim/show.html.twig')]
    public function __invoke(#[ApiValueResolverAttribute(parameters: ['resource' => VendorWarrantyClaimController::VENDOR_WARRANTY_CLAIM_URL, 'allowedTypes' => ['wcVendorWarrantyClaim', 'ncrVendorWarrantyClaim']])] ApiData $vendorWarrantyClaim): array
    {
        try {
            $supplier = $this->client->get(\sprintf(
                '%s/%s',
                SupplierCorrectiveActionRequestController::RESOURCE_URL_SUPPLIER,
                $vendorWarrantyClaim['supplierNumber']
            ));
        } catch (\Exception $e) {
            $supplier = [];
        }

        $supplier['contacts'] = array_filter($supplier['contacts'] ?? [], static function ($contact) {
            return $contact['grantedQualityCategory'];
        });

        return [
            'contacts' => $supplier['contacts'],
            'vendorWarrantyClaim' => $vendorWarrantyClaim,
            'status_form' => $this->statusForm->create($vendorWarrantyClaim)->createView(),
        ];
    }
}
