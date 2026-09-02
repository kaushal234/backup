<?php

declare(strict_types=1);

namespace App\Dto\MIS\Module;

use App\Entity\Module\ThirdPartyApp\Type\Extended;
use App\Entity\User;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class UserListThirdPartyApp
{
    #[Assert\NotNull]
    #[Groups(['extended_list:write'])]
    public User $user;

    #[Assert\NotNull]
    #[Groups(['extended_list:write'])]
    public Extended $module;
}
