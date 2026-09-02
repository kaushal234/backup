<?php

declare(strict_types=1);

namespace LegacyBundle\Entity\Engineering\MasterEngineeringActivityProcess;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
class EconomicsHeader
{
    #[ORM\Column(name: 'econ_capitalized', type: 'string', length: 1)]
    public string $isEngineeringProgramCapitalized = '';

    #[ORM\Column(name: 'econ_currency', type: 'string', length: 3)]
    public string $programCurrency = '';

    #[ORM\Column(name: 'cost_calculation_method', type: 'string', length: 35)]
    public string $costCalculationMethod = '';
}
