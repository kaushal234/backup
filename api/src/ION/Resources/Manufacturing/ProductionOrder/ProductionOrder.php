<?php

declare(strict_types=1);

namespace App\ION\Resources\Manufacturing\ProductionOrder;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Symfony\Action\NotFoundAction;
use App\ION\DataProcessor\IONDataProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new Post(name: 'post_production_order', processor: IONDataProcessor::class),
        new Get(
            requirements: ['id' => '.*'],
            controller: NotFoundAction::class,
            output: false,
            read: false,
        ),
    ],
    routePrefix: 'ion',
    normalizationContext: ['groups' => ['production_order:view']],
    denormalizationContext: ['groups' => ['production_order:write']],
    security: "is_granted('FEATURE_PRODUCTION_ORDER_WRITE')",
)]
class ProductionOrder
{
    #[ApiProperty(identifier: true)]
    #[Groups(['production_order:view', 'production_order:write', IONDataProcessor::ION_SYNC])]
    public int $site;

    /**
     * @var Collection<ProductionOrderProject>
     */
    #[Groups(['production_order:view', 'production_order:write', IONDataProcessor::ION_SYNC])]
    private Collection $project;

    public function __construct()
    {
        $this->project = new ArrayCollection();
    }

    public function addProject(ProductionOrderProject $project): self
    {
        $this->project->add($project);

        return $this;
    }

    public function removeProject(ProductionOrderProject $project): self
    {
        return $this;
    }

    /**
     * @return Collection<ProductionOrderProject>
     */
    public function getProject(): Collection
    {
        return $this->project;
    }
}
