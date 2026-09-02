<?php

declare(strict_types=1);

namespace App\ION\Serializer\Denormalizer\MasterData\EnterpriseModel\Entities;

use App\ION\Resources\MasterData\EnterpriseModel\Entities\Department;
use App\ION\Serializer\Encoder\IONXmlDecoder;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class DepartmentDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    /**
     * @var string
     */
    final public const ALREADY_CALLED = 'DEPARTMENT_DENORMALIZER_ALREADY_CALLED';

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return Department::class === $type && !($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @return array|mixed|object
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        $context[self::ALREADY_CALLED] = true;

        $data['erp'] = mb_substr((string) $data['code'], 0, 3);
        $data['code'] = mb_substr((string) $data['code'], 3);
        $data['name'] = IONXmlDecoder::trim($data['name']);
        $data['buyer'] = [
            'employeeCode' => IONXmlDecoder::trim($data['buyer']['code']),
            'fullName' => IONXmlDecoder::trim($data['buyer']['name'] ?? ''),
            'emailAddress' => IONXmlDecoder::trim($data['buyer']['emailAddress'] ?? ''),
        ];

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }
}
