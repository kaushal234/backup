<?php

declare(strict_types=1);

namespace Shared\Persister\Service;

use Shared\Models\Service\CustomerServiceRecord;
use Shared\Persister\AbstractPersister;

class CustomerServiceRecordPersister extends AbstractPersister
{
    public const CSR_URL = '/service/service_bulletin_customer_service_records';

    public function post(array $data): CustomerServiceRecord
    {
        $csr = $this->client->save(self::CSR_URL, $data);
        $csr['iri'] = $csr['@id'];

        return $this->serializer->denormalize($csr, CustomerServiceRecord::class);
    }
}