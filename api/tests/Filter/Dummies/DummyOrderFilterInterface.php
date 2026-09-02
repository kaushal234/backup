<?php

declare(strict_types=1);

namespace App\Tests\Filter\Dummies;

use ApiPlatform\Doctrine\Common\Filter\OrderFilterInterface;
use ApiPlatform\Metadata\FilterInterface;

interface DummyOrderFilterInterface extends OrderFilterInterface, FilterInterface
{
}
