<?php

declare(strict_types=1);

namespace App\Dto\Quality;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Controller\Quality\CrabFromOrderLineBatchController;
use App\Entity\Quality\Crab;
use App\Entity\Quality\CrabCode;
use App\Entity\Quality\CrabDepartment;
use App\Entity\Quality\NonConformity;
use App\Validator\Constraints\CrabCodeRequirePartNumber;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[CrabCodeRequirePartNumber]
#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/crabs/add_from_order_line',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            controller: CrabFromOrderLineBatchController::class,
            output: false,
            validate: false,
            name: 'post_from_sol',
        ),
    ],
    routePrefix: 'quality',
    denormalizationContext: ['groups' => ['crab:sales_order_line']]
)]
class CrabSalesOrderLine
{
    #[Groups(['crab:sales_order_line'])]
    public string $description;

    #[Assert\Choice(choices: [Crab::ASSY, Crab::PDI, Crab::QA, Crab::TEST, Crab::PDI_CSC, Crab::PDI_SOL])]
    #[Groups(['crab:sales_order_line'])]
    public string $category;

    #[Groups(['crab:sales_order_line'])]
    public CrabDepartment $department;

    #[Groups(['crab:sales_order_line'])]
    public ?CrabCode $code = null;

    #[Groups(['crab:sales_order_line'])]
    public ?NonConformity $nonConformity = null;

    #[Groups(['crab:sales_order_line'])]
    public ?string $eapId = null;

    #[Groups(['crab:sales_order_line'])]
    public string $orderLine;

    #[Groups(['crab:sales_order_line'])]
    public ?string $partNumber = null;

    #[Groups(['crab:sales_order_line'])]
    public ?string $partDescription = null;
}
