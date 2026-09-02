<?php

declare(strict_types=1);

namespace App\Doctrine\Filter;

use App\Entity\File;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class PublicFileFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias): string
    {
        if (File::class !== $targetEntity->name) {
            return '';
        }

        return \sprintf('%s.public = 1', $targetTableAlias);
    }
}
