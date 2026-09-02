<?php

declare(strict_types=1);

namespace App\Entity\Sales;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use App\Doctrine\Mapping\Attributes as App;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Entity\LegacyIdentifierTrait;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new Post(
            denormalizationContext: ['groups' => ['sales_forecast_write']],
            security: "is_granted('FEATURE_SALES_FORECAST_CREATE')",
            validationContext: ['groups' => ['Default', 'sales_forecast_create']]
        ),
        new Get(),
    ],
    routePrefix: 'sales'
)]
#[ORM\Table(name: 'sales_forecasts_master')]
#[App\Loggable]
#[Legacy\Synchronize(table: 'sfr_master')]
#[Legacy\ExtraColumn(column: 'name', value: '')]
class MasterSalesForecast
{
    use LegacyIdentifierTrait;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    /**
     * @var Collection<SalesForecast>
     */
    #[ORM\OneToMany(mappedBy: 'masterSalesForecast', targetEntity: 'App\Entity\Sales\SalesForecast', cascade: ['persist'])]
    #[Groups('sales_forecast_write')]
    #[Assert\Count(min: 1)]
    #[Assert\Valid]
    private Collection $salesForecasts;

    public function __construct()
    {
        $this->salesForecasts = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Collection<SalesForecast>
     */
    public function getSalesForecasts(): Collection
    {
        return $this->salesForecasts;
    }

    /**
     * @return $this
     */
    public function addSalesForecast(SalesForecast $salesForecast): self
    {
        $this->salesForecasts->add($salesForecast);

        $salesForecast->setMasterSalesForecast($this);

        return $this;
    }

    /**
     * @return $this
     */
    public function removeSalesForecast(SalesForecast $salesForecast): self
    {
        $this->salesForecasts->removeElement($salesForecast);

        return $this;
    }
}
