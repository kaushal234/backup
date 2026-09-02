<?php

declare(strict_types=1);

namespace App\Entity\Directory;

use ApiPlatform\Metadata\ApiProperty;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Embeddable]
class LocationState
{
    #[ApiProperty(iris: ['https://schema.org/Boolean'])]
    #[ORM\Column(name: 'public', type: 'boolean')]
    #[Assert\Type(type: 'boolean')]
    #[Groups(['location', 'location_detail', 'location_write'])]
    private bool $public = false;

    #[ApiProperty(iris: ['https://schema.org/Boolean'])]
    #[ORM\Column(name: 'hidden', type: 'boolean')]
    #[Assert\Type(type: 'boolean')]
    #[Groups(['location', 'location_detail', 'location_write'])]
    private bool $hidden = false;

    #[ApiProperty(iris: ['https://schema.org/Boolean'])]
    #[ORM\Column(name: 'disabled', type: 'boolean')]
    #[Assert\Type(type: 'boolean')]
    #[Groups(['location', 'location_detail', 'location_write'])]
    private bool $disabled = false;

    /**
     * @return bool
     */
    public function isPublic()
    {
        return $this->public;
    }

    /**
     * @param bool $public
     *
     * @return $this
     */
    public function setPublic($public)
    {
        $this->public = $public;

        return $this;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return $this->hidden;
    }

    /**
     * @param bool $hidden
     *
     * @return $this
     */
    public function setHidden($hidden)
    {
        $this->hidden = $hidden;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDisabled()
    {
        return $this->disabled;
    }

    /**
     * @param bool $disabled
     *
     * @return $this
     */
    public function setDisabled($disabled)
    {
        $this->disabled = $disabled;

        return $this;
    }
}
