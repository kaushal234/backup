<?php

declare(strict_types=1);

namespace App\Traits\ORM\Survey;

use App\Entity\Directory\People;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Attribute\Groups;

trait ModifiedByTrait
{
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'id', nullable: false)]
    #[Groups(['people_public'])]
    #[Gedmo\Blameable(on: 'create')]
    protected People $createdBy;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinColumn(name: 'updated_by', referencedColumnName: 'id', nullable: true)]
    #[Groups(['people_public'])]
    #[Gedmo\Blameable(on: 'update')]
    protected ?People $updatedBy = null;

    public function setCreatedBy(People $createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy.
     */
    public function getCreatedBy(): ?People
    {
        return $this->createdBy;
    }

    /**
     * Set updatedBy.
     *
     * @return $this
     */
    public function setUpdatedBy(?People $updatedBy = null)
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * Get updatedBy.
     */
    public function getUpdatedBy(): ?People
    {
        return $this->updatedBy;
    }
}
