<?php

declare(strict_types=1);

namespace App\ION\Resources\Manufacturing\JobShop;

use Symfony\Component\Serializer\Attribute\Groups;

class Operation
{
    #[Groups(['project'])]
    public string $operationIdentifier;

    #[Groups(['project'])]
    public string $status;

    #[Groups(['project'])]
    public string $reference;

    #[Groups(['project'])]
    public string $referenceDescription;

    #[Groups(['project'])]
    public string $task;
}
