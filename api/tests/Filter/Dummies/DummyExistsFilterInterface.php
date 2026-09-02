<?php

declare(strict_types=1);

namespace App\Tests\Filter\Dummies;

use ApiPlatform\Doctrine\Common\Filter\ExistsFilterInterface;
use ApiPlatform\Metadata\FilterInterface;

interface DummyExistsFilterInterface extends ExistsFilterInterface, FilterInterface
{
}
