<?php

declare(strict_types=1);

namespace App\Dto\Emails;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Controller\Sales\ExtranetUser\ExtranetUserContactEmailController;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/extranet/contact',
            controller: ExtranetUserContactEmailController::class,
            denormalizationContext: ['groups' => ['contact']],
            security: "is_granted('ACCESS_EXTRANET_USER')",
        ),
    ]
)]
class ExtranetEmail
{
    #[Assert\Email(mode: 'strict')]
    #[Groups(['contact'])]
    public string $to;

    #[Groups(['contact'])]
    public string $message;
}
