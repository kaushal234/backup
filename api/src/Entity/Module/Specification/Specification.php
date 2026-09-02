<?php

declare(strict_types=1);

namespace App\Entity\Module\Specification;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Serializer\Filter\GroupFilter;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\Module\Module;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[App\Loggable]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(
            denormalizationContext: ['groups' => ['specification:create']],
            securityPostDenormalize: "is_granted('FEATURE_SPECIFICATION_WRITE_VOTER', object)",
        ),
        new Put(
            uriTemplate: '/specifications/{id}/status',
            denormalizationContext: ['groups' => ['specification_status']],
            security: "is_granted('FEATURE_SPECIFICATION_UPDATE_STATUS')",
            name: 'update_specification_status',
        ),
    ],
    routePrefix: 'mis',
    normalizationContext: ['groups' => ['specification', 'notification', 'email', 'access', 'group', 'file', 'module_light', 'user_story:light', 'people_public']],
    denormalizationContext: ['groups' => ['specification:edit']]
)]
#[ApiFilter(GroupFilter::class, arguments: ['parameterName' => 'normalizationGroups', 'overrideDefaultGroups' => false, 'whitelist' => ['workflow']])]
class Specification
{
    // WORKFLOW
    final public const DEVELOPMENT = 'DEVELOPMENT';
    final public const PRODUCTION = 'PRODUCTION';

    #[ORM\OneToOne(inversedBy: 'specification', targetEntity: Module::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['specification', 'specification:create'])]
    #[MaxDepth(1)]
    public Module $module;

    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['specification', 'module'])]
    private int $id;

    #[ORM\OneToMany(mappedBy: 'specification', targetEntity: UserStory::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['specification', 'specification:edit'])]
    private Collection $userStories;

    #[ORM\Column(type: 'string')]
    #[Groups(['specification', 'specification_status'])]
    #[Assert\Choice(choices: [self::DEVELOPMENT, self::PRODUCTION])]
    private string $status = self::DEVELOPMENT;

    public function __construct()
    {
        $this->userStories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getUserStories(): Collection
    {
        return $this->userStories;
    }

    public function addUserStory(UserStory $userStories): self
    {
        $this->userStories->add($userStories);
        $userStories->specification = $this;

        return $this;
    }

    public function removeUserStory(UserStory $userStories): self
    {
        $this->userStories->removeElement($userStories);

        return $this;
    }

    public function getModule(): Module
    {
        return $this->module;
    }

    public function setModule(Module $module): self
    {
        $this->module = $module;
        $module->specification = $this;

        return $this;
    }
}
