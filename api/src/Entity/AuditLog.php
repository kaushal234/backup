<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Symfony\Action\NotFoundAction;
use App\DataProvider\AuditLogByReferenceDataProvider;
use App\DataProvider\AuditLogDataProvider;
use App\Dto\Audit\AuditLogProperty;
use App\Filter\AuditLogFilter;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\MaxDepth;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['audit_log']],
            provider: AuditLogDataProvider::class,
        ),
        new GetCollection(
            uriTemplate: 'audit_logs/time_by_reference',
            normalizationContext: ['groups' => ['audit_log']],
            name: 'audit_logs_time_by_reference',
            provider: AuditLogByReferenceDataProvider::class
        ),
        new GetCollection(
            uriTemplate: 'audit_logs/time',
            normalizationContext: ['groups' => ['audit_log']],
            name: 'audit_logs_time',
            provider: AuditLogDataProvider::class
        ),
        new GetCollection(
            uriTemplate: 'audit_logs/by_reference',
            normalizationContext: ['groups' => ['audit_log', 'people_public', 'audit_log:reference']],
            name: 'audit_logs_by_reference',
            provider: AuditLogByReferenceDataProvider::class
        ),
        new GetCollection(
            uriTemplate: 'audit_logs/by_month',
            normalizationContext: ['groups' => ['audit_log', 'people_public', 'audit_log:month']],
            output: AuditLogProperty::class,
            name: 'audit_logs_by_month',
            provider: AuditLogDataProvider::class,
        ),
        new Get(controller: NotFoundAction::class),
    ],
)]
#[ApiFilter(SearchFilter::class, properties: ['auditType', 'property', 'referenceId'])]
#[ApiFilter(OrderFilter::class, properties: ['id'])]
#[ApiFilter(AuditLogFilter::class)]
class AuditLog
{
    #[ORM\Column(type: 'datetime')]
    public \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'string')]
    public string $property;

    #[ORM\Column(type: 'string')]
    public string $value;

    #[ORM\OneToOne(targetEntity: self::class, inversedBy: 'previous')]
    #[MaxDepth(1)]
    public ?AuditLog $next = null;

    #[ORM\Column(type: 'string')]
    public string $auditType;

    #[ORM\ManyToOne(targetEntity: User::class)]
    public ?User $createdBy = null;

    #[ORM\Column(type: 'integer')]
    public int $referenceId;

    #[ORM\OneToOne(targetEntity: self::class, mappedBy: 'next')]
    #[MaxDepth(1)]
    private ?AuditLog $previous = null;

    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }

    public function getPrevious(): ?self
    {
        return $this->previous;
    }

    public function setPrevious(?self $previous): self
    {
        $this->previous = $previous;
        $previous->next = $this;

        return $this;
    }
}
