<?php

declare(strict_types=1);

namespace App\ION\Serializer\Denormalizer\MasterData\EnterpriseModel;

use App\ION\Resources\MasterData\EnterpriseModel\Employee;
use App\ION\Serializer\Encoder\IONXmlDecoder;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class EmployeeDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    /**
     * @var string
     */
    final public const ALREADY_CALLED = 'EMPLOYEE_DENORMALIZER_ALREADY_CALLED';

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return Employee::class === $type && !($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @return array|mixed|object
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        $context[self::ALREADY_CALLED] = true;

        if ('' === IONXmlDecoder::trim($data['employeeCode'])) {
            return null;
        }

        $data['fullName'] = IONXmlDecoder::trim(\is_array($data['fullName']) ? $data['fullName'][0]['#'] : $data['fullName']);
        $data['employeeCode'] = IONXmlDecoder::trim($data['employeeCode']);
        $data['emailAddress'] = isset($data['emailAddress']) && '' !== $data['emailAddress'] ? IONXmlDecoder::trim($data['emailAddress']) : null;
        $data['baanLegacyId'] = mb_substr((string) $data['employeeCode'], 3);
        $data['erp'] = isset($data['department']) ? mb_substr((string) $data['department'], 0, 3) : null;

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }
}
