<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Exception\MissingResourceException;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An address.
 */
#[ORM\Embeddable]
class AddressWithCountry extends Address implements \Stringable
{
    #[ApiProperty(iris: ['https://schema.org/addressCountry'])]
    #[ORM\Column(type: 'string', length: 2, nullable: true)]
    #[Assert\Country]
    #[Groups(['address', 'address_write', 'people_detail', 'location_detail', 'juridical_location_detail', 'user:me', 'people:export', 'people:buyer'])]
    private ?string $country = null;

    public function __toString()
    {
        try {
            $country = null !== $this->country && '' !== $this->country ? Countries::getName($this->country) : '';
        } catch (MissingResourceException $missingResourceException) {
            $country = '';
        }

        return \sprintf('%s
%s',
            parent::__toString(),
            $country
        );
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * @return AddressWithCountry
     */
    public function setCountry(?string $country)
    {
        $this->country = $country;

        return $this;
    }
}
