<?php

declare(strict_types=1);

namespace App\Entity\AI;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'ai_requests')]
class Request
{
    #[ORM\Column(type: 'string')]
    #[Groups(['request'])]
    public string $url;

    #[ORM\Column(type: 'json')]
    #[Groups(['request'])]
    public array $options;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['request'])]
    public ?string $content = null;

    #[ORM\Column(type: 'datetime')]
    #[Gedmo\Timestampable(on: 'create')]
    #[Groups(['request'])]
    public \DateTimeInterface $createdAt;

    #[ORM\ManyToOne(targetEntity: AILog::class, inversedBy: 'requests')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    public AILog $log;

    #[ORM\OneToOne(targetEntity: Response::class, inversedBy: 'request', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[Groups(['request'])]
    public ?Response $response = null;

    #[ORM\OneToOne(targetEntity: AIFile::class, mappedBy: 'request', cascade: ['persist', 'remove'])]
    #[Groups(['request'])]
    private ?AIFile $file = null;

    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }

    public function getFile(): ?AIFile
    {
        return $this->file;
    }

    public function setFile(?AIFile $file): self
    {
        $this->file = $file;
        $file->setRequest($this);

        return $this;
    }
}
