<?php

declare(strict_types=1);

namespace App\Entity\Directory;

use ApiPlatform\Metadata\ApiProperty;
use Doctrine\ORM\Mapping as ORM;
use Misd\PhoneNumberBundle\Validator\Constraints as PhoneAssert;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Embeddable]
class LocationContact
{
    #[ApiProperty(iris: ['https://schema.org/telephone'])]
    #[ORM\Column(type: 'string', length: 60, nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 60)]
    #[Groups(['location', 'location_detail', 'location_write'])]
    #[PhoneAssert\PhoneNumber]
    private ?string $telephone = null;

    #[ApiProperty(iris: ['https://schema.org/telephone'])]
    #[ORM\Column(type: 'string', length: 60, nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 60)]
    #[Groups(['location', 'location_detail', 'location_write'])]
    #[PhoneAssert\PhoneNumber]
    private ?string $fax = null;

    #[ApiProperty(iris: ['https://schema.org/email'])]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Email]
    #[Assert\Length(max: 255)]
    #[Groups(['location', 'location_detail', 'location_write'])]
    private ?string $sparePartsEmail = null;

    #[ApiProperty(iris: ['https://schema.org/email'])]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Email]
    #[Assert\Length(max: 255)]
    #[Groups(['location', 'location_detail', 'location_write'])]
    private ?string $partsCustomerSupportEmail = null;

    #[ApiProperty(iris: ['https://schema.org/telephone'])]
    #[ORM\Column(type: 'string', length: 60, nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 60)]
    #[Groups(['location', 'location_detail', 'location_write'])]
    #[PhoneAssert\PhoneNumber]
    private ?string $sparePartsTelephone = null;

    #[ApiProperty(iris: ['https://schema.org/telephone'])]
    #[ORM\Column(type: 'string', length: 60, nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 60)]
    #[Groups(['location', 'location_detail', 'location_write'])]
    #[PhoneAssert\PhoneNumber]
    private ?string $sparePartsFax = null;

    #[ApiProperty(iris: ['https://schema.org/email'])]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Email]
    #[Assert\Length(max: 255)]
    #[Groups(['location', 'location_detail', 'location_write'])]
    private ?string $serviceHubEmail = null;

    #[ApiProperty(iris: ['https://schema.org/telephone'])]
    #[ORM\Column(type: 'string', length: 60, nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 60)]
    #[Groups(['location', 'location_detail', 'location_write'])]
    #[PhoneAssert\PhoneNumber]
    private ?string $serviceHubTelephone = null;

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    /**
     * @return string
     */
    public function getFax()
    {
        return $this->fax;
    }

    /**
     * @param string $fax
     *
     * @return $this
     */
    public function setFax($fax)
    {
        $this->fax = $fax;

        return $this;
    }

    public function getSparePartsEmail(): ?string
    {
        return $this->sparePartsEmail;
    }

    public function setSparePartsEmail(?string $sparePartsEmail): self
    {
        $this->sparePartsEmail = $sparePartsEmail;

        return $this;
    }

    /**
     * @return string
     */
    public function getSparePartsTelephone()
    {
        return $this->sparePartsTelephone;
    }

    /**
     * @param string $sparePartsTelephone
     *
     * @return $this
     */
    public function setSparePartsTelephone($sparePartsTelephone)
    {
        $this->sparePartsTelephone = $sparePartsTelephone;

        return $this;
    }

    /**
     * @return string
     */
    public function getSparePartsFax()
    {
        return $this->sparePartsFax;
    }

    /**
     * @param string $sparePartsFax
     *
     * @return $this
     */
    public function setSparePartsFax($sparePartsFax)
    {
        $this->sparePartsFax = $sparePartsFax;

        return $this;
    }

    /**
     * @return string
     */
    public function getServiceHubEmail()
    {
        return $this->serviceHubEmail;
    }

    /**
     * @param string $serviceHubEmail
     *
     * @return $this
     */
    public function setServiceHubEmail($serviceHubEmail)
    {
        $this->serviceHubEmail = $serviceHubEmail;

        return $this;
    }

    /**
     * @return string
     */
    public function getServiceHubTelephone()
    {
        return $this->serviceHubTelephone;
    }

    /**
     * @param string $serviceHubTelephone
     *
     * @return $this
     */
    public function setServiceHubTelephone($serviceHubTelephone)
    {
        $this->serviceHubTelephone = $serviceHubTelephone;

        return $this;
    }

    public function getPartsCustomerSupportEmail(): ?string
    {
        return $this->partsCustomerSupportEmail;
    }

    public function setPartsCustomerSupportEmail(?string $partsCustomerSupportEmail): self
    {
        $this->partsCustomerSupportEmail = $partsCustomerSupportEmail;

        return $this;
    }
}
