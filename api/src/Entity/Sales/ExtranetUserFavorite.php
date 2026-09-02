<?php

declare(strict_types=1);

namespace App\Entity\Sales;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\Sales\ExtranetUser\ExtranetUserFavoriteController;
use App\Doctrine\Mapping\Attributes as App;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Doctrine\Transformer\ObjectToProperty;
use LegacyBundle\Entity\LegacyIdentifierTrait;
use LegacyBundle\Entity\LegacyIdInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(controller: ExtranetUserFavoriteController::class),
        new Get(security: "is_granted('ACCESS_PEOPLE') or is_granted('FEATURE_EXTRANET_USER_EDIT', object) or is_granted('FEATURE_EXTRANET_USER_FAVORITE', object)"),
        new Delete(security: "is_granted('FEATURE_EXTRANET_USER_DELETE', object) or is_granted('FEATURE_EXTRANET_USER_FAVORITE', object)"),
        new Put(securityPostDenormalize: "is_granted('FEATURE_EXTRANET_USER_EDIT', previous_object) or is_granted('FEATURE_EXTRANET_USER_FAVORITE', previous_object)"),
    ],
    routePrefix: 'sales',
    normalizationContext: ['groups' => ['extranet_user_favorite', 'extranet_user', 'address', 'people_public', 'expose_legacy']],
    denormalizationContext: ['groups' => ['extranet_user_favorite_write']],
    security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_EXTRANET_USER')",
)]
#[ORM\Table(name: 'extranet_user_favorite')]
#[ApiFilter(SearchFilter::class, properties: ['legacyId' => 'exact'])]
#[App\Loggable]
#[Legacy\Synchronize(table: 'extranet_users_fav')]
class ExtranetUserFavorite implements LegacyIdInterface
{
    use LegacyIdentifierTrait;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['extranet_user_favorite', 'extranet_user_favorite_write'])]
    private int $id;

    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\ExtranetUser', inversedBy: 'extranetUserFavorites')]
    #[Groups(['extranet_user_favorite', 'extranet_user_favorite_write'])]
    #[Legacy\Column(column: 'parent_id', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    private ExtranetUser $extranetUser;

    /**
     * Aero username : Only for Fedex !
     */
    #[ORM\Column(nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Groups(['extranet_user_favorite', 'extranet_user_favorite_write'])]
    #[Legacy\Column(column: 'fav_user')]
    private ?string $aeroUsername = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Groups(['extranet_user_favorite', 'extranet_user_favorite_write'])]
    #[Legacy\Column(column: 'fav_pn')]
    private ?string $partNumber = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Groups(['extranet_user_favorite', 'extranet_user_favorite_write'])]
    #[Legacy\Column(column: 'fav_dsca')]
    private ?string $description = null;

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

    public function getAeroUsername(): ?string
    {
        return $this->aeroUsername;
    }

    /**
     * @return $this
     */
    public function setAeroUsername(?string $aeroUsername): self
    {
        $this->aeroUsername = $aeroUsername;

        return $this;
    }

    public function getPartNumber(): ?string
    {
        return $this->partNumber;
    }

    /**
     * @return $this
     */
    public function setPartNumber(?string $partNumber): self
    {
        $this->partNumber = $partNumber;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return $this
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }
}
