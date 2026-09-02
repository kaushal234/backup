<?php

declare(strict_types=1);

namespace App\Controller\Purchasing;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Purchasing\VendorWarrantyClaim;
use App\Entity\Purchasing\VendorWarrantyClaimStatus;
use App\Manager\Purchasing\VendorWarrantyClaim\VendorWarrantyClaimManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

class VendorWarrantyClaimController extends AbstractController
{
    private readonly IriConverterInterface $iriConverter;
    private readonly VendorWarrantyClaimManager $manager;
    private readonly DecoderInterface $decoder;

    public function __construct(IriConverterInterface $iriConverter, VendorWarrantyClaimManager $manager, DecoderInterface $decoder)
    {
        $this->iriConverter = $iriConverter;
        $this->manager = $manager;
        $this->decoder = $decoder;
    }

    public function __invoke(VendorWarrantyClaim $vendorWarrantyClaim, Request $request): VendorWarrantyClaim
    {
        $content = $this->decoder->decode((string) $request->getContent(), JsonEncoder::FORMAT);
        if (!isset($content['status'])) {
            throw new BadRequestHttpException('Status should be set.');
        }

        $resolution = $content['resolution'] ?? null;

        $status = $this->iriConverter->getResourceFromIri($content['status']);
        if (!$status instanceof VendorWarrantyClaimStatus) {
            throw new UnprocessableEntityHttpException(\sprintf('An instance of "VendorWarrantyClaimStatus" was expected. Got a "%s"', $status::class));
        }

        if (VendorWarrantyClaimStatus::VENDOR_TO_RESPOND === $status->name && null === $vendorWarrantyClaim->getBusinessPartnerCode()) {
            throw new BadRequestHttpException(\sprintf('You need to set a supplier before changing status to %s', VendorWarrantyClaimStatus::VENDOR_TO_RESPOND));
        }

        if (VendorWarrantyClaimStatus::VENDOR_TO_RESPOND === $status->name && $this->manager->findQualityContacts($vendorWarrantyClaim)->isEmpty()) {
            throw new BadRequestHttpException(\sprintf('None of the contacts of the LN Business Partner %s have the PUR-QA category, status VENDOR_TO_RESPOND not available', mb_trim($vendorWarrantyClaim->getBusinessPartnerCode())));
        }

        $vendorWarrantyClaim->status = $status;
        if (null !== $resolution) {
            $vendorWarrantyClaim->resolution = $resolution;
        }
        $vendorWarrantyClaim->assignee = $this->manager->getAssigneeOnStatusChange($vendorWarrantyClaim);

        return $vendorWarrantyClaim;
    }
}
