<?php

declare(strict_types=1);

namespace App\Sdk\Http\ResourceSourceProvider\Manufacturing;

use App\Sdk\Http\DistributedHttpSource;
use App\Sdk\Http\HttpSource;
use App\Sdk\Http\ResourceSourceProvider\ResourceSourceProviderInterface;
use App\Sdk\Resource\Manufacturing\BillOfMaterials;
use DateTime;
use DateTimeInterface;
use Symfony\Component\HttpFoundation\Request;

use function sprintf;

/**
 * @implements ResourceSourceProviderInterface<BillOfMaterials>
 */
final class BillOfMaterialsResourceSourceProvider implements ResourceSourceProviderInterface
{
    public function supports(string $resource): bool
    {
        return BillOfMaterials::class === $resource;
    }

    public function getFindSource(array|string $identifier): ?HttpSource
    {
        $options = [
            'query' => ['depth' => 20],
        ];

        if (null !== $identifier['effectiveDate']) {
            $options['query']['date'] = $identifier['effectiveDate'];
        }

        return HttpSource::create(Request::METHOD_GET, sprintf('/ion/bill_of_material_items/site=%s;project=;product=%s', $identifier['site'], $identifier['item']), $options);
    }

    public function getUpdateSource(array|string $identifier, array $update): ?HttpSource
    {
        return null;
    }

    public function getDownloadSource(string|array $identifier): HttpSource
    {
        $dateTime = new DateTime($identifier['effectiveDate']);

        $id = sprintf('site=%d;project=%s;product=%s', $identifier['site'], '', $identifier['item']);

        $basePath = 'ion/bill-of-materials/drawings';

        if (!empty($identifier['use3dFiles'])) {
            $basePath = 'ion/bill-of-materials/drawing_3d_files';
        }

        return HttpSource::create(Request::METHOD_GET,
            sprintf('%s/%s', $basePath, $id),
            [
                'query' => [
                    'date' => $dateTime->format(DateTimeInterface::ATOM),
                ],
            ]
        );
    }

    public function getFindAllSource(array $criteria = []): DistributedHttpSource|HttpSource|null
    {
        return null;
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
