<?php

declare(strict_types=1);

namespace App\Report\Handler\Quality\Crab\Report;

use App\Entity\Directory\Location;
use App\Entity\Quality\Crab;
use App\Entity\Sales\ProductFamily;
use App\Report\DataProvider\ReportDataProvider;

class CrabByFamilyReportHandler extends AbstractCrabReportHandler
{
    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (!isset($options['factory'], $options['family']) || Crab::class !== $resourceClass || 'reportLastYearByFamily' !== $x) {
            return null;
        }

        $this->addJoins('products', 'p', 'id', 'er', 'product_id');

        $this
            ->add($options['factory'], Location::class, 'er', 'manufacturer_location_id')
            ->add($options['family'], ProductFamily::class, 'p', 'family_id')
        ;

        return $this->getReport($options);
    }
}
