<?php

declare(strict_types=1);

namespace App\Controller\Sales\ExtranetUser;

use App\Entity\Sales\ExtranetUser;
use App\Manager\UserManager;
use App\Notifier\Sales\ExtranetUser\ExtranetUserNotifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ExtranetUserPasswordUpdateLinkController extends AbstractController
{
    private UserManager $userManager;
    private ExtranetUserNotifier $notifier;
    private EntityManagerInterface $entityManager;

    public function __construct(UserManager $userManager, ExtranetUserNotifier $notifier, EntityManagerInterface $entityManager)
    {
        $this->userManager = $userManager;
        $this->notifier = $notifier;
        $this->entityManager = $entityManager;
    }

    public function __invoke(ExtranetUser $extranetUser)
    {
        $this->userManager->generateToken($extranetUser);

        $this->entityManager->persist($extranetUser);
        $this->entityManager->flush();

        $this->notifier->sendPasswordUpdateLinkEmail($extranetUser);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
