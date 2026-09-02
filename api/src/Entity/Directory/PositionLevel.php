<?php

declare(strict_types=1);

namespace App\Entity\Directory;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Validator\Constraints\LockedValue;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A position level.
 */
#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
    ])]
#[ORM\Table(name: 'directory_position_level')]
#[ApiFilter(OrderFilter::class, properties: ['label' => 'ASC'])]
#[ApiFilter(SearchFilter::class, properties: ['legacyId' => 'exact'])]
#[LockedValue(value: self::EXECUTIVES, propertyPath: 'label')]
#[LockedValue(value: self::OTHER_EMPLOYEES, propertyPath: 'label')]
#[LockedValue(value: self::MANAGERS, propertyPath: 'label')]
#[LockedValue(value: self::ALVEST_STEERING_COMMITTEE, propertyPath: 'label')]
#[LockedValue(value: self::SUPERVISORS, propertyPath: 'label')]

class PositionLevel implements \Stringable
{
    final public const OTHER_EMPLOYEES = 'OTHER EMPLOYEES WITHOUT DIRECT REPORT';
    final public const MANAGERS = 'MANAGERS';
    final public const EXECUTIVES = 'EXECUTIVES';
    final public const SUPERVISORS = 'SUPERVISORS';
    final public const ALVEST_STEERING_COMMITTEE = 'ALVEST STEERING COMMITTEE';

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['position_level'])]
    private int $id;

    #[ApiProperty(iris: ['https://schema.org/text'])]
    #[ORM\Column(name: 'label', type: 'string', length: 60, unique: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\NotBlank]
    #[Assert\Length(max: 60)]
    #[Groups(['position_level', 'position_detail', 'people_detail', 'user:me'])]
    private string $label;

    public function __toString()
    {
        return $this->label;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     *
     * @return $this
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }
}
