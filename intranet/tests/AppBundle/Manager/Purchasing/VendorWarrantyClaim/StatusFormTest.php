<?php

declare(strict_types=1);

namespace Tests\AppBundle\Manager\Purchasing\VendorWarrantyClaim;

use ApiBundle\Client;
use ApiBundle\Model\ApiData;
use AppBundle\Controller\Purchasing\VendorWarrantyClaimController;
use AppBundle\Form\Type\StatusChoiceType;
use AppBundle\Manager\Purchasing\VendorWarrantyClaim\StatusForm;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

final class StatusFormTest extends TestCase
{
    public function testCreateFiltersValidateScarWhenScarIsNotRequested(): void
    {
        $client = $this->createMock(Client::class);
        $formFactory = $this->createMock(FormFactoryInterface::class);
        $form = $this->createMock(FormInterface::class);

        $vendorWarrantyClaim = new ApiData([
            '@id' => '/vendor-warranty-claims/1',
            '@type' => 'WcVendorWarrantyClaim',
            'scarRequested' => false,
        ]);

        $client->expects(self::once())->method('get')
            ->with(
                VendorWarrantyClaimController::VENDOR_WARRANTY_CLAIM_STATUS_URL,
                [
                    'query' => ['order' => ['position' => 'ASC']],
                ]
            )
            ->willReturn([
                'hydra:member' => [
                    ['name' => 'OPEN'],
                    ['name' => 'VALIDATE_SCAR'],
                    ['name' => 'CLOSED'],
                ],
            ]);

        $formFactory->expects(self::once())->method('create')
            ->with(
                StatusChoiceType::class,
                null,
                [
                    'choices' => ['OPEN', 'CLOSED'],
                    'id' => $vendorWarrantyClaim->getIriId(),
                    'route' => 'vendor_warranty_claim_status',
                ]
            )
            ->willReturn($form);

        $service = new StatusForm($client, $formFactory);

        self::assertSame($form, $service->create($vendorWarrantyClaim));
    }

    public function testCreateKeepsValidateScarWhenScarIsRequested(): void
    {
        $client = $this->createMock(Client::class);
        $formFactory = $this->createMock(FormFactoryInterface::class);
        $form = $this->createMock(FormInterface::class);

        $vendorWarrantyClaim = new ApiData([
            '@id' => '/vendor-warranty-claims/1',
            '@type' => 'WcVendorWarrantyClaim',
            'scarRequested' => true,
        ]);

        $client->expects(self::once())->method('get')
            ->willReturn([
                'hydra:member' => [
                    ['name' => 'OPEN'],
                    ['name' => 'VALIDATE_SCAR'],
                    ['name' => 'CLOSED'],
                ],
            ]);

        $formFactory->expects(self::once())->method('create')
            ->with(
                StatusChoiceType::class,
                null,
                [
                    'choices' => ['OPEN', 'VALIDATE_SCAR', 'CLOSED'],
                    'id' => $vendorWarrantyClaim->getIriId(),
                    'route' => 'vendor_warranty_claim_status',
                ]
            )
            ->willReturn($form);

        $service = new StatusForm($client, $formFactory);

        self::assertSame($form, $service->create($vendorWarrantyClaim));
    }
}
