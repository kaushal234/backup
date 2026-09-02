<?php

declare(strict_types=1);

namespace App\ION\Dto\SpartaExtensions\Times;

use Symfony\Component\Serializer\Attribute\Groups;

class TimeKeepingPostTransactionOutput
{
    /**
     * @var TimeKeepingPostTransactionOutputLine[]
     */
    #[Groups(['time_keeping'])]
    private array $lines = [];

    /**
     * @return TimeKeepingPostTransactionOutputLine[]
     */
    public function getLines(): array
    {
        return $this->lines;
    }

    public function addLine(TimeKeepingPostTransactionOutputLine $timeKeepingPostTransactionOutputLine): self
    {
        $this->lines[] = $timeKeepingPostTransactionOutputLine;

        return $this;
    }

    public function removeLine(TimeKeepingPostTransactionOutputLine $timeKeepingPostTransactionOutputLine): self
    {
        // do nothing, we do not remove element from this resource
        return $this;
    }
}
