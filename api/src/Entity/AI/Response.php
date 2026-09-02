<?php

declare(strict_types=1);

namespace App\Entity\AI;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'ai_responses')]
class Response
{
    #[ORM\Column(type: 'text', options: ['charset' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci'])]
    #[Groups(['response'])]
    public string $content;

    #[ORM\Column(type: 'datetime')]
    #[Gedmo\Timestampable(on: 'create')]
    #[Groups(['response'])]
    public \DateTimeInterface $createdAt;

    #[ORM\OneToOne(targetEntity: Request::class, mappedBy: 'response')]
    public Request $request;

    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }
}
