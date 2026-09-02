<?php

declare(strict_types=1);

namespace App\ION\Validator\Constraints\MasterData\LogisticCodes;

use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use App\ION\DataProvider\CachedIONCollectionDataProvider;
use App\ION\Resources\MasterData\CodeDefinitions\LogisticCodes\Language as LanguageResource;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class LanguageValidator extends ConstraintValidator
{
    public function __construct(
        private readonly CachedIONCollectionDataProvider $provider,
        private readonly ResourceMetadataCollectionFactoryInterface $resourceMetadataCollectionFactory,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        $metadata = $this->resourceMetadataCollectionFactory->create(LanguageResource::class);
        $languages = $this->provider->provide($metadata->getOperation(forceCollection: true));

        foreach ($languages as $language) {
            if ($value === $language->iso639_2) {
                return;
            }
        }

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ language }}', $value)
            ->addViolation();
    }
}
