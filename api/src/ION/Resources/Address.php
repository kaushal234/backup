<?php

declare(strict_types=1);

namespace App\ION\Resources;

use ApiPlatform\Metadata\ApiProperty;
use App\Security\JWT\PayloadGenerator\PayloadGeneratorInterface;
use Symfony\Component\Serializer\Attribute\Groups;

class Address
{
    #[ApiProperty(identifier: true)]
    #[Groups(['address', PayloadGeneratorInterface::PAYLOAD_NORMALIZATION_GROUP])]
    public string $addressCode;

    #[Groups(['address', PayloadGeneratorInterface::PAYLOAD_NORMALIZATION_GROUP])]
    public string $addressLine1;

    #[Groups(['address', PayloadGeneratorInterface::PAYLOAD_NORMALIZATION_GROUP])]
    public string $addressLine2;

    #[Groups(['address', PayloadGeneratorInterface::PAYLOAD_NORMALIZATION_GROUP])]
    public string $addressLine3;

    #[Groups(['address', PayloadGeneratorInterface::PAYLOAD_NORMALIZATION_GROUP])]
    public string $addressLine4;

    #[Groups(['address', PayloadGeneratorInterface::PAYLOAD_NORMALIZATION_GROUP])]
    public string $addressLine5;

    #[Groups(['address', PayloadGeneratorInterface::PAYLOAD_NORMALIZATION_GROUP])]
    public string $addressLine6;
}
