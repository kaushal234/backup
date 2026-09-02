<?php

declare(strict_types=1);

namespace App\Entity\Common\Notification;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\Common\Notification\DeleteAllNotificationController;
use App\Controller\Common\Notification\ReadAllNotificationController;
use App\Entity\Directory\People;
use App\Filter\SimpleSearchFilter;
use App\Repository\Common\NotificationRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Timestampable;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: NotificationRepository::class)]
#[ORM\Table(name: 'notifications')]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(
            uriTemplate: '/notifications/read_all',
            controller: ReadAllNotificationController::class,
            input: false,
            deserialize: false,
            validate: false,
            name: 'read_all',
        ),
        new Post(
            uriTemplate: '/notifications/delete_all',
            controller: DeleteAllNotificationController::class,
            input: false,
            deserialize: false,
            validate: false,
            name: 'delete_all',
        ),
        new Get(security: 'user === object.people'),
        new Delete(security: 'user === object.people'),
        new Put(denormalizationContext: ['groups' => ['notification:edit']], security: 'user === object.people'),
    ],
    normalizationContext: ['groups' => ['notification', 'notification_template', 'module:list']]
)]
#[ApiFilter(OrderFilter::class, properties: ['createdAt'])]
#[ApiFilter(SimpleSearchFilter::class, properties: ['textDisplayed' => 'partial'])]
#[ApiFilter(SearchFilter::class, properties: ['template.module.name'])]
#[ApiFilter(BooleanFilter::class, properties: ['unread'])]
#[ApiFilter(DateFilter::class, properties: ['createdAt'])]
class Notification
{
    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['notification'])]
    #[Timestampable(on: 'create')]
    public ?\DateTime $createdAt;

    #[ORM\ManyToOne(targetEntity: People::class)]
    public People $people;

    #[ORM\ManyToOne(targetEntity: NotificationTemplate::class)]
    #[Groups(['notification'])]
    public NotificationTemplate $template;

    #[ORM\Column(type: 'integer')]
    public int $referenceId;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['notification', 'notification:edit'])]
    public bool $unread = true;

    #[ORM\Column(type: 'string')]
    #[Groups(['notification'])]
    public string $textDisplayed;

    #[ORM\Column(type: 'string')]
    #[Groups(['notification'])]
    public string $url;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    #[Groups(['notification'])]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }
}
