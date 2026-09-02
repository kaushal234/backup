<?php

declare(strict_types=1);

namespace App\Entity\Common;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Serializer\Filter\GroupFilter;
use App\Doctrine\Mapping\Attributes\Transferable;
use App\Entity\User;
use App\Filter\SimpleSearchFilter;
use App\Filter\SubscriptionModuleFilter;
use App\Validator\Constraints\ResourceExists;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: 'App\Repository\Common\SubscriptionRepository')]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(securityPostDenormalize: "is_granted('SUBSCRIPTION_CREATE_VOTER', object)"),
        new Get(),
        new Delete(security: "is_granted('SUBSCRIPTION_DELETE_VOTER', object)"),
    ],
    normalizationContext: ['groups' => ['follower', 'people_public']],
    denormalizationContext: ['groups' => ['follower:write']],
    security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_EXTRANET_USER')",
)]
#[UniqueEntity(fields: ['user', 'resource'])]
#[ORM\Table(name: 'subscriptions')]
#[ORM\Index(columns: ['resource'])]
#[ORM\UniqueConstraint(name: 'unique_subscription_per_resource', columns: ['resource', 'user_id'])]
#[ApiFilter(SearchFilter::class, properties: ['resource', 'user'])]
#[ApiFilter(SimpleSearchFilter::class, properties: ['resource', 'user.lastname'])]
#[ApiFilter(OrderFilter::class, properties: ['createdAt', 'user.lastname'])]
#[ApiFilter(DateFilter::class, properties: ['createdAt'])]
#[ApiFilter(SubscriptionModuleFilter::class)]
#[ApiFilter(GroupFilter::class, arguments: ['parameterName' => 'normalization_groups', 'overrideDefaultGroups' => false, 'whitelist' => ['people_photo', 'file:light']])]
class Subscription
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\Type(type: 'string')]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Groups(['follower', 'follower:write'])]
    #[ResourceExists]
    private string $resource;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\User')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['follower', 'follower:write', 'people_public'])]
    #[Transferable(handler: 'handler.follower')]
    private User $user;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['follower'])]
    #[Gedmo\Timestampable(on: 'create')]
    private \DateTimeInterface $createdAt;

    public function getId(): int
    {
        return $this->id;
    }

    public function getResource(): string
    {
        return $this->resource;
    }

    public function setResource(string $resource): self
    {
        $this->resource = $resource;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
