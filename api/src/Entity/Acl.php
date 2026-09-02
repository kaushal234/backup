<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Serializer\Filter\GroupFilter;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\Directory\Location;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Doctrine\Transformer\ObjectToLegacyProperty;
use LegacyBundle\Doctrine\Transformer\ObjectToProperty;
use LegacyBundle\Entity\LegacyIdentifierTrait;
use LegacyBundle\Entity\LegacyIdInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An Acl.
 */
#[ORM\Entity(repositoryClass: 'App\Repository\AclRepository')]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Delete(security: "is_granted('FEATURE_PEOPLE_ACL_VOTER', object)"),
    ],
    normalizationContext: ['groups' => ['acl', 'group', 'expose_legacy', 'people_public']],
    denormalizationContext: ['groups' => ['acl_write']]
)]
#[ORM\Table]
#[ApiFilter(OrderFilter::class, properties: ['group.name' => 'ASC'])]
#[ApiFilter(SearchFilter::class, properties: ['user' => 'exact', 'legacyId' => 'exact', 'group.features.name' => 'exact', 'group.name' => 'exact', 'location.name' => 'exact'])]
#[ApiFilter(GroupFilter::class, arguments: ['parameterName' => 'normalization_groups_override', 'overrideDefaultGroups' => true, 'whitelist' => ['acl_list']])]
#[App\Loggable(owner: 'user', ownerRelation: 'acl')]
#[Legacy\Synchronize(table: 'people_groups')]
class Acl implements \Stringable, LegacyIdInterface
{
    use LegacyIdentifierTrait;
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['acl'])]
    private int $id;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People', inversedBy: 'acls')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    #[Groups(['acl', 'acl_write'])]
    #[Legacy\Column(column: 'parent_id', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    #[Legacy\Column(column: 'email', transformer: ObjectToProperty::class, options: ['property' => 'email'])]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Group', inversedBy: 'acls')]
    #[ORM\JoinColumn(name: 'group_id', referencedColumnName: 'id')]
    #[Groups(['acl', 'acl_write', 'group_member', 'acl_list'])]
    #[Legacy\Column(column: 'group_name', transformer: ObjectToLegacyProperty::class, options: ['property' => 'group_name'])]
    private ?Group $group = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Location')]
    #[ORM\JoinColumn(referencedColumnName: 'id')]
    #[Groups(['acl', 'acl_write', 'group_member', 'acl_list'])]
    #[Legacy\Column(column: 'level', transformer: ObjectToProperty::class, options: ['property' => 'erp'])]
    private ?Location $location = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Assert\GreaterThan('yesterday')]
    #[Groups(['acl', 'acl_write', 'group_member'])]
    private ?\DateTime $expiredAt;

    public function __toString()
    {
        return $this->group.(null !== $this->location ? ' ('.$this->location->getName().')' : '');
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getGroup(): ?Group
    {
        return $this->group;
    }

    public function setGroup(Group $group): self
    {
        $this->group = $group;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location = null): self
    {
        $this->location = $location;

        return $this;
    }

    public function getExpiredAt(): ?\DateTime
    {
        return $this->expiredAt;
    }

    public function setExpiredAt(?\DateTime $expiredAt): self
    {
        $this->expiredAt = $expiredAt;

        return $this;
    }
}
