<?php

declare(strict_types=1);

namespace App\ION\Resources\Manufacturing\JobShop;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Controller\Manufacturing\ManualCustomizedBillOfMaterialsPdfController;
use App\ION\DataProvider\CachedIONItemDataProvider;
use App\ION\Filter\DataAreaFilter;

/**
 * @deprecated
 */
#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/manual_customized_bill_of_materials_pdf/{id}',
            formats: ['pdf' => 'application/pdf'],
            controller: ManualCustomizedBillOfMaterialsPdfController::class,
            name: 'pdf',
        ),
        new Get(
            requirements: ['id' => '.*'],
            security: "is_granted('BILL_OF_MATERIAL_VENDOR_VOTER', object)",
        ),
    ],
    routePrefix: 'ion',
    normalizationContext: ['groups' => ['cbom']],
    denormalizationContext: [],
    provider: CachedIONItemDataProvider::class,
)]
#[ApiFilter(DataAreaFilter::class, properties: ['date', 'signalCodeFilter', 'signalCodeFilterMethod', 'signalCodeAttribute', 'otherLanguage'])]
class ManualCustomizedBillOfMaterials extends CustomizedBillOfMaterials
{
}
