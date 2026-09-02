<?php

declare(strict_types=1);

namespace App\Entity\Module\ThirdPartyApp;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Serializer\Filter\GroupFilter;
use App\Entity\Module\ThirdPartyApp\Type\Extended;
use App\Entity\User;
use App\Filter\ColumnsFilter;
use App\Filter\SimpleSearchFilter;
use App\Repository\Module\ThirdPartyApp\MemberRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: MemberRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(
            formats: ['jsonld', 'json', 'csv', 'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']],
            paginationItemsPerPage: 100,
        ),
        new Post(securityPostDenormalize: "is_granted('FEATURE_THIRD_PARTY_APP_ADMIN_VOTER', object)"),
        new Put(security: "is_granted('FEATURE_THIRD_PARTY_APP_ADMIN_VOTER', object)"),
        new Get(),
        new GetCollection(
            uriTemplate: '/{thirdPartyAppId}/members',
            formats: ['jsonld', 'json', 'csv', 'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']],
            uriVariables: [
                'thirdPartyAppId' => new Link(
                    toProperty: 'thirdPartyApp',
                    fromClass: Extended::class,
                ),
            ],
        ),
        new Delete(security: "is_granted('FEATURE_THIRD_PARTY_APP_ADMIN_VOTER', object)"),
    ],
    routePrefix: '/modules/third_party_app',
    normalizationContext: ['groups' => ['member:read', 'module', 'module_light', 'business_unit', 'people', 'people_public', 'group_member', 'location_public', 'application', 'guest']],
    order: ['user.lastname' => 'ASC', 'user.firstname' => 'ASC'],
)]
#[ORM\Table(name: 'third_party_app_member')]
#[ApiFilter(OrderFilter::class, properties: ['user.lastname', 'user.firstname', 'admin', 'user.businessUnit.name', 'user.position.description'])]
#[ApiFilter(SearchFilter::class, properties: [
    'thirdPartyApp',
    'admin',
    'user',
    'user.firstname' => 'partial',
    'user.lastname' => 'partial',
    'user.businessUnit',
    'user.position',
])]
#[ApiFilter(SimpleSearchFilter::class, properties: [
    'user.firstname' => 'partial',
    'user.lastname' => 'partial',
    'user.businessUnit.name' => 'partial',
    'user.position.description' => 'partial',
])]
#[ApiFilter(ColumnsFilter::class)]
#[ApiFilter(GroupFilter::class, id: 'override', arguments: ['parameterName' => 'normalizationGroupsOverride', 'overrideDefaultGroups' => true, 'whitelist' => ['member:export', 'people_public', 'people:export']])]
class Member
{
    #[ORM\Column(type: 'boolean')]
    #[Groups(['member:read', 'member:export'])]
    public bool $admin = false;
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['member:read'])]
    private int $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'appMembers')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(['member:read', 'member:export'])]
    #[MaxDepth(1)]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Extended::class, inversedBy: 'members')]
    #[Groups(['member:read'])]
    private Extended $thirdPartyApp;

    public function getId(): int
    {
        return $this->id;
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

    public function getThirdPartyApp(): Extended
    {
        return $this->thirdPartyApp;
    }

    public function setThirdPartyApp(Extended $thirdPartyApp): self
    {
        $this->thirdPartyApp = $thirdPartyApp;

        return $this;
    }
}
