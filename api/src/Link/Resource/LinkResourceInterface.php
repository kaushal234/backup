<?php

declare(strict_types=1);

namespace App\Link\Resource;

interface LinkResourceInterface
{
    public function getLinkId(): ?int;
}
