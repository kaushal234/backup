<?php

declare(strict_types=1);

namespace AppBundle\Manager\Service;

use ApiBundle\Model\ApiData;
use AppBundle\DataProvider\ION\ItemMonologisticProvider;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class TechnicianOnCallManager
{
    public function __construct(
        private TranslatorInterface $translator,
        private ItemMonologisticProvider $itemMonologisticProvider,
    ) {
    }

    public function getItemMonologisticPartsFromList(ApiData $technicianOnCall, array $partsList)
    {
        if ([] === $partsList) {
            return [];
        }

        $itemMonologisticParts = [];
        $partsFiltered = $this->filterParts($technicianOnCall, $partsList);

        foreach ($partsFiltered as $part) {
            $itemMonologisticPart = [];

            try {
                $itemMonologisticPart = $this->itemMonologisticProvider->getItem(str_replace(' ', '', $part['partNumber']))->toArray();
            } catch (ClientException) {
                $itemMonologisticPart['itemCode'] = $part['partNumber'];
                $itemMonologisticPart['description'] = $part['description'];
                $itemMonologisticPart['partNumber']['error'] = $this->translator->trans('crab.errors.part_not_found', ['%part%' => $part['partNumber']], 'crab');
            }

            $itemMonologisticPart['quantity'] = $part['quantity'];
            $itemMonologisticPart['comment'] = $part['comment'];
            $itemMonologisticParts[] = $itemMonologisticPart;
        }

        return $itemMonologisticParts;
    }

    public function filterParts(ApiData $technicianOnCall, array $partsList)
    {
        return array_filter($technicianOnCall['parts'], static function ($technicianOnCallPart) use ($partsList) {
            return \in_array($technicianOnCallPart['@id'], $partsList, true);
        });
    }
}
