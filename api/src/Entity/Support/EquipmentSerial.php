<?php

declare(strict_types=1);

namespace App\Entity\Support;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use ApiPlatform\Serializer\Filter\GroupFilter;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\Directory\People;
use App\Entity\EquipmentRecord;
use App\Filter\Support\EquipmentSerialSchematicsFilter;
use App\Repository\Support\EquipmentSerialRepository;
use App\Validator\Constraints\NotNullEquipmentSerialComponent;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Doctrine\Transformer\ObjectToProperty;
use LegacyBundle\Doctrine\Transformer\Utf8ToHtmlEntities;
use LegacyBundle\Entity\LegacyIdentifierTrait;
use LegacyBundle\Entity\LegacyIdInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[UniqueEntity(
    fields: ['equipmentRecord', 'component', 'model', 'serial', 'brand'],
    message: 'This combination of component, model, serial and brand already exist on this equipment record.',
    repositoryMethod: 'findBySkippingRemovedFromCollection',
    errorPath: 'serial',
    ignoreNull: false
)]
#[ORM\Entity(repositoryClass: EquipmentSerialRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(
            openapi: new Operation(
                parameters: [
                    new Parameter(
                        name: 'schematics',
                        in: 'query',
                        description: 'Filter only schematics serials',
                        required: true,
                        schema: ['type' => 'boolean'],
                    ),
                ],
            ),
            security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_EXTRANET_USER')",
        ),
        new Post(security: "is_granted('FEATURE_EQUIPMENT_SERIAL_ADMIN')"),
        new Get(),
        new Delete(security: "is_granted('FEATURE_EQUIPMENT_SERIAL_ADMIN')"),
    ],
    normalizationContext: ['groups' => EquipmentSerial::NORMALIZATION_GROUPS],
    denormalizationContext: ['groups' => ['equipment_serial:create']]
)]
#[ORM\Table(name: 'equipment_serials')]
#[ORM\Index(columns: ['serial'])]
#[ApiFilter(SearchFilter::class, properties: ['id', 'legacyId' => 'exact', 'equipmentRecord.serialNumber' => 'exact'])]
#[ApiFilter(EquipmentSerialSchematicsFilter::class)]
#[App\Loggable(owner: 'equipmentRecord', ownerRelation: 'serials')]
#[Legacy\Synchronize(table: 'service_serials')]
#[ApiFilter(GroupFilter::class, arguments: ['parameterName' => 'normalization_groups', 'overrideDefaultGroups' => false, 'whitelist' => ['signal_code']])]
class EquipmentSerial implements \Stringable, LegacyIdInterface
{
    use LegacyIdentifierTrait;

    /** @var string[] */
    final public const NORMALIZATION_GROUPS = ['expose_legacy', 'equipment_serial', 'people_public', 'component'];

    #[ORM\ManyToOne(targetEntity: 'App\Entity\EquipmentRecord', inversedBy: 'serials')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Legacy\Column(column: 'parent_id', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    #[Groups(['equipment_serial:create'])]
    public EquipmentRecord $equipmentRecord;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Support\Component')]
    #[NotNullEquipmentSerialComponent]
    #[Legacy\Column(column: 'component', transformer: ObjectToProperty::class, options: ['property' => 'name'])]
    #[Groups(['equipment_serial', 'equipment_serial:create', 'equipment_record:admin'])]
    public ?Component $component = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['equipment_serial', 'equipment_serial:create', 'equipment_record:admin'])]
    #[Legacy\Column(column: 'model', transformer: Utf8ToHtmlEntities::class)]
    public ?string $model = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['equipment_serial', 'equipment_serial:create', 'equipment_record:admin'])]
    #[Legacy\Column(column: 'serial', transformer: Utf8ToHtmlEntities::class)]
    public ?string $serial = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['equipment_serial', 'equipment_serial:create', 'equipment_record:admin'])]
    #[Legacy\Column(column: 'brand', transformer: Utf8ToHtmlEntities::class)]
    public ?string $brand = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['equipment_serial'])]
    #[Gedmo\Timestampable(on: 'create')]
    public ?\DateTime $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Gedmo\Timestampable(on: 'update')]
    public ?\DateTime $updatedAt = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'id')]
    #[Groups(['equipment_serial'])]
    #[Gedmo\Blameable(on: 'create')]
    public ?People $createdBy;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinColumn(name: 'updated_by', referencedColumnName: 'id')]
    #[Gedmo\Blameable(on: 'update')]
    public ?People $updatedBy = null;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['equipment_serial'])]
    private int $id;

    public function __toString()
    {
        return $this->serial ?? '';
    }

    public function getId(): int
    {
        return $this->id;
    }
}
