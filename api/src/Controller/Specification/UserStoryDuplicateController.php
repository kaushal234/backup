<?php

declare(strict_types=1);

namespace App\Controller\Specification;

use App\Entity\Module\Specification\UserStory;

class UserStoryDuplicateController
{
    public function __invoke(UserStory $userStory): UserStory
    {
        return clone $userStory;
    }
}
