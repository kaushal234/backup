<?php

declare(strict_types=1);

namespace App\Entity\Sales;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Serializer\Filter\GroupFilter;
use App\Doctrine\Mapping\Attributes as App;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Doctrine\Transformer\ObjectToProperty;
use LegacyBundle\Entity\LegacyIdentifierTrait;
use LegacyBundle\Entity\LegacyIdInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ExtranetUserAcl.
 */
#[UniqueEntity(fields: ['extranetUser', 'crt', 'extranetUserGroup'])]
#[ORM\Entity(repositoryClass: 'App\Repository\Sales\ExtranetUserAclRepository')]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(security: "is_granted('FEATURE_EXTRANET_USER_CREATE')"),
        new Get(),
        new Delete(),
    ],
    routePrefix: 'sales',
    normalizationContext: ['groups' => ['extranet_user_acl', 'extranet_user_group', 'customer_relationship_team', 'people_public', 'location_public', 'expose_legacy']],
    denormalizationContext: ['groups' => ['extranet_user_acl_write']],
    security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_EXTRANET_USER')"
)]
#[ORM\Table(name: 'extranet_user_acl')]
#[ApiFilter(OrderFilter::class, properties: ['id' => 'DESC'])]
#[ApiFilter(SearchFilter::class, properties: ['extranetUser' => 'exact', 'legacyId' => 'exact', 'crt' => 'exact', 'crt.customer' => 'exact', 'crt.erpLocation' => 'exact', 'extranetUserGroup.name' => 'exact'])]
#[ApiFilter(GroupFilter::class, arguments: ['parameterName' => 'normalizationGroups', 'overrideDefaultGroups' => false, 'whitelist' => ['location', 'location_address', 'address', 'people:business_unit', 'business_unit_public', 'file:light', 'people_photo']])]
#[App\Loggable]
#[Legacy\Synchronize(table: 'extranet_users_roles')]
class ExtranetUserAcl implements LegacyIdInterface
{
    use LegacyIdentifierTrait;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['extranet_user_acl', 'extranet_user_acl_write'])]
    private int $id;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\ExtranetUser', inversedBy: 'extranetUserAcls')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['extranet_user_acl', 'extranet_user_acl_write'])]
    #[Legacy\Column(column: 'parent_id', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    private ExtranetUser $extranetUser;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\CustomerRelationshipTeam', cascade: ['persist'], inversedBy: 'acls')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['extranet_user_acl', 'extranet_user_acls', 'extranet_user_acl_write'])]
    #[Legacy\Column(column: 'crt_id', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    private CustomerRelationshipTeam $crt;

    #[ApiProperty(iris: ['https://schema.org/text'])]
    #[ORM\Column(type: 'string', length: 3, nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 3)]
    private ?string $cDel = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\ExtranetUserGroup')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['extranet_user_acl', 'extranet_user_acl_write', 'extranet_user_acls', 'extranet_user_fetch_eager'])]
    #[Legacy\Column(column: 'role', transformer: ObjectToProperty::class, options: ['property' => 'name'])]
    private ExtranetUserGroup $extranetUserGroup;

    public function __toString(): string
    {
        return \sprintf('%s (CRT#%d)', $this->extranetUserGroup->getName(), $this->crt->getId());
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getExtranetUser(): ExtranetUser
    {
        return $this->extranetUser;
    }

    /**
     * @return $this
     */
    public function setExtranetUser(ExtranetUser $extranetUser): self
    {
        $this->extranetUser = $extranetUser;

        return $this;
    }

    public function getCrt(): CustomerRelationshipTeam
    {
        return $this->crt;
    }

    /**
     * @return $this
     */
    public function setCrt(CustomerRelationshipTeam $crt): self
    {
        $this->crt = $crt;

        return $this;
    }

    public function getCDel(): ?string
    {
        return $this->cDel;
    }

    /**
     * @return $this
     */
    public function setCDel(?string $cDel): self
    {
        $this->cDel = $cDel;

        return $this;
    }

    public function getExtranetUserGroup(): ExtranetUserGroup
    {
        return $this->extranetUserGroup;
    }

    /**
     * @return $this
     */
    public function setExtranetUserGroup(ExtranetUserGroup $extranetUserGroup): self
    {
        $this->extranetUserGroup = $extranetUserGroup;

        return $this;
    }
}
