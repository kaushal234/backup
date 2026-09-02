<?php

declare(strict_types=1);

namespace App\Tests\Filter\Dummies;

use ApiPlatform\Doctrine\Common\Filter\RangeFilterInterface;
use ApiPlatform\Metadata\FilterInterface;

interface DummyRangeFilterInterface extends RangeFilterInterface, FilterInterface
{
}
