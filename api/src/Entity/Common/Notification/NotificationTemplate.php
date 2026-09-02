<?php

declare(strict_types=1);

namespace App\Entity\Common\Notification;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Module\Module;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'notification_templates')]
#[ApiResource(
    operations: [new Get(), new GetCollection()],
    normalizationContext: ['groups' => ['notification_template', 'module:list']]
)]
class NotificationTemplate
{
    #[ORM\ManyToOne(targetEntity: Module::class)]
    #[Groups(['notification_template'])]
    public Module $module;

    #[ORM\Column(type: 'string')]
    #[Groups(['notification_template'])]
    public string $name;

    #[ORM\Column(type: 'string')]
    #[Groups(['notification_template'])]
    public string $text;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }
}
