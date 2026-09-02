<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type;

use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Kreyu\Bundle\DataTableBundle\Type\DataTableTypeInterface;

abstract class AbstractSimpleDataTableType extends AbstractDataTableType implements DataTableTypeInterface
{
    public function getParent(): string
    {
        return SimpleDataTableType::class;
    }
}
