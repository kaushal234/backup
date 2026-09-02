<?php

declare(strict_types=1);

namespace App\Controller\Sales\ExtranetUser;

use App\Dto\Emails\ExtranetEmail;
use App\Entity\Sales\ExtranetUser;
use App\Notifier\Sales\ExtranetUser\ExtranetUserNotifier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;

class ExtranetUserContactEmailController extends AbstractController
{
    public function __construct(
        private readonly Security $security,
        private readonly ExtranetUserNotifier $notifier,
    ) {
    }

    public function __invoke(ExtranetEmail $email)
    {
        /** @var ExtranetUser $user */
        $user = $this->security->getUser();

        $this->notifier->sendContactEmail($user, $email->message, $email->to);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
