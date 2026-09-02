<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An address.
 */
#[ORM\Embeddable]
class Address implements \Stringable
{
    #[ApiProperty(iris: ['https://schema.org/streetAddress'])]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 255)]
    #[Groups(['address', 'address_write', 'people_detail', 'location_detail', 'juridical_location_detail', 'user:me', 'people:export', 'people:buyer', 'extranet_user_address_campaign'])]
    protected ?string $street1 = null;

    #[ApiProperty(iris: ['https://schema.org/streetAddress'])]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 255)]
    #[Groups(['address', 'address_write', 'people_detail', 'location_detail', 'juridical_location_detail', 'user:me', 'people:export', 'people:buyer', 'extranet_user_address_campaign'])]
    protected ?string $street2 = null;

    #[ApiProperty(iris: ['https://schema.org/postalCode'])]
    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 20)]
    #[Groups(['address', 'address_write', 'people_detail', 'location_detail', 'juridical_location_detail', 'user:me', 'people:export', 'people:buyer', 'extranet_user_address_campaign'])]
    protected ?string $postalCode = null;

    #[ApiProperty(iris: ['https://schema.org/addressLocality'])]
    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 50)]
    #[Groups(['address', 'address_write', 'people_detail', 'location_detail', 'juridical_location_detail', 'user:me', 'people:export', 'people:buyer', 'extranet_user_address_campaign'])]
    protected ?string $city = null;

    #[ApiProperty(iris: ['https://schema.org/addressLocality'])]
    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 50)]
    #[Groups(['address', 'address_write', 'people_detail', 'location_detail', 'juridical_location_detail', 'user:me'])]
    protected ?string $town = null;

    #[ApiProperty(iris: ['https://schema.org/addressLocality'])]
    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 50)]
    #[Groups(['address', 'address_write', 'people_detail', 'location_detail', 'juridical_location_detail', 'user:me', 'people:buyer', 'extranet_user_address_campaign'])]
    protected ?string $state = null;

    public function __toString()
    {
        return \sprintf('%s
%s
%s
%s',
            $this->street1,
            mb_trim($this->street2.' '.$this->town),
            mb_trim($this->postalCode.' '.$this->city),
            mb_trim($this->state.' ')
        );
    }

    /**
     * @return string
     */
    public function getStreet1()
    {
        return $this->street1;
    }

    /**
     * @param string $street1
     *
     * @return $this
     */
    public function setStreet1($street1)
    {
        $this->street1 = $street1;

        return $this;
    }

    /**
     * @return string
     */
    public function getStreet2()
    {
        return $this->street2;
    }

    /**
     * @param string $street2
     *
     * @return $this
     */
    public function setStreet2($street2)
    {
        $this->street2 = $street2;

        return $this;
    }

    /**
     * @return string
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * @param string $postalCode
     *
     * @return $this
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $city
     *
     * @return $this
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return string
     */
    public function getTown()
    {
        return $this->town;
    }

    /**
     * @param string $town
     *
     * @return $this
     */
    public function setTown($town)
    {
        $this->town = $town;

        return $this;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param string $state
     *
     * @return $this
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }
}
