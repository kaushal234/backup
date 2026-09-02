<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\EquipmentRecord;
use App\Entity\Sales\ExtranetUser;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class ExtranetUserWithLinkToEquipmentRecordNormalizer extends UserNormalizer
{
    private const ALREADY_CALLED = 'EXTRANET_USER_WITH_LINK_TO_ER_NORMALIZER_ALREADY_CALLED';

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly IriConverterInterface $iriConverter,
        private readonly EntityManagerInterface $entityManager
    ) {
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

        if (!($equipmentRecord = $this->getEquipmentRecord())) {
            return $normalizedData;
        }

        $normalizedData['asBuyer'] = false;
        $normalizedData['asUser'] = false;
        $normalizedData['asMaintainer'] = false;

        foreach ($object->getExtranetUserAcls() as $acl) {
            try {
                $customer = $acl->getCrt()->getCustomer();

                $normalizedData['asBuyer'] = $customer->getEquipmentRecordsAsBuyer()->contains($equipmentRecord);
                $normalizedData['asUser'] = $customer->getEquipmentRecordsAsUser()->contains($equipmentRecord);
                $normalizedData['asMaintainer'] = $customer->getEquipmentRecordsAsMaintainer()->contains($equipmentRecord);
            } catch (EntityNotFoundException|\Error) {
            }
        }

        return $normalizedData;
    }

    private function getEquipmentRecord()
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return null;
        }

        $equipmentRecord = null;

        if ('technician_on_call_get_item' === $request->get('_route')) {
            $equipmentRecord =
                $this->entityManager->getRepository($request->get('_api_resource_class'))
                    ->find($request->get('id'))
                    ->equipmentRecord;
        }

        if ($request->query->has('relatedToEquipmentRecord')) {
            /** @var EquipmentRecord $equipmentRecord */
            $equipmentRecord = $this->iriConverter->getResourceFromIri($request->query->get('relatedToEquipmentRecord'));
        }

        return $equipmentRecord;
    }
}
