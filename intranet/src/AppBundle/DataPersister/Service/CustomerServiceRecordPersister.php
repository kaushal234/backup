<?php

declare(strict_types=1);

namespace AppBundle\DataPersister\Service;

use ApiBundle\Client;

class CustomerServiceRecordPersister
{
    public const CUSTOMER_SERVICE_RECORD_TYPE = [
        'TOC' => 'toc',
        'Service Bulletin' => 'sb',
        'Commissioning' => 'commissioning',
    ];

    public const TOC_CUSTOMER_SERVICE_RECORD_URL = 'service/technician_on_call_customer_service_records';
    public const SB_CUSTOMER_SERVICE_RECORD_URL = 'service/service_bulletin_customer_service_records';
    public const COMMISSIONING_CUSTOMER_SERVICE_RECORD_URL = 'service/commissioning_customer_service_records';
    public const DEFAULT_CUSTOMER_SERVICE_RECORD_URL = 'service/default_customer_service_records';
    public const CUSTOMER_SERVICE_RECORD_URL = 'service/customer_service_records';

    public function __construct(
        private readonly Client $client,
    ) {
    }

    public function save(array $data)
    {
        if (\array_key_exists('tocId', $data)) {
            $data['technicianOnCall'] = \sprintf('/%s/%d', TechnicianOnCallPersister::RESOURCE_URL, $data['tocId']);
            unset($data['tocId']);

            return $this->client->save(self::TOC_CUSTOMER_SERVICE_RECORD_URL, $data);
        }

        switch ($data['module'] ?? $data['type']) {
            case 'toc':
                $url = self::TOC_CUSTOMER_SERVICE_RECORD_URL;
                break;
            case 'sb':
                $url = self::SB_CUSTOMER_SERVICE_RECORD_URL;
                break;
            case 'commissioning':
                $url = self::COMMISSIONING_CUSTOMER_SERVICE_RECORD_URL;
                break;
            case 'default':
                $url = self::DEFAULT_CUSTOMER_SERVICE_RECORD_URL;
                break;
            default:
                throw new \Exception(\sprintf('Invalid CSR type %s', $data['module'] ?? $data['type']));
        }

        return $this->client->save($url, $data);
    }
}
