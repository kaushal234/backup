<?php

declare(strict_types=1);

namespace App\Entity\Parts;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\File\DeleteController;
use App\Controller\File\DownloadController;
use App\Controller\File\UploadController;
use App\Doctrine\Mapping\Attributes\Exclude;
use App\Doctrine\Mapping\Attributes\Loggable;
use App\Entity\Country;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[UniqueEntity(fields: ['country'])]
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['transportation_note', 'country_list']]),
        new Post(security: "is_granted('FEATURE_TRANSPORTATION_NOTE_WRITE')"),
        new Put(
            denormalizationContext: ['groups' => ['transportation_note:update']],
            security: "is_granted('FEATURE_TRANSPORTATION_NOTE_WRITE')",
        ),
        new Delete(
            uriTemplate: '/transportation_notes/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: TransportationNoteFile::class),
                'id' => new Link(fromClass: TransportationNote::class),
            ],
            defaults: ['parentProperty' => 'note', 'class' => TransportationNoteFile::class],
            controller: DeleteController::class,
            security: "is_granted('FEATURE_TRANSPORTATION_NOTE_WRITE')",
            name: 'delete_transportation_note_file',
        ),
        new Post(
            uriTemplate: '/transportation_notes/{id}/files',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            defaults: ['method' => 'getFiles', 'class' => TransportationNoteFile::class],
            controller: UploadController::class,
            security: "is_granted('FEATURE_TRANSPORTATION_NOTE_WRITE')",
            deserialize: false,
            name: 'upload_transportation_note_file'
        ),
        new Get(),
        new Get(
            uriTemplate: '/transportation_notes/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: TransportationNoteFile::class),
                'id' => new Link(fromClass: TransportationNote::class),
            ],
            defaults: ['parentProperty' => 'note', 'class' => TransportationNoteFile::class],
            controller: DownloadController::class,
            name: 'download_transportation_note_file',
        ),
    ],
    routePrefix: 'parts',
    normalizationContext: ['groups' => ['transportation_note:detail', 'country_list', 'file', 'people_public']],
    denormalizationContext: ['groups' => ['transportation_note:create']],
)]
#[ORM\Table(name: 'transportation_notes')]
#[ApiFilter(OrderFilter::class, properties: ['updatedAt'])]
#[Loggable]
class TransportationNote
{
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['transportation_note', 'transportation_note:detail'])]
    private int $id;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['transportation_note', 'transportation_note:detail'])]
    #[Exclude]
    #[Gedmo\Timestampable(on: 'update')]
    private \DateTimeInterface $updatedAt;

    #[ORM\OneToOne(targetEntity: 'App\Entity\Country')]
    #[Assert\NotNull]
    #[Groups(['transportation_note', 'transportation_note:detail', 'transportation_note:create'])]
    private ?Country $country = null;

    #[ORM\Column(type: 'text', length: 10000)]
    #[Assert\NotNull]
    #[Assert\Length(max: 10000)]
    #[Groups(['transportation_note', 'transportation_note:detail', 'transportation_note:create', 'transportation_note:update'])]
    private string $note;

    /**
     * @var Collection<TransportationNoteFile>
     */
    #[ORM\OneToMany(mappedBy: 'note', targetEntity: 'App\Entity\Parts\TransportationNoteFile', cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['transportation_note:detail'])]
    private Collection $files;

    public function __construct()
    {
        $this->files = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function getCountry(): Country
    {
        return $this->country;
    }

    public function setCountry(Country $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getNote(): string
    {
        return $this->note;
    }

    public function setNote(string $note): self
    {
        $this->note = $note;

        return $this;
    }

    /**
     * @return Collection<TransportationNoteFile>
     */
    public function getFiles()
    {
        return $this->files;
    }

    public function addFile(TransportationNoteFile $file): self
    {
        $this->files[] = $file;
        $file->setNote($this);

        return $this;
    }

    public function removeFile(TransportationNoteFile $file): self
    {
        $this->files->removeElement($file);

        return $this;
    }
}
