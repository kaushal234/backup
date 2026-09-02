<?php

declare(strict_types=1);

namespace App\CQRS\QueryHandler\TechnicianOnCall;

use App\CQRS\Query\TechnicianOnCall\ExportTechnicianOnCallQuery;
use App\CQRS\QueryHandler\QueryHandlerInterface;
use App\Sdk\Downloader;
use App\Sdk\Resource\TechnicianOnCall;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[AsMessageHandler(bus: 'query.bus')]
readonly class ExportTechnicianOnCallQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private Downloader $downloader,
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function __invoke(ExportTechnicianOnCallQuery $query): BinaryFileResponse
    {
        return $this->downloader->export(
            resource: TechnicianOnCall::class,
            format: $query->format,
            criteria: $query->options,
            filename: $query->filename,
        );
    }
}
