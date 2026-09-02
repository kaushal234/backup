<?php

declare(strict_types=1);

namespace App\Entity\AI;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Directory\People;
use App\Repository\AI\AILogRepository;
use App\Validator\Constraints\AI\MaxPinnedAILogs;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: AILogRepository::class)]
#[ORM\Table(name: 'ai_logs')]
#[ApiResource(
    shortName: 'AiLogs',
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['ai_logs', 'request', 'response', 'file:light']],
        ),
        new GetCollection(
            uriTemplate: '/ai_logs/history',
            normalizationContext: ['groups' => ['ai_logs', 'ai_logs:item', 'request', 'response', 'file:light', 'rating']],
            security: "is_granted('FEATURE_AI_LOG_HISTORY')",
            name: 'get_ai_logs_history',
        ),
        new Get(),
        new Post(),
        new Put(
            denormalizationContext: ['groups' => ['ai_logs:edit']],
            security: 'user === object.people'
        ),
        new Delete(),
    ],
    normalizationContext: ['groups' => ['ai_logs', 'ai_logs:item', 'request', 'response', 'file:light', 'rating']],
    denormalizationContext: ['groups' => ['ai_logs:write']],
)]
#[MaxPinnedAILogs]
#[ApiFilter(OrderFilter::class, properties: ['id', 'pinned'])]
#[ApiFilter(SearchFilter::class, properties: ['type' => 'exact', 'people' => 'exact'])]
class AILog
{
    public const string HEADER = 'X-AI-Log';

    #[ORM\Column(type: 'datetime')]
    #[Gedmo\Timestampable(on: 'create')]
    #[Groups(['ai_logs'])]
    public \DateTimeInterface $createdAt;

    #[ORM\ManyToOne(targetEntity: People::class)]
    #[Gedmo\Blameable(on: 'create')]
    public People $people;

    #[ORM\OneToOne(targetEntity: Rating::class, mappedBy: 'log', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['ai_logs:item'])]
    public ?Rating $rating = null;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $conversationSummary = null;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    public int $summarizedRequestsCount = 0;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['ai_logs:write'])]
    public ?string $type = null;

    #[ORM\Column(type: 'string', nullable: true, options: ['charset' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci'])]
    #[Groups(['ai_logs'])]
    public ?string $title = null;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => 0])]
    #[Groups(['ai_logs', 'ai_logs:edit'])]
    public bool $pinned = false;

    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['ai_logs'])]
    private int $id;

    /**
     * @var Collection<Request>
     */
    #[ORM\OneToMany(targetEntity: Request::class, mappedBy: 'log', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['ai_logs:item'])]
    private Collection $requests;

    public function __construct()
    {
        $this->requests = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Collection<Request>
     */
    public function getRequests(): Collection
    {
        return $this->requests;
    }

    public function addRequest(Request $request): self
    {
        if (!$this->requests->contains($request)) {
            $this->requests->add($request);
            $request->log = $this;
        }

        return $this;
    }

    public function removeRequest(Request $request): self
    {
        if ($this->requests->contains($request)) {
            $this->requests->removeElement($request);
        }

        return $this;
    }

    /**
     * @return array<AIFile>
     */
    public function getFiles(): array
    {
        return $this->requests
            ->map(static fn (Request $request) => $request->getFile())
            ->filter(static fn (?AIFile $file) => null !== $file)
            ->toArray();
    }
}
