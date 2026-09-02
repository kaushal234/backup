<?php

declare(strict_types=1);

namespace App\DataTransferObject\User;

use App\DataTransferObject\Country;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateUser
{
    public string $iri;
    public string $profileIri;

    #[Assert\NotBlank(message: 'extranet.error.lastname')]
    public string $lastname;

    #[Assert\NotBlank(message: 'extranet.error.firstname')]
    public string $firstname;

    #[Assert\Length(max: 255, maxMessage: 'extranet.error.title')]
    public string $title;

    #[Assert\NotBlank(message: 'extranet.error.division')]
    public string $division;

    #[Assert\NotBlank(message: 'extranet.error.department')]
    public string $department;

    #[Assert\Length(max: 255, maxMessage: 'extranet.error.street')]
    public ?string $street = null;

    #[Assert\Length(max: 255, maxMessage: 'extranet.error.street2')]
    public ?string $street2 = null;

    #[Assert\Length(max: 50, maxMessage: 'extranet.error.city')]
    public ?string $city = null;

    #[Assert\Length(max: 20, maxMessage: 'extranet.error.postalCode')]
    public ?string $postalCode = null;

    #[Assert\Length(max: 50, maxMessage: 'extranet.error.state')]
    public ?string $state = null;

    #[PhoneNumber(message: 'extranet.error.reception')]
    public ?string $reception = null;

    #[PhoneNumber(message: 'extranet.error.phone')]
    public ?string $phone = null;

    #[PhoneNumber(message: 'extranet.error.mobile')]
    public ?string $mobile = null;

    #[PhoneNumber(message: 'extranet.error.fax')]
    public ?string $fax = null;

    #[Assert\NotNull(message: 'extranet.error.language')]
    public ?string $language = null;

    #[Assert\NotNull(message: 'extranet.error.country')]
    public ?Country $country = null;
}
