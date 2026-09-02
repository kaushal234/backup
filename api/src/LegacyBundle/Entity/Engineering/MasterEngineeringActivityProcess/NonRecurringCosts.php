<?php

declare(strict_types=1);

namespace LegacyBundle\Entity\Engineering\MasterEngineeringActivityProcess;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
class NonRecurringCosts
{
    #[ORM\Column(name: 'econ_target_dh', type: 'integer', nullable: true)]
    private ?int $developmentHoursTarget = null;

    #[ORM\Column(name: 'econ_eac_dh', type: 'integer', nullable: true)]
    private ?int $developmentHoursEac = null;

    #[ORM\Column(name: 'econ_actual_dh', type: 'integer', nullable: true)]
    private ?int $developmentHoursActual = null;

    #[ORM\Column(name: 'econ_actual_dh_dt', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $developmentHoursActualReportedAt = null;

    #[ORM\Column(name: 'econ_target_sea', type: 'integer', nullable: true)]
    private ?int $subcontractedEngineeringAmountTarget = null;

    #[ORM\Column(name: 'econ_eac_sea', type: 'integer', nullable: true)]
    private ?int $subcontractedEngineeringAmountEac = null;

    #[ORM\Column(name: 'econ_actual_sea', type: 'integer', nullable: true)]
    private ?int $subcontractedEngineeringAmountActual = null;

    #[ORM\Column(name: 'econ_actual_sea_dt', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $subcontractedEngineeringAmountActualReportedAt = null;

    #[ORM\Column(name: 'econ_target_maoc', type: 'integer', nullable: true)]
    private ?int $materialAndOtherCostsTarget = null;

    #[ORM\Column(name: 'econ_eac_maoc', type: 'integer', nullable: true)]
    private ?int $materialAndOtherCostsEac = null;

    #[ORM\Column(name: 'econ_actual_maoc', type: 'integer', nullable: true)]
    private ?int $materialAndOtherCostsActual = null;

    #[ORM\Column(name: 'econ_actual_maoc_dt', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $materialAndOtherCostsActualReportedAt = null;

    #[ORM\Column(name: 'econ_target_pmc', type: 'integer', nullable: true)]
    private ?int $prototypeMaterialCostsTarget = null;

    #[ORM\Column(name: 'econ_eac_pmc', type: 'integer', nullable: true)]
    private ?int $prototypeMaterialCostsEac = null;

    #[ORM\Column(name: 'econ_actual_pmc', type: 'integer', nullable: true)]
    private ?int $prototypeMaterialCostsActual = null;

    #[ORM\Column(name: 'econ_actual_pmc_dt', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $prototypeMaterialCostsActualReportedAt = null;

    #[ORM\Column(name: 'econ_target_plh', type: 'integer', nullable: true)]
    private ?int $prototypeLaborHoursTarget = null;

    #[ORM\Column(name: 'econ_eac_plh', type: 'integer', nullable: true)]
    private ?int $prototypeLaborHoursEac = null;

    #[ORM\Column(name: 'econ_actual_plh', type: 'integer', nullable: true)]
    private ?int $prototypeLaborHoursActual = null;

    #[ORM\Column(name: 'econ_actual_plh_dt', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $prototypeLaborHoursActualReportedAt = null;

    public function setDevelopmentHoursTarget(?int $developmentHoursTarget): self
    {
        $this->developmentHoursTarget = $developmentHoursTarget;

        return $this;
    }

    public function setDevelopmentHoursEac(?int $developmentHoursEac): self
    {
        $this->developmentHoursEac = $developmentHoursEac;

        return $this;
    }

    public function setDevelopmentHoursActual(?int $developmentHoursActual): self
    {
        $this->developmentHoursActual = $developmentHoursActual;

        return $this;
    }

    public function setDevelopmentHoursActualReportedAt(?\DateTimeInterface $developmentHoursActualReportedAt): self
    {
        $this->developmentHoursActualReportedAt = $developmentHoursActualReportedAt;

        return $this;
    }

    public function setMaterialAndOtherCostsActual(?int $materialAndOtherCostsActual): self
    {
        $this->materialAndOtherCostsActual = $materialAndOtherCostsActual;

        return $this;
    }

    public function setMaterialAndOtherCostsEac(?int $materialAndOtherCostsEac): self
    {
        $this->materialAndOtherCostsEac = $materialAndOtherCostsEac;

        return $this;
    }

    public function setMaterialAndOtherCostsTarget(?int $materialAndOtherCostsTarget): self
    {
        $this->materialAndOtherCostsTarget = $materialAndOtherCostsTarget;

        return $this;
    }

    public function setSubcontractedEngineeringAmountActualReportedAt(?\DateTimeInterface $subcontractedEngineeringAmountActualReportedAt): self
    {
        $this->subcontractedEngineeringAmountActualReportedAt = $subcontractedEngineeringAmountActualReportedAt;

        return $this;
    }

    public function setSubcontractedEngineeringAmountActual(?int $subcontractedEngineeringAmountActual): self
    {
        $this->subcontractedEngineeringAmountActual = $subcontractedEngineeringAmountActual;

        return $this;
    }

    public function setSubcontractedEngineeringAmountEac(?int $subcontractedEngineeringAmountEac): self
    {
        $this->subcontractedEngineeringAmountEac = $subcontractedEngineeringAmountEac;

        return $this;
    }

    public function setSubcontractedEngineeringAmountTarget(?int $subcontractedEngineeringAmountTarget): self
    {
        $this->subcontractedEngineeringAmountTarget = $subcontractedEngineeringAmountTarget;

        return $this;
    }

    public function setMaterialAndOtherCostsActualReportedAt(?\DateTimeInterface $materialAndOtherCostsActualReportedAt): self
    {
        $this->materialAndOtherCostsActualReportedAt = $materialAndOtherCostsActualReportedAt;

        return $this;
    }

    public function setPrototypeMaterialCostsTarget(?int $prototypeMaterialCostsTarget): self
    {
        $this->prototypeMaterialCostsTarget = $prototypeMaterialCostsTarget;

        return $this;
    }

    public function setPrototypeMaterialCostsEac(?int $prototypeMaterialCostsEac): self
    {
        $this->prototypeMaterialCostsEac = $prototypeMaterialCostsEac;

        return $this;
    }

    public function setPrototypeMaterialCostsActual(?int $prototypeMaterialCostsActual): self
    {
        $this->prototypeMaterialCostsActual = $prototypeMaterialCostsActual;

        return $this;
    }

    public function setPrototypeMaterialCostsActualReportedAt(?\DateTimeInterface $prototypeMaterialCostsActualReportedAt): self
    {
        $this->prototypeMaterialCostsActualReportedAt = $prototypeMaterialCostsActualReportedAt;

        return $this;
    }

    public function setPrototypeLaborHoursTarget(?int $prototypeLaborHoursTarget): self
    {
        $this->prototypeLaborHoursTarget = $prototypeLaborHoursTarget;

        return $this;
    }

    public function setPrototypeLaborHoursEac(?int $prototypeLaborHoursEac): self
    {
        $this->prototypeLaborHoursEac = $prototypeLaborHoursEac;

        return $this;
    }

    public function setPrototypeLaborHoursActual(?int $prototypeLaborHoursActual): self
    {
        $this->prototypeLaborHoursActual = $prototypeLaborHoursActual;

        return $this;
    }

    public function setPrototypeLaborHoursActualReportedAt(?\DateTimeInterface $prototypeLaborHoursActualReportedAt): self
    {
        $this->prototypeLaborHoursActualReportedAt = $prototypeLaborHoursActualReportedAt;

        return $this;
    }

    public function getDevelopmentHours(): EconomicMetric
    {
        return new EconomicMetric(
            $this->developmentHoursTarget,
            $this->developmentHoursEac,
            $this->developmentHoursActual,
            $this->developmentHoursActualReportedAt,
        );
    }

    public function getSubcontractedEngineeringAmount(): EconomicMetric
    {
        return new EconomicMetric(
            $this->subcontractedEngineeringAmountTarget,
            $this->subcontractedEngineeringAmountEac,
            $this->subcontractedEngineeringAmountActual,
            $this->subcontractedEngineeringAmountActualReportedAt,
        );
    }

    public function getMaterialAndOtherCosts(): EconomicMetric
    {
        return new EconomicMetric(
            $this->materialAndOtherCostsTarget,
            $this->materialAndOtherCostsEac,
            $this->materialAndOtherCostsActual,
            $this->materialAndOtherCostsActualReportedAt,
        );
    }

    public function getPrototypeMaterialCosts(): EconomicMetric
    {
        return new EconomicMetric(
            $this->prototypeMaterialCostsTarget,
            $this->prototypeMaterialCostsEac,
            $this->prototypeMaterialCostsActual,
            $this->prototypeMaterialCostsActualReportedAt,
        );
    }

    public function getPrototypeLaborHours(): EconomicMetric
    {
        return new EconomicMetric(
            $this->prototypeLaborHoursTarget,
            $this->prototypeLaborHoursEac,
            $this->prototypeLaborHoursActual,
            $this->prototypeLaborHoursActualReportedAt,
        );
    }
}
