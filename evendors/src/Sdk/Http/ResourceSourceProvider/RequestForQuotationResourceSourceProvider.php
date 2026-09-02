<?php

declare(strict_types=1);

namespace App\Sdk\Http\ResourceSourceProvider;

use App\Sdk\Http\HttpSource;
use App\Sdk\Internal\Utility;
use App\Sdk\Resource\RequestForQuotation;
use App\Security\Security;
use App\Security\User\User;
use Symfony\Component\HttpFoundation\Request;

use function sprintf;

/**
 * @implements ResourceSourceProviderInterface<RequestForQuotation>
 */
final class RequestForQuotationResourceSourceProvider implements ResourceSourceProviderInterface
{
    public function __construct(
        private readonly Security $security,
    ) {
    }

    public function supports(string $resource): bool
    {
        return RequestForQuotation::class === $resource;
    }

    public function getFindSource(string|array $identifier): ?HttpSource
    {
        return HttpSource::create(Request::METHOD_GET, Utility::buildIri('/ion/request_for_quotations/%s', $identifier['id']));
    }

    /**
     * {@inheritDoc}
     */
    public function getUpdateSource(array|string $identifier, array $update): ?HttpSource
    {
        return null;
    }

    public function getDownloadSource(string|array $identifier): ?HttpSource
    {
        $iri = sprintf('/ion/request_for_quotations/%s/zip', $identifier['id']);

        return HttpSource::create(Request::METHOD_GET, $iri, [
            'headers' => [
                'Accept' => 'application/zip',
            ],
        ]);
    }

    public function getFindAllSource(array $criteria = []): ?HttpSource
    {
        $user = $this->security->getAuthenticatedUser();
        if (!$user instanceof User) {
            return null;
        }

        return HttpSource::create(Request::METHOD_GET, '/ion/request_for_quotations');
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
