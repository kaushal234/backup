<?php

declare(strict_types=1);

namespace App\Tests\Filter\Dummies;

use ApiPlatform\Doctrine\Common\Filter\SearchFilterInterface;
use ApiPlatform\Metadata\FilterInterface;

interface DummySearchFilterInterface extends SearchFilterInterface, FilterInterface
{
}
