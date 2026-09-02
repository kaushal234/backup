<?php

declare(strict_types=1);

namespace App\Controller\Common\Notification;

use App\Entity\Common\Notification\Notification;
use App\Entity\Directory\People;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;

class DeleteAllNotificationController extends AbstractController
{
    public function __construct(
        private readonly Security $security,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke()
    {
        /** @var People $user */
        $user = $this->security->getUser();

        foreach ($this->entityManager->getRepository(Notification::class)->findBy(['people' => $user]) as $notification) {
            $this->entityManager->remove($notification);
        }

        $this->entityManager->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
