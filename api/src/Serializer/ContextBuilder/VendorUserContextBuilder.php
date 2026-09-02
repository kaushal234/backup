<?php

declare(strict_types=1);

namespace App\Serializer\ContextBuilder;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Put;
use ApiPlatform\State\SerializerContextBuilderInterface;
use App\Entity\Purchasing\VendorUser;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class VendorUserContextBuilder implements SerializerContextBuilderInterface
{
    private readonly SerializerContextBuilderInterface $decorated;

    private readonly Security $security;

    public function __construct(SerializerContextBuilderInterface $decorated, Security $security)
    {
        $this->decorated = $decorated;
        $this->security = $security;
    }

    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);

        if (VendorUser::class !== $context['resource_class']) {
            return $context;
        }

        /** @var Operation $operation */
        $operation = $request->attributes->get('_api_operation');
        if ($normalization && $operation instanceof Get && $this->security->isGranted('FEATURE_VENDOR_USER_WRITE')) {
            $context[AbstractNormalizer::GROUPS] = ['vendor_user'];

            return $context;
        }

        if (!$normalization && $operation instanceof Put && $this->security->isGranted('FEATURE_VENDOR_USER_WRITE')) {
            $context[AbstractNormalizer::GROUPS] = ['vendor_user:write_admin'];

            return $context;
        }

        return $context;
    }
}
