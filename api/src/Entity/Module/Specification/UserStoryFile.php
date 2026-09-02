<?php

declare(strict_types=1);

namespace App\Entity\Module\Specification;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\File;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(operations: [new Get()])]
#[ORM\Entity]
#[ORM\Table(name: 'user_story_files')]
#[App\Loggable(owner: 'userStory', ownerRelation: 'userStoryFiles')]
class UserStoryFile extends File
{
    #[ORM\ManyToOne(targetEntity: UserStory::class, inversedBy: 'userStoryFiles')]
    private ?UserStory $userStory = null;

    public function getUserStory(): UserStory
    {
        return $this->userStory;
    }

    public function setUserStory(UserStory $userStory): self
    {
        $this->userStory = $userStory;

        return $this;
    }
}
