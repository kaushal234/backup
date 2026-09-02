<?php

declare(strict_types=1);

namespace App\Entity\Module\Specification;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\File\DeleteController;
use App\Controller\File\DownloadController;
use App\Controller\File\UploadController;
use App\Controller\Specification\UserStoryDuplicateController;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\Directory\People;
use App\Jira\Resources\IssueInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Blameable;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * UserStory.
 */
#[ORM\Entity]
#[ApiResource(
    operations: [
        new Post(
            securityPostDenormalize: "is_granted('FEATURE_USER_STORY_WRITE_VOTER', object)"
        ),
        new GetCollection(
            formats: ['jsonld', 'json', 'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']],
        ),
        new Get(),
        new Put(
            security: "is_granted('FEATURE_USER_STORY_WRITE_VOTER', object)"
        ),
        new Delete(
            security: "is_granted('FEATURE_USER_STORY_WRITE_VOTER', object)"
        ),
        new Put(
            uriTemplate: '/user_stories/{id}/status',
            denormalizationContext: ['groups' => ['user_stories_status']],
            security: "is_granted('FEATURE_USER_STORY_UPDATE_STATUS')",
            name: 'update_user_story_status',
        ),
        new Post(
            uriTemplate: '/user_stories/{id}/duplicate',
            controller: UserStoryDuplicateController::class,
            name: 'duplicate_user_story',
        ),
        new Delete(
            uriTemplate: '/user_stories/{id}/files/{fileId}',
            defaults: ['parentProperty' => 'userStory', 'class' => UserStoryFile::class],
            controller: DeleteController::class,
            security: "is_granted('FEATURE_USER_STORY_WRITE_VOTER', object)",
            name: 'delete_user_story_file',
        ),
        new Get(
            uriTemplate: '/user_stories/{id}/files/{fileId}',
            defaults: ['parentProperty' => 'userStory', 'class' => UserStoryFile::class],
            controller: DownloadController::class,
            security: "is_granted('FEATURE_USER_STORY_WRITE_VOTER', object)",
            name: 'download_user_story_file',
        ),
        new Post(
            uriTemplate: '/user_stories/{id}/files',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            defaults: ['method' => 'getUserStoryFiles', 'class' => UserStoryFile::class],
            controller: UploadController::class,
            security: "is_granted('FEATURE_USER_STORY_WRITE_VOTER', object)",
            deserialize: false,
            name: 'upload_user_story_files',
        ),
    ],
    routePrefix: 'mis',
    normalizationContext: ['groups' => ['user_story', 'notification', 'email', 'access', 'group', 'file', 'people_public']],
    denormalizationContext: ['groups' => ['user_story:write', 'access:write', 'email:write', 'notification:write']]
)]
#[ApiFilter(SearchFilter::class, properties: [
    'specification',
])]
#[App\Loggable]
class UserStory implements IssueInterface
{
    // WORKFLOW
    final public const PENDING = 'PENDING';
    final public const PLANNED = 'PLANNED';
    final public const HAS_BEEN_EDITED = 'HAS BEEN EDITED';
    final public const DEVELOPMENT = 'DEVELOPMENT';
    final public const TESTING = 'TESTING';
    final public const VALIDATED = 'VALIDATED';
    final public const CATEGORIES = ['CREATE', 'READ', 'UPDATE', 'DELETE', 'REPORT'];

    // Force IT project on JIRA
    final public const JIRA_PROJECT_ID = 10026;

    #[ORM\ManyToOne(targetEntity: People::class)]
    #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'id')]
    #[Blameable(on: 'create')]
    #[Groups(['user_story', 'user_story:light', 'user_story:write'])]
    public People $createdBy;

    #[ORM\Column(type: 'string')]
    #[Assert\NotNull]
    #[Assert\Choice(choices: self::CATEGORIES)]
    #[Groups(['user_story', 'user_story:light', 'user_story:write'])]
    public string $category;

    #[ORM\Column(type: 'text')]
    #[Assert\NotNull]
    #[Groups(['user_story', 'user_story:light', 'user_story:write'])]
    public string $description;

    #[ORM\Column(type: 'simple_array', nullable: true)]
    #[Groups(['user_story', 'user_story:write'])]
    public array $peopleProperties = [];

    #[ORM\ManyToOne(targetEntity: Specification::class, inversedBy: 'userStories')]
    #[Assert\NotNull]
    #[Groups(['user_story', 'user_story:write'])]
    #[MaxDepth(1)]
    public Specification $specification;

    #[ORM\OneToMany(targetEntity: Email::class, mappedBy: 'userStory', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['user_story:write'])]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[Assert\Count(max: 1)]
    public Collection $emails;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['user_story'])]
    public ?string $jiraIssueNumber = null;

    #[ORM\OneToMany(targetEntity: Notification::class, mappedBy: 'userStory', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['user_story:write'])]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[Assert\Count(max: 1)]
    private Collection $notifications;

    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['user_story', 'user_story:light'])]
    private int $id;

    #[ORM\Column(type: 'string')]
    #[Groups(['user_story', 'user_story:light', 'user_stories_status'])]
    #[Assert\Choice(choices: [self::PENDING, self::PLANNED, self::HAS_BEEN_EDITED, self::DEVELOPMENT, self::TESTING, self::VALIDATED])]
    private string $status = self::PENDING;
    #[ORM\OneToMany(mappedBy: 'userStory', targetEntity: UserStoryFile::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['user_story'])]
    private Collection $userStoryFiles;
    #[ORM\OneToMany(mappedBy: 'userStory', targetEntity: RoleAccess::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['user_story', 'user_story:write'])]
    private Collection $roleAccesses;

    public function __construct()
    {
        $this->userStoryFiles = new ArrayCollection();
        $this->roleAccesses = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->emails = new ArrayCollection();
    }

    public function __clone(): void
    {
        $roleAccesses = $this->getRoleAccesses();
        $this->roleAccesses = new ArrayCollection();
        if (!$roleAccesses->isEmpty()) {
            foreach ($roleAccesses as $roleAccess) {
                $this->addRoleAccess(clone $roleAccess);
            }
        }
        $notifications = $this->getNotifications();
        $this->notifications = new ArrayCollection();
        if (!$notifications->isEmpty()) {
            foreach ($notifications as $notification) {
                $this->addNotification(clone $notification);
            }
        }
        $emails = $this->getEmails();
        $this->emails = new ArrayCollection();
        if (!$emails->isEmpty()) {
            foreach ($emails as $email) {
                $this->addEmail(clone $email);
            }
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getSpecification(): Specification
    {
        return $this->specification;
    }

    public function setSpecification(Specification $specification): self
    {
        $this->specification = $specification;

        return $this;
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

    public function getRoleAccesses(): Collection
    {
        return $this->roleAccesses;
    }

    public function addRoleAccess(RoleAccess $roleAccess): self
    {
        $this->roleAccesses->add($roleAccess);
        $roleAccess->userStory = $this;

        return $this;
    }

    public function removeRoleAccess(RoleAccess $roleAccess): self
    {
        $this->roleAccesses->removeElement($roleAccess);

        return $this;
    }

    public function getUserStoryFiles(): ?Collection
    {
        return $this->userStoryFiles;
    }

    public function addUserStoryFile(UserStoryFile $userStoryFile): self
    {
        $this->userStoryFiles->add($userStoryFile);
        $userStoryFile->setUserStory($this);

        return $this;
    }

    public function removeUserStoryFile(UserStoryFile $userStoryFile): self
    {
        $this->userStoryFiles->removeElement($userStoryFile);

        return $this;
    }

    public function addEmail(?Email $email): self
    {
        $this->emails->add($email);
        $email->userStory = $this;

        return $this;
    }

    public function getEmails(): ?Collection
    {
        return $this->emails;
    }

    #[Groups(['user_story'])]
    public function getCurrentEmail(): ?Email
    {
        if (0 === $this->emails->count()) {
            return null;
        }

        return $this->emails->first();
    }

    public function removeEmail(?Email $email): self
    {
        $this->emails->removeElement($email);

        return $this;
    }

    public function addNotification(?Notification $notification): self
    {
        $this->notifications->add($notification);
        $notification->userStory = $this;

        return $this;
    }

    public function getNotifications(): ?Collection
    {
        return $this->notifications;
    }

    #[Groups(['user_story'])]
    public function getCurrentNotification(): ?Notification
    {
        if (0 === $this->notifications->count()) {
            return null;
        }

        return $this->notifications->first();
    }

    public function removeNotification(?Notification $notification): self
    {
        $this->notifications->removeElement($notification);

        return $this;
    }

    #[Groups(['user_story'])]
    public function getProjectNumber(): int
    {
        return self::JIRA_PROJECT_ID;
    }
}
