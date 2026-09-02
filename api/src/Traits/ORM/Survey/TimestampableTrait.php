<?php

declare(strict_types=1);

namespace App\Traits\ORM\Survey;

use ApiPlatform\Metadata\ApiProperty;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Attribute\Groups;

trait TimestampableTrait
{
    #[ApiProperty(iris: ['https://schema.org/Date'])]
    #[ORM\Column(type: 'datetime')]
    #[Groups(['survey_detail', 'people_public'])]
    #[Gedmo\Timestampable(on: 'create')]
    protected \DateTimeInterface $createdAt;

    #[ApiProperty(iris: ['https://schema.org/Date'])]
    #[ORM\Column(type: 'datetime')]
    #[Groups(['survey_detail', 'people_public'])]
    #[Gedmo\Timestampable(on: 'update')]
    protected \DateTimeInterface $updatedAt;

    /**
     * Sets createdAt.
     *
     * @return $this
     */
    public function setCreatedAt(\DateTimeInterface $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Returns createdAt.
     */
    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * Sets updatedAt.
     *
     * @return $this
     */
    public function setUpdatedAt(\DateTimeInterface $updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Returns updatedAt.
     */
    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }
}
