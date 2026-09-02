<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer\Legal;

use App\Entity\Legal\Category;
use App\Entity\Legal\SubCategory;
use App\Serializer\Normalizer\UserNormalizer;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class ContractCategoriesNormalizer extends UserNormalizer
{
    private const string ALREADY_CALLED = 'CONTRACT_CATEGORIES_NORMALIZER_ALREADY_CALLED';

    public function __construct(
        protected TokenStorageInterface $tokenStorage,
        private readonly Security $security,
    ) {
        parent::__construct($tokenStorage);
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return ($data instanceof Category || $data instanceof SubCategory) && null === ($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @param Category|SubCategory $object
     *
     * @throws ExceptionInterface
     */
    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $context[self::ALREADY_CALLED] = true;
        /** @var array $normalizedData */
        $normalizedData = $this->normalizer->normalize($object, $format, $context);
        $normalizedData['isAllow'] = $this->security->isGranted('CONTRACT_CATEGORIES_VOTER');

        return $normalizedData;
    }
}
