<?php

declare(strict_types=1);

namespace App\Serializer\ContextBuilder;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Put;
use ApiPlatform\State\SerializerContextBuilderInterface;
use App\Entity\Directory\People;
use App\Entity\Sales\Customer;
use App\Manager\Directory\PeopleManager;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

class CustomerContextBuilder implements SerializerContextBuilderInterface
{
    private readonly SerializerContextBuilderInterface $decorated;
    private readonly Security $security;

    public function __construct(SerializerContextBuilderInterface $decorated, Security $security)
    {
        $this->decorated = $decorated;
        $this->security = $security;
    }

    /**
     * {@inheritdoc}
     */
    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);

        if (Customer::class !== $context['resource_class'] || $normalization) {
            return $context;
        }

        /** @var Operation $operation */
        $operation = $request->attributes->get('_api_operation');
        if (!$operation instanceof Put) {
            return $context;
        }

        if ($this->security->isGranted('FEATURE_CUSTOMER_ADMIN')
            || $this->security->isGranted('MOO_ECUST')) {
            $context[AbstractObjectNormalizer::GROUPS][] = 'customer_write_admin';
        }

        /** @var People $user */
        $user = $this->security->getUser();
        if ($this->security->isGranted('MOO_ECUST') || PeopleManager::hasGroup($user, 'SUPERUSER')) {
            $context[AbstractObjectNormalizer::GROUPS][] = 'customer:finance_write';

            return $context;
        }

        if ($this->security->isGranted('FEATURE_CUSTOMER_FINANCE_ADMIN')) {
            $context[AbstractObjectNormalizer::GROUPS] = array_diff($context[AbstractObjectNormalizer::GROUPS], ['customer_write', 'address_write', 'country_write']);
            $context[AbstractObjectNormalizer::GROUPS][] = 'customer:finance_write';
        }

        return $context;
    }
}
