<?php

declare(strict_types=1);

namespace App\Serializer\Denormalizer;

use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Serializer\ContextTrait;
use App\Entity\SurveyTargetInterface;
use App\Manager\Survey\SurveyPublicationModel;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class SurveyPublicationDenormalizer implements DenormalizerInterface
{
    use ContextTrait;

    private readonly IriConverterInterface $iriConverter;

    public function __construct(IriConverterInterface $iriConverter)
    {
        $this->iriConverter = $iriConverter;
    }

    /**
     * @return array|mixed|object
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        $model = new SurveyPublicationModel();

        if (isset($data['description'])) {
            $model->setDescription($data['description']);
        }

        foreach ($data['targets'] as $target) {
            try {
                $item = $this->iriConverter->getResourceFromIri($target);
            } catch (\Exception $exception) {
                throw new \InvalidArgumentException(\sprintf('Could not find any resource matching %s', $target), Response::HTTP_BAD_REQUEST, $exception);
            }
            if (!$item instanceof SurveyTargetInterface) {
                throw new \InvalidArgumentException('Resource should implement EmailInterface', Response::HTTP_BAD_REQUEST);
            }
            $model->addTarget($item);
        }

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return SurveyPublicationModel::class === $type;
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }
}
