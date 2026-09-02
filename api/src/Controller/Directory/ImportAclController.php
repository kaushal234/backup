<?php

declare(strict_types=1);

namespace App\Controller\Directory;

use ApiPlatform\Validator\Exception\ValidationException;
use App\Entity\Acl;
use App\Entity\AclImport;
use App\Entity\User;
use App\Repository\AclRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ImportAclController extends AbstractController
{
    private readonly EntityManagerInterface $entityManager;
    private readonly SerializerInterface $serializer;
    private readonly ValidatorInterface $validator;

    public function __construct(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ) {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    public function __invoke(Request $request, User $user)
    {
        /** @var AclImport $content */
        $content = $this->serializer->deserialize((string) $request->getContent(), AclImport::class, 'jsonld');
        $violations = $this->validator->validate($content);
        if ($violations->count() > 0) {
            throw new ValidationException($violations);
        }
        /** @var AclRepository $repository */
        $repository = $this->entityManager->getRepository(Acl::class);

        $repository->importAcls($user, $content->getAcls(), $content->getLocation());

        $this->entityManager->flush();

        return new Response();
    }
}
