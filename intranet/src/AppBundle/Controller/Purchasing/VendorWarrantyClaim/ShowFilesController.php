<?php

declare(strict_types=1);

namespace AppBundle\Controller\Purchasing\VendorWarrantyClaim;

use ApiBundle\Form\ViolationMapper;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Controller\Purchasing\VendorWarrantyClaimController;
use AppBundle\Form\Type\SimpleFileType;
use AppBundle\Manager\FileManager;
use AppBundle\Manager\Purchasing\VendorWarrantyClaim\StatusForm;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/purchasing/vendor-warranty-claims', defaults: ['alvest_module' => 'VWC', 'moduleDomain' => 'vendor_warranty_claim'])]
class ShowFilesController extends AbstractController
{
    public function __construct(
        private readonly StatusForm $statusForm,
        private readonly TranslatorInterface $translator,
        private readonly ViolationMapper $violationMapper,
        private readonly FileManager $fileManager,
    ) {
    }

    #[Route(path: '/{id}/show/files', name: 'vendor_warranty_claim_show_files', methods: ['GET', 'POST'])]
    public function __invoke(#[ApiValueResolverAttribute(parameters: ['resource' => VendorWarrantyClaimController::VENDOR_WARRANTY_CLAIM_URL, 'allowedTypes' => ['wcVendorWarrantyClaim', 'ncrVendorWarrantyClaim']])] ApiData $vendorWarrantyClaim, Request $request): Response
    {
        if (null !== ($ncr = $vendorWarrantyClaim['nonConformity'])) {
            $ncr['files'] = array_filter($ncr['files'], static function ($file) {
                return $file['public'];
            });

            $vendorWarrantyClaim['nonConformity'] = $ncr;
        }

        $formFiles = $this->createForm(SimpleFileType::class, null,
            [
                'action' => $this->generateUrl('vendor_warranty_claim_show_files', [
                    'id' => $vendorWarrantyClaim['id'],
                ]),
                'method' => 'POST',
            ]
        );

        $formFiles->handleRequest($request);

        if ($formFiles->isSubmitted() && $formFiles->isValid()) {
            try {
                /** @var UploadedFile|null $file */
                $file = $formFiles->get('file')->getData();

                if ($file instanceof UploadedFile) {
                    $this->fileManager->uploadFile(
                        $vendorWarrantyClaim,
                        $file,
                        VendorWarrantyClaimController::VENDOR_WARRANTY_CLAIM_URL,
                        $formFiles->get('description')->getData(),
                        'files',
                        true,
                    );
                }

                $this->addFlash(
                    'success',
                    $this->translator->trans(
                        'files.upload_success',
                        [],
                        'messages'
                    )
                );

                return $this->redirectToRoute('vendor_warranty_claim_show_files', [
                    'id' => $vendorWarrantyClaim['id'],
                ]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $formFiles);
            }
        }

        if (null !== ($ncr = $vendorWarrantyClaim['nonConformity'])) {
            $ncr['files'] = array_filter($ncr['files'], static function ($file) {
                return $file['public'];
            });

            $vendorWarrantyClaim['nonConformity'] = $ncr;
        }

        $data = [
            'form_files' => $formFiles->createView(),
            'vendorWarrantyClaim' => $vendorWarrantyClaim,
        ];

        return $this->render('purchasing/vendor_warranty_claim/show_files.html.twig', array_merge($data, [
            'status_form' => $this->statusForm->create($vendorWarrantyClaim)->createView(),
        ]));
    }
}
