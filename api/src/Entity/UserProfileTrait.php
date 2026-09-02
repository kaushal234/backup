<?php

declare(strict_types=1);

namespace App\Entity;

use App\Doctrine\Mapping\Attributes\Exclude;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Doctrine\Transformer\ObjectToProperty;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

trait UserProfileTrait
{
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 255)]
    #[Assert\NotNull]
    #[Groups(['user_profile:detail', 'user_profile:write'])]
    #[Legacy\Column(column: 'title')]
    public ?string $jobTitle = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 255)]
    #[Assert\NotNull]
    #[Groups(['user_profile', 'user_profile:write'])]
    #[Legacy\Column(column: 'department')]
    public ?string $department = null;

    #[ORM\Column(type: 'string', length: 2, nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 2)]
    #[Groups(['user_profile:detail', 'user_profile:write', 'user_profile_language'])]
    #[Legacy\Column(column: 'lang')]
    public ?string $language = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Country')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['user_profile:detail', 'user_profile:write', 'extranet_user_address_campaign'])]
    #[Legacy\Column(column: 'country', transformer: ObjectToProperty::class, options: ['property' => 'name'])]
    public ?Country $country = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 2048)]
    #[Groups(['user_profile:detail', 'user_profile:write'])]
    #[Legacy\Column(column: 'note')]
    public ?string $note = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 255)]
    #[Groups(['user_profile:detail', 'user_profile:write', 'vendor_user'])]
    #[Legacy\Column(column: 'company_name')]
    public ?string $companyName = null;

    #[ORM\Column(name: 'counter', type: 'integer')]
    #[Groups(['user_profile:detail'])]
    #[Exclude]
    #[Legacy\Column(column: 'counter')]
    public int $counter = 0;

    #[ORM\Embedded(class: '\App\Entity\Address')]
    #[Assert\Valid]
    #[Legacy\Column(column: 'address', options: ['embeddedFields' => ['address.street1', 'address.street2', 'address.town', 'address.postalCode', 'address.city', 'address.state']])]
    public ?Address $address = null;

    #[ORM\Column(type: 'text', length: 65000, nullable: true)]
    #[Groups(['user_profile:detail', 'extranet_user_full_write'])]
    public ?string $legacyAddress = null;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['user_profile'])]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }

    public function incrementCounter()
    {
        ++$this->counter;
    }
}
