<?php

declare(strict_types=1);

namespace App\Serializer\Denormalizer;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Manager\Transfer\Model\TransferCustomerModel;
use App\Manager\Transfer\Model\TransferPeopleModel;
use App\Manager\Transfer\Model\TransferSalesForecastModel;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class TransferModelDenormalizer implements DenormalizerInterface
{
    private readonly IriConverterInterface $iriConverter;

    private readonly PropertyAccessorInterface $propertyAccessor;

    public function __construct(IriConverterInterface $iriConverter, PropertyAccessorInterface $propertyAccessor)
    {
        $this->iriConverter = $iriConverter;
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * @return array|mixed|object
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        $transferParams = new $type();

        foreach ($data as $key => $param) {
            if (null !== $param) {
                try {
                    $item = $this->iriConverter->getResourceFromIri($param);
                } catch (\Exception $exception) {
                    throw new \InvalidArgumentException(\sprintf('Could not find any resource matching %s', $param), 400, $exception);
                }
                $this->propertyAccessor->setValue($transferParams, $key, $item);
            }
        }

        return $transferParams;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return \in_array($type, [TransferPeopleModel::class, TransferSalesForecastModel::class, TransferCustomerModel::class], true);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            TransferCustomerModel::class => true,
            TransferPeopleModel::class => true,
            TransferSalesForecastModel::class => true,
        ];
    }
}
