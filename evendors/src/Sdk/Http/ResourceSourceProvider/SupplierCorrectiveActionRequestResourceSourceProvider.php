<?php

declare(strict_types=1);

namespace App\Sdk\Http\ResourceSourceProvider;

use App\Sdk\Http\HttpSource;
use App\Sdk\Internal\Utility;
use App\Sdk\Resource\NCRVendorWarrantyClaim;
use App\Sdk\Resource\SupplierCorrectiveActionRequest;
use App\Sdk\Resource\VendorWarrantyClaimPart;
use App\Sdk\Resource\WCVendorWarrantyClaim;
use App\Security\Security;
use Psl\Iter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;

use function sprintf;

/**
 * @implements ResourceSourceProviderInterface<SupplierCorrectiveActionRequest>
 */
class SupplierCorrectiveActionRequestResourceSourceProvider implements ResourceSourceProviderInterface
{
    public function __construct(
        protected readonly Security $security,
    ) {
    }

    public function supports(string $resource): bool
    {
        return SupplierCorrectiveActionRequest::class === $resource;
    }

    public function getFindSource(array|string $identifier): ?HttpSource
    {
        return HttpSource::create(Request::METHOD_GET, Utility::buildIri('/quality/supplier_corrective_action_requests/%s', $identifier));
    }

    public function getUpdateSource(array|string $identifier, array $update): ?HttpSource
    {
        // add the comment on SCAR
        if (Iter\contains_key($update, 'comment') && Iter\contains_key($update, 'file') && Iter\contains_key($identifier, 'iri')) {
            $formFields = [
                'message' => $update['comment'],
                'resource' => $identifier['iri'],
                'discriminator' => 'scar_conversation',
            ];

            if (null !== ($update['description'] ?? null)) {
                $formFields['description'] = $update['description'];
            }

            if (null !== ($update['file'] ?? null)) {
                $formFields['file'] = DataPart::fromPath($update['file']);
            }
            $formData = new FormDataPart($formFields);

            return HttpSource::create(Request::METHOD_POST, '/comments', [
                'headers' => $formData->getPreparedHeaders()->toArray() + ['Accept' => 'application/ld+json'],
                'body' => $formData->bodyToIterable(),
            ]);
        }

        // add SCAR from VWC
        if (Iter\contains_key($update, 'issueOrigin') && Iter\contains_key($update, 'correctiveAction') && Iter\contains_key($update, 'vendorWarrantyClaim')) {
            /** @var NCRVendorWarrantyClaim|WCVendorWarrantyClaim $claim */
            $claim = $update['vendorWarrantyClaim'];
            $user = $this->security->getAuthenticatedUser();

            $request = [
                'issueOrigin' => $update['issueOrigin'],
                'correctiveAction' => $update['correctiveAction'],
                'description' => $update['description'],
                'shortDescription' => $update['shortDescription'],
                'factory' => $claim->location->iri,
                'supplierErp' => $claim->supplierErp,
                'supplierNumber' => $claim->supplierNumber,
                'supplierName' => $claim->supplierName,
                'vendorWarrantyClaims' => [$claim->iri],
                'iFactor' => 'IF1',
                'posterInformation' => sprintf('%s %s - %s', $user->getFirstname(), $user->getLastname(), $user->getEmail()),
            ];

            /** @var VendorWarrantyClaimPart $part */
            foreach ($claim->parts as $part) {
                $request['parts'][] = [
                    'partNumber' => $part->partNumber,
                    'description' => $part->description,
                    'unitOfMeasure' => $part->unitOfMeasure,
                    'quantity' => $part->quantity,
                ];
            }

            return HttpSource::create(Request::METHOD_POST, '/quality/supplier_corrective_action_requests', [
                'json' => $request,
            ]);
        }

        return null;
    }

    public function getDownloadSource(array|string $identifier): ?HttpSource
    {
        return HttpSource::create(Request::METHOD_GET, sprintf('/quality/supplier_corrective_action_requests/%s/files/%s', $identifier['resource_id'], $identifier['file_id']));
    }

    public function getFindAllSource(array $criteria = []): HttpSource
    {
        return HttpSource::create(Request::METHOD_GET, '/quality/supplier_corrective_action_requests', [
            'query' => $criteria,
        ]);
    }

    public function getPaginationSource(int $page, int $itemsPerPage, array $criteria = []): ?HttpSource
    {
        return null;
    }

    public function getExcelSource(array $criteria = []): ?HttpSource
    {
        return null;
    }
}
