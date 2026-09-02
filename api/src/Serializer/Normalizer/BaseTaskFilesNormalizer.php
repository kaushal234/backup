<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Activity\Comment;
use App\Entity\BaseTask;
use App\Serializer\Encoder\XlsxEncoder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class BaseTaskFilesNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'TASK_FILES_NORMALIZER_ALREADY_CALLED';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly IriConverterInterface $iriConverter,
    ) {
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof BaseTask && XlsxEncoder::FORMAT !== $format && (false === ($context[self::ALREADY_CALLED] ?? false));
    }

    /**
     * @param BaseTask $object
     *
     * @throws ExceptionInterface
     */
    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $context[self::ALREADY_CALLED] = true;

        /** @var array $normalizedData */
        $normalizedData = $this->normalizer->normalize($object, $format, $context);

        if (!($context['operation'] ?? null) instanceof Get) {
            return $normalizedData;
        }

        $repository = $this->entityManager->getRepository(Comment::class);
        /** @var Comment $comment */
        foreach ($repository->findBy(['resource' => $this->iriConverter->getIriFromResource($object)]) as $comment) {
            foreach ($comment->getFiles() as $file) {
                $normalizedData['files'][] = [
                    'commentId' => $comment->getId(),
                    ...$this->normalizer->normalize($file, 'jsonld', ['groups' => ['people_public', 'file']]),
                ];
            }
        }

        return $normalizedData;
    }
}
