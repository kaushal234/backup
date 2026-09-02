<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Common\Subscription;
use App\Entity\Common\SubscriptionResourceModule;
use App\Entity\Module\Module;
use App\Manager\EntityDictionaryManager;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class SubscriptionNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @var string
     */
    private const string ALREADY_CALLED = 'SUBSCRIPTION_NORMALIZER_ALREADY_CALLED';

    public function __construct(
        private readonly EntityDictionaryManager $entityDictionaryManager,
        private readonly IriConverterInterface $iriConverter,
        #[AutowireIterator(tag: 'app.short_description_resolver')]
        private readonly iterable $resolvers,
    ) {
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Subscription && null === ($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @param Subscription $object
     *
     * @throws ExceptionInterface
     */
    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $context[self::ALREADY_CALLED] = true;

        /** @var array $data */
        $data = $this->normalizer->normalize($object, $format, $context);

        $iri = $object->getResource();
        $parts = explode('/', $iri);
        $data['resourceId'] = (int) end($parts);

        $resource = $this->iriConverter->getResourceFromIri($iri);
        $data['shortDescription'] = null;
        foreach ($this->resolvers as $resolver) {
            if ($resolver->supports($resource)) {
                $data['shortDescription'] = $resolver->resolve($resource);
                break;
            }
        }

        $moduleEnum = SubscriptionResourceModule::fromResourceIri($iri);
        if (null === $moduleEnum) {
            $data['module'] = null;

            return $data;
        }

        /** @var array<string, Module> $modulesByName */
        $modulesByName = $this->entityDictionaryManager->getIndexedTable(Module::class, ['name']);
        $module = $modulesByName[$moduleEnum->value] ?? null;

        $data['module'] = null !== $module ? [
            'name' => $module->getName(),
            'frontEndRoute' => $module->frontEndRoute,
        ] : null;

        return $data;
    }
}
