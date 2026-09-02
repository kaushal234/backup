<?php

declare(strict_types=1);

namespace App\Controller\Directory;

use ApiPlatform\Validator\Exception\ValidationException;
use App\Entity\Acl;
use App\Entity\AclImport;
use App\Entity\Directory\Location;
use App\Entity\Group;
use App\Entity\User;
use App\Repository\AclRepository;
use App\Util\Iri;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PeopleController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly DecoderInterface $decoder,
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function sageAuth(Request $request)
    {
        throw new \LogicException('this method should not be used anymore !');
    }

    public function addAcl(Request $request, User $user)
    {
        $content = $this->decoder->decode((string) $request->getContent(), JsonEncoder::FORMAT);

        /** @var AclRepository $aclRepository */
        $aclRepository = $this->entityManager->getRepository(Acl::class);

        if ($aclRepository->aclExists($user->getId(), Iri::id($content['group']), Iri::id($content['location']))) {
            throw new BadRequestHttpException('User already has this ACL.');
        }

        /** @var Group $group */
        $group = $this->entityManager->getReference(Group::class, Iri::id($content['group']));

        /** @var Location $location */
        $location = $this->entityManager->getReference(Location::class, Iri::id($content['location']));

        $acl = (new Acl())
            ->setUser($user)
            ->setGroup($group)
            ->setLocation($location);

        $this->entityManager->persist($acl);
        $this->entityManager->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    public function importAcl(Request $request, User $user)
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
