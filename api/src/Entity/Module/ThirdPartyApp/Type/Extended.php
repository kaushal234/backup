<?php

declare(strict_types=1);

namespace App\Entity\Module\ThirdPartyApp\Type;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\MIS\ThirdPartyApp\AddUserToBlacklistController;
use App\Controller\MIS\ThirdPartyApp\AddUserToWhitelistController;
use App\Controller\MIS\ThirdPartyApp\MoveFromBlacklistController;
use App\Controller\MIS\ThirdPartyApp\MoveToBlacklistController;
use App\Controller\MIS\ThirdPartyApp\MoveToWhitelistController;
use App\Controller\MIS\ThirdPartyApp\RemoveUserToBlacklistController;
use App\Controller\MIS\ThirdPartyApp\RemoveUserToWhitelistController;
use App\Dto\MIS\Module\UserListThirdPartyApp;
use App\Entity\Module\ThirdPartyApp\BusinessUnitPosition;
use App\Entity\Module\ThirdPartyApp\Member;
use App\Entity\Module\ThirdPartyApp\UpdateTask;
use App\Entity\User;
use App\Repository\Module\ThirdPartyApp\ExtendedRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ExtendedRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['module', 'people_public', 'people_photo', 'file:light', 'expose_legacy', 'department_list', 'type_assignee', 'third_party_app', 'business_unit', 'security_level', 'application']],
        ),
        new Post(
            uriTemplate: '/modules/third_party_app/extended',
            security: "is_granted('FEATURE_MODULE_WRITE')",
        ),
        new Get(),
        new Put(
            security: "is_granted('FEATURE_MODULE_WRITE')",
        ),
        new Post(
            uriTemplate: '/modules/whitelist/add',
            controller: AddUserToWhitelistController::class,
            normalizationContext: ['groups' => ['module', 'people_public', 'security_level']],
            denormalizationContext: ['groups' => ['extended_list:write']],
            securityPostDenormalize: "is_granted('FEATURE_MODULE_WRITE') or is_granted('FEATURE_THIRD_PARTY_APP_ADMIN_VOTER', object)",
            input: UserListThirdPartyApp::class,
            validate: true,
        ),
        new Post(
            uriTemplate: '/modules/whitelist/remove',
            controller: RemoveUserToWhitelistController::class,
            normalizationContext: ['groups' => ['module', 'people_public', 'security_level']],
            denormalizationContext: ['groups' => ['extended_list:write']],
            securityPostDenormalize: "is_granted('FEATURE_MODULE_WRITE') or is_granted('FEATURE_THIRD_PARTY_APP_ADMIN_VOTER', object)",
            input: UserListThirdPartyApp::class,
            validate: true,
        ),
        new Post(
            uriTemplate: '/modules/blacklist/add',
            controller: AddUserToBlacklistController::class,
            normalizationContext: ['groups' => ['module', 'people_public', 'security_level']],
            denormalizationContext: ['groups' => ['extended_list:write']],
            securityPostDenormalize: "is_granted('FEATURE_MODULE_WRITE') or is_granted('FEATURE_THIRD_PARTY_APP_ADMIN_VOTER', object)",
            input: UserListThirdPartyApp::class,
            validate: true,
        ),
        new Post(
            uriTemplate: '/modules/blacklist/remove',
            controller: RemoveUserToBlacklistController::class,
            normalizationContext: ['groups' => ['module', 'people_public', 'security_level']],
            denormalizationContext: ['groups' => ['extended_list:write']],
            securityPostDenormalize: "is_granted('FEATURE_MODULE_WRITE') or is_granted('FEATURE_THIRD_PARTY_APP_ADMIN_VOTER', object)",
            input: UserListThirdPartyApp::class,
            validate: true,
        ),
        new Post(
            uriTemplate: '/modules/whitelist/move',
            controller: MoveToWhitelistController::class,
            normalizationContext: ['groups' => ['module', 'people_public', 'security_level']],
            denormalizationContext: ['groups' => ['extended_list:write']],
            securityPostDenormalize: "is_granted('FEATURE_MODULE_WRITE') or is_granted('FEATURE_THIRD_PARTY_APP_ADMIN_VOTER', object)",
            input: UserListThirdPartyApp::class,
            validate: true,
        ),
        new Post(
            uriTemplate: '/modules/blacklist/move',
            controller: MoveToBlacklistController::class,
            normalizationContext: ['groups' => ['module', 'people_public', 'security_level']],
            denormalizationContext: ['groups' => ['extended_list:write']],
            securityPostDenormalize: "is_granted('FEATURE_MODULE_WRITE') or is_granted('FEATURE_THIRD_PARTY_APP_ADMIN_VOTER', object)",
            input: UserListThirdPartyApp::class,
            validate: true,
        ),
        new Post(
            uriTemplate: '/modules/blacklist/move_from',
            controller: MoveFromBlacklistController::class,
            normalizationContext: ['groups' => ['module', 'people_public', 'security_level']],
            denormalizationContext: ['groups' => ['extended_list:write']],
            securityPostDenormalize: "is_granted('FEATURE_MODULE_WRITE') or is_granted('FEATURE_THIRD_PARTY_APP_ADMIN_VOTER', object)",
            input: UserListThirdPartyApp::class,
            validate: true,
        ),
    ],
    normalizationContext: ['groups' => ['module', 'module_detail', 'people_public', 'people_photo', 'file:light', 'expose_legacy', 'department_list', 'type_assignee', 'application', 'security_level', 'business_unit']],
    denormalizationContext: ['groups' => ['module_write']],
)]
#[ApiFilter(SearchFilter::class, properties: [
    'name' => 'partial',
])]
class Extended extends Light
{
    #[ORM\OneToMany(targetEntity: BusinessUnitPosition::class, mappedBy: 'thirdPartyApp')]
    private Collection $businessUnitPositions;

    #[ORM\ManyToMany(targetEntity: User::class)]
    #[ORM\JoinTable(name: 'third_party_app_whitelist',
        joinColumns: [new ORM\JoinColumn(name: 'extended_id', referencedColumnName: 'id', onDelete: 'CASCADE')],
        inverseJoinColumns: [new ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    )]
    #[Groups(['module_detail'])]
    private Collection $whitelistedUsers;

    #[ORM\ManyToMany(targetEntity: User::class)]
    #[ORM\JoinTable(name: 'third_party_app_blacklist',
        joinColumns: [new ORM\JoinColumn(name: 'extended_id', referencedColumnName: 'id', onDelete: 'CASCADE')],
        inverseJoinColumns: [new ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    )]
    #[Groups(['module_detail'])]
    private Collection $blacklistedUsers;

    #[ORM\OneToMany(targetEntity: Member::class, mappedBy: 'thirdPartyApp')]
    private Collection $members;

    #[ORM\OneToMany(targetEntity: UpdateTask::class, mappedBy: 'thirdPartyApp')]
    private Collection $updateTasks;

    public function __construct()
    {
        parent::__construct();
        $this->businessUnitPositions = new ArrayCollection();
        $this->whitelistedUsers = new ArrayCollection();
        $this->blacklistedUsers = new ArrayCollection();
        $this->members = new ArrayCollection();
        $this->updateTasks = new ArrayCollection();
    }

    #[Groups(['count_update_tasks'])]
    public function getCountUpdateTasks(): int
    {
        return $this->updateTasks
            ->filter(static fn (UpdateTask $updateTask) => !$updateTask->done)
            ->count();
    }

    public function getBusinessUnitPositions(): Collection
    {
        return $this->businessUnitPositions;
    }

    public function getWhitelistedUsers(): Collection
    {
        return $this->whitelistedUsers;
    }

    public function addWhitelistedUser(User $whitelistedUser): self
    {
        if (!$this->whitelistedUsers->contains($whitelistedUser)) {
            $this->whitelistedUsers->add($whitelistedUser);
        }

        return $this;
    }

    public function removeWhitelistedUser(User $whitelistedUser): self
    {
        if ($this->whitelistedUsers->contains($whitelistedUser)) {
            $this->whitelistedUsers->removeElement($whitelistedUser);
        }

        return $this;
    }

    public function isWhitelisted(User $user): bool
    {
        return $this->whitelistedUsers->contains($user);
    }

    public function getBlacklistedUsers(): Collection
    {
        return $this->blacklistedUsers;
    }

    public function addBlacklistedUser(User $blacklistedUser): self
    {
        if (!$this->blacklistedUsers->contains($blacklistedUser)) {
            $this->blacklistedUsers->add($blacklistedUser);
        }

        return $this;
    }

    public function removeBlacklistedUser(User $blacklistedUser): self
    {
        if ($this->blacklistedUsers->contains($blacklistedUser)) {
            $this->blacklistedUsers->removeElement($blacklistedUser);
        }

        return $this;
    }

    public function isBlacklisted(User $user): bool
    {
        return $this->blacklistedUsers->contains($user);
    }

    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function getUpdateTasks(): Collection
    {
        return $this->updateTasks;
    }
}
