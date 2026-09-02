<?php

declare(strict_types=1);

namespace App\ION\Resources\MasterData\BusinessPartners;

use App\Security\JWT\PayloadGenerator\PayloadGeneratorInterface;
use Symfony\Component\Serializer\Attribute\Groups;

class BusinessPartnerContactCategory
{
    /** @var string */
    final public const REQUIRED_CATEGORY_NAME = 'PUR-EV';

    /** @var string */
    final public const QUALITY_CATEGORY_NAME = 'PUR-QA';

    #[Groups(['category', PayloadGeneratorInterface::PAYLOAD_NORMALIZATION_GROUP])]
    public string $code;

    #[Groups(['category', PayloadGeneratorInterface::PAYLOAD_NORMALIZATION_GROUP])]
    public string $description;
}
