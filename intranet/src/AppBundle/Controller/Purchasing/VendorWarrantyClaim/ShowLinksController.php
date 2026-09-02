<?php

declare(strict_types=1);

namespace AppBundle\Controller\Purchasing\VendorWarrantyClaim;

use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Controller\Purchasing\VendorWarrantyClaimController;
use AppBundle\Manager\Purchasing\VendorWarrantyClaim\StatusForm;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/purchasing/vendor-warranty-claims', defaults: ['alvest_module' => 'VWC', 'moduleDomain' => 'vendor_warranty_claim'])]
class ShowLinksController extends AbstractController
{
    public function __construct(
        private readonly StatusForm $statusForm,
    ) {
    }

    #[Route(path: '/{id}/show/links', name: 'vendor_warranty_claim_show_links', methods: ['GET'])]
    #[Template('purchasing/vendor_warranty_claim/show_links.html.twig')]
    public function __invoke(#[ApiValueResolverAttribute(parameters: ['resource' => VendorWarrantyClaimController::VENDOR_WARRANTY_CLAIM_URL, 'allowedTypes' => ['wcVendorWarrantyClaim', 'ncrVendorWarrantyClaim']])] ApiData $vendorWarrantyClaim): array
    {
        return [
            'vendorWarrantyClaim' => $vendorWarrantyClaim,
            'status_form' => $this->statusForm->create($vendorWarrantyClaim)->createView(),
        ];
    }
}
