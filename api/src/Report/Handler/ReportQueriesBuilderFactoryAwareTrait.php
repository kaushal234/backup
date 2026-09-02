<?php

declare(strict_types=1);

namespace App\Report\Handler;

use App\Report\ReportQueriesBuilderFactory;
use Symfony\Contracts\Service\Attribute\Required;

trait ReportQueriesBuilderFactoryAwareTrait
{
    protected ReportQueriesBuilderFactory $factory;

    #[Required]
    public function setFactory(ReportQueriesBuilderFactory $factory): void
    {
        $this->factory = $factory;
    }
}
