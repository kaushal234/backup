<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Query;

use ApiBundle\Client;
use ApiBundle\Http\CsvStreamedResponseFactory;
use ApiBundle\Http\FileStreamedResponseFactory;
use Kreyu\Bundle\DataTableBundle\Query\ProxyQueryFactoryInterface;
use Kreyu\Bundle\DataTableBundle\Query\ProxyQueryInterface;

readonly class ApiProxyQueryFactory implements ProxyQueryFactoryInterface
{
    public function __construct(
        private Client $client,
        private FileStreamedResponseFactory $fileStreamedResponseFactory,
        private CsvStreamedResponseFactory $csvStreamedResponseFactory,
    ) {
    }

    public function create(mixed $data): ProxyQueryInterface
    {
        return new ApiProxyQuery($data, $this->client, $this->fileStreamedResponseFactory, $this->csvStreamedResponseFactory);
    }

    public function supports(mixed $data): bool
    {
        return \is_string($data);
    }
}
