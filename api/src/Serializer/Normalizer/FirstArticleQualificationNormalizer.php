<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer;

use App\Entity\Quality\FirstArticleQualification\FirstArticleQualification;
use App\Manager\Quality\FirstArticleQualification\FirstArticleQualificationManager;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class FirstArticleQualificationNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'FIRST_ARTICLE_QUALIFICATION_NORMALIZER_ALREADY_CALLED';

    private readonly FirstArticleQualificationManager $manager;

    public function __construct(FirstArticleQualificationManager $manager)
    {
        $this->manager = $manager;
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof FirstArticleQualification && null === ($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @param FirstArticleQualification $object
     *
     * @throws ExceptionInterface
     */
    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $context[self::ALREADY_CALLED] = true;
        /** @var array $normalizedData */
        $normalizedData = $this->normalizer->normalize($object, $format, $context);
        if (\in_array('faq_progress', $context[AbstractNormalizer::GROUPS] ?? [], true)) {
            $normalizedData['progress'] = $this->manager->getProgressPercentage($object);
        }

        if (\in_array('faq_export', $context[AbstractNormalizer::GROUPS] ?? [], true)) {
            $tags = [];
            foreach ($object->getTags() as $tag) {
                $tags[] = $tag->getName();
            }

            $partNumberRevisions = [];
            foreach ($object->getPartNumbers() as $partNumber) {
                $partNumberRevisions[] = \sprintf('%s - %s - %s', $partNumber->getRevision(), $partNumber->getNumber(), $partNumber->getDescription());
            }

            $normalizedData['tags'] = implode('/ ', $tags);
            $normalizedData['partNumbers'] = implode('/ ', $partNumberRevisions);
            $normalizedData['location'] = $object->getLocation()->getName();
            $normalizedData['buyer'] = null !== $object->getBuyer() ? $object->getBuyer()->getDisplayName() : '';
            $normalizedData['poster'] = $object->getPoster()->getDisplayName();
            $normalizedData['owner'] = $object->getOwner()->getDisplayName();
            $normalizedData['productFamily'] = null !== $object->getProductFamily() ? $object->getProductFamily()->getName() : '';
        }

        return $normalizedData;
    }
}
