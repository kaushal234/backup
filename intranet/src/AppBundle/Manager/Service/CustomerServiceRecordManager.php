<?php

declare(strict_types=1);

namespace AppBundle\Manager\Service;

use ApiBundle\Client;

class CustomerServiceRecordManager
{
    public function __construct(
        private readonly Client $client,
    ) {
    }

    public function getWeek(\DateTime $dateTime): string
    {
        $plannedWeek = (int) $dateTime->format('W');
        $year = (int) $dateTime->format('Y');

        return match (true) {
            $year < (int) (new \DateTimeImmutable())->format('Y')
            || $plannedWeek <= (int) (new \DateTimeImmutable())->format('W') => 'currentWeek',
            $plannedWeek === (int) (new \DateTimeImmutable('+1 week'))->format('W') => 'nextWeek',
            $plannedWeek === (int) (new \DateTimeImmutable('+2 week'))->format('W') => 'secondWeek',
            $plannedWeek === (int) (new \DateTimeImmutable('+3 week'))->format('W') => 'thirdWeek',
            $plannedWeek === (int) (new \DateTimeImmutable('+4 week'))->format('W') => 'fourthWeek',
            default => 'afterFourWeeks',
        };
    }

    public function getCustomerServiceRecordsByWeekByUserOrdered(array $customerServiceRecords, array $technicians = []): array
    {
        $technicians = $this->client->findBy('people', ['id' => array_values($technicians)]);
        $customerServiceRecordsOrdered = [];

        foreach ($customerServiceRecords as $customerServiceRecord) {
            if (null === $customerServiceRecord['plannedAt']) {
                continue;
            }

            $week = $this->getWeek(new \DateTime($customerServiceRecord['plannedAt']));
            $leader = $customerServiceRecord['openIntervention']['leader'];
            $airport = $customerServiceRecord['airport']['code'] ?? null;

            $customerServiceRecordsOrdered[$week][$leader['@id']]['customerServiceRecord'][] = $customerServiceRecord;
            $customerServiceRecordsOrdered[$week][$leader['@id']]['technicianName'] = $leader['firstname'].' '.$leader['lastname'];

            if (null !== $airport && (
                !\array_key_exists('airport', $customerServiceRecordsOrdered[$week][$leader['@id']])
                || !\in_array($airport, $customerServiceRecordsOrdered[$week][$leader['@id']]['airport'], true)
            )) {
                $customerServiceRecordsOrdered[$week][$leader['@id']]['airport'][] = $airport;
            }

            $operators = $customerServiceRecord['openIntervention']['operators'];

            foreach ($operators as $operator) {
                $customerServiceRecordsOrdered[$week][$operator['@id']]['customerServiceRecord'][] = $customerServiceRecord;
                $customerServiceRecordsOrdered[$week][$operator['@id']]['technicianName'] = $operator['firstname'].' '.$operator['lastname'];

                if (null !== $airport && (
                    !\array_key_exists('airport', $customerServiceRecordsOrdered[$week][$operator['@id']])
                    || !\in_array($airport, $customerServiceRecordsOrdered[$week][$operator['@id']]['airport'], true))
                ) {
                    $customerServiceRecordsOrdered[$week][$operator['@id']]['airport'][] = $airport;
                }
            }
        }

        foreach (['currentWeek', 'nextWeek', 'secondWeek', 'thirdWeek', 'fourthWeek', 'afterFourWeeks'] as $week) {
            foreach ($technicians as $technician) {
                if (!\array_key_exists($week, $customerServiceRecordsOrdered)) {
                    $customerServiceRecordsOrdered[$week] = [];
                }

                if (\array_key_exists($technician->getIri(), $customerServiceRecordsOrdered[$week])) {
                    continue;
                }

                $customerServiceRecordsOrdered[$week][$technician->getIri()] = [
                    'customerServiceRecord' => [],
                    'technicianName' => \sprintf('%s %s', $technician['firstname'], $technician['lastname']),
                    'airport' => [],
                ];
            }
        }

        return $customerServiceRecordsOrdered;
    }

    public function getCustomerServiceRecordsByUserOrdered(array $allCustomerServiceRecords)
    {
        $customerServiceRecordsOrdered = [
            'currentWeek' => ['count' => 0],
            'nextWeek' => ['count' => 0],
            'secondWeek' => ['count' => 0],
            'thirdWeek' => ['count' => 0],
            'fourthWeek' => ['count' => 0],
        ];
        $statusOrder = array_flip([
            'ASSIGNED',
            'COMPLETED',
            'IN-PROGRESS',
            'SOLVED',
            'PENDING',
            'TO_CONTINUE',
            'CLOSED',
        ]);

        usort($allCustomerServiceRecords, static function ($record1, $record2) use ($statusOrder) {
            return $statusOrder[$record1['status']] - $statusOrder[$record2['status']];
        });

        foreach ($allCustomerServiceRecords as $customerServiceRecord) {
            if (null === $customerServiceRecord['openIntervention']
                || !\array_key_exists('plannedAt', $customerServiceRecord['openIntervention'])
                || null === $customerServiceRecord['openIntervention']['plannedAt']
            ) {
                continue;
            }
            $airportCodes = null;

            $week = $this->getWeek(new \DateTime($customerServiceRecord['openIntervention']['plannedAt']));

            if ('afterFourWeeks' === $week) {
                continue;
            }

            if ($customerServiceRecord['airport'] && \array_key_exists('code', $customerServiceRecord['airport'])) {
                $airportCodes = $customerServiceRecord['airport']['code'];
            }
            ++$customerServiceRecordsOrdered[$week]['count'];

            $customerServiceRecordsOrdered[$week]['customerServiceRecord'][$airportCodes][] = $customerServiceRecord;
        }

        return $customerServiceRecordsOrdered;
    }

    public function orderCustomerServicesRecords(array $customerServiceRecords)
    {
        $plannedCustomerServiceRecords = ['customerServiceRecords' => [], 'currentWeek' => [], 'nextWeek' => [], 'secondWeek' => [], 'thirdWeek' => [], 'fourthWeek' => [], 'afterFourWeeks' => []];

        foreach ($customerServiceRecords as $customerServiceRecord) {
            if (!$customerServiceRecord['plannedAt']) {
                $plannedCustomerServiceRecords['customerServiceRecords'][] = $customerServiceRecord;
                continue;
            }

            $key = $this->getWeek(new \DateTime($customerServiceRecord['plannedAt']));

            $plannedCustomerServiceRecords[$key] = $this->dispatchCustomerServiceRecordsByWeeksByTechnicians($customerServiceRecord, $plannedCustomerServiceRecords[$key]);
        }

        return $plannedCustomerServiceRecords;
    }

    public function getAvailableAirportCode(array $customerServiceRecords)
    {
        $airports = array_column($customerServiceRecords, 'airport');
        $airports = array_filter($airports);
        $codes = array_column($airports, 'code');

        $codes = array_unique($codes);

        return array_combine($codes, $codes);
    }

    private function dispatchCustomerServiceRecordsByWeeksByTechnicians(array $customerServiceRecord, array $csrInWeek): array
    {
        $openIntervention = $customerServiceRecord['openIntervention'];

        if (null === $openIntervention) {
            $csrInWeek['unassigned'][] = $customerServiceRecord;

            return $csrInWeek;
        }

        $user = $openIntervention['leader'] ?? null;
        $operatorsKey = [];

        foreach ($openIntervention['operators'] ?? [] as $operator) {
            if (!$operator) {
                continue;
            }
            $operatorsKey[] = $operator['@id'];
        }
        $userKey = $user ? $user['@id'] : 'unassigned';

        $csrInWeek[$userKey][] = $customerServiceRecord;
        foreach ($operatorsKey as $operatorKey) {
            $csrInWeek[$operatorKey][] = $customerServiceRecord;
        }

        return $csrInWeek;
    }
}
