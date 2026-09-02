<?php

declare(strict_types=1);

namespace App\Controller\Directory;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Agile\Message\UpdateUserMessage;
use App\Entity\Acl;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\Group;
use App\Entity\User;
use App\Javelo\Event\UpdateJaveloUserEvent;
use App\Repository\AclRepository;
use App\Util\Iri;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

class AddAclController extends AbstractController
{
    public function __construct(
        private readonly DecoderInterface $decoder,
        private readonly EntityManagerInterface $entityManager,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly MessageBusInterface $messageBus,
        private readonly IriConverterInterface $iriConverter,
    ) {
    }

    public function __invoke(Request $request, User $user)
    {
        $content = $this->decoder->decode((string) $request->getContent(), JsonEncoder::FORMAT);

        /** @var AclRepository $aclRepo */
        $aclRepo = $this->entityManager->getRepository(Acl::class);

        $exists = $aclRepo->aclExists($user->getId(), Iri::id($content['group']), Iri::id($content['location']));
        if ($exists) {
            throw new BadRequestHttpException('User already has this ACL.');
        }

        /** @var Group $group */
        $group = $this->entityManager->getReference(Group::class, Iri::id($content['group']));

        /** @var Location $location */
        $location = $this->entityManager->getReference(Location::class, Iri::id($content['location']));

        $acl = (new Acl())
            ->setUser($user)
            ->setGroup($group)
            ->setLocation($location)
            ->setExpiredAt(isset($content['expiredAt']) ? new \DateTime($content['expiredAt']) : null)
        ;

        $this->entityManager->persist($acl);
        $this->entityManager->flush();

        if ($user instanceof People) {
            if ('ACL_AUTH_JAVELO' === $group->getName()) {
                $this->eventDispatcher->dispatch(new UpdateJaveloUserEvent($user, []));
            }
            if ('ACL_AUTH_AGILE' === $group->getName()) {
                $this->messageBus->dispatch(new UpdateUserMessage($this->iriConverter->getIriFromResource($user)));
            }
        }

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
