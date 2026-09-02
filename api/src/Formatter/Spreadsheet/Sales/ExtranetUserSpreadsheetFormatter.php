<?php

declare(strict_types=1);

namespace App\Formatter\Spreadsheet\Sales;

use App\Entity\Directory\Phone;
use App\Entity\Sales\ExtranetUser;
use App\Entity\Sales\ExtranetUserAcl;
use App\Formatter\Spreadsheet\AbstractSpreadsheetFormatter;

class ExtranetUserSpreadsheetFormatter extends AbstractSpreadsheetFormatter
{
    public function getComputedColumns(): array
    {
        return ['phones', 'extranetUserAcls'];
    }

    /**
     * @param ExtranetUser $item
     */
    public function computeColumn(object $item, string $column): mixed
    {
        return match ($column) {
            'phones' => implode(', ', array_map(
                static fn (Phone $phone) => \sprintf('%s: %s', $phone->getType(), $phone->getNumber()),
                $item->getPhones()->toArray()
            )),
            'extranetUserAcls' => implode(', ', array_map(
                static fn (ExtranetUserAcl $acl) => \sprintf('%s (CRT#%d)', $acl->getExtranetUserGroup()->getName(), $acl->getCrt()->getId()),
                $item->getExtranetUserAcls()->toArray()
            )),
            default => null,
        };
    }

    public function supports(string $class, string $operationName): bool
    {
        return ExtranetUser::class === $class && 'get_extranet_users' === $operationName;
    }
}
