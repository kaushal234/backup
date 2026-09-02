<?php

declare(strict_types=1);

namespace App\Report\Handler\Quality\Crab\Report;

use App\Entity\Directory\Location;
use App\Entity\Quality\Crab;
use App\Entity\Sales\Product;
use App\Report\DataProvider\ReportDataProvider;

class CrabByProductReportHandler extends AbstractCrabReportHandler
{
    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (!isset($options['factory'], $options['product']) || Crab::class !== $resourceClass || 'reportLastYearByProduct' !== $x) {
            return null;
        }

        $this
            ->add($options['factory'], Location::class, 'er', 'manufacturer_location_id')
            ->add($options['product'], Product::class, 'er', 'product_id')
        ;

        return $this->getReport($options);
    }
}
