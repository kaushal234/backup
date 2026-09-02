<?php

declare(strict_types=1);

namespace App\Sdk\Http\ResourceSourceProvider;

use App\Sdk\Http\HttpSource;
use App\Sdk\Resource\VendorWarrantyClaimInterface;
use Psl\Iter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;

use function sprintf;

/**
 * @implements ResourceSourceProviderInterface<VendorWarrantyClaimInterface>
 */
class VendorWarrantyClaimResourceSourceProvider implements ResourceSourceProviderInterface
{
    public function supports(string $resource): bool
    {
        return VendorWarrantyClaimInterface::class === $resource;
    }

    public function getFindSource(array|string $identifier): ?HttpSource
    {
        return null;
    }

    public function getUpdateSource(array|string $identifier, array $update): ?HttpSource
    {
        // add comment on VWC
        if (Iter\contains_key($update, 'comment') && Iter\contains_key($update, 'file') && Iter\contains_key($identifier, 'iri')) {
            $formFields = [
                'message' => $update['comment'],
                'resource' => $identifier['iri'],
                'metadata' => $update['metadata'],
            ];

            if ($update['file']) {
                $formFields['file'] = DataPart::fromPath($update['file']);
            }
            $formData = new FormDataPart($formFields);

            return HttpSource::create(Request::METHOD_POST, '/comments', [
                'headers' => $formData->getPreparedHeaders()->toArray() + ['Accept' => 'application/ld+json'],
                'body' => $formData->bodyToIterable(),
            ]);
        }

        // edit VWC when VENDOR TO RESPOND
        if (Iter\contains_key($update, 'supplierCreditAmount')
            && Iter\contains_key($update, 'supplierShippingInstruction')
            && Iter\contains_key($update, 'accepted')
            && Iter\contains_key($update, 'shipBackDefectivePart')
            && Iter\contains_key($update, 'supplierReturnMerchandiseAuthorization')
            && Iter\contains_key($identifier, 'iri')
        ) {
            $request = [
                'supplierCreditAmount' => $update['supplierCreditAmount'],
                'supplierShippingInstruction' => $update['supplierShippingInstruction'],
                'accepted' => $update['accepted'],
                'shipBackDefectivePart' => $update['shipBackDefectivePart'],
                'supplierReturnMerchandiseAuthorization' => $update['supplierReturnMerchandiseAuthorization'],
            ];

            if (!empty($identifier['iri'])) {
                return HttpSource::create(Request::METHOD_PUT, $identifier['iri'], [
                    'json' => $request,
                ]);
            }

            return null;
        }

        // add file on a VWC
        if (Iter\contains_key($update, 'file') && Iter\contains_key($update, 'description') && Iter\contains_key($identifier, 'iri')) {
            $formFields = [
                'file' => DataPart::fromPath($update['file']),
                'public' => true,
            ];
            if (null !== $update['description']) {
                $formFields['description'] = $this->formatFileDescription($update['description']);
            }
            $formData = new FormDataPart($formFields);
            $path = str_replace(['/purchasing/wc_vendor_warranty_claims/', '/purchasing/ncr_vendor_warranty_claims/'], '/purchasing/vendor_warranty_claims/', $identifier['iri']);

            return HttpSource::create(Request::METHOD_POST, $path.'/files', [
                'headers' => $formData->getPreparedHeaders()->toArray() + ['Accept' => 'application/ld+json'],
                'body' => $formData->bodyToIterable(),
            ]);
        }

        // update the status of VWC
        if (Iter\contains_key($update, 'status') && Iter\contains_key($identifier, 'iri')) {
            $request = ['status' => $update['status']];

            return HttpSource::create(Request::METHOD_PUT, $identifier['iri'].'/status', [
                'json' => $request,
            ]);
        }

        return null;
    }

    public function getDownloadSource(array|string $identifier): ?HttpSource
    {
        return HttpSource::create(Request::METHOD_GET, sprintf('purchasing/vendor_warranty_claims/%s/files/%s', $identifier['resource_id'], $identifier['file_id']));
    }

    public function getFindAllSource(array $criteria = []): HttpSource
    {
        return HttpSource::create(Request::METHOD_GET, '/purchasing/vendor_warranty_claims', ['query' => $criteria]);
    }

    public function getPaginationSource(int $page, int $itemsPerPage, array $criteria = []): ?HttpSource
    {
        return null;
    }

    public function getExcelSource(array $criteria = []): ?HttpSource
    {
        return HttpSource::create(Request::METHOD_GET, '/purchasing/vendor_warranty_claims', [
            'headers' => ['Accept' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            'query' => [
                'columns' => 'id,status.name,createdAt,closedAt,statusUpdateAt,supplierName,supplierNumber,assignee,module,requestedSupplierAction,supplierCorrectiveActionRequest.id,partNumber,requestedCreditAmount,supplierCreditAmount,actualCreditAmount,currency',
            ],
        ]);
    }

    protected function formatFileDescription(string $description): string
    {
        return $description;
    }
}
