<?php

declare(strict_types=1);

namespace AppBundle\Manager\Purchasing\VendorWarrantyClaim;

use ApiBundle\Client;
use ApiBundle\Model\ApiData;
use AppBundle\Controller\Purchasing\VendorWarrantyClaimController;
use AppBundle\Form\Type\StatusChoiceType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class StatusForm
{
    public function __construct(private readonly Client $client, private readonly FormFactoryInterface $formFactory)
    {
    }

    public function create(ApiData $vendorWarrantyClaim): FormInterface
    {
        $statuses = $this->client->get(VendorWarrantyClaimController::VENDOR_WARRANTY_CLAIM_STATUS_URL, [
            'query' => ['order' => ['position' => 'ASC']],
        ]);

        $choiceTypeStatuses = [];
        foreach ($statuses['hydra:member'] as $status) {
            if (false === $vendorWarrantyClaim['scarRequested'] && 'VALIDATE_SCAR' === $status['name']) {
                continue;
            }

            $choiceTypeStatuses[] = $status['name'];
        }

        return $this->formFactory->create(StatusChoiceType::class, null, [
            'choices' => $choiceTypeStatuses,
            'id' => $vendorWarrantyClaim->getIriId(),
            'route' => 'vendor_warranty_claim_status',
        ]);
    }
}
