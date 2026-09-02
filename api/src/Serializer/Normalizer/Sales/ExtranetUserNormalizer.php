<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer\Sales;

use ApiPlatform\Metadata\GetCollection;
use App\Entity\Sales\ExtranetUser;
use App\Entity\Task\Task;
use App\Security\JWT\PayloadGenerator\PayloadGeneratorInterface;
use App\Serializer\Normalizer\UserNormalizer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

class ExtranetUserNormalizer extends UserNormalizer
{
    /**
     * @var string
     */
    private const ALREADY_CALLED = 'EXTRANET_USER_NORMALIZER_ALREADY_CALLED';

    public function __construct(
        protected TokenStorageInterface $tokenStorage,
        private EntityManagerInterface $entityManager,
    ) {
        parent::__construct($tokenStorage);
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof ExtranetUser && null === ($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @param ExtranetUser $object
     *
     * @throws ExceptionInterface
     */
    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $context[self::ALREADY_CALLED] = true;
        /** @var array $normalizedData */
        $normalizedData = $this->normalizer->normalize($object, $format, $context);

        if (\in_array(PayloadGeneratorInterface::PAYLOAD_NORMALIZATION_GROUP, $context[AbstractNormalizer::GROUPS], true) || \in_array('user:me', $context[AbstractObjectNormalizer::GROUPS], true)) {
            $normalizedData['roles'] = $this->extractRoles($object, $context);
        }

        if (\in_array('extranet_user_public', $context[AbstractNormalizer::GROUPS], true)) {
            unset($normalizedData['passwordUpdatedAt'], $normalizedData['passwordExpirationDate']);
        }

        $operation = $context['root_operation'] ?? null;
        if ($operation instanceof GetCollection && 'get_campaign_contacts' === $operation->getName()) {
            $tasks = $this->entityManager->getRepository(Task::class)->findBy(['referenceId' => $object->getId()]);
            $task = !empty($tasks) ? end($tasks) : null;

            if (null !== $task) {
                $normalizedData['taskId'] = $task->getId();
                $normalizedData['taskClosedAt'] = $task->closedAt;
                $normalizedData['taskStatus'] = $task->getStatus();
            }
        }

        return $normalizedData;
    }
}
