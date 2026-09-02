<?php

declare(strict_types=1);

namespace LegacyBundle\Entity\Engineering\MasterEngineeringActivityProcess;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
class RecurringCosts
{
    #[ORM\Column(name: 'econ_target_material', type: 'integer', nullable: true)]
    private ?int $materialTarget = null;

    #[ORM\Column(name: 'econ_eac_material', type: 'integer', nullable: true)]
    private ?int $materialEac = null;

    #[ORM\Column(name: 'econ_actual_material', type: 'integer', nullable: true)]
    private ?int $materialActual = null;

    #[ORM\Column(name: 'econ_actual_material_dt', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $materialActualReportedAt = null;

    #[ORM\Column(name: 'econ_target_hours', type: 'integer', nullable: true)]
    private ?int $hoursTarget = null;

    #[ORM\Column(name: 'econ_eac_hours', type: 'integer', nullable: true)]
    private ?int $hoursEac = null;

    #[ORM\Column(name: 'econ_actual_hours', type: 'integer', nullable: true)]
    private ?int $hoursActual = null;

    #[ORM\Column(name: 'econ_actual_hours_dt', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $hoursActualReportedAt = null;

    public function setMaterialTarget(?int $materialTarget): self
    {
        $this->materialTarget = $materialTarget;

        return $this;
    }

    public function setMaterialEac(?int $materialEac): self
    {
        $this->materialEac = $materialEac;

        return $this;
    }

    public function setMaterialActual(?int $materialActual): self
    {
        $this->materialActual = $materialActual;

        return $this;
    }

    public function setMaterialActualReportedAt(?\DateTimeInterface $materialActualReportedAt): self
    {
        $this->materialActualReportedAt = $materialActualReportedAt;

        return $this;
    }

    public function setHoursTarget(?int $hoursTarget): self
    {
        $this->hoursTarget = $hoursTarget;

        return $this;
    }

    public function setHoursEac(?int $hoursEac): self
    {
        $this->hoursEac = $hoursEac;

        return $this;
    }

    public function setHoursActual(?int $hoursActual): self
    {
        $this->hoursActual = $hoursActual;

        return $this;
    }

    public function setHoursActualReportedAt(?\DateTimeInterface $hoursActualReportedAt): self
    {
        $this->hoursActualReportedAt = $hoursActualReportedAt;

        return $this;
    }

    public function getMaterial(): EconomicMetric
    {
        return new EconomicMetric(
            $this->materialTarget,
            $this->materialEac,
            $this->materialActual,
            $this->materialActualReportedAt,
        );
    }

    public function getHours(): EconomicMetric
    {
        return new EconomicMetric(
            $this->hoursTarget,
            $this->hoursEac,
            $this->hoursActual,
            $this->hoursActualReportedAt,
        );
    }
}
