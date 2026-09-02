<?php

declare(strict_types=1);

namespace App\ION\Dto\SpartaExtensions\Times;

use App\ION\DataProcessor\IONDataProcessor;
use Symfony\Component\Serializer\Attribute\Groups;

class TimeKeepingPostTransactionInput
{
    #[Groups(['time_keeping:write', IONDataProcessor::ION_SYNC])]
    public string $employeeNumber;

    #[Groups(['time_keeping:write', IONDataProcessor::ION_SYNC])]
    public string $transactionType;

    #[Groups(['time_keeping:write', IONDataProcessor::ION_SYNC])]
    public ?string $task = null;

    #[Groups(['time_keeping:write'])]
    public ?string $orderNumber = null;

    #[Groups(['time_keeping:write'])]
    public ?int $operationNumber = null;

    #[Groups(['time_keeping:write'])]
    public ?string $comment = null;

    #[Groups(['time_keeping:write'])]
    public ?\DateTime $endDate = null;

    #[Groups(['time_keeping:xml'])]
    private readonly \DateTime $timestamp;

    public function __construct()
    {
        $this->timestamp = new \DateTime();
    }

    public function getTimestamp(): \DateTime
    {
        return $this->timestamp;
    }
}
