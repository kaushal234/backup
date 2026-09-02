<?php

declare(strict_types=1);

namespace App\Sdk\Http\ResourceSourceProvider;

use App\Sdk\Http\DistributedHttpSource;
use App\Sdk\Http\HttpSource;
use App\Sdk\Internal\Utility;
use App\Sdk\Resource\PurchaseOrder;
use App\Security\Security;
use App\Security\User\User;
use Psl\Iter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

use function array_key_exists;

/**
 * @implements ResourceSourceProviderInterface<PurchaseOrder>
 */
class PurchaseOrderResourceSourceProvider implements ResourceSourceProviderInterface
{
    public function __construct(
        protected readonly Security $security,
        protected readonly TranslatorInterface $translator,
    ) {
    }

    public function supports(string $resource): bool
    {
        return PurchaseOrder::class === $resource;
    }

    public function getFindSource(string|array $identifier): ?HttpSource
    {
        return HttpSource::create(Request::METHOD_GET, Utility::buildIri('/ion/purchase_orders/%s', $identifier),
            [
                'query' => $this->addOtherLanguageFilter([]),
            ],
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getUpdateSource(array|string $identifier, array $update): ?HttpSource
    {
        $request = [];
        if (Iter\contains_key($update, 'message')) {
            $request['message'] = $update['message'];
            $request['resource'] = $identifier['iri'];

            return HttpSource::create(Request::METHOD_POST, '/comments', [
                'json' => $request,
            ]);
        }

        return HttpSource::create(Request::METHOD_PUT, Utility::buildIri('/ion/purchase_orders/%s', $identifier), [
            'json' => $update,
        ]);
    }

    public function getDownloadSource(string|array $identifier): ?HttpSource
    {
        if (Iter\contains_key($identifier, 'lines')) {
            $iri = Utility::buildIri('%s', $identifier['iri']);
            $data = ['lines' => $identifier['lines']];

            return HttpSource::create(Request::METHOD_PUT, $iri, [
                'headers' => [
                    'content-type' => 'application/json',
                ],
                'json' => $data,
            ]);
        }

        $iri = Utility::buildIri('/ion/purchase_orders/%s', $identifier);

        return HttpSource::create(Request::METHOD_GET, $iri.'/zip', [
            'headers' => [
                'Accept' => 'application/zip',
            ],
        ]);
    }

    public function getFindAllSource(array $criteria = []): ?DistributedHttpSource
    {
        if (!$this->security->getAuthenticatedUser() instanceof User) {
            return null;
        }

        return DistributedHttpSource::combine(HttpSource::create(Request::METHOD_GET, '/ion/purchase_orders', [
            'query' => $this->getFilters($criteria),
        ]));
    }

    public function getPaginationSource(int $page, int $itemsPerPage, array $criteria = []): ?HttpSource
    {
        return null;
    }

    public function getExcelSource(array $criteria = []): ?HttpSource
    {
        if (!$this->security->getAuthenticatedUser() instanceof User) {
            return null;
        }

        return HttpSource::create(Request::METHOD_GET, '/ion/purchase_orders', [
            'query' => $this->getFilters($criteria),
            'headers' => ['Accept' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        ]);
    }

    /**
     * @param array<string, mixed> $criteria
     *
     * @return array<string, mixed>
     */
    private function getFilters(array $criteria): array
    {
        $openPurchaseOrder = array_key_exists('open', $criteria) ? $criteria['open'] : true;
        $PurchaseOrderAfterDate = array_key_exists('orderDate', $criteria) ? $criteria['orderDate']['after'] : null;
        $PurchaseOrderBeforeDate = array_key_exists('orderDate', $criteria) ? $criteria['orderDate']['before'] : null;

        $filters = [
            'onlyOpenedLines' => array_key_exists('onlyOpenedLines', $criteria) ? $criteria['onlyOpenedLines'] : false,
            'open' => $openPurchaseOrder,
            'orderDate' => [
                'after' => $PurchaseOrderAfterDate,
                'before' => $PurchaseOrderBeforeDate,
            ],
        ];

        $filters = $this->addOtherLanguageFilter($filters);

        return $filters;
    }

    /**
     * @param array<string, mixed> $filters
     *
     * @return array<string, mixed>
     */
    private function addOtherLanguageFilter(array $filters): array
    {
        $locale = $this->translator->getLocale();

        $otherLanguageFilter = ['otherLanguage' => 'en' !== $locale ? mb_substr($locale, 0, 2) : null];

        return array_merge($otherLanguageFilter, $filters);
    }
}
