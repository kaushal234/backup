<?php

declare(strict_types=1);

namespace Alvest\TwigHelper\Model;

use ArrayAccess;

interface ApiDataInterface extends ArrayAccess, \IteratorAggregate
{
    public function getIri(): string;

    public function getIriId(): int;

    public function getIriType(): ?string;
}
