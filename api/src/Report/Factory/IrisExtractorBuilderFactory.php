<?php

declare(strict_types=1);

namespace App\Report\Factory;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Report\DataProvider\Extractor\IrisExtractorBuilder;

class IrisExtractorBuilderFactory
{
    private readonly IriConverterInterface $iriConverter;

    public function __construct(IriConverterInterface $iriConverter)
    {
        $this->iriConverter = $iriConverter;
    }

    public function createBuilder(): IrisExtractorBuilder
    {
        return new IrisExtractorBuilder($this->iriConverter);
    }
}
