<?php

declare(strict_types=1);

namespace App\Serializer\Encoder;

use App\Entity\DMS;
use LegacyBundle\Manager\DMSManager;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Encoder\NormalizationAwareInterface;

class DMSPdfEncoder implements EncoderInterface, NormalizationAwareInterface
{
    public function __construct(
        private readonly DMSManager $DMSManager,
    ) {
    }

    public function encode($data, string $format, array $context = []): string
    {
        if (!$data instanceof DMS) {
            throw new \RuntimeException('Only DMS entities should use this format');
        }

        return $this->DMSManager->getDmsFile($data->getLegacyId())->getContent();
    }

    public function supportsEncoding(string $format): bool
    {
        return 'dms' === $format;
    }
}
