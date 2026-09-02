<?php

declare(strict_types=1);

namespace AppBundle\Controller\Purchasing\VendorWarrantyClaim;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Controller\Purchasing\VendorWarrantyClaimController;
use AppBundle\Form\Type\Purchasing\VendorWarrantyClaim\VendorWarrantyClaimEditPartialType;
use AppBundle\Manager\Purchasing\VendorWarrantyClaim\IriResolver;
use AppBundle\Manager\Purchasing\VendorWarrantyClaim\StatusForm;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/purchasing/vendor-warranty-claims', defaults: ['alvest_module' => 'VWC', 'moduleDomain' => 'vendor_warranty_claim'])]
class ShowFinanceShippingController extends AbstractController
{
    public function __construct(
        private readonly Client $client,
        private readonly StatusForm $statusForm,
        private readonly IriResolver $iriResolver,
        private readonly TranslatorInterface $translator,
        private readonly ViolationMapper $violationMapper,
    ) {
    }

    #[Route(path: '/{id}/show/finance-shipping', name: 'vendor_warranty_claim_show_finance_shipping', methods: ['GET', 'POST'])]
    #[Template('purchasing/vendor_warranty_claim/show_finance_shipping.html.twig')]
    public function __invoke(#[ApiValueResolverAttribute(parameters: ['resource' => VendorWarrantyClaimController::VENDOR_WARRANTY_CLAIM_URL, 'allowedTypes' => ['wcVendorWarrantyClaim', 'ncrVendorWarrantyClaim']])] ApiData $vendorWarrantyClaim, Request $request): RedirectResponse|array
    {
        $authorizedFields = $this->client->get('/fields', [
            'query' => ['iri' => $vendorWarrantyClaim['@id'], 'method' => 'PUT'],
        ]);

        $form = $this->createForm(VendorWarrantyClaimEditPartialType::class, $vendorWarrantyClaim,
            [
                'authorized_fields' => $authorizedFields,
                'action' => $this->generateUrl('vendor_warranty_claim_show_finance_shipping', [
                    'id' => $vendorWarrantyClaim['id'],
                ]),
                'method' => 'POST',
            ]
        );

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $payload = [
                '@id' => $data['@id'],
                'supplierReturnMerchandiseAuthorization' => $data['supplierReturnMerchandiseAuthorization'],
                'supplierShippingInstruction' => $data['supplierShippingInstruction'],
                'trackingNumber' => $data['trackingNumber'],
                'requestedCreditAmount' => $data['requestedCreditAmount'],
                'supplierCreditNote' => $data['supplierCreditNote'],
                'supplierCreditAmount' => $data['supplierCreditAmount'],
                'actualCreditAmount' => $data['actualCreditAmount'],
                'currency' => $data['currency'],
            ];
            $iri = $this->iriResolver->resolve($vendorWarrantyClaim);

            try {
                $this->client->save($iri, $payload);
                $this->addFlash('success', $this->translator->trans('vendor_warranty_claim.success.edition', [], 'vendor_warranty_claim'));

                return $this->redirectToRoute('vendor_warranty_claim_show_finance_shipping', [
                    'id' => $vendorWarrantyClaim['id'],
                ]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
            'vendorWarrantyClaim' => $vendorWarrantyClaim,
            'status_form' => $this->statusForm->create($vendorWarrantyClaim)->createView(),
        ];
    }
}
