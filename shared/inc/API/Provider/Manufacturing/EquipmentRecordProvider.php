<?php

declare(strict_types=1);

namespace Shared\Provider\Manufacturing;

use Shared\Models\Manufacturing\EquipmentRecord;
use Shared\Provider\AbstractProvider;

class EquipmentRecordProvider extends AbstractProvider
{
    public const EQUIPMENT_RECORD_URL = '/equipment_records';

    public function findByLegacyId(string|int $id): EquipmentRecord
    {
        $data =  $this->client->get(sprintf('%s?legacyId=%d', self::EQUIPMENT_RECORD_URL, $id));
        $data = $data['hydra:member'][0];
        $data['iri'] = $data['@id'];

        return $this->serializer->denormalize($data, EquipmentRecord::class);
    }
}