<?php

declare(strict_types=1);

namespace App\Entity\Sales;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Serializer\Filter\GroupFilter;
use App\Filter\SimpleSearchFilter;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An Extranet User Group.
 */
#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(security: "is_granted('FEATURE_EXTRANET_USER_GROUP_EDIT', object)"),
        new Get(),
        new Put(security: "is_granted('FEATURE_EXTRANET_USER_GROUP_EDIT', object)"),
    ],
    routePrefix: 'sales',
    security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_EXTRANET_USER')",
)]
#[UniqueEntity(fields: ['name'])]
#[ORM\Table(name: 'extranet_user_group')]
#[ApiFilter(OrderFilter::class, properties: ['id' => 'DESC', 'name' => 'ASC', 'description' => 'ASC'])]
#[ApiFilter(SearchFilter::class, properties: ['name' => 'exact'])]
#[ApiFilter(SimpleSearchFilter::class, properties: ['name' => 'partial'])]
#[ApiFilter(GroupFilter::class, id: 'override', arguments: ['parameterName' => 'normalization_groups_override', 'overrideDefaultGroups' => true, 'whitelist' => ['extranet_user_group']])]
class ExtranetUserGroup
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['extranet_user_group', 'extranet_user_group_write'])]
    private int $id;

    #[ORM\Column(type: 'string', length: 50, unique: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    #[Groups(['extranet_user_group', 'extranet_user_acls', 'extranet_user_group_write', 'extranet_user_fetch_eager'])]
    private string $name;

    #[ORM\Column(type: 'string')]
    #[Assert\Type(type: 'string')]
    #[Assert\NotBlank]
    #[Groups(['extranet_user_group', 'extranet_user_group_write'])]
    private string $description;

    #[ORM\Column(type: 'boolean', options: ['default' => 1])]
    #[Assert\Type('boolean')]
    #[Groups(['extranet_user_group', 'extranet_user_acls', 'extranet_user_group_write', 'extranet_user_fetch_eager'])]
    private bool $public = true;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return $this
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function isPublic(): bool
    {
        return $this->public;
    }

    public function setPublic(bool $public): void
    {
        $this->public = $public;
    }
}
