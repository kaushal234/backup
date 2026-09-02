<?php

declare(strict_types=1);

namespace App\Entity\AI;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(
            // no need for security here, you can't post a rating without a log, and AILogExtension will not let you
            // see a log that is not yours, so it would throw a 400 error because AILog not found.
        ),
    ],
    routePrefix: 'ai',
    normalizationContext: ['groups' => ['rating']],
    denormalizationContext: ['groups' => ['rating:write']]
)]
#[ORM\Entity]
#[ORM\Table(name: 'ai_ratings')]
#[ORM\UniqueConstraint(name: 'unique_rating_log', columns: ['log_id'])]
#[UniqueEntity(fields: ['log'])]
class Rating
{
    #[ORM\Column(type: 'integer')]
    #[Assert\Range(min: 1, max: 4)]
    #[Assert\NotNull]
    #[Groups(groups: ['rating', 'rating:write'])]
    public int $rating;

    #[ORM\Column(type: 'string')]
    #[Groups(groups: ['rating', 'rating:write'])]
    public ?string $comment = null;

    #[ORM\OneToOne(targetEntity: AILog::class, inversedBy: 'rating')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull]
    #[Groups(groups: ['rating:write'])]
    private AILog $log;

    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }

    public function getLog(): AILog
    {
        return $this->log;
    }

    public function setLog(AILog $log): self
    {
        $this->log = $log;
        $log->rating = $this;

        return $this;
    }
}
