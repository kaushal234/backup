<?php

declare(strict_types=1);

namespace App\Report\Handler;

use App\Report\Factory\IrisExtractorBuilderFactory;
use Symfony\Contracts\Service\Attribute\Required;

trait IrisExtractorBuilderFactoryAwareTrait
{
    protected IrisExtractorBuilderFactory $irisExtractorBuilderFactory;

    #[Required]
    public function setIriExtractorFactory(IrisExtractorBuilderFactory $irisExtractorBuilderFactory): void
    {
        $this->irisExtractorBuilderFactory = $irisExtractorBuilderFactory;
    }
}
