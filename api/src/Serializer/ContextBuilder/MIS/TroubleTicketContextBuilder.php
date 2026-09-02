<?php

declare(strict_types=1);

namespace App\Serializer\ContextBuilder\MIS;

use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\State\ProviderInterface;
use ApiPlatform\State\SerializerContextBuilderInterface;
use App\Entity\MIS\TroubleTicket\TroubleTicket;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

class TroubleTicketContextBuilder implements SerializerContextBuilderInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.item_provider')]
        private readonly ProviderInterface $provider,
        private readonly ResourceMetadataCollectionFactoryInterface $resourceMetadataCollectionFactory,
        private readonly SerializerContextBuilderInterface $decorated,
        private readonly Security $security
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);

        if (TroubleTicket::class !== $context['resource_class'] || $normalization) {
            return $context;
        }

        if ($this->security->isGranted('FEATURE_TROUBLE_TICKET_OPEN_ON_BEHALF')) {
            $context[AbstractObjectNormalizer::GROUPS][] = 'trouble_ticket:on_behalf';
        }

        if ($this->security->isGranted('FEATURE_TROUBLE_TICKET_EDIT')) {
            $context[AbstractObjectNormalizer::GROUPS][] = 'trouble_ticket:edit';
        }
        $user = $this->security->getUser();
        $troubleTicket = $this->provider->provide($this->resourceMetadataCollectionFactory->create(TroubleTicket::class)->getOperation(), ['id' => $request->attributes->get('id')]);
        if (!$troubleTicket instanceof TroubleTicket) {
            return $context;
        }
        $moo = $troubleTicket->module->getOperationalOwner();
        if ($user === $moo || $this->security->isGranted('FEATURE_TROUBLE_TICKET_ADD_USER_STORY')) {
            $context[AbstractObjectNormalizer::GROUPS][] = 'trouble_ticket:user_story:add';
        }

        return $context;
    }
}
