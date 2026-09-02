<?php

declare(strict_types=1);

namespace AppBundle\Manager;

use ApiBundle\Client;
use AppBundle\Util\DateUtil;

class AuditLogManager
{
    public const API_ENDPOINT = '/audit_logs';
    public const TIME_ROUTE = 'time';
    public const TIME_BY_REFERENCE_ROUTE = 'time_by_reference';

    public function __construct(
        private readonly Client $client,
    ) {
    }

    public function find(string $operation, string $type, string $property, array $options = [])
    {
        return $this->client->get(\sprintf('%s/%s', self::API_ENDPOINT, $operation), ['query' => [
            'auditType' => $type,
            'property' => $property,
            ...$options,
        ]]);
    }

    public function reduce(array $data, string $searchValue): \DateInterval
    {
        $totalTime = array_reduce($data, static function ($time, $auditLog) use ($searchValue) {
            return $searchValue === $auditLog['value'] ? $time + $auditLog['time'] : $time;
        }, 0);

        return DateUtil::secondToDateInterval($totalTime);
    }

    public function findAndReduce(string $operation, string $type, string $property, string $searchValue, array $options = []): \DateInterval
    {
        $results = $this->find($operation, $type, $property, $options);

        return $this->reduce($results['hydra:member'], $searchValue);
    }

    public function timeByReference(string $type, string $property, int $referenceId, string $searchValue): \DateInterval
    {
        return $this->findAndReduce(self::TIME_BY_REFERENCE_ROUTE, $type, $property, $searchValue, ['referenceId' => $referenceId]);
    }

    public function time(string $type, string $property, array $options, string $searchValue): \DateInterval
    {
        return $this->findAndReduce(self::TIME_ROUTE, $type, $property, $searchValue, $options);
    }
}
