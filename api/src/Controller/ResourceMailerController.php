<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\Emails\ResourceEmail;
use App\Entity\Directory\People;
use App\Notifier\Resource\ResourceNotifier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class ResourceMailerController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly ResourceNotifier $resourceMailer)
    {
    }

    #[Route(path: '/mailer', methods: ['POST'])]
    public function mail(Request $request)
    {
        /** @var ResourceEmail $data */
        $data = $this->serializer->deserialize($request->getContent(), ResourceEmail::class, 'json');

        /** @var People $user */
        $user = $this->getUser();

        $data->setSender($user);
        $this->resourceMailer->sendEmail($data);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
