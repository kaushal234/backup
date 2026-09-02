<?php

declare(strict_types=1);

namespace LegacyBundle\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\LegacyBundle\Dto\EquipmentRecordFileOutput;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Controller\DownloadEquipmentRecordFileController;
use LegacyBundle\DataProvider\EquipmentRecordFileOutputProvider;

/**
 * Customer files attached to an equipment record (legacy "service" table).
 * Stored in the dedicated "service_files" table, unrelated to "mod_files":
 * every row here is a customer file (TLD files live in "mod_files" with module "ER").
 */
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Get(
            uriTemplate: '/equipment_record_files/{id}/download',
            controller: DownloadEquipmentRecordFileController::class,
            output: null,
            name: 'equipment_record_file_download',
            provider: ItemProvider::class
        ),
    ],
    routePrefix: '/legacy',
    output: EquipmentRecordFileOutput::class,
    security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_EXTRANET_USER')",
    provider: EquipmentRecordFileOutputProvider::class
)]
#[ApiFilter(SearchFilter::class, properties: ['parentId'])]
#[ORM\Entity(readOnly: true)]
#[ORM\Table(name: 'service_files', options: ['charset' => 'latin1'])]
#[ORM\Index(name: 'parent_id', columns: ['parent_id'])]
class EquipmentRecordFile
{
    #[ORM\Column(name: 'parent_id', type: 'integer', options: ['default' => 0])]
    public int $parentId = 0;

    #[ORM\Column(name: 'date', type: 'date', nullable: true)]
    public ?\DateTimeInterface $date = null;

    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    public ?string $description = null;

    #[ORM\Column(name: 'filename', type: 'string', length: 255, options: ['default' => ''])]
    public string $filename = '';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }
}
