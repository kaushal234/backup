<?php

declare(strict_types=1);

namespace Alvest\TwigHelper\Model;

final class ApiData extends AbstractApiData
{
    public function getIri(): string
    {
        return '';
    }

    public function getIriId(): int
    {
        return 0;
    }

    public function getIriType(): ?string
    {
        return null;
    }
}
