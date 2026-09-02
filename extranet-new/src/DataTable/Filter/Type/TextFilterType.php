<?php

declare(strict_types=1);

namespace App\DataTable\Filter\Type;

use App\DataTable\Filter\AbstractApiFilterType;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(name: 'kreyu_data_table.filter.type')]
class TextFilterType extends AbstractApiFilterType
{
}
