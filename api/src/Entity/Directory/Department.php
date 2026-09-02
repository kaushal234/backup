<?php

declare(strict_types=1);

namespace App\Entity\Directory;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\Directory\DepartmentController;
use App\Doctrine\Mapping\Attributes as App;
use App\Validator\Constraints\LockedValue;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Doctrine\Transformer\BooleanToChar;
use LegacyBundle\Entity\LegacyIdentifierTrait;
use LegacyBundle\Entity\LegacyIdInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A department in the company.
 */
#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['department', 'expose_legacy']]),
        new Post(security: "is_granted('FEATURE_DEPARTMENT_WRITE')"),
        new Get(),
        new Put(security: "is_granted('FEATURE_DEPARTMENT_WRITE')"),
        new Delete(controller: DepartmentController::class, security: "is_granted('FEATURE_DEPARTMENT_WRITE')"),
    ],
    normalizationContext: ['groups' => ['department_detail', 'expose_legacy']],
    denormalizationContext: ['groups' => ['department_write']],
)]
#[UniqueEntity(fields: ['name'])]
#[ORM\Table(name: 'directory_department')]
#[ApiFilter(OrderFilter::class, properties: ['name' => 'ASC'])]
#[ApiFilter(SearchFilter::class, properties: ['legacyId' => 'exact', 'name' => 'exact'])]
#[App\Loggable]
#[Legacy\Synchronize(table: 'tld_departments')]
#[LockedValue(value: Department::SPARE_PARTS, propertyPath: 'name')]
class Department implements \Stringable, LegacyIdInterface
{
    use LegacyIdentifierTrait;

    public const SPARE_PARTS = 'Spare Parts';

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['department', 'department_detail'])]
    private int $id;

    #[ApiProperty(iris: ['https://schema.org/text'])]
    #[ORM\Column(name: 'name', type: 'string', length: 100, unique: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    #[Groups(['department', 'department_detail', 'department_list', 'department_write', 'people_detail', 'user:me', 'training_attendee:reports', 'people:export', 'people:buyer', 'map_premise_people'])]
    #[Legacy\Column(column: 'dpt')]
    private string $name;

    #[ApiProperty(iris: ['https://schema.org/Boolean'])]
    #[ORM\Column(name: 'sso', type: 'boolean')]
    #[Assert\Type(type: 'boolean')]
    #[Groups(['department', 'department_detail', 'department_write', 'people:buyer'])]
    #[Legacy\Column(column: 'sso', transformer: BooleanToChar::class)]
    private bool $sso;

    #[ApiProperty(iris: ['https://schema.org/Boolean'])]
    #[ORM\Column(name: 'factory', type: 'boolean')]
    #[Assert\Type(type: 'boolean')]
    #[Groups(['department', 'department_detail', 'department_write', 'people:buyer'])]
    #[Legacy\Column(column: 'erp', transformer: BooleanToChar::class)]
    private bool $factory;

    public function __toString()
    {
        return $this->name;
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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSso()
    {
        return $this->sso;
    }

    /**
     * @param bool $sso
     *
     * @return $this
     */
    public function setSso($sso)
    {
        $this->sso = $sso;

        return $this;
    }

    /**
     * @return bool
     */
    public function isFactory()
    {
        return $this->factory;
    }

    /**
     * @param bool $factory
     *
     * @return $this
     */
    public function setFactory($factory)
    {
        $this->factory = $factory;

        return $this;
    }
}
