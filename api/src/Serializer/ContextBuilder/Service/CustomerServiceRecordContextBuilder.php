<?php

declare(strict_types=1);

namespace App\Serializer\ContextBuilder\Service;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\State\SerializerContextBuilderInterface;
use App\Entity\Service\CustomerServiceRecord\CommissioningCustomerServiceRecord;
use App\Entity\Service\CustomerServiceRecord\ServiceBulletinCustomerServiceRecord;
use App\Entity\Service\CustomerServiceRecord\TechnicianOnCallCustomerServiceRecord;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

readonly class CustomerServiceRecordContextBuilder implements SerializerContextBuilderInterface
{
    public function __construct(
        private SerializerContextBuilderInterface $decorated,
    ) {
    }

    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);
        $resourceClass = $context['resource_class'] ?? null;

        if ($context['operation'] instanceof GetCollection) {
            return $context;
        }

        if (TechnicianOnCallCustomerServiceRecord::class === $resourceClass) {
            $context[AbstractNormalizer::GROUPS][] = 'toc:read';
        }

        if (ServiceBulletinCustomerServiceRecord::class === $resourceClass) {
            $context[AbstractNormalizer::GROUPS][] = 'legacy:service_bulletin';
        }

        if (CommissioningCustomerServiceRecord::class === $resourceClass) {
            $context[AbstractNormalizer::GROUPS][] = 'customer_service_record:answer';
        }

        return $context;
    }
}
