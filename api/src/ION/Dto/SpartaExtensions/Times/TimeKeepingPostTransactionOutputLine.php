<?php

declare(strict_types=1);

namespace App\ION\Dto\SpartaExtensions\Times;

use Symfony\Component\Serializer\Attribute\Groups;

class TimeKeepingPostTransactionOutputLine
{
    #[Groups(['time_keeping'])]
    public string $employeeNumber;

    #[Groups(['time_keeping'])]
    public string $firstname;

    #[Groups(['time_keeping'])]
    public string $lastname;

    #[Groups(['time_keeping'])]
    public string $transactionType;

    #[Groups(['time_keeping'])]
    public int $year;

    #[Groups(['time_keeping'])]
    public int $period;

    #[Groups(['time_keeping'])]
    public int $sequenceNumber;

    #[Groups(['time_keeping'])]
    public ?string $startTime = null;

    #[Groups(['time_keeping'])]
    public ?string $endTime = null;

    #[Groups(['time_keeping'])]
    public string $status;

    #[Groups(['time_keeping'])]
    public ?string $productionOrder = null;

    #[Groups(['time_keeping'])]
    public ?string $operationNumber = null;

    #[Groups(['time_keeping'])]
    public ?string $task = null;
}
