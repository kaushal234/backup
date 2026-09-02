<?php

declare(strict_types=1);

namespace App\Dto\Sales;

use App\Entity\Sales\CustomerRelationshipTeam;
use App\Entity\Sales\ExtranetUser;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class ExtranetUserWithCrtInput
{
    #[Assert\NotBlank]
    #[Groups(['user_write', 'extranet_user_write', 'phone:write', 'user_profile:write'])]
    public ExtranetUser $extranetUser;

    #[Assert\NotBlank]
    #[Groups(['customer_relationship_team'])]
    public CustomerRelationshipTeam $customerRelationshipTeam;

    #[Assert\NotBlank]
    #[Groups(['extranet_user_write'])]
    public string $groupName;
}
