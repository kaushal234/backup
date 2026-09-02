<?php

declare(strict_types=1);

namespace App\Entity\Activity;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Serializer\Filter\GroupFilter;
use App\Doctrine\Mapping\Attributes\Exclude;
use App\Entity\User;
use App\Repository\Common\ActivityRepository;
use App\Validator\Constraints\ResourceExists;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Doctrine\Transformer\DateTimeToString;
use LegacyBundle\Doctrine\Transformer\IriToModule;
use LegacyBundle\Doctrine\Transformer\IriToProperty;
use LegacyBundle\Doctrine\Transformer\ObjectToProperty;
use LegacyBundle\Entity\LegacyIdentifierTrait;
use LegacyBundle\Entity\LegacyIdInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A user activity.
 */
#[ORM\Entity(repositoryClass: ActivityRepository::class)]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'discr', type: 'string')]
#[ORM\DiscriminatorMap(['log' => 'Log', 'comment' => 'Comment', 'user_connection' => 'UserConnection'])]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
    ],
    normalizationContext: ['groups' => ['activity', 'people_public', 'expose_legacy']],
    denormalizationContext: ['groups' => ['activity', 'public']],
)]
#[ORM\Table(name: 'activity')]
#[ORM\Index(columns: ['resource'])]
#[ApiFilter(OrderFilter::class, properties: ['createdAt'])]
#[ApiFilter(SearchFilter::class, properties: ['resource' => 'exact', 'legacyId' => 'exact', 'user' => 'exact'])]
#[ApiFilter(DateFilter::class, properties: ['createdAt'])]
#[ApiFilter(GroupFilter::class, arguments: ['parameterName' => 'normalizationGroups', 'whitelist' => ['activity_position']])]
#[Legacy\Synchronize(table: 'mod_logs')]
abstract class Activity implements LegacyIdInterface
{
    use LegacyIdentifierTrait;

    #[ORM\Column(name: 'metadata', type: 'json', options: ['default' => '[]'])]
    #[Assert\Type(type: 'array')]
    #[Groups(['activity', 'activity:write'])]
    #[Exclude]
    public array $metadata = [];

    #[ORM\Column(name: 'discriminator', type: 'string', nullable: true)]
    #[Groups(['activity:write', 'activity'])]
    #[ApiProperty(
        description: 'Use to mark some particular activity.<br/>
            Example for TOC
            <ul>
                <li><strong>OPEN_FACTORY_FLAG</strong> : to enable factory flag</li>
                <li><strong>CLOSE_FACTORY_FLAG</strong> : to disable factory flag</li>
            </ul>
        '
    )]
    public ?string $discriminator = null;

    #[ORM\Column(name: 'id', type: 'bigint')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['activity'])]
    private int $id;

    #[ApiProperty(iris: ['https://schema.org/propertyID'])]
    #[ORM\Column(name: 'resource', type: 'string', length: 255)]
    #[Assert\Type(type: 'string')]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Groups(['activity', 'activity:write'])]
    #[ResourceExists]
    #[Legacy\Column(column: 'module', transformer: IriToModule::class)]
    #[Legacy\Column(column: 'parent_id', transformer: IriToProperty::class, options: ['property' => 'legacyId', 'allow_missing' => true])]
    private ?string $resource = null;

    #[ApiProperty(iris: ['https://schema.org/creator'])]
    #[ORM\ManyToOne(targetEntity: 'App\Entity\User')]
    #[ORM\JoinColumn(name: 'user_id', nullable: true)]
    #[Legacy\Column(column: 'poster', transformer: ObjectToProperty::class, options: ['property' => 'legacyId', 'allow_missing' => true])]
    #[Groups(['activity'])]
    private ?User $user = null;

    #[ApiProperty(iris: ['https://schema.org/dateCreated'])]
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    #[Groups(['activity'])]
    #[Legacy\Column(column: 'date', transformer: DateTimeToString::class)]
    private \DateTimeInterface $createdAt;

    #[ApiProperty(iris: ['https://schema.org/dateModified'])]
    #[ORM\Column(name: 'updated_at', type: 'datetime')]
    #[Groups(['activity'])]
    #[Legacy\Column(column: 'date', transformer: DateTimeToString::class)]
    private \DateTimeInterface $updatedAt;

    #[ApiProperty(iris: ['https://schema.org/Boolean'])]
    #[ORM\Column(name: 'public', type: 'boolean')]
    #[Groups(['activity', 'activity:write'])]
    private bool $public = true;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
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

    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function isPublic(): bool
    {
        return $this->public;
    }

    public function setPublic(bool $public): self
    {
        $this->public = $public;

        return $this;
    }

    #[ORM\PrePersist]
    public function onPersist()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    #[ORM\PreUpdate]
    public function onUpdate()
    {
        $this->updatedAt = new \DateTime();
    }
}
