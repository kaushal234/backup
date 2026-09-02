<?php

declare(strict_types=1);

namespace App\Serializer\ContextBuilder;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\SerializerContextBuilderInterface;
use App\Entity\AuthorizedApplication;
use App\Entity\Purchasing\VendorUser;
use App\Entity\Quality\SupplierCorrectiveActionRequest;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

class SupplierCorrectiveActionRequestContextBuilder implements SerializerContextBuilderInterface
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
        if (SupplierCorrectiveActionRequest::class !== $context['resource_class'] || $normalization) {
            return $context;
        }

        $user = $this->security->getUser();
        if ($request->isMethod(Request::METHOD_POST) && ($user instanceof VendorUser || $user instanceof AuthorizedApplication)) {
            $context[AbstractObjectNormalizer::GROUPS] = array_diff($context[AbstractObjectNormalizer::GROUPS], ['supplier_corrective_action_request:create']);
            $context[AbstractObjectNormalizer::GROUPS][] = 'supplier_corrective_action_request:create_vendor';
        }

        /** @var Operation|null $operation */
        $operation = $request->attributes->get('_api_operation');
        if (!$request->isMethod(Request::METHOD_PUT) || (null !== $operation && 'update_supplier_corrective_action_request_status' === $operation->getName())) {
            return $context;
        }

        if ($this->security->isGranted('FEATURE_SCAR_EDIT_FULL')) {
            $context[AbstractObjectNormalizer::GROUPS][] = 'supplier_corrective_action_request:edit_full';
        }

        return $context;
    }
}
