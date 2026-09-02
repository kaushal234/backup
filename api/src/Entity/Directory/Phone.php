<?php

declare(strict_types=1);

namespace App\Entity\Directory;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\Sales\ExtranetUser;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Misd\PhoneNumberBundle\Validator\Constraints as PhoneAssert;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An phone number.
 */
#[ORM\Entity]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
    ],
    normalizationContext: ['groups' => ['location']])]
#[App\Loggable(owner: 'people', ownerRelation: 'phones')]
class Phone implements \Stringable
{
    /**
     * @var string
     */
    final public const TYPE_RECEPTION = 'reception';
    /**
     * @var string
     */
    final public const TYPE_PHONE = 'phone';
    /**
     * @var string
     */
    final public const TYPE_MOBILE = 'mobile';
    /**
     * @var string
     */
    final public const TYPE_MOBILE_ALTERNATE = 'mobile_alternate';
    /**
     * @var string
     */
    final public const TYPE_FAX = 'fax';
    /**
     * @var string
     */
    final public const TYPE_HOME = 'home';

    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ApiProperty(iris: ['https://schema.org/text'])]
    #[ORM\Column(type: 'string', length: 25)]
    #[Assert\NotBlank]
    #[Assert\Type(type: 'string')]
    #[Assert\Choice(callback: 'getAvailableTypes')]
    #[Groups(['phone', 'people_detail', 'phone:write', 'people:export', 'people:buyer'])]
    private string $type;

    #[ApiProperty(iris: ['https://schema.org/telephone'])]
    #[ORM\Column(type: 'string', length: 60)]
    #[Assert\NotBlank]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 60)]
    #[Groups(['phone', 'people_detail', 'phone:write', 'people:export', 'people:buyer'])]
    #[PhoneAssert\PhoneNumber]
    private string $number;

    /**
     * @var Collection<People>
     */
    #[ORM\ManyToMany(targetEntity: 'App\Entity\Directory\People', mappedBy: 'phones')]
    #[Groups(['hidden'])]
    private Collection $people;

    /**
     * @var Collection<ExtranetUser>
     */
    #[ORM\ManyToMany(targetEntity: 'App\Entity\Sales\ExtranetUser', mappedBy: 'phones')]
    #[Groups(['hidden'])]
    private Collection $extranetUser;

    public function __construct()
    {
        $this->people = new ArrayCollection();
        $this->extranetUser = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->number;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param string $number
     *
     * @return $this
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    public static function getAvailableTypes()
    {
        return [
            static::TYPE_RECEPTION,
            static::TYPE_MOBILE,
            static::TYPE_MOBILE_ALTERNATE,
            static::TYPE_FAX,
            static::TYPE_PHONE,
            static::TYPE_HOME,
        ];
    }

    /**
     * @return Collection<People>
     */
    public function getPeople()
    {
        return $this->people;
    }

    /**
     * @return $this
     */
    public function addPeople(People $people)
    {
        $this->people->add($people);

        return $this;
    }

    /**
     * @return $this
     */
    public function removePeople(People $people)
    {
        $this->people->removeElement($people);

        return $this;
    }

    /**
     * @return Collection<ExtranetUser>
     */
    public function getExtranetUser()
    {
        return $this->extranetUser;
    }

    public function addExtranetUser(ExtranetUser $extranetUser): self
    {
        $this->extranetUser->add($extranetUser);

        return $this;
    }

    public function removeExtranetUser(ExtranetUser $extranetUser): self
    {
        $this->extranetUser->removeElement($extranetUser);

        return $this;
    }
}
