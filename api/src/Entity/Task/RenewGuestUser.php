<?php

declare(strict_types=1);

namespace App\Entity\Task;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Controller\Task\RenewGuestUserAcceptController;
use App\Controller\Task\RenewGuestUserDenyController;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\MIS\GuestUser\GuestUser;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/tasks/renew_guest_user/{id}/accept',
            outputFormats: ['jsonld'],
            controller: RenewGuestUserAcceptController::class,
            denormalizationContext: ['groups' => ['task:renew_guest_user:write']],
            security: 'is_granted("RENEW_GUEST_USER_VOTER", object)',
            validationContext: ['groups' => ['Default', 'accept']],
            name: 'task_renew_guest_user_accept',
        ),
        new Post(
            uriTemplate: '/tasks/renew_guest_user/{id}/deny',
            outputFormats: ['jsonld'],
            controller: RenewGuestUserDenyController::class,
            denormalizationContext: ['groups' => ['task:renew_guest_user:write']],
            security: 'is_granted("RENEW_GUEST_USER_VOTER", object)',
            validationContext: ['groups' => ['Default']],
            name: 'task_renew_guest_user_deny',
        ),
    ]
)]
#[App\Loggable]
class RenewGuestUser extends Task
{
    final public const string RENEWAL_YES = 'YES';
    final public const string RENEWAL_NO = 'NO';
    final public const string RENEWAL_PENDING = 'PENDING';

    #[ORM\ManyToOne(targetEntity: GuestUser::class)]
    #[ORM\JoinColumn(name: 'guest_user_id', referencedColumnName: 'id')]
    #[Groups(['task'])]
    public GuestUser $guestUser;

    #[ORM\Column(type: 'string', length: 10)]
    #[Assert\Choice(choices: [self::RENEWAL_YES, self::RENEWAL_NO, self::RENEWAL_PENDING])]
    #[Groups(['task', 'task:renew_guest_user:write'])]
    public string $renewalDecision = self::RENEWAL_PENDING;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Assert\Range(min: 1, max: 12, groups: ['accept'])]
    #[Assert\NotBlank(groups: ['accept'])]
    #[Groups(['task', 'task:renew_guest_user:write'])]
    public ?int $renewalDurationMonths = null;

    #[Assert\Choice(choices: [self::CLOSED, self::PENDING])]
    #[Legacy\Column(column: 'status')]
    #[Groups(['task', 'task:renew_guest_user:write'])]
    protected string $status = self::PENDING;
}
