<?php

declare(strict_types=1);

namespace App\ION\Serializer\Denormalizer\SpartaExtensions\Times;

use App\Entity\Directory\People;
use App\ION\Dto\SpartaExtensions\Times\TimeKeepingPostTransactionOutputLine;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class TimeKeepingPostTransactionOutputLineDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'TIME_KEEPING_POST_TRANSACTION_OUTPUT_LINE_DENORMALIZER_ALREADY_CALLED';
    private readonly EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return TimeKeepingPostTransactionOutputLine::class === $type && !($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @return array|mixed|object
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        $context[self::ALREADY_CALLED] = true;

        $data['employeeNumber'] = $data['employee'];
        $data['firstname'] = '';
        $data['lastname'] = '';
        $data['sequenceNumber'] = (int) $data['sequenceNumber'];
        $data['period'] = (int) $data['period'];
        $data['year'] = (int) $data['year'];

        if (null !== ($user = $this->entityManager->getRepository(People::class)->find($data['employee']))) {
            $data['firstname'] = (null !== ($firstname = $user->getFirstname())) ? $firstname : '';
            $data['lastname'] = (null !== ($lastname = $user->getLastname())) ? $lastname : '';
        }

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }
}
