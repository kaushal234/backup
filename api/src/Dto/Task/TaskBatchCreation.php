<?php

declare(strict_types=1);

namespace App\Dto\Task;

use App\Entity\Communication\ContactCampaign;
use App\Entity\Module\Module;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class TaskBatchCreation
{
    #[Groups(['batch_task:write'])]
    public string $type;

    #[Groups(['batch_task:write'])]
    public Module $module;

    #[Groups(['batch_task:write'])]
    public array $referenceId;

    #[Groups(['batch_task:write'])]
    #[Assert\NotNull(groups: ['contact_campaign'])]
    public ?ContactCampaign $campaign = null;
}
