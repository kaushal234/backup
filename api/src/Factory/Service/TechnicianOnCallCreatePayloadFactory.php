<?php

declare(strict_types=1);

namespace App\Factory\Service;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Dto\Service\TechnicianOnCallDuplicateLineInput;
use App\Entity\Service\TechnicianOnCall;
use App\Serializer\Normalizer\Service\TechnicianOnCallNormalizer;

final readonly class TechnicianOnCallCreatePayloadFactory
{
    public function __construct(
        private TechnicianOnCallNormalizer $normalizer,
        private IriConverterInterface $iriConverter,
    ) {
    }

    public function fromClone(TechnicianOnCall $clonedToc, TechnicianOnCallDuplicateLineInput $inputLine): array
    {
        $payload = $this->normalizer->normalize($clonedToc, null, ['groups' => ['toc:write']]);

        if ($inputLine->nestedCustomerServiceRecord?->leader) {
            $payload['nestedCustomerServiceRecord'] ??= [];
            $payload['nestedCustomerServiceRecord']['leader'] =
                $this->iriConverter->getIriFromResource($inputLine->nestedCustomerServiceRecord->leader);
        }

        return $payload;
    }
}
