<?php

declare(strict_types=1);

namespace App\Serializer\ContextBuilder;

use ApiPlatform\Metadata\Exception\ItemNotFoundException;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\State\ProviderInterface;
use ApiPlatform\State\SerializerContextBuilderInterface;
use App\Entity\Finance\InvoiceRecord;
use App\Entity\Sales\CustomerErpReference;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

class InvoiceRecordContextBuilder implements SerializerContextBuilderInterface
{
    public function __construct(
        private readonly SerializerContextBuilderInterface $decorated,
        private readonly Security $security,
        private readonly DecoderInterface $decoder,
        private readonly IriConverterInterface $iriConverter,
        #[Autowire(service: 'api_platform.doctrine.orm.state.item_provider')]
        private readonly ProviderInterface $provider,
        private readonly ResourceMetadataCollectionFactoryInterface $resourceMetadataCollectionFactory,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);

        if (InvoiceRecord::class !== $context['resource_class'] || $normalization) {
            return $context;
        }

        if (Request::METHOD_POST === $request->getMethod()) {
            $content = $this->decoder->decode((string) $request->getContent(), JsonEncoder::FORMAT);
            if (!isset($content['revisedDueDate']) || !isset($content['customerErpReference'])) {
                return $context;
            }

            try {
                /** @var CustomerErpReference $customerErpReference */
                $customerErpReference = $this->iriConverter->getResourceFromIri($content['customerErpReference']);
            } catch (ItemNotFoundException $e) {
                // if no customerErpReference, validation of object will throw an error
                return $context;
            }

            if ($this->security->isGranted('INVOICE_RECORD_DUE_DATE_VOTER', $customerErpReference->getSso())) {
                $context[AbstractObjectNormalizer::GROUPS][] = 'invoice_record:write_once';
            }

            return $context;
        }

        /** @var InvoiceRecord $invoiceRecord */
        $invoiceRecord = $this->provider->provide($this->resourceMetadataCollectionFactory->create(InvoiceRecord::class)->getOperation(), ['id' => $request->attributes->get('id')]);
        if (null === $invoiceRecord->revisedDueDate && $this->security->isGranted('INVOICE_RECORD_DUE_DATE_VOTER', $invoiceRecord->customerErpReference->getSso())) {
            $context[AbstractObjectNormalizer::GROUPS][] = 'invoice_record:write_once';
        }

        return $context;
    }
}
