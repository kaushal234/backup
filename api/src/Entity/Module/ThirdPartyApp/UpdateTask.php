<?php

declare(strict_types=1);

namespace App\Entity\Module\ThirdPartyApp;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
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
use App\Controller\MIS\ThirdPartyApp\SyncUpdateTaskController;
use App\Entity\Directory\People;
use App\Entity\Module\ThirdPartyApp\Type\Extended;
use App\Entity\User;
use App\Filter\MIS\Module\UpdateTaskSupportTeamFilter;
use App\Filter\MIS\UpdateTaskStatusFilter;
use App\Filter\SimpleSearchFilter;
use App\Repository\Module\ThirdPartyApp\UpdateTaskRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Attribute\Groups;

/**
 * This resource use a doctrine extension to manage permissions directly on data.
 * That's why you don't have any security configuration on the two GetCollection.
 */
#[ORM\Entity(repositoryClass: UpdateTaskRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(
            denormalizationContext: ['groups' => ['update_task:create']],
        ),
        new Get(
            security: "is_granted('FEATURE_THIRD_PARTY_APP_ADMIN_VOTER', object.thirdPartyApp)",
        ),
        new Put(
            denormalizationContext: ['groups' => ['update_task:update']],
            security: "is_granted('FEATURE_THIRD_PARTY_APP_ADMIN_VOTER', object.thirdPartyApp)",
        ),
        new GetCollection(
            uriTemplate: '/{thirdPartyAppId}/update_tasks',
            uriVariables: [
                'thirdPartyAppId' => new Link(
                    toProperty: 'thirdPartyApp',
                    fromClass: Extended::class,
                ),
            ],
        ),
        new Delete(
            security: "is_granted('FEATURE_MODULE_WRITE')",
        ),
        new Post(
            uriTemplate: '/sync_update_tasks/{userId}',
            controller: SyncUpdateTaskController::class,
            security: "is_granted('FEATURE_MODULE_WRITE')",
            output: false,
            read: false,
        ),
    ],
    routePrefix: '/modules/third_party_app',
    normalizationContext: ['groups' => ['update_task:read', 'module', 'module_light', 'people', 'people_public', 'business_unit', 'application', 'guest', 'update_task:light']],
    order: ['done' => 'ASC', 'updatedAt' => 'DESC'],
)]
#[ORM\Table(name: 'third_party_app_update_task')]
#[ApiFilter(SearchFilter::class, properties: [
    'thirdPartyApp',
    'user',
    'done',
    'confirmed',
    'updatedBy',
    'originType',
    'demandType',
    'user.businessUnit',
])]
#[ApiFilter(DateFilter::class, properties: ['updatedAt'])]
#[ApiFilter(SimpleSearchFilter::class, properties: [
    'user.firstname' => 'partial',
    'user.lastname' => 'partial',
    'user.businessUnit.name' => 'partial',
])]
#[ApiFilter(OrderFilter::class, properties: [
    'user.lastname',
    'user.firstname',
    'user.businessUnit.name',
    'createdAt',
    'updatedAt',
    'updatedBy.lastname',
    'originType',
    'demandType',
])]
#[ApiFilter(UpdateTaskStatusFilter::class)]
#[ApiFilter(UpdateTaskSupportTeamFilter::class)]
class UpdateTask
{
    final public const GRANT_ACCESS = 'GRANT_ACCESS';
    final public const REMOVE_ACCESS = 'REMOVE_ACCESS';

    final public const ORIGIN_TYPE_BUSINESS_UNIT_POSITION = 'BUSINESS_UNIT_POSITION';
    final public const ORIGIN_TYPE_WHITELIST = 'WHITELIST';
    final public const ORIGIN_TYPE_BLACKLIST = 'BLACKLIST';
    final public const ORIGIN_TYPE_USER = 'USER';
    final public const ORIGIN_TYPE_MANUAL = 'MANUAL';

    #[ORM\Column(length: 15)]
    #[Groups(['update_task:read', 'update_task:create'])]
    public string $demandType = self::GRANT_ACCESS;

    /**
     * Origin of the task.
     * Created from business unit/position or whitelist.
     * Or created when we remove a user from the member list for ex.
     */
    #[ORM\Column(nullable: true)]
    #[Groups(['update_task:read', 'update_task:create'])]
    public ?string $originType = null;

    /**
     * TRUE = update task is closed.
     * FALSE = update tasks is open and in progress.
     */
    #[ORM\Column(type: 'boolean')]
    #[Groups(['update_task:read', 'update_task:update', 'update_task:light'])]
    public bool $done = false;

    /**
     * If the admin confirm or denied the demand.
     */
    #[ORM\Column(type: 'boolean')]
    #[Groups(['update_task:read', 'update_task:update', 'update_task:light'])]
    public bool $confirmed = false;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'updateTasks')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(['update_task:read', 'update_task:create'])]
    public User $user;

    #[ORM\ManyToOne(targetEntity: Extended::class, inversedBy: 'updateTasks')]
    #[Groups(['update_task:read', 'update_task:create'])]
    #[MaxDepth(1)]
    public Extended $thirdPartyApp;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    #[Groups(['update_task:read'])]
    #[Gedmo\Timestampable(on: 'create')]
    public \DateTimeInterface $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime')]
    #[Groups(['update_task:read'])]
    #[Gedmo\Timestampable(on: 'update')]
    public \DateTimeInterface $updatedAt;

    #[ORM\ManyToOne(targetEntity: People::class)]
    #[ORM\JoinColumn(name: 'created_by')]
    #[Groups(['update_task:read'])]
    #[Gedmo\Blameable(on: 'create')]
    public People $createdBy;

    #[ORM\ManyToOne(targetEntity: People::class)]
    #[ORM\JoinColumn(name: 'updated_by', referencedColumnName: 'id')]
    #[Groups(['update_task:read'])]
    #[Gedmo\Blameable(on: 'update')]
    public ?People $updatedBy;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['update_task:read', 'update_task:update'])]
    public ?string $comment = null;

    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['update_task:read', 'update_task:light'])]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }
}
